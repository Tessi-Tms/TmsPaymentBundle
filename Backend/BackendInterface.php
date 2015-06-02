<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\StepBundle\Payment\Backend;

use Symfony\Component\HttpFoundation\Request;

interface PaymentBackendInterface
{
    /**
     * Returns the backend name
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the payment results.
     *
     * @param Request $request The HTTP request returned by the bank.
     *
     * @return array|null
     */
    public function getPaymentResults(Request $request);

    /**
     * Returns the payment content.
     *
     * @param array $options The options.
     *
     * @return string
     */
    public function getPaymentContent(array $options);

    /**
     * Is a valid payment.
     *
     * @param array $results The bank return results.
     *
     * @return boolean
     */
    public function isValidPayment(array $results);
}