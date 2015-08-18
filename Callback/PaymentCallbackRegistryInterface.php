<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Callback;

interface PaymentCallbackRegistryInterface
{
    /**
     * Sets a callback identify by alias.
     *
     * @param string                   $alias    The callback alias.
     * @param PaymentCallbackInterface $callback The callback.
     *
     * @return PaymentCallbackRegistryInterface
     */
    public function setCallback($alias, PaymentCallbackInterface $callback);

    /**
     * Returns a callback by alias.
     *
     * @param string $alias The alias of the callback.
     *
     * @return PaymentCallbackInterface The callback
     *
     * @throws Exception\UnexpectedTypeException  if the passed alias is not a string.
     * @throws Exception\InvalidArgumentException if the callback can not be retrieved.
     */
    public function getCallback($alias);

    /**
     * Returns whether the given callback is supported.
     *
     * @param string $alias The alias of the callback.
     *
     * @return bool Whether the callback is supported.
     */
    public function hasCallback($alias);
}