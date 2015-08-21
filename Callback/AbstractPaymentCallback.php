<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Callback;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tms\Bundle\PaymentBundle\Model\Payment;

abstract class AbstractPaymentCallback implements PaymentCallbackInterface
{
    /**
     * Set default parameters.
     *
     * @param OptionsResolverInterface $resolver
     */
    abstract protected function setDefaultParameters(OptionsResolverInterface $resolver);

    /**
     * Do execute action.
     *
     * @param Payment $payment    The payment.
     * @param array   $parameters The resolved parameters.
     */
    abstract protected function doExecute(Payment $payment, array $parameters = array());

    /**
     * {@inheritdoc}
     */
    public function execute(Payment $payment, array $parameters = array())
    {
        $resolver = new OptionsResolver();
        $this->setDefaultParameters($resolver);

        return $this->doExecute($payment, $resolver->resolve($parameters));
    }
}