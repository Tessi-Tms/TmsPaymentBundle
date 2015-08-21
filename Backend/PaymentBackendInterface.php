<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Backend;

use Symfony\Component\HttpFoundation\Request;

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
     * Returns the payment.
     *
     * @param Request $request The HTTP request returned by the bank.
     *
     * @return Tms\Bundle\PaymentBundle\Model\Payment
     */
    public function getPayment(Request $request);

    /**
     * Returns the HTML payment form.
     *
     * @param array $parameters The payment parameters.
     *
     * @return string
     */
    public function getPaymentForm(array $parameters);
}