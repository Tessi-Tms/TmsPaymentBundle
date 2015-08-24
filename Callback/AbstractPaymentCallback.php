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
    protected function setDefaultParameters(OptionsResolverInterface $resolver)
    {
        return;
    }


    /**
     * {@inheritdoc}
     */
    public function execute(array $order, Payment $payment, array $parameters = array())
    {
        $resolver = new OptionsResolver();
        $this->setDefaultParameters($resolver);

        return $this->doExecute($order, $payment, $resolver->resolve($parameters));
    }

    /**
     * Do execute action.
     *
     * @param array   $order      The order.
     * @param Payment $payment    The payment.
     * @param array   $parameters The resolved parameters.
     */
    abstract protected function doExecute(array $order, Payment $payment, array $parameters = array());
}