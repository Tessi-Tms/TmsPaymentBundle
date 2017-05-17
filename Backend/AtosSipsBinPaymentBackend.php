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

class AtosSipsBinPaymentBackend extends AbstractPaymentBackend
{
    /**
     * {@inheritdoc}
     */
    protected function configureParameters(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array('pathfile', 'request_bin_path', 'response_bin_path'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array(
                'merchant_id',
                'merchant_country',
                'order_id',
                'customer_email',
                'amount',
                'automatic_response_url',
                'normal_return_url',
                'cancel_return_url',
            ))
            ->setDefaults(array(
                'currency_code' => 'EUR',
                'capture_day'   => 0,
                'capture_mode'  => 'AUTHOR_CAPTURE',
            ))
            ->setNormalizers(array(
                'currency_code' => function(Options $options, $value) {
                    return CurrencyCode::getNumericCode($value);
                },
            ))
            ->setAllowedTypes(array(
                'automatic_response_url' => array('string'),
                'normal_return_url'      => array('string'),
                'cancel_return_url'      => array('string'),
                'merchant_id'            => array('string'),
                'merchant_country'       => array('string'),
                'amount'                 => array('integer'),
                'order_id'               => array('string'),
                'customer_email'         => array('string'),
                'capture_day'            => array('integer'),
            ))
            ->setAllowedValues(array(
                //'currency_code' => CurrencyCode::getAlphabeticCodes(),
                'capture_mode'  => array('AUTHOR_CAPTURE', 'VALIDATION'),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function preConfigureOptions(array & $options)
    {
        if (isset($options['bank_delays'])) {
            $options['capture_day'] = $options['bank_delays'];
        }

        $availableOptionKeys = array(
            'merchant_id',
            'merchant_country',
            'order_id',
            'customer_email',
            'amount',
            'automatic_response_url',
            'normal_return_url',
            'cancel_return_url',
            'currency_code',
            'capture_day',
            'capture_mode'
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
    protected function postConfigureOptions(array & $options)
    {
        $options['pathfile'] = $this->getParameter('pathfile');
    }

    /**
     * {@inheritdoc}
     */
    protected function doBuildPaymentOptions(array $options)
    {
        ksort($options);

        return implode(' ', array_map(
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
        $process = new Process(sprintf('%s %s',
            $this->getParameter('request_bin_path'),
            $builtOptions
        ));
        $process->run();

        list($_, $code, $error, $message) = explode("!", $process->getOutput());
        if ('0' !== $code) {
            return $error;
        }

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function doPayment(Request $request, Payment & $payment)
    {
        if (!$request->request->has('DATA')) {
            throw new \Exception("The request not contains 'DATA'");
        }

        $shellOptions = array(
            'pathfile' => $this->getParameter('pathfile'),
            'message'  => $request->request->get('DATA'),
        );

        $args = implode(' ', array_map(
            function ($k, $v) { return sprintf('%s=%s', $k, $v); },
            array_keys($shellOptions),
            $shellOptions
        ));

        $process = new Process(sprintf('%s %s',
            $this->getParameter('response_bin_path'),
            $args
        ));
        $process->run();

        $keys = array(
            '_',
            'code',
            'error',
            'merchant_id',
            'merchant_country',
            'amount',
            'transaction_id',
            'payment_means',
            'transmission_date',
            'payment_time',
            'payment_date',
            'response_code',
            'payment_certificate',
            'authorisation_id',
            'currency_code',
            'card_number',
            'cvv_flag',
            'cvv_response_code',
            'bank_response_code',
            'complementary_code',
            'complementary_info',
            'return_context',
            'caddie',
            'receipt_complement',
            'merchant_language',
            'language',
            'customer_id',
            'order_id',
            'customer_email',
            'customer_ip_address',
            'capture_day',
            'capture_mode',
            'data',
            'order_validity',
            'transaction_condition',
            'statement_reference',
            'card_validity',
            'score_value',
            'score_color',
            'score_info',
            'score_thershold',
            'score_profile',
            '_',
            '_',
            '_',
        );

        $raw = array_combine($keys, explode("!", $process->getOutput()));
        unset($raw['_']);

        // TODO: check amount !

        $payment
            ->setTransactionId($raw['transaction_id'])
            ->setReferenceId($raw['order_id'])
            ->setState(Payment::STATE_FAILED)
            ->setRaw($raw)
        ;

        // Look at documentation for the '17' response code return value.
        if ('0' === $raw['code'] && '00' === $raw['response_code']) {
            $payment->setState(Payment::STATE_APPROVED);

            return true;
        }

        if ('17' == $raw['response_code']) {
            $payment->setState(Payment::STATE_CANCELED);

        }

        return false;
    }
}
