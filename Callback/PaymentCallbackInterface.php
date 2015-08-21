<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Callback;

use Tms\Bundle\PaymentBundle\Model\Payment;

interface PaymentCallbackInterface
{
    /**
     * Executed on callback
     *
     * @param Payment $payment    The payment.
     * @param array   $parameters The parameters to use.
     */
    public function execute(Payment $payment, array $parameters = array());
}