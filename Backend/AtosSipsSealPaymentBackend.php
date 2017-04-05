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
                'orderId',
                'amount',
                'automaticResponseUrl',
                'normalReturnUrl',
            ))
            ->setDefaults(array(
                'transactionReference' => null,
                'currencyCode'         => 'EUR',
                'captureDay'           => 0,
                'captureMode'          => 'AUTHOR_CAPTURE',
            ))
            ->setNormalizers(array(
                'transactionReference' => function(Options $options, $value) {
                    return sprintf('%s%s', date("mdHis"), $options['orderId']);
                },
                'currencyCode'         => function(Options $options, $value) {
                    return CurrencyCode::getNumericCode($value);
                },
            ))
            ->setAllowedTypes(array(
                'automaticResponseUrl' => array('string'),
                'normalReturnUrl'      => array('string'),
                'merchantId'           => array('string'),
                'amount'               => array('integer'),
                'orderId'              => array('string'),
                'transactionReference' => array('null'),
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
        $options['currencyCode']         = $options['currency_code'];
        $options['orderId']              = $options['order_id'];
        $options['automaticResponseUrl'] = $options['automatic_response_url'];
        $options['normalReturnUrl']      = $options['normal_return_url'];

        $availableOptionKeys = array(
            'merchantId',
            'orderId',
            'transactionReference',
            'amount',
            'automaticResponseUrl',
            'normalReturnUrl',
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

        $keyData = file_get_contents($this->getKeyPath($options['merchantId']));
        preg_match('/version=(?P<version>\d+)\nsecret=(?P<secret>\w+)/', $keyData, $matches);
        $options['keyVersion'] = $matches['version'];

        $build = implode('|', array_map(
            function ($k, $v) { return sprintf('%s=%s', $k, $v); },
            array_keys($options),
            $options
        ));

        return array(
            'options' => $options,
            'build'   => $build,
            'secret'  => $matches['secret']
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function buildPaymentForm($builtOptions)
    {
        $seal = hash('sha256', mb_convert_encoding($builtOptions['build'].$builtOptions['secret'], "UTF-8"));

        return $this->twig->render(
            'TmsPaymentBundle:Payment:atosSipsSeal.html.twig',
            array(
                'url'   => sprintf('https://%s/paymentInit', $this->getParameter('web_server')),
                'build' => $builtOptions['build'],
                'seal'  => $seal,
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
