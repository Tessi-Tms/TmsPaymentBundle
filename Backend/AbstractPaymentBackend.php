<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Backend;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractPaymentBackend implements PaymentBackendInterface
{
    /**
     * @var array
     */
    protected $parameters;

    /**
     * Construtor
     *
     * @param array  $parameters
     */
    public function __construct($parameters)
    {
        $resolver = new OptionsResolver();
        $this->configureParameters($resolver);
        $this->parameters = $resolver->resolve($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Returns whether the given parameter key is defined.
     *
     * @param string $key The parameter key.
     *
     * @return bool Whether the parameter key is defined.
     */
    public function hasParameter($key)
    {
        return null !== $this->parameters && array_key_exists($key, $this->parameters);
    }

    /**
     * Returns needed parameter
     *
     * @param string $key The parameter key
     *
     * @return mixed The parameter's value.
     * @throw InvalidArgumentException
     */
    public function getParameter($key)
    {
        if (!$this->hasParameter($key)) {
            throw new \InvalidArgumentException(sprintf(
                'The payment backend parameter "%s" doesn\'t exist',
                $key
            ));
        }

        return $this->parameters[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentForm(array $options)
    {
        return $this->buildPaymentForm($this->buildPaymentOptions($options));
    }

    /**
     * {@inheritdoc}
     */
    public function checkUserRequestFromBank(Request $request)
    {
        return true;
    }

    /**
     * Build payment options.
     *
     * @param array $options
     *
     * @return mixed
     */
    public function buildPaymentOptions(array $options)
    {
        $this->preConfigureOptions($options);

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $resolvedOptions = $resolver->resolve($options);

        $this->postConfigureOptions($resolvedOptions);

        return $this->doBuildPaymentOptions($resolvedOptions);
    }

    /**
     * Pre configure options.
     *
     * @param array $options
     */
    protected function preConfigureOptions(array & $options)
    {
    }

    /**
     * Post configure options.
     *
     * @param array $options
     */
    protected function postConfigureOptions(array & $options)
    {
    }

    /**
     * Configure options.
     *
     * @param OptionsResolver $resolver
     */
    abstract protected function configureOptions(OptionsResolver $resolver);

    /**
     * Do build payment options.
     *
     * @param array $options
     *
     * @return mixed
     */
    abstract protected function doBuildPaymentOptions(array $options);

    /**
     * Configure parameters.
     *
     * @param OptionsResolver $resolver
     */
    abstract protected function configureParameters(OptionsResolver $resolver);

    /**
     * Build payment form.
     *
     * @param mixed $builtOptions
     *
     * @return string
     */
    abstract protected function buildPaymentForm($builtOptions);
}
