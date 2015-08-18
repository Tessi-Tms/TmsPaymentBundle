<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Callback;

use Tms\Bundle\PaymentBundle\Exception\UnexpectedTypeException;

class PaymentCallbackRegistry
{
    /**
     * @var PaymentCallbackInterface[]
     */
    private $callbacks = array();

    /**
     * {@inheritdoc}
     */
    public function setCallback($alias, PaymentCallbackInterface $callback)
    {
        $this->callbacks[$alias] = $callback;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCallback($alias)
    {
        if (!is_string($alias)) {
            throw new UnexpectedTypeException($alias, 'string');
        }

        if (!isset($this->callbacks[$alias])) {
            throw new \InvalidArgumentException(sprintf('Could not load payment callback "%s"', $alias));
        }

        return $this->callbacks[$alias];
    }

    /**
     * {@inheritdoc}
     */
    public function hasCallback($alias)
    {
        if (!isset($this->callbacks[$alias])) {
            return true;
        }

        return false;
    }
}