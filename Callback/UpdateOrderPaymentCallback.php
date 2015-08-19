<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Callback;

use Tms\Bundle\RestClientBundle\Hypermedia\Crawling\CrawlerInterface;
use Tms\Bundle\PaymentBundle\Model\Payment;

class UpdateOrderPaymentCallback implements PaymentCallbackInterface
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
    public function execute(Payment $payment, $parameters)
    {
        $order = $this
            ->crawler
            ->go('order')
            ->findOne('/orders', $parameters['order_id'])
            ->getData()
        ;

        if ('Q' != $order['processingState']) {
            throw new \RuntimeException(sprintf('The order %s must be at the state "Q" and not "%s"',
                $parameters['order_id'],
                $order['processingState']
            ));
        }

        $this->initPayment($payment, $order);
        $patchOrder = array('payment' => $payment->toArray());

        if ($payment->getState() == Payment::STATE_NEW) {
            throw new \RuntimeException(sprintf('The payment "%s" is still in the NEW state',
                $backend_alias
            ));
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