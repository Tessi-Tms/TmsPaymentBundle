<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\StepBundle\Payment;

class BackendRegistry implements BackendRegistryInterface
{
    /**
     * @var BackendInterface[]
     */
    private $backends = array();

    /**
     * {@inheritdoc}
     */
    public function setBackend($alias, BackendInterface $backend)
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
        if (!isset($this->backends[$alias])) {
            return false;
        }

        return true;
    }
}