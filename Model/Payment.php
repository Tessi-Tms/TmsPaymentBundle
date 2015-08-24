<?php

/**
 * @author:  Gabriel BONDAZ <gabriel.bondaz@idci-consulting.fr>
 * @license: MIT
 */

namespace Tms\Bundle\PaymentBundle\Model;

class Payment
{
    const STATE_APPROVED = 'approved';
    const STATE_CANCELED = 'canceled';
    const STATE_EXPIRED  = 'expired';
    const STATE_FAILED   = 'failed';
    const STATE_NEW      = 'new';

    /**
     * @var string
     */
    protected $transactionId;

    /**
     * @var string
     */
    protected $referenceId;

    /**
     * @var integer
     */
    protected $amount;

    /**
     * @var string
     */
    protected $currencyCode;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @var string
     */
    protected $backendAlias;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var array
     */
    protected $raw;

    /**
     * The constructor
     *
     * @param array $data The default data.
     */
    public function __construct(array $data = array())
    {
        $this
            ->setTransactionId(isset($data['transactionId']) ? $data['transactionId'] : null)
            ->setReferenceId(isset($data['referenceId']) ? $data['referenceId'] : null)
            ->setAmount(isset($data['amount']) ? $data['amount'] : 0)
            ->setCurrencyCode(isset($data['currencyCode']) ? $data['currencyCode'] : null)
            ->setCreatedAt(isset($data['createdAt']) ? $data['createdAt'] : new \DateTime('now'))
            ->setBackendAlias(isset($data['backendAlias']) ? $data['backendAlias'] : null)
            ->setState(isset($data['state']) ? $data['state'] : self::STATE_NEW)
            ->setRaw(isset($data['raw']) ? $data['raw'] : array())
        ;
    }

    /**
     * To array
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'transactionId' => $this->transactionId,
            'referenceId'   => $this->referenceId,
            'amount'        => $this->amount,
            'currencyCode'  => $this->currencyCode,
            'createdAt'     => $this->createdAt->format(\DateTime::ISO8601),
            'backendAlias'  => $this->backendAlias,
            'state'         => $this->state,
            'raw'           => $this->raw,
        );
    }

    /**
     * Returns payment transaction id.
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * Set payment transaction id.
     *
     * @param string $transactionId
     *
     * @return Payment
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    /**
     * Returns payment reference id.
     *
     * @return string
     */
    public function getReferenceId()
    {
        return $this->referenceId;
    }

    /**
     * Set payment reference id.
     *
     * @param string $referenceId
     *
     * @return Payment
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    /**
     * Returns payment amount.
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set payment amount.
     *
     * @param integer $amount
     *
     * @return Payment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Returns payment currency code.
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * Set payment currency code.
     *
     * @param string $currencyCode
     *
     * @return Payment
     */
    public function setCurrencyCode($currencyCode)
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    /**
     * Returns payment created at.
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set payment created at.
     *
     * @param DateTime|string $createdAt
     *
     * @return Payment
     */
    public function setCreatedAt($createdAt)
    {
        if (is_string($createdAt)) {
            $createdAt = \DateTime::createFromFormat(\DateTime::ISO8601, $createdAt);
        }

        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Returns payment backend alias used.
     *
     * @return string
     */
    public function getBackendAlias()
    {
        return $this->backendAlias;
    }

    /**
     * Set payment backend alias used.
     *
     * @param string $backendAlias
     *
     * @return Payment
     */
    public function setBackendAlias($backendAlias)
    {
        $this->backendAlias = $backendAlias;

        return $this;
    }

    /**
     * Returns payment state.
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set payment state.
     *
     * @param string $state
     *
     * @return Payment
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Returns payment raw.
     *
     * @return array
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * Set payment raw.
     *
     * @param array $raw
     *
     * @return Payment
     */
    public function setRaw(array $raw)
    {
        $this->raw = $raw;

        return $this;
    }
 }