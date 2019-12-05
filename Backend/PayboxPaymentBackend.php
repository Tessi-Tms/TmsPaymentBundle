<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Backend;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
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
        return sprintf('%s/%s.bin', $this->getParameter('keys_path'), $site);
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
    protected function configureParameters(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(array('keys_path', 'web_servers'))
        ;
    }

    /**
     * Returns paybox PBX_RETOUR string.
     *
     * @return string
     */
    protected function getPayboxReturnString()
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
    protected function configureOptions(OptionsResolver $resolver)
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
                'PBX_EFFECTUE',
                'PBX_REFUSE',
                'PBX_ANNULE',
                'PBX_ATTENTE',
                'PBX_DIFF',
            ))
            ->setDefaults(array(
                'PBX_HASH'         => 'sha512',
                'PBX_RUF1'         => 'POST',
                'PBX_RETOUR'       => $this->getPayboxReturnString(),
                'PBX_TIME'         => date("c"), // ISO-8601
                'PBX_TYPEPAIEMENT' => 'CARTE',
                'PBX_TYPECARTE'    => 'CB',
                //'PBX_ERRORCODETEST' => '999',
            ))
            ->setOptional(array(
            ))
            ->setNormalizers(array(
                'PBX_DEVISE' => function(Options $options, $value) {
                    return CurrencyCode::getNumericCode($value);
                },
                'PBX_DIFF' => function(Options $options, $value) {
                    return sprintf('%02d', $value);
                }
            ))
            ->setAllowedValues(array(
                'PBX_HASH' => array(
                    'sha512',
                    'sha384',
                    'sha256',
                    'sha224',
                    'ripemd160',
                    'mdc2',
                ),
                //'PBX_DEVISE' => CurrencyCode::getAlphabeticCodes(),
            ))
            ->setAllowedTypes(array(
                'PBX_SITE'        => array('string'),
                'PBX_RANG'        => array('string'),
                'PBX_IDENTIFIANT' => array('string'),
                'PBX_TOTAL'       => array('integer'),
                'PBX_DEVISE'      => array('string'),
                'PBX_CMD'         => array('string'),
                'PBX_PORTEUR'     => array('string'),
                'PBX_REPONDRE_A'  => array('string'),
                'PBX_EFFECTUE'    => array('string'),
                'PBX_REFUSE'      => array('string'),
                'PBX_ANNULE'      => array('string'),
                'PBX_ATTENTE'     => array('string'),
                'PBX_DIFF'        => array('string', 'integer'),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function preConfigureOptions(array & $options)
    {
        list($pbxSite, $pbxRang, $pbxIdentifiant) = explode('|', $options['merchant_id']);

        $options['PBX_SITE']        = $pbxSite;
        $options['PBX_RANG']        = $pbxRang;
        $options['PBX_IDENTIFIANT'] = $pbxIdentifiant;
        $options['PBX_TOTAL']       = $options['amount'];
        $options['PBX_DEVISE']      = $options['currency_code'];
        $options['PBX_CMD']         = $options['order_id'];
        $options['PBX_PORTEUR']     = $options['customer_email'];
        $options['PBX_REPONDRE_A']  = $options['automatic_response_url'];
        $options['PBX_EFFECTUE']    = $options['normal_return_url'];
        $options['PBX_REFUSE']      = $options['cancel_return_url'];
        $options['PBX_ANNULE']      = $options['cancel_return_url'];
        $options['PBX_ATTENTE']     = $options['normal_return_url'];
        $options['PBX_DIFF']        = $options['bank_delays'];

        if (isset($options['hash_method'])) {
            $options['PBX_HASH'] = $options['hash_method'];
        }

        $availableOptionKeys = array(
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
            'PBX_TYPEPAIEMENT',
            'PBX_TYPECARTE',
            'PBX_ERRORCODETEST'
        );

        foreach ($options as $key => $value) {
            if (!in_array($key, $availableOptionKeys)) {
                unset($options[$key]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doBuildPaymentOptions(array $options)
    {
        $build = implode('&', array_map(
            function ($k, $v) { return sprintf('%s=%s', $k, $v); },
            array_keys($options),
            $options
        ));

        return array(
            'options' => $options,
            'build'   => $build
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function buildPaymentForm($builtOptions)
    {
        $binKey = file_get_contents($this->getKeyPath($builtOptions['options']['PBX_SITE']));
        $builtOptions['options']['PBX_HMAC'] = strtoupper(
            hash_hmac($builtOptions['options']['PBX_HASH'], $builtOptions['build'], $binKey)
        );

        return $this->twig->render(
            'TmsPaymentBundle:Payment:paybox.html.twig',
            array(
                'url'     => sprintf('https://%s/cgi/MYchoix_pagepaiement.cgi', $this->getAvailableServer()),
                'options' => $builtOptions['options'],
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
            throw new \Exception("The request not contains 'error'");
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
        if ('00000' === $code) {
            $payment->setState(Payment::STATE_APPROVED);

            return true;
        }

        if ('00001' === $code) {
            $payment->setState(Payment::STATE_CANCELED);
        } elseif ('001' == substr($code, 0, 3)) {
            $payment->setState(Payment::STATE_FAILED);
        }

        return false;
    }
}
