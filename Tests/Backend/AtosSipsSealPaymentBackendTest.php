<?php

namespace Tms\Bundle\PaymentBundle\Tests\Backend;

use Tms\Bundle\PaymentBundle\Backend\AtosSipsSealPaymentBackend;
use Tms\Bundle\PaymentBundle\Currency\CurrencyCode;
use Symfony\Component\HttpFoundation\Request;
use Tms\Bundle\PaymentBundle\Model\Payment;

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
        // Without 'Data' data
        $payment = new Payment();
        $request = Request::create(
            'tms-payment.test/payment/paybox/autoresponse',
            'POST'
        );

        try {
            $this->paymentBackend->doPayment($request, $payment);
            $this->fail("Expected exception not thrown");
        } catch(\Exception $e) {
            $this->assertEquals("The request not contains 'Data'", $e->getMessage());
        }

        // Valid payment
        $payment = new Payment();
        $request = Request::create(
            'tms-payment.test/payment/paybox/autoresponse',
            'POST',
            array(
                'Data'             => 'captureDay=0|captureMode=AUTHOR_CAPTURE|currencyCode=978|merchantId=002001000000001|orderChannel=INTERNET|responseCode=00|transactionDateTime=2017-05-17T17:54:40+02:00|transactionReference=0517174916591c70fa3ae0cae51b8b4594|keyVersion=1|acquirerResponseCode=00|amount=300|authorisationId=12345|guaranteeIndicator=N|cardCSCResultCode=4D|panExpiryDate=202202|paymentMeanBrand=VISA|paymentMeanType=CARD|customerIpAddress=81.255.219.33|maskedPan=4100##########00|orderId=591c70fa3ae0cae51b8b4594|holderAuthentRelegation=N|holderAuthentStatus=3D_ERROR|transactionOrigin=INTERNET|paymentPattern=ONE_SHOT',
                'Seal'             => '69c0c0bb5c60eaca38edc06f8c89ac6fb380ae476299e3f1e0e311bf30074f75',
                'InterfaceVersion' => 'HP_2.0'
            )
        );

        try {
            $isValid = $this->paymentBackend->doPayment($request, $payment);
        } catch(\Exception $e) {
            var_dump($e->getMessage()); die;
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
                'Data'             => 'captureDay=0|captureMode=AUTHOR_CAPTURE|currencyCode=978|merchantId=002001000000001|orderChannel=INTERNET|responseCode=17|transactionDateTime=2017-05-17T17:54:40+02:00|transactionReference=0517174916591c70fa3ae0cae51b8b4594|keyVersion=1|acquirerResponseCode=00|amount=300|authorisationId=12345|guaranteeIndicator=N|cardCSCResultCode=4D|panExpiryDate=202202|paymentMeanBrand=VISA|paymentMeanType=CARD|customerIpAddress=81.255.219.33|maskedPan=4100##########00|orderId=591c70fa3ae0cae51b8b4594|holderAuthentRelegation=N|holderAuthentStatus=3D_ERROR|transactionOrigin=INTERNET|paymentPattern=ONE_SHOT',
                'Seal'             => '4b30609b22864e075afe6c96165c5ac88659b20889c89514e65656230188424a',
                'InterfaceVersion' => 'HP_2.0'
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
                'Data'             => 'captureDay=0|captureMode=AUTHOR_CAPTURE|currencyCode=978|merchantId=002001000000001|orderChannel=INTERNET|responseCode=05|transactionDateTime=2017-05-17T17:54:40+02:00|transactionReference=0517174916591c70fa3ae0cae51b8b4594|keyVersion=1|acquirerResponseCode=00|amount=300|authorisationId=12345|guaranteeIndicator=N|cardCSCResultCode=4D|panExpiryDate=202202|paymentMeanBrand=VISA|paymentMeanType=CARD|customerIpAddress=81.255.219.33|maskedPan=4100##########00|orderId=591c70fa3ae0cae51b8b4594|holderAuthentRelegation=N|holderAuthentStatus=3D_ERROR|transactionOrigin=INTERNET|paymentPattern=ONE_SHOT',
                'Seal'             => 'e1e80de853f324d92d2a0c2141161dfddf79e6a884033c8f0c380dcebfd1a330',
                'InterfaceVersion' => 'HP_2.0'
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
