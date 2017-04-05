<?php

namespace Tms\Bundle\PaymentBundle\Tests\Backend;

use Tms\Bundle\PaymentBundle\Backend\AtosSipsBinPaymentBackend;
use Tms\Bundle\PaymentBundle\Currency\CurrencyCode;

class AtosSipsBinPaymentBackendTest extends \PHPUnit_Framework_TestCase
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
            'pathfile'          => __DIR__.'/../Resources/sips/atos/bin/param/pathfile.test',
            'request_bin_path'  => __DIR__.'/../Resources/sips/atos/bin/static/request',
            'response_bin_path' => __DIR__.'/../Resources/sips/atos/bin/static/response'
        );

        $this->paymentBackend = new AtosSipsBinPaymentBackend($parameters);
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
        );

        $builtOptions = $this->paymentBackend->buildPaymentOptions($data);

        $this->assertEquals(
            sprintf(
                'amount="%d" automatic_response_url="%s" cancel_return_url="%s" capture_day="%d" capture_mode="%s" currency_code="%d" customer_email="%s" merchant_country="%s" merchant_id="%s" normal_return_url="%s" order_id="%s" pathfile="%s"',
                $data['amount'],
                $data['automatic_response_url'],
                $data['cancel_return_url'],
                0,
                'AUTHOR_CAPTURE',
                978,
                $data['customer_email'],
                $data['merchant_country'],
                $data['merchant_id'],
                $data['normal_return_url'],
                $data['order_id'],
                '/var/www/html/Tests/Backend/../Resources/sips/atos/bin/param/pathfile.test'
            ),
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
