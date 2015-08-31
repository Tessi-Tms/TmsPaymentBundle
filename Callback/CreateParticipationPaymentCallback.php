<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Callback;


use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Tms\Bundle\PaymentBundle\Model\Payment;
use Tms\Bundle\RestClientBundle\Hypermedia\Crawling\CrawlerInterface;

class CreateParticipationPaymentCallback extends AbstractPaymentCallback
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
            ->setRequired(array('operation', 'offer'))
            ->setDefaults(array(
                'status'           => 'unknown',
                'processing_state' => 'N',
                'raw_controls'     => array(),
                'raw_eligibility'  => array(),
                'raw_benefit'      => array(),
                'search'           => array(),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecute(array $order, Payment $payment, array $parameters = array())
    {
        $participation = array(
            'source'           => $order['source'],
            'order'            => $order['id'],
            'operation'        => $parameters['operation'],
            'offer'            => $parameters['offer'],
            'customer'         => $order['customer'],
            'user'             => $order['user'],
            'status'           => $parameters['status'],
            'processing_state' => $parameters['processing_state'],
            'raw_source_data'  => $order['rawSourceData'],
            'raw_data'         => $order['rawData'],
            'raw_controls'     => json_encode($parameters['raw_controls'], JSON_UNESCAPED_UNICODE),
            'raw_eligibility'  => json_encode($parameters['raw_eligibility'], JSON_UNESCAPED_UNICODE),
            'raw_benefit'      => json_encode($parameters['raw_benefit'], JSON_UNESCAPED_UNICODE),
            'search'           => json_encode($parameters['search'], JSON_UNESCAPED_UNICODE),
        );

        $result = $this
            ->crawler
            ->go('participation')
            ->execute(
                '/participations',
                'POST',
                $participation
            )
        ;

        $this
            ->crawler
            ->go('order')
            ->execute(
                sprintf('/orders/%s', $order['id']),
                'PATCH',
                array('participation' => $result['id'])
            )
        ;
    }
}