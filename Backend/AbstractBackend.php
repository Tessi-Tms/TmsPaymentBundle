<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\StepBundle\Payment\Backend;

abstract class AbstractPaymentBackend implements PaymentBackendInterface
{
    /**
     * Payment configuration
     *
     * @var array
     */
    protected $configuration;

    /**
     * Constructor
     *
     * @param array $configuration Payment configuration.
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration[$this->getName()];
    }
}