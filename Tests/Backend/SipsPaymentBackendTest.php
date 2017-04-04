<?php

namespace Tms\Bundle\PaymentBundle\Tests\Backend;

use Tms\Bundle\PaymentBundle\Backend\SipsPaymentBackend;
use Tms\Bundle\PaymentBundle\Currency\CurrencyCode;

class SipsPaymentBackendTest extends \PHPUnit_Framework_TestCase
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
            'pathfile'          => __DIR__.'/../Resources/bin/sips/param/pathfile.test',
            'request_bin_path'  => __DIR__.'/../Resources/bin/sips/static/request',
            'response_bin_path' => __DIR__.'/../Resources/bin/sips/static/response'
        );

        $this->paymentBackend = new SipsPaymentBackend($parameters);
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
        $builtOptions = $this->paymentBackend->buildPaymentOptions(array(
            'merchant_id'            => '014213245611111',
            'merchant_country'       => 'fr',
            'order_id'               => 'order_id',
            'customer_email'         => 'customer@email.com',
            'amount'                 => 100,
            'currency_code'          => 'EUR',
            'bank_delays'            => 0,
            'automatic_response_url' => 'http://automatic_response_url',
            'cancel_return_url'      => 'http://cancel_return_url',
            'normal_return_url'      => 'http://normal_return_url',
        ));

        $this->assertEquals(
            'amount="100" automatic_response_url="http://automatic_response_url" cancel_return_url="http://cancel_return_url" capture_day="0" capture_mode="AUTHOR_CAPTURE" currency_code="978" customer_email="customer@email.com" merchant_country="fr" merchant_id="014213245611111" normal_return_url="http://normal_return_url" order_id="order_id" pathfile="/var/www/html/Tests/Backend/../Resources/bin/sips/param/pathfile.test"',
            $builtOptions
        );
    }

    /**
     * Test doPayement
     */
    public function testGetPaymentForm()
    {
        $htmlForm = $this->paymentBackend->getPaymentForm(array(
            'merchant_id'            => '014213245611111',
            'merchant_country'       => 'fr',
            'order_id'               => 'order_id',
            'amount'                 => 100,
            'currency_code'          => 'EUR',
            'bank_delays'            => 0,
            'customer_email'         => 'customer@email.com',
            'automatic_response_url' => 'http://automatic_response_url',
            'normal_return_url'      => 'http://normal_return_url',
            'cancel_return_url'      => 'http://cancel_return_url',
        ));

        $this->assertEquals(1, preg_match('/VALUE="(?P<value>\w+)"/', $htmlForm, $matches));
        $value = $matches['value'];

        $this->assertEquals(1, preg_match('/^[a-f0-9]*$/i', $value, $matches));
    }
}
