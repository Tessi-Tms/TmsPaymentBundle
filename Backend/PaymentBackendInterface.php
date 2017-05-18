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
     * @return boolean True if the payment is valid, false otherwise.
     */
    public function doPayment(Request $request, Payment & $payment);

    /**
     * Check the user request once back from the bank.
     *
     * @param Request $request The HTTP request.
     *
     * @return boolean True if the payment is valid, false otherwise.
     */
    public function checkUserRequestFromBank(Request $request);

    /**
     * Returns the HTML payment form.
     *
     * @param array $options The payment options.
     *
     * @return string
     */
    public function getPaymentForm(array $options);


}
