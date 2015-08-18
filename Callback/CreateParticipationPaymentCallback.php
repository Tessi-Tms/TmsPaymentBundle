<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Callback;

use Tms\Bundle\RestClientBundle\Hypermedia\Crawling\CrawlerInterface;
use Tms\Bundle\PaymentBundle\Model\Payment;

class CreateParticipationPaymentCallback implements PaymentCallbackInterface
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
        var_dump($parameters);die;
    }
}