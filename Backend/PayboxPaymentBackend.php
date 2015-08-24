<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Backend;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Tms\Bundle\PaymentBundle\Model\Payment;
use Tms\Bundle\PaymentBundle\Currency\CurrencyCode;

class PayboxPaymentBackend extends AbstractPaymentBackend
{
    /**
     * The kernel
     *
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * Constructor
     *
     * @param KernelInterface $kernel The kernel.
     * @param Twig_Environment $twig The twig environment.
     */
    public function __construct(KernelInterface $kernel, \Twig_Environment $twig)
    {
        $this->kernel = $kernel;
        $this->twig = $twig;
    }

    /**
     * Returns paybox available server.
     * This is an ugly way to make a loadbalancing (client side !).
     *
     * @return string The available server host name.
     */
    protected function getAvailableServer()
    {
        $webServers = $this->getConfigurationParameter('web_servers');
        if (count($webServers ) == 1) {
            return $webServers[0];
        }

        foreach ($webServers as $server) {
            try {
                $doc = new \DOMDocument();
                $doc->loadHTMLFile(sprintf('https://%s/load.html', $server));
                $element = $doc->getElementById('server_status');
                if ($element && "OK" == $element->textContent) {
                    return $server;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // If no server available, return the first one by default (could be '#')
        return $webServers[0];
    }

    /**
     * Returns the paybox keypath
     *
     * @param string $site The paybox customer site id.
     *
     * @return string
     */
    public function getKeyPath($site)
    {
        $path = $this->getConfigurationParameter('keyspath');

        if (null === $path) {
            $path = sprintf('%s/../%s',
                $this->kernel->getRootDir(),
                'vendor/tms/payment-bundle/Tms/Bundle/PaymentBundle/Resources/bin/paybox/keys'
            );
        }

        return sprintf('%s/%s.bin', realpath($path), $site);
    }

    /**
     * Returns paybox PBX_RETOUR string.
     *
     * @return string
     */
    public function getPayboxReturnString()
    {
        //return 'total:M;orderid:R;auth:A;trans:T;error:E;sign:K';
        // Look at paybox documentation for PBX_RETOUR (p.43)
        $codeMap = array(
            'M' => 'amount',
            'R' => 'reference',
            'A' => 'authorisation_id',
            'P' => 'payment_type',
            'T' => 'call',
            'B' => 'subscription',
            'C' => 'card_type',
            'D' => 'card_validity',
            /*
            'N' => 'card_fnumber',
            'J' => 'card_lnumber',
            'O' => 'card_3dsecure',
            'F' => 'card_3dsecurestate',
            'H' => 'card_imprint',
            'Q' => 'transaction_time',
            'S' => 'transaction_number',
            'W' => 'transaction_processtime',
            'Z' => 'mixed_index',
            */
            'E' => 'error',
            'I' => 'country',
            'Y' => 'bank_country',
            'K' => 'hash',
        );

        return implode(';', array_map(
            function ($k, $v) { return sprintf('%s:%s', $v, $k); },
            array_keys($codeMap),
            $codeMap
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentForm(array $parameters)
    {
        // ISO-8601
        $dateTime = date("c");

        list($pbxSite, $pbxRang, $pbxIdentifiant) = explode('|', $parameters['merchant_id']);

        $pbxOptions = array(
            'PBX_SITE'         => $pbxSite,
            'PBX_RANG'         => $pbxRang,
            'PBX_IDENTIFIANT'  => $pbxIdentifiant,
            'PBX_TOTAL'        => $parameters['amount'],
            'PBX_DEVISE'       => CurrencyCode::getNumericCode($parameters['currency_code']),
            'PBX_CMD'          => $parameters['order_id'],
            'PBX_PORTEUR'      => $parameters['customer_email'],
            'PBX_REPONDRE_A'   => $parameters['automatic_response_url'],
            'PBX_RUF1'         => 'POST',
            'PBX_EFFECTUE'     => $parameters['return_url'],
            'PBX_REFUSE'       => $parameters['return_url'],
            'PBX_ANNULE'       => $parameters['return_url'],
            'PBX_ATTENTE'      => $parameters['return_url'],
            'PBX_RETOUR'       => $this->getPayboxReturnString(),
            'PBX_HASH'         => $parameters['hash_method'],
            'PBX_TIME'         => $dateTime,
            //'PBX_TYPECARTE'    => 'CARTE', // cf p52
            //'PBX_TYPEPAIEMENT' => 'CB', //cf p52
            //'PBX_ERRORCODETEST' => '999',
        );

        $msg = implode('&', array_map(
            function ($k, $v) { return sprintf('%s=%s', $k, $v); },
            array_keys($pbxOptions),
            $pbxOptions
        ));

        $binKey = file_get_contents($this->getKeyPath($pbxSite));
        $pbxOptions['PBX_HMAC'] = strtoupper(hash_hmac($parameters['hash_method'], $msg, $binKey));

        return $this->twig->render(
            'TmsPaymentBundle:Payment:paybox.html.twig',
            array(
                'url' => sprintf('https://%s/cgi/MYchoix_pagepaiement.cgi', $this->getAvailableServer()),
                'PBX' => $pbxOptions,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function doPayment(Request $request, Payment & $payment)
    {
        $requestData = $request->isMethod('POST') ? $request->request : $request->query;
        if (!$requestData->has('error')) {
            return false;
        }
        $raw  = $requestData->all();
        $code = $raw['error'];

        $payment
            ->setTransactionId(isset($raw['authorisation_id']) ? $raw['authorisation_id'] : null)
            ->setReferenceId($raw['reference'])
            ->setState(Payment::STATE_FAILED)
            ->setRaw($raw)
        ;

        // Look at paybox documentation for the PBX_RETOUR variable.
        if ('00001' === $code) {
            $payment->setState(Payment::STATE_CANCELED);
        } elseif ('00000' === $code) {
            $payment->setState(Payment::STATE_APPROVED);
        } elseif ('001' == substr($code, 0, 3)) {
            $payment->setState(Payment::STATE_FAILED);
        }

        return true;
    }
}