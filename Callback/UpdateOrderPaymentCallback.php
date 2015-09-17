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
    protected function doExecute(array $order, Payment $payment, array $parameters = array())
    {
        if ('Q' != $order['processingState']) {
            throw new \RuntimeException(sprintf('The order %s must be at the state "Q" and not "%s"',
                $order['id'],
                $order['processingState']
            ));
        }

        if ($payment->getState() == Payment::STATE_NEW) {
            throw new \RuntimeException('The payment is still in the NEW state');
        }

        $patchOrder = array('payment' => $payment->toArray());

        if ($payment->getState() == Payment::STATE_APPROVED) {
            $patchOrder['processingState'] = 'N';
            $now = new \DateTime();
            $patchOrder['confirmedAt'] = $now->format(\DateTime::ISO8601);
        }

        $this
            ->crawler
            ->go('order')
            ->execute(
                sprintf('/orders/%s', $order['id']),
                'PATCH',
                $patchOrder
            )
        ;
    }
}