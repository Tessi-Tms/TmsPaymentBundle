<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Backend;

use Symfony\Component\Process\Process;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Tms\Bundle\PaymentBundle\Model\Payment;
use Tms\Bundle\PaymentBundle\Currency\CurrencyCode;

class SogenactifPaymentBackend extends SipsPaymentBackend
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultPathfilePath()
    {
        return '@TmsPaymentBundle/Resources/bin/sips/param/pathfile.sogenactif';
    }
}