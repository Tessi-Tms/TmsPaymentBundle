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
            ->setRequired(array('order_id', 'operation', 'offer',))
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
    protected function doExecute(Payment $payment, array $parameters = array())
    {
        $order = $this
            ->crawler
            ->go('order')
            ->findOne('/orders', $parameters['order_id'])
            ->getData()
        ;

        $parameters['source']          = $order['source'];
        $parameters['customer']        = $order['customer'];
        $parameters['user']            = $order['user'];
        $parameters['raw_source_data'] = $order['rawSourceData'];

        $participation = $this->buildParticipationData($parameters);

        var_dump($order, $participation); die;
        $result = $this->crawler
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
                sprintf('/orders/%s', $parameters['order_id']),
                'PATCH',
                array('participation' => $result['id'])
            )
        ;
    }

    /**
     * Build participation data
     *
     * @param array $parameters
     *
     * @return array
     */
    protected function buildParticipationData(array $parameters = array())
    {
        $data = array();
        /*
        foreach ($navigator->getFlow()->getData()->getAll() as $stepData) {
            if (null === $stepData['data']) {
                continue;
            }

            foreach ($stepData['data'] as $k => $v) {
                if ('__' !== substr($k, 0, 2)) {
                    if ($v instanceof \Tms\Bundle\MediaClientBundle\Model\Media) {
                        $stepData['data'][$k] = $v->getPublicData();
                    } elseif ($v instanceof \DateTime) {
                        $stepData['data'][$k] = $v->format(\DateTime::ISO8601);
                    }
                } else {
                    unset($stepData['data'][$k]);
                }
            }

            $data = array_merge_recursive($data, $stepData);
        }
        */

        return array(
            'source'           => $parameters['source'],
            'order'            => $parameters['order_id'],
            'operation'        => $parameters['operation'],
            'offer'            => $parameters['offer'],
            'customer'         => $parameters['customer'],
            'user'             => $parameters['user'],
            'status'           => $parameters['status'],
            'processing_state' => $parameters['processing_state'],
            'raw_source_data'  => $parameters['raw_source_data'],
            'raw_data'         => json_encode($data, JSON_UNESCAPED_UNICODE),
            'raw_controls'     => json_encode($parameters['raw_controls'], JSON_UNESCAPED_UNICODE),
            'raw_eligibility'  => json_encode($parameters['raw_eligibility'], JSON_UNESCAPED_UNICODE),
            'raw_benefit'      => json_encode($parameters['raw_benefit'], JSON_UNESCAPED_UNICODE),
            'search'           => json_encode($parameters['search'], JSON_UNESCAPED_UNICODE),
        );
    }
}