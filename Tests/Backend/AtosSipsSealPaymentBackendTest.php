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
        $builtOptions = $this->paymentBackend->buildPaymentOptions(array(
            'merchantId'           => '002001000000001',
            'nationalityCountry'   => 'fr',
            'orderId'              => 'order_id',
            'customerEmail'        => 'customer@email.com',
            'amount'               => 100,
            'currencyCode'         => 'EUR',
            'bankDelays'           => 0,
            'automaticResponseUrl' => 'http://automatic_response_url',
            'cancelReturnUrl'      => 'http://cancel_return_url',
            'normalReturnUrl'      => 'http://normal_return_url',
        ));

        $this->assertEquals(
            'amount="100"|automaticResponseUrl="http://automatic_response_url"|cancelReturnUrl="http://cancel_return_url"|captureDay="0"|captureMode="AUTHOR_CAPTURE"|currencyCode="978"|customerEmail="customer@email.com"|nationalityCountry="fr"|merchantId="002001000000001"|normalReturnUrl="http://normal_return_url"|orderId="order_id"',
            $builtOptions
        );
    }
}
