<?php

namespace Tms\Bundle\PaymentBundle\Controller\Payments;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Order Payment controller.
 *
 * @Route("/order/{order_id}/payment")
 */
class OrderPaymentController extends Controller
{
    /**
     * Auto response
     *
     * @Route("/{backend_alias}/autoresponse", name="tms_payment_order_autoresponse")
     * @Method({"GET", "POST"})
     */
    public function autoResponseAction(Request $request, $order_id, $backend_alias)
    {
        $requestData = $request->isMethod('POST') ? $request->request : $request->query;

        $logger = $this->container->get('logger');
        $logger->info(sprintf('Payment server "%s" response: %s',
            $backend_alias,
            json_encode($requestData->all())
        ));

        $paymentBackend = $this
            ->container
            ->get('tms_payment.backend_registry')
            ->getBackend($alias)
        ;
/*
        $crawler = $this
            ->container
            ->get('tms_rest_client.hypermedia.crawler')
        ;

        $order = $crawler
            ->go('order')
            ->findOne('/orders', $results['order_id'])
            ->getData()
        ;

        if ($order['processingState'] == 'N') {
            return new Response();
        }

        $patchOrder = array(
            'payment' => array_merge(
                isset($order['payment']) ? $order['payment'] : array(),
                array(
                    'type' => $name,
                    'raw'  => $results,
                )
            )
        );

        if ($paymentBackend->isValidPayment($results)) {
            $patchOrder['payment']['status'] = 'success';
            $patchOrder['processingState'] = 'N';
            $now = new \DateTime();
            $patchOrder['confirmedAt'] = $now->format(\DateTime::ISO8601);
        } else {
            $patchOrder['payment']['status'] = 'error';
        }

        $crawler
            ->go('order')
            ->execute(
                sprintf('/orders/%s', $results['order_id']),
                'PATCH',
                $patchOrder
            )
        ;
*/
        return new Response();
    }
}