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
            'keyspath'    => '',
            'web_servers' => '',
        );

        $this->paymentBackend = new PayboxPaymentBackend(
            $parameters,
            $this->createMock("\Twig_Environment")
        );
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
    }
}
