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
     * Returns payment backend parameters
     *
     * @return array
     */
    public function getParameters();

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
     * @param array $options The payment options.
     *
     * @return string
     */
    public function getPaymentForm(array $options);
}
