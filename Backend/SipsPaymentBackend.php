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

class SipsPaymentBackend extends AbstractPaymentBackend
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
                'pathfile'      => $this->getParameter('pathfile'),
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
                'pathfile'               => array('string'),
                'capture_day'            => array('integer'),
            ))
            ->setAllowedValues(array(
                'currency_code' => CurrencyCode::getAlphabeticCodes(),
                'capture_mode'  => array('AUTHOR_CAPTURE', 'VALIDATION'),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function preConfigureOptions(array & $options)
    {
        $options['capture_day'] = $options['bank_delays'];
        unset($options['bank_delays']);
    }

    /**
     * {@inheritdoc}
     */
    public function doBuildPaymentOptions(array $options)
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
    public function buildPaymentForm($builtOptions)
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
            return false;
        }

        $shellOptions = array(
            'pathfile' => $this->getPathFile(),
            'message'  => $request->request->get('DATA'),
        );

        $args = implode(' ', array_map(
            function ($k, $v) { return sprintf('%s=%s', $k, $v); },
            array_keys($shellOptions),
            $shellOptions
        ));

        $process = new Process(sprintf('%s %s',
            $this->getResponseBinPath(),
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

        // Look at sogenactif documentation for the '17' response code return value.
        if ($raw['response_code'] == '17') {
            $payment->setState(Payment::STATE_CANCELED);
        } elseif ('0' === $raw['code'] && '00' === $raw['response_code']) {
            $payment->setState(Payment::STATE_APPROVED);
        }

        return true;
    }
}
