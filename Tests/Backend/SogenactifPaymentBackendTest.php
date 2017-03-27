<?php

namespace Tms\Bundle\PaymentBundle\Tests\Backend;

use Tms\Bundle\PaymentBundle\Backend\SogenactifPaymentBackend;

class SogenactifPaymentBackendTest extends \PHPUnit_Framework_TestCase
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
            'pathfile'        => __DIR__.'/../Resources/bin/sips/param/pathfile.test',
            'request_bin_path'  => __DIR__.'/../Resources/bin/sips/static/request',
            'response_bin_path' => __DIR__.'/../Resources/bin/sips/static/response'
        );

        $this->paymentBackend = new SogenactifPaymentBackend($parameters);
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
     * Test doPayement
     */
    public function testGetPaymentForm()
    {
        /**
        * Returns the HTML payment form.
        *
        * @param array $parameters The payment parameters.
        *
        * @return string
        public function getPaymentForm(array $parameters);
        */
        $htmlForm = $this->paymentBackend->getPaymentForm(array(
            'automatic_response_url' => 'http://automatic_response_url',
            'normal_return_url'      => 'http://normal_return_url',
            'cancel_return_url'      => 'http://cancel_return_url',
            'merchant_id'            => '014213245611111',
            'merchant_country'       => 'fr',
            'amount'                 => 100,
            'currency_code'          => 'EUR',
            'order_id'               => 'order_id',
            'customer_email'         => 'customer@email.com',
            'bank_delays'            => 0,
        ));

        print($htmlForm);
    }
}
