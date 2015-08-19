<?php

namespace Tms\Bundle\PaymentBundle\Controller\Payments;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

        if (!$requestData->has('callbacks')) {
            throw new HttpException(400, 'Callbacks parameter is missing');
        }

        $callbacks = is_array($requestData->get('callbacks')) ?
            $requestData->get('callbacks') :
            json_decode(base64_decode($requestData->get('callbacks')), true)
        ;

        $payment = $paymentBackend->getPayment($request);

        $response = new Response();

        foreach ($callbacks as $callback => $parameters) {
            try {
                $this->container->get('tms_payment.callback_registry')
                    ->getCallback($callback)
                    ->execute($payment, $parameters)
                ;
            } catch (\Exception $e) {
                $this->container->get('logger')->error($e->getMessage());
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