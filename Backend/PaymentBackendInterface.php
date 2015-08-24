<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Backend;

use Symfony\Component\HttpFoundation\Request;
use Tms\Bundle\PaymentBundle\Model\Payment;

interface PaymentBackendInterface
{
    /**
     * Set payment backend name
     *
     * @param string $name
     *
     * @return PaymentBackendInterface
     */
    public function setName($name);

    /**
     * Returns payment backend name
     *
     * @return string
     */
    public function getName();

    /**
     * Set payment backend configuration parameters
     *
     * @param array $parameters
     *
     * @return PaymentBackendInterface
     */
    public function setConfigurationParameters(array $parameters);

    /**
     * Returns payment backend configuration parameters
     *
     * @return array
     */
    public function getConfigurationParameters();

    /**
     * Do the payment process
     *
     * @param Request $request The HTTP request.
     * @param Payment $payment The payment.
     *
     * @return boolean
     */
    public function doPayment(Request $request, Payment & $payment);

    /**
     * Returns the HTML payment form.
     *
     * @param array $parameters The payment parameters.
     *
     * @return string
     */
    public function getPaymentForm(array $parameters);
}