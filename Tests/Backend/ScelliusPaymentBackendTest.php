<?php

namespace Tms\Bundle\PaymentBundle\Tests\Backend;

use Tms\Bundle\PaymentBundle\Backend\ScelliusPaymentBackend;
use Tms\Bundle\PaymentBundle\Currency\CurrencyCode;

class ScelliusPaymentBackendTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @PaymentBackendInterface
     */
    private $paymentBackend;

    /**
     * Returns a mock for the abstract kernel.
     *
     * @param array $methods Additional methods to mock (besides the abstract ones)
     * @param array $bundles Bundles to register
     *
     * @return Kernel
     */
    protected function getKernel(array $methods = array(), array $bundles = array())
    {
        /*
        $methods[] = 'registerBundles';
        $kernel = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Kernel')
            ->setMethods($methods)
            ->setConstructorArgs(array('test', false))
            ->getMockForAbstractClass()
        ;
        $kernel->expects($this->any())
            ->method('registerBundles')
            ->will($this->returnValue($bundles))
        ;
        $p = new \ReflectionProperty($kernel, 'rootDir');
        $p->setAccessible(true);
        $p->setValue($kernel, __DIR__.'/Fixtures');

        return $kernel;
        */
    }

    /**
     * Returns a mock for the BundleInterface.
     *
     * @return BundleInterface
     */
    protected function getBundle($dir = null, $parent = null, $className = null, $bundleName = null)
    {
        /*
        $bundle = $this
            ->getMockBuilder('Symfony\Component\HttpKernel\Bundle\BundleInterface')
            ->setMethods(array('getPath', 'getParent', 'getName'))
            ->disableOriginalConstructor()
        ;
        if ($className) {
            $bundle->setMockClassName($className);
        }
        $bundle = $bundle->getMockForAbstractClass();
        $bundle
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(null === $bundleName ? get_class($bundle) : $bundleName))
        ;
        $bundle
            ->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue($dir))
        ;
        $bundle
            ->expects($this->any())
            ->method('getParent')
            ->will($this->returnValue($parent))
        ;

        return $bundle;
        */
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        /*
        $kernel = $this->getKernel(array('getBundle'));
        $kernel
            ->expects($this->any())
            ->method('getBundle')
            ->will($this->returnValue(array(
                $this->getBundle(__DIR__.'/..',  null, 'TmsPaymentBundle', 'TmsPaymentBundle')
            )))
        ;

        $this->paymentBackend = new ScelliusPaymentBackend($kernel);
        $this->paymentBackend->setConfigurationParameters(array(
            'pathfile' => '@TmsPaymentBundle/Resources/bin/sips/param/pathfile.test')
        );
        */

        $parameters = array(
            'pathfile'          => __DIR__.'/../Resources/bin/sips/param/pathfile.test',
            'request_bin_path'  => __DIR__.'/../Resources/bin/sips/static/request',
            'response_bin_path' => __DIR__.'/../Resources/bin/sips/static/response'
        );

        $this->paymentBackend = new ScelliusPaymentBackend($parameters);
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
