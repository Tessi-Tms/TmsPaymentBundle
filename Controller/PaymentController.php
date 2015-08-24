<?php

namespace Tms\Bundle\PaymentBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tms\Bundle\PaymentBundle\Model\Payment;

/**
 * Payment controller.
 *
 * @Route("/payment")
 */
class PaymentController extends Controller
{
    /**
     * Auto response
     *
     * @Route("/{backend_alias}/autoresponse", name="tms_payment_order_autoresponse")
     * @Method({"GET", "POST"})
     */
    public function autoResponseAction(Request $request, $backend_alias)
    {
        $requestData = $request->isMethod('POST') ? $request->request : $request->query;

        $paymentBackend = $this->container->get('tms_payment.backend_registry')
            ->getBackend($backend_alias)
        ;

        if (!$requestData->has('order_id')) {
            throw new HttpException(400, 'order_id parameter is missing');
        }
        $order_id = $requestData->get('order_id');

        if (!$requestData->has('callbacks')) {
            throw new HttpException(400, 'Callbacks parameter is missing');
        }

        $callbacks = is_array($requestData->get('callbacks')) ?
            $requestData->get('callbacks') :
            json_decode(base64_decode($requestData->get('callbacks')), true)
        ;

        $order = $this
            ->container
            ->get('tms_rest_client.hypermedia.crawler')
            ->go('order')
            ->findOne('/orders', $order_id)
            ->getData()
        ;

        if (empty($order['payment'])) {
            throw new \LogicException('The payment must exist in the order');
        }

        $payment = new Payment($order['payment']);
        $paymentBackend->doPayment($request, $payment);
        $response = new Response();

        foreach ($callbacks as $callback => $parameters) {
            if (null === $parameters) {
                $parameters = array();
            }

            try {
                $this
                    ->container
                    ->get('tms_payment.callback_registry')
                    ->getCallback($callback)
                    ->execute($order, $payment, $parameters)
                ;
            } catch (\Exception $e) {
                $this->container->get('logger')->error(
                    $e->getMessage(),
                    array(
                        'backend' => $backend_alias,
                        'request' => $request,
                    )
                );
                $response
                    ->setStatusCode(500, $e->getMessage())
                    ->setContent($e->getMessage())
                ;

                return $response;
            }
        }

        return $response;
    }
}