<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Backend;

class PaymentBackendRegistry implements PaymentBackendRegistryInterface
{
    /**
     * @var BackendInterface[]
     */
    private $backends = array();

    /**
     * {@inheritdoc}
     */
    public function setBackend($alias, PaymentBackendInterface $backend)
    {
        $this->backends[$alias] = $backend;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBackend($alias)
    {
        if (!is_string($alias)) {
            throw new UnexpectedTypeException($alias, 'string');
        }
        if (!$this->hasBackend($alias)) {
            throw new \InvalidArgumentException(sprintf('Could not load payment backend "%s"', $alias));
        }

        return $this->backends[$alias];
    }

    /**
     * {@inheritdoc}
     */
    public function hasBackend($alias)
    {
        return isset($this->backends[$alias]);
    }
}
