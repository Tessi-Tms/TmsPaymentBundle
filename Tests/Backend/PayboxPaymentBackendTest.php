<?php

namespace Tms\Bundle\PaymentBundle\Tests\Backend;

use Tms\Bundle\PaymentBundle\Backend\PayboxPaymentBackend;

class PayboxPaymentBackendTest extends \PHPUnit_Framework_TestCase
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
            'keys_path'   => __DIR__.'/../Resources/sips/paybox/keys',
            'web_servers' => array('preprod-tpeweb.paybox.com'),
        );

        $loader = new \Twig_Loader_Filesystem(__DIR__.'/../../Resources/views');

        $this->paymentBackend = new PayboxPaymentBackend($parameters, new \Twig_Environment($loader));
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
            'merchant_id'            => '1999888|32|110647233',
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

        $this->assertEquals('POST', $builtOptions['PBX']['PBX_RUF1']);
        $this->assertEquals(
            'amount:M;reference:R;authorisation_id:A;payment_type:P;call:T;subscription:B;card_type:C;card_validity:D;error:E;country:I;bank_country:Y;hash:K',
            $builtOptions['PBX']['PBX_RETOUR']
        );
        $this->assertEquals(date('c'), $builtOptions['PBX']['PBX_TIME']);
        $this->assertEquals('CARTE', $builtOptions['PBX']['PBX_TYPEPAIEMENT']);
        $this->assertEquals('CB', $builtOptions['PBX']['PBX_TYPECARTE']);
        $this->assertEquals('sha512', $builtOptions['PBX']['PBX_HASH']);
        $this->assertEquals('1999888', $builtOptions['PBX']['PBX_SITE']);
        $this->assertEquals('32', $builtOptions['PBX']['PBX_RANG']);
        $this->assertEquals('110647233', $builtOptions['PBX']['PBX_IDENTIFIANT']);
        $this->assertEquals(100, $builtOptions['PBX']['PBX_TOTAL']);
        $this->assertEquals('978', $builtOptions['PBX']['PBX_DEVISE']);
        $this->assertEquals('customer@email.com', $builtOptions['PBX']['PBX_PORTEUR']);
        $this->assertEquals('http://automatic_response_url', $builtOptions['PBX']['PBX_REPONDRE_A']);
        $this->assertEquals('http://normal_return_url', $builtOptions['PBX']['PBX_EFFECTUE']);
        $this->assertEquals('http://cancel_return_url', $builtOptions['PBX']['PBX_REFUSE']);
        $this->assertEquals('http://cancel_return_url', $builtOptions['PBX']['PBX_ANNULE']);
        $this->assertEquals('http://normal_return_url', $builtOptions['PBX']['PBX_ATTENTE']);
        $this->assertEquals('00', $builtOptions['PBX']['PBX_DIFF']);
    }
}
