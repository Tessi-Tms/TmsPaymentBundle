<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Backend;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Request;
use Tms\Bundle\PaymentBundle\Model\Payment;
use Tms\Bundle\PaymentBundle\Currency\CurrencyCode;

class AtosSipsSealPaymentBackend extends AbstractPaymentBackend
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * Constructor
     *
     * @param array $parameters
     * @param Twig_Environment $twig The twig environment.
     */
    public function __construct(array $parameters, \Twig_Environment $twig)
    {
        parent::__construct($parameters);

        $this->twig = $twig;
    }

    /**
     * Returns the seal path
     *
     * @param string $merchantId The merchant id.
     *
     * @return string
     */
    protected function getKeyPath($merchantId)
    {
        return sprintf('%s/%s.txt', $this->getParameter('keys_path'), $merchantId);
    }


    /**
     * {@inheritdoc}
     */
    protected function configureParameters(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array('keys_path', 'web_server'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array(
                'merchantId',
                'nationalityCountry',
                'orderId',
                'customerEmail',
                'amount',
                'automaticResponseUrl',
                'normalReturnUrl',
                'cancelReturnUrl',
            ))
            ->setDefaults(array(
                'currencyCode' => 'EUR',
                'captureDay'   => 0,
                'captureMode'  => 'AUTHOR_CAPTURE',
            ))
            ->setNormalizers(array(
                'currencyCode' => function(Options $options, $value) {
                    return CurrencyCode::getNumericCode($value);
                },
            ))
            ->setAllowedTypes(array(
                'automaticResponseUrl' => array('string'),
                'normalReturnUrl'      => array('string'),
                'cancelReturnUrl'      => array('string'),
                'merchantId'           => array('string'),
                'nationalityCountry'   => array('string'),
                'amount'               => array('integer'),
                'orderId'              => array('string'),
                'customerEmail'        => array('string'),
                'captureDay'           => array('integer'),
            ))
            ->setAllowedValues(array(
                'currencyCode' => CurrencyCode::getAlphabeticCodes(),
                'captureMode'  => array('AUTHOR_CAPTURE', 'VALIDATION'),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function preConfigureOptions(array & $options)
    {
        if (isset($options['bank_delays'])) {
            $options['captureDay'] = $options['bank_delays'];
        }

        if (isset($options['capture_mode'])) {
            $options['captureMode'] = $options['capture_mode'];
        }

        $options['merchantId']           = $options['merchant_id'];
        $options['nationalityCountry']   = $options['merchant_country'];
        $options['currencyCode']         = $options['currency_code'];
        $options['orderId']              = $options['order_id'];
        $options['customerEmail']        = $options['customer_email'];
        $options['automaticResponseUrl'] = $options['automatic_response_url'];
        $options['normalReturnUrl']      = $options['normal_return_url'];
        $options['cancelReturnUrl']      = $options['cancel_return_url'];

        $availableOptionKeys = array(
            'merchantId',
            'nationalityCountry',
            'orderId',
            'customerEmail',
            'amount',
            'automaticResponseUrl',
            'normalReturnUrl',
            'cancelReturnUrl',
            'currencyCode',
            'captureDay',
            'captureMode'
        );

        foreach ($options as $key => $value) {
            if (!in_array($key, $availableOptionKeys)) {
                unset($options[$key]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doBuildPaymentOptions(array $options)
    {
        ksort($options);

        return implode('|', array_map(
            function ($k, $v) { return sprintf('%s="%s"', $k, $v); },
            array_keys($options),
            $options
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function buildPaymentForm($builtOptions)
    {
        $secretKey = '002001000000001_KEY1';
        $seal = hash('sha256', mb_convert_encoding($builtOptions.$secretKey, "UTF-8"));

        return $this->twig->render(
            'TmsPaymentBundle:Payment:atosSipsSeal.html.twig',
            array(
                'web_server' => $this->getParameter('web_server'),
                'data'       => $builtOptions,
                'seal'       => $seal,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function doPayment(Request $request, Payment & $payment)
    {
    }
}
