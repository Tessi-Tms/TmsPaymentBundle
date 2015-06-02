<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\StepBundle\Payment\Backend;

use Symfony\Component\Process\Process;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Request;

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
     * @param array           $configuration Payment configuration.
     * @param KernelInterface $kernel        The kernel.
     */
    public function __construct(array $configuration, KernelInterface $kernel)
    {
        parent::__construct($configuration);
        $this->kernel = $kernel;
    }

    /**
     * Returns the sogenactif pathfile
     *
     * @return string
     */
    protected function getPathFile()
    {
        $pathfile = $this->configuration['parameters']['pathfile'];

        if (null === $pathfile) {
            $pathfile = $this->kernel->locateResource(
                '@TmsStepBundle/bin/sogenactif/param/pathfile'
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
             '@TmsStepBundle/bin/sogenactif/static/request'
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
             '@TmsStepBundle/bin/sogenactif/static/response'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sogenactif';
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentContent(array $options)
    {
        $shellOptions = array(
            'pathfile'               => $this->getPathFile(),
            'automatic_response_url' => $options['automatic_response_url'],
            'normal_return_url'      => $options['return_url'],
            'cancel_return_url'      => $options['return_url'],
            'merchant_id'            => $options['merchant_id'],
            'merchant_country'       => $options['merchant_country'],
            'amount'                 => $options['amount'],
            'currency_code'          => $options['currency_code'],
            'order_id'               => $options['order_id'],
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
        if (0 !== $code) {
            return $error;
        }

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentResults(Request $request)
    {
        if (!$request->request->has('DATA')) {
            return null;
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

        $results = array_combine($keys, explode("!", $process->getOutput()));
        unset($results['_']);

        // Look at sogenactif documentation for the '17' response code return value.
        if ($results['response_code'] == '17') {
            return null;
        }

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidPayment(array $results)
    {
        if ('0' === $results['code'] && '00' === $results['response_code']) {
            return true;
        }

        return false;
    }
}