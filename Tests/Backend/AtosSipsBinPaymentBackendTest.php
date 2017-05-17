<?php

namespace Tms\Bundle\PaymentBundle\Tests\Backend;

use Tms\Bundle\PaymentBundle\Backend\AtosSipsBinPaymentBackend;
use Tms\Bundle\PaymentBundle\Currency\CurrencyCode;
use Symfony\Component\HttpFoundation\Request;
use Tms\Bundle\PaymentBundle\Model\Payment;

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
        // Without 'DATA' data
        $payment = new Payment();
        $request = Request::create(
            'tms-payment.test/payment/paybox/autoresponse',
            'POST'
        );

        try {
            $this->paymentBackend->doPayment($request, $payment);
            $this->fail("Expected exception not thrown");
        } catch(\Exception $e) {
            $this->assertEquals("The request not contains 'DATA'", $e->getMessage());
        }

        // Valid payment
        $payment = new Payment();
        $request = Request::create(
            'tms-payment.test/payment/paybox/autoresponse',
            'POST',
            array(
                'DATA' => '2020333933603028502c2360532e332c532d2360522d4360502c4360502c3334502c3330522c332c522d2334562c3324512c33242a2c2360532c2360502d2328502d33602a2c2360552c2360502d4324562c4344542c5048512c2334502c232450333425333524353230542532316048502c2338502c2324542c4360512d3324512c2334512d3328592d232c2a2c3360532c2360502d4324562c5340512e3048512c2330502c2360582c4360512d3324512c23342a2c3360512c2360502c4360505c224324502c4360502c3329442c462d4239233454385628502d43442a2c3360502c2360502d4324562c5340512e3048502c4340502c2360523947282a2c2328592c2360502c4639525c224360502e2360502c232c592d53402a2c3360562c2360502d532c512c43284e2c23602a2c3324542c2360502d4328502c3338502c3048502c2344502c2360523947282a2c232c502c2360512d56594f2b3729453c265159302731453c572d492b4639525c224360512d4360502c232d333454502a2c2360572c2360502c4360525c224360532e2360502c4330552d432d422d532d412c532d413933214338332c572c362858384330552d433c2a2c2330502c2360512d2425353524412f34455d2330352134353529255c224360532e3360502c2324505c224324502e3360502c2328502c6048512c3360502c2360512c3048512c3324502c2360522c23602a2c3328532c2360502c33602a87be2023ae20137d'
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
                'DATA' => '2020333539603028502c2360532d3344532d2360522d2360502c4360502c3334502c3330512d2324562d5334592c3324512c33242a2c2360532c2360502c5338592e3048502c2334502c2360562c333c532c2360575c224324502d3360502c23313632352d215c224360502d4360502c3330522c2324572c2334512d5324552c5360502d5048512c232c502c2360562c333c532c232c525c224324502d2360502c2340522c2324572c2334512d5048512c2324502c2360522c333c2a2c2328582c2360502c4639525c224360522e3360502c2329463c4048502c2340502c2360532e333c585c224324502d4360502c233c502c2360502b4324575c224324512d2360502c2338522c2324592c23302a2c2360592c2360502c4639525c224360532c2360502c4331483d372d533936454e2b46354c385641413b2651603d2635533c56444e3947282a2c2324562c2360502c552d33336048502c233c502c2360522c23282a2c232c582c2360522d2334592c362c5638533c552c5625452c262d412c536051393341422d2334592c5048502d2360502c2324543035353432245d3237542d21342531353444342a2c232c592c2360502c33382a2c3360592c2360502c44592f5c224324512c2360502c23252e5c224324512c3360502c2328512d5048512c432c502c2360512c6048607d0ba835c67ee9f6'
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
                'DATA' => '2020333539603028502c2360532d3344532d2360522d2360502c4360502c3334502c3330512d2324562d5334592c3324512c33242a2c2360532c2360502c5338592e3048502c2334502c2360562c333c532c2334515c224324502d3360502c23313632352d215c224360502d4360502c3330522c2324572c2334512d5324552c5360552c3048512c232c502c2360562c333c532c3328555c224324502d2360502c2340522c2324572c2334512d5048512c2324502c2360522c23342a2c2328582c2360502c4639525c224360522e3360502c2329463c4048502c2340502c2360532e333c585c224324502d4360502c233c502c2360502b4360555c224324512d2360502c2338522c2328502c23302a2c2360592c2360502c4639525c224360532c2360502c4331483d372d533936454e2b46354c385641413b2651603d2635533c56444e3947282a2c2324562c2360502c552d33336048502c233c502c2360522c23282a2c232c582c2360522d2334592c362c5638533c552c5625452c262d412c536051393341422d2334592c5048502d2360502c2324543035353432245d3237542d21342531353444342a2c232c592c2360502c33382a2c3360592c2360502c44592f5c224324512c2360502c23252e5c224324512c3360502c2328502d3048512c432c502c2360512c6048603b41f508944d7f7f'
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
}
