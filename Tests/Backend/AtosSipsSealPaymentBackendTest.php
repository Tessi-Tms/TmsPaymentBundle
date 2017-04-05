<?php

namespace Tms\Bundle\PaymentBundle\Tests\Backend;

use Tms\Bundle\PaymentBundle\Backend\AtosSipsSealPaymentBackend;
use Tms\Bundle\PaymentBundle\Currency\CurrencyCode;

class AtosSipsSealPaymentBackendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @PaymentBackendInterface
     */
    private $paymentBackend;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $parameters = array(
            'keys_path' => __DIR__.'/../Resources/sips/atos/seal/keys',
            'web_server' => array('preprod-tpeweb.paybox.com'),
        );

        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../Resources/views');

        $this->paymentBackend = new AtosSipsSealPaymentBackend($parameters, new \Twig_Environment($loader));
    }

    /**
     * Test doPayement
     */
    public function testDoPayment()
    {
        /**
        * Do the payment process
        *
        * @param Request $request The HTTP request.
        * @param Payment $payment The payment.
        *
        * @return boolean
        public function doPayment(Request $request, Payment & $payment);
        */
    }

    /**
     * Test buildPaymentOptions
     */
    public function testBuildPaymentOptions()
    {
        $data = array(
            'merchant_id'            => '002001000000001',
            'order_id'               => 'order_id',
            'amount'                 => 100,
            'currency_code'          => 'EUR',
            'bank_delays'            => 0,
            'automatic_response_url' => 'http://automatic_response_url',
            'normal_return_url'      => 'http://normal_return_url',
        );

        $builtOptions = $this->paymentBackend->buildPaymentOptions($data);

        $this->assertEquals(
            sprintf(
                'amount=%s|automaticResponseUrl=%s|captureDay=%d|captureMode=%s|currencyCode=%d|merchantId=%s|normalReturnUrl=%s|orderId=%s|transactionReference=%s|keyVersion=%d',
                $data['amount'],
                $data['automatic_response_url'],
                0,
                'AUTHOR_CAPTURE',
                978,
                $data['merchant_id'],
                $data['normal_return_url'],
                $data['order_id'],
                date('mdHis').$data['order_id'],
                1
            ),
            $builtOptions['build']
        );
    }
}
