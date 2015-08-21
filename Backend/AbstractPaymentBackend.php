<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Backend;

use Symfony\Component\HttpFoundation\Request;
use Tms\Bundle\PaymentBundle\Model\Payment;

abstract class AbstractPaymentBackend implements PaymentBackendInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setConfigurationParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns whether the given configuration parameter key is defined.
     *
     * @param string $key The configuration parameter key.
     *
     * @return bool Whether the configuration parameter key is defined.
     */
    public function hasConfigurationParameter($key)
    {
        return array_key_exists($key, $this->parameters);
    }

    /**
     * Returns needed configuration parameter
     *
     * @param string $key The configuration parameter key
     *
     * @return mixed The configuration parameter's value.
     * @throw InvalidArgumentException
     */
    public function getConfigurationParameter($key)
    {
        if (!$this->hasConfigurationParameter($key)) {
            throw new \InvalidArgumentException(sprintf(
                'The %s payment backend configuration parameter "%s" doesn\'t exist',
                $this->getName(),
                $key
            ));
        }

        return $this->parameters[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getPayment(Request $request)
    {
        $payment = new Payment();
        $payment->setBackendAlias($this->getName());

        return $this->updatePayment($payment, $request);
    }

    /**
     * Update payment
     *
     * @param Payment $payment The payment.
     * @param Request $request The HTTP request.
     *
     * @return Payment
     */
    abstract protected function updatePayment(Payment $payment, Request $request);
}