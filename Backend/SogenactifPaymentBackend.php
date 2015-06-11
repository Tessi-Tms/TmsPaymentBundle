<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Backend;

use Symfony\Component\Process\Process;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Tms\Bundle\PaymentBundle\Model\Payment;
use Tms\Bundle\PaymentBundle\Currency\CurrencyCode;

class SogenactifPaymentBackend extends AbstractPaymentBackend
{
    /**
     * The kernel
     *
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * Constructor
     *
     * @param KernelInterface $kernel The kernel.
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * Returns the sogenactif pathfile
     *
     * @return string
     */
    public function getPathFile()
    {
        $pathfile = $this->getConfigurationParameter('pathfile');

        if (null === $pathfile) {
            $pathfile = $this->kernel->locateResource(
                '@TmsPaymentBundle/Resources/bin/sogenactif/param/pathfile'
            );
        }

        return $pathfile;
    }

    /**
     * Returns the request bin path
     *
     * @return string
     */
    protected function getRequestBinPath()
    {
        return $this->kernel->locateResource(
             '@TmsPaymentBundle/Resources/bin/sogenactif/static/request'
        );
    }

    /**
     * Returns the response bin path
     *
     * @return string
     */
    protected function getResponseBinPath()
    {
        return $this->kernel->locateResource(
             '@TmsPaymentBundle/Resources/bin/sogenactif/static/response'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentForm(array $parameters)
    {
        $shellOptions = array(
            'pathfile'               => $this->getPathFile(),
            'automatic_response_url' => $parameters['automatic_response_url'],
            'normal_return_url'      => $parameters['return_url'],
            'cancel_return_url'      => $parameters['return_url'],
            'merchant_id'            => $parameters['merchant_id'],
            'merchant_country'       => $parameters['merchant_country'],
            'amount'                 => $parameters['amount'],
            'currency_code'          => CurrencyCode::getNumericCode($parameters['currency_code']),
            'order_id'               => $parameters['order_id'],
        );

        $args = implode(' ', array_map(
            function ($k, $v) { return sprintf('%s=%s', $k, $v); },
            array_keys($shellOptions),
            $shellOptions
        ));

        $process = new Process(sprintf('%s %s',
            $this->getRequestBinPath(),
            $args
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
    protected function updatePayment(Payment $payment, Request $request)
    {
        if (!$request->request->has('DATA')) {
            return $payment;
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

        return $payment;
    }
}