<?php

namespace Tms\Bundle\PaymentBundle\Controller\Payments;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tms\Bundle\PaymentBundle\Model\Payment;

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

        $paymentBackend = $this->container->get('tms_payment.backend_registry')
            ->getBackend($backend_alias)
        ;

        $crawler = $this->container->get('tms_rest_client.hypermedia.crawler');
        $order = $crawler->go('order')->findOne('/orders', $order_id)->getData();

        if ('Q' != $order['processingState']) {
            $logger->error(sprintf('The order %s must be at the state "Q" and not "%s"',
                $order_id,
                $order['processingState']
            ));

            return new Response();
        }

        $payment = $paymentBackend->getPayment($request, $order['payment']);
        $patchOrder = array('payment' => $payment->toArray());

        if ($payment->getState() == Payment::STATE_APPROVED) {
            $patchOrder['processingState'] = 'N';
            $now = new \DateTime();
            $patchOrder['confirmedAt'] = $now->format(\DateTime::ISO8601);
        }

        $crawler
            ->go('order')
            ->execute(
                sprintf('/orders/%s', $order_id),
                'PATCH',
                $patchOrder
            )
        ;

        return new Response();
    }
}