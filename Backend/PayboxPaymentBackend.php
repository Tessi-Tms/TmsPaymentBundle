<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Backend;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Tms\Bundle\PaymentBundle\Model\Payment;
use Tms\Bundle\PaymentBundle\Currency\CurrencyCode;

class PayboxPaymentBackend extends AbstractPaymentBackend
{
    /**
     * @var Twig_Environment
     */
    private $twig;

    /**
     * Constructor
     *
     * @param array $parameters
     * @param Twig_Environment $twig The twig environment.
     */
    public function __construct(array $parameters, \Twig_Environment $twig)
    {
        parent::__construct($parameters);

        $this->twig = $twig;
    }

    /**
     * Returns the paybox keypath
     *
     * @param string $site The paybox customer site id.
     *
     * @return string
     */
    protected function getKeyPath($site)
    {
        return sprintf('%s/%s.bin', $this->getParameter('keyspath'), $site);
    }

    /**
     * Returns paybox available server.
     * This is an ugly way to make a loadbalancing (client side !).
     *
     * @return string The available server host name.
     */
    protected function getAvailableServer()
    {
        $webServers = $this->getParameter('web_servers');
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
    protected function configureParameters(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array('keyspath', 'web_servers'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setRequired(array(
                'PBX_SITE',
                'PBX_RANG',
                'PBX_IDENTIFIANT',
                'PBX_TOTAL',
                'PBX_DEVISE',
                'PBX_CMD',
                'PBX_PORTEUR',
                'PBX_REPONDRE_A',
                'PBX_RUF1',
                'PBX_EFFECTUE',
                'PBX_REFUSE',
                'PBX_ANNULE',
                'PBX_ATTENTE',
                'PBX_RETOUR',
                'PBX_HASH',
                'PBX_TIME',
                'PBX_DIFF',
            ))
            ->setOptionals(array(
                'PBX_TYPEPAIEMENT',
                'PBX_TYPECARTE',
                'PBX_ERRORCODETEST',
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function doBuildPaymentOptions(array $options)
    {
        $msg = implode('&', array_map(
            function ($k, $v) { return sprintf('%s=%s', $k, $v); },
            array_keys($options),
            $options
        ));

        $binKey = file_get_contents($this->getKeyPath($options['PBX_SITE']));
        $options['PBX_HMAC'] = strtoupper(hash_hmac($options['PBX_HASH'], $msg, $binKey));

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    public function buildPaymentForm($builtOptions)
    {
        return $this->twig->render(
            'TmsPaymentBundle:Payment:paybox.html.twig',
            array(
                'url' => sprintf('https://%s/cgi/MYchoix_pagepaiement.cgi', $this->getAvailableServer()),
                'PBX' => $builtOptions,
            )
        );
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
            'PBX_EFFECTUE'     => $parameters['normal_return_url'],
            'PBX_REFUSE'       => $parameters['cancel_return_url'],
            'PBX_ANNULE'       => $parameters['cancel_return_url'],
            'PBX_ATTENTE'      => $parameters['normal_return_url'],
            'PBX_RETOUR'       => $this->getPayboxReturnString(),
            'PBX_HASH'         => $parameters['hash_method'],
            'PBX_TIME'         => $dateTime,
            'PBX_DIFF'         => sprintf('%02d', $parameters['bank_delays']),
            //'PBX_TYPEPAIEMENT' => 'CARTE',
            //'PBX_TYPECARTE'    => 'CB',
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
    */

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
