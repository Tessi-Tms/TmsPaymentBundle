<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\StepBundle\Payment\Backend;

use Symfony\Component\HttpFoundation\Request;

class PayboxPaymentBackend extends AbstractPaymentBackend
{
    const PAYMENT_STATE_DONE    = 'done';
    const PAYMENT_STATE_FAIL    = 'fail';
    const PAYMENT_STATE_CANCEL  = 'cancel';
    const PAYMENT_STATE_PENDING = 'pending';

    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * Constructor
     *
     * @param array            $configuration Payment configuration.
     * @param Twig_Environment $twig          The twig environment.
     */
    public function __construct(array $configuration, \Twig_Environment $twig)
    {
        parent::__construct($configuration);
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
        $webServers = $this->configuration['parameters']['web_servers'];
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'paybox';
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentContent(array $options)
    {
        // ISO-8601
        $dateTime = date("c");

        list($pbxSite, $pbxRang, $pbxIdentifiant) = explode('|', $options['merchant_id']);

        $pbxOptions = array(
            'PBX_SITE'         => $pbxSite,
            'PBX_RANG'         => $pbxRang,
            'PBX_IDENTIFIANT'  => $pbxIdentifiant,
            'PBX_TOTAL'        => $options['amount'],
            'PBX_DEVISE'       => $options['currency_code'],
            'PBX_CMD'          => $options['order_id'],
            'PBX_PORTEUR'      => 'test@tessi.fr',
            'PBX_REPONDRE_A'   => $options['automatic_response_url'],
            'PBX_RUF1'         => 'POST',
            'PBX_EFFECTUE'     => sprintf('%s?state=%s',
                $options['return_url'],
                self::PAYMENT_STATE_DONE
            ),
            'PBX_REFUSE'       => sprintf('%s?state=%s',
                $options['return_url'],
                self::PAYMENT_STATE_FAIL
            ),
            'PBX_ANNULE'       => sprintf('%s?state=%s',
                $options['return_url'],
                self::PAYMENT_STATE_CANCEL
            ),
            'PBX_ATTENTE'      => sprintf('%s?state=%s',
                $options['return_url'],
                self::PAYMENT_STATE_PENDING
            ),
            'PBX_RETOUR'       => 'amount:M;reference:R;authorisation_id:A;payment_type:P;error:E', // cf p43
            'PBX_HASH'         => $options['hash_method'],
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

        $binKey = pack("H*", $this->configuration['parameters']['key']);
        $pbxOptions['PBX_HMAC'] = strtoupper(hash_hmac($options['hash_method'], $msg, $binKey));

        return $this->twig->render(
            'TmsStepBundle:Payment:paybox.html.twig',
            array(
                'url' => sprintf('https://%s/cgi/MYchoix_pagepaiement.cgi', $this->getAvailableServer()),
                'PBX' => $pbxOptions,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPaymentResults(Request $request)
    {
        if (!$request->request->has('state')) {
            return null;
        }

        switch ($request->request->has('state')) {
            case self::PAYMENT_STATE_DONE:
                die('PAYMENT DONE');
                break;
            case self::PAYMENT_STATE_FAIL:
                die('PAYMENT FAIL');
                break;
            case self::PAYMENT_STATE_CANCEL:
                die('PAYMENT CANCEL');
                break;
            case self::PAYMENT_STATE_PENDING:
                die('PAYMENT PENDING');
                break;
            default:
                die('PAYMENT WTF');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isValidPayment(array $results)
    {
        var_dump('isValidPayment');
        die('Paybox valid payment todo');

        return true;
    }
}