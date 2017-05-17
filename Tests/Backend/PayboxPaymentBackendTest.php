<?php

namespace Tms\Bundle\PaymentBundle\Tests\Backend;

use Tms\Bundle\PaymentBundle\Backend\PayboxPaymentBackend;
use Symfony\Component\HttpFoundation\Request;
use Tms\Bundle\PaymentBundle\Model\Payment;

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
        // Without error data
        $payment = new Payment();
        $request = Request::create(
            'tms-payment.test/payment/paybox/autoresponse',
            'POST'
        );

        try {
            $this->paymentBackend->doPayment($request, $payment);
            $this->fail("Expected exception not thrown");
        } catch(\Exception $e) {
            $this->assertEquals("The request not contains 'error'", $e->getMessage());
        }

        // Valid payment
        $payment = new Payment();
        $request = Request::create(
            'tms-payment.test/payment/paybox/autoresponse',
            'POST',
            array(
                'error'     => '00000',
                'reference' => 'dummy',
            )
        );

        try {
            $isValid = $this->paymentBackend->doPayment($request, $payment);
        } catch(\Exception $e) {
            $this->fail("Exception not expected thrown");
        }
        $this->assertTrue($isValid);
        $this->assertEquals($payment->getState(), Payment::STATE_APPROVED);


        // Canceled payment
        $payment = new Payment();
        $request = Request::create(
            'tms-payment.test/payment/paybox/autoresponse',
            'POST',
            array(
                'error'     => '00001',
                'reference' => 'dummy',
            )
        );

        try {
            $isValid = $this->paymentBackend->doPayment($request, $payment);
        } catch(\Exception $e) {
            $this->fail("Exception not expected thrown");
        }
        $this->assertFalse($isValid);
        $this->assertEquals($payment->getState(), Payment::STATE_CANCELED);


        // Failed payment
        $request = Request::create(
            'tms-payment.test/payment/paybox/autoresponse',
            'POST',
            array(
                'error'     => '001',
                'reference' => 'dummy',
            )
        );

        try {
            $isValid = $this->paymentBackend->doPayment($request, $payment);
        } catch(\Exception $e) {
            $this->fail("Exception not expected thrown");
        }
        $this->assertFalse($isValid);
        $this->assertEquals($payment->getState(), Payment::STATE_FAILED);
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

        $this->assertEquals('POST', $builtOptions['options']['PBX_RUF1']);
        $this->assertEquals(
            'amount:M;reference:R;authorisation_id:A;payment_type:P;call:T;subscription:B;card_type:C;card_validity:D;error:E;country:I;bank_country:Y;hash:K',
            $builtOptions['options']['PBX_RETOUR']
        );
        $this->assertEquals(date('c'), $builtOptions['options']['PBX_TIME']);
        $this->assertEquals('CARTE', $builtOptions['options']['PBX_TYPEPAIEMENT']);
        $this->assertEquals('CB', $builtOptions['options']['PBX_TYPECARTE']);
        $this->assertEquals('sha512', $builtOptions['options']['PBX_HASH']);
        $this->assertEquals('1999888', $builtOptions['options']['PBX_SITE']);
        $this->assertEquals('32', $builtOptions['options']['PBX_RANG']);
        $this->assertEquals('110647233', $builtOptions['options']['PBX_IDENTIFIANT']);
        $this->assertEquals(100, $builtOptions['options']['PBX_TOTAL']);
        $this->assertEquals('978', $builtOptions['options']['PBX_DEVISE']);
        $this->assertEquals('customer@email.com', $builtOptions['options']['PBX_PORTEUR']);
        $this->assertEquals('http://automatic_response_url', $builtOptions['options']['PBX_REPONDRE_A']);
        $this->assertEquals('http://normal_return_url', $builtOptions['options']['PBX_EFFECTUE']);
        $this->assertEquals('http://cancel_return_url', $builtOptions['options']['PBX_REFUSE']);
        $this->assertEquals('http://cancel_return_url', $builtOptions['options']['PBX_ANNULE']);
        $this->assertEquals('http://normal_return_url', $builtOptions['options']['PBX_ATTENTE']);
        $this->assertEquals('00', $builtOptions['options']['PBX_DIFF']);
    }
}
