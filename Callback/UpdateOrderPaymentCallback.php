<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Callback;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tms\Bundle\PaymentBundle\Model\Payment;
use Tms\Bundle\RestClientBundle\Hypermedia\Crawling\CrawlerInterface;

class UpdateOrderPaymentCallback extends AbstractPaymentCallback
{
    /**
     * @var CrawlerInterface
     */
    private $crawler;

    /**
     * The constructor
     *
     * @param CrawlerInterface $crawler;
     */
    public function __construct(CrawlerInterface $crawler)
    {
        $this->crawler = $crawler;
    }

    /**
     * {@inheritdoc}
     */
    protected function setDefaultParameters(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array('order_id'))
            ->setAllowedTypes(array(
                'order_id' => array('string')
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(Payment $payment, array $parameters = array())
    {
        $order = $this
            ->crawler
            ->go('order')
            ->findOne('/orders', $parameters['order_id'])
            ->getData()
        ;

        if ('Q' != $order['processingState']) {
            //
            return true;
            throw new \RuntimeException(sprintf('The order %s must be at the state "Q" and not "%s"',
                $parameters['order_id'],
                $order['processingState']
            ));
        }

        $this->initPayment($payment, $order);
        // $payment->setState(Payment::STATE_APPROVED);
        $patchOrder = array('payment' => $payment->toArray());

        if ($payment->getState() == Payment::STATE_NEW) {
            throw new \RuntimeException('The payment is still in the NEW state');
        }

        if ($payment->getState() == Payment::STATE_APPROVED) {
            $patchOrder['processingState'] = 'N';
            $now = new \DateTime();
            $patchOrder['confirmedAt'] = $now->format(\DateTime::ISO8601);
        }

        $this
            ->crawler
            ->go('order')
            ->execute(
                sprintf('/orders/%s', $parameters['order_id']),
                'PATCH',
                $patchOrder
            )
        ;
    }

    /**
     * Init payment
     *
     * @param Payment $payment
     * @param array   $order
     */
    protected function initPayment(Payment & $payment, array $order = null)
    {
        $rc = new \ReflectionClass($payment);
        foreach ($order['payment'] as $k => $v) {
            $setter = sprintf('set%s', ucfirst($k));
            if ($rc->hasMethod($setter)) {
                call_user_func(array($payment, $setter), $v);
            }
        }
    }
}