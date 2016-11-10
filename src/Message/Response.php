<?php

namespace Omnipay\Vindicia\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Vindicia\ObjectHelper;
use Omnipay\Common\Message\RequestInterface;

class Response extends AbstractResponse
{
    const SUCCESS_CODE = 200;

    protected $objectHelper;

    // Cached objects:
    protected $transaction;
    protected $subscription;
    protected $customer;
    protected $plan;
    protected $product;
    protected $paymentMethod;
    protected $refunds;
    protected $transactions;
    protected $subscriptions;
    protected $chargebacks;
    protected $paymentMethods;

    /**
     * Constructor
     *
     * @param RequestInterface $request the initiating request.
     * @param mixed $data
     */
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);
        $this->objectHelper = new ObjectHelper();
    }

    /**
     * Is the response successful?
     * Throws an exception if there's no code.
     *
     * @return boolean
     * @throws \Omnipay\Common\Exception\InvalidResponseException
     */
    public function isSuccessful()
    {
        return intval($this->getCode()) === self::SUCCESS_CODE;
    }

    /**
     * Get the response message from the payment gateway.
     * Throws an exception if it's not present.
     *
     * @return string
     * @throws \Omnipay\Common\Exception\InvalidResponseException
     */
    public function getMessage()
    {
        if (isset($this->data->return)) {
            return $this->data->return->returnString;
        }
        throw new InvalidResponseException('Response has no message.');
    }

    /**
     * Get the response code from the payment gateway.
     * Throws an exception if it's not present.
     *
     * @return string
     * @throws \Omnipay\Common\Exception\InvalidResponseException
     */
    public function getCode()
    {
        if (isset($this->data->return)) {
            return $this->data->return->returnCode;
        }
        throw new InvalidResponseException('Response has no code.');
    }

    public function isCvvValidationFailure()
    {
        return !$this->isSuccessful()
            && in_array($this->getCode(), array('408', '409'))
            && strpos($this->getMessage(), 'CVN');
    }

    public function getTransaction()
    {
        if (!isset($this->transaction) && isset($this->data->transaction)) {
            $this->transaction = $this->objectHelper->buildTransaction($this->data->transaction);
        }
        return isset($this->transaction) ? $this->transaction : null;
    }

    /**
     * Get the reference provided by the gateway to represent this transaction
     *
     * @return string|null
     */
    public function getTransactionReference()
    {
        if ($this->getTransaction()) {
            return $this->getTransaction()->getReference();
        }
        return null;
    }

    /**
     * Get the id you (the merchant) provided to represent this transaction
     * NOTE: When you create a new transaction, Vindicia automatically
     * generates this value with the prefix you specified during initial
     * configuration.
     *
     * @return string|null
     */
    public function getTransactionId()
    {
        if ($this->getTransaction()) {
            return $this->getTransaction()->getId();
        }
        return null;
    }

    public function getCustomer()
    {
        if (!isset($this->customer) && isset($this->data->account)) {
            $this->customer = $this->objectHelper->buildCustomer($this->data->account);
        }
        return isset($this->customer) ? $this->customer : null;
    }

    /**
     * Get the reference provided by the gateway to represent this customer.
     *
     * @return string|null
     */
    public function getCustomerReference()
    {
        if ($this->getCustomer()) {
            return $this->getCustomer()->getReference();
        }
        return null;
    }

    /**
     * Get the id you (the merchant) provided to represent this customer.
     * This ID must be provided when creating a purchase/authorize request.
     *
     * @return string|null
     */
    public function getCustomerId()
    {
        if ($this->getCustomer()) {
            return $this->getCustomer()->getId();
        }
        return null;
    }

    public function getPlan()
    {
        if (!isset($this->plan) && isset($this->data->billingPlan)) {
            $this->plan = $this->objectHelper->buildPlan($this->data->billingPlan);
        }
        return isset($this->plan) ? $this->plan : null;
    }

    /**
     * Get the reference provided by the gateway to represent this plan.
     *
     * @return string|null
     */
    public function getPlanReference()
    {
        if ($this->getPlan()) {
            return $this->getPlan()->getReference();
        }
        return null;
    }

    /**
     * Get the id you (the merchant) provided to represent this plan.
     *
     * @return string|null
     */
    public function getPlanId()
    {
        if ($this->getPlan()) {
            return $this->getPlan()->getId();
        }
        return null;
    }

    public function getProduct()
    {
        if (!isset($this->product) && isset($this->data->product)) {
            $this->product = $this->objectHelper->buildProduct($this->data->product);
        }
        return isset($this->product) ? $this->product : null;
    }

    /**
     * Get the reference provided by the gateway to represent this product.
     *
     * @return string|null
     */
    public function getProductReference()
    {
        if ($this->getProduct()) {
            return $this->getProduct()->getReference();
        }
        return null;
    }

    /**
     * Get the id you (the merchant) provided to represent this product.
     *
     * @return string|null
     */
    public function getProductId()
    {
        if ($this->getProduct()) {
            return $this->getProduct()->getId();
        }
        return null;
    }

    public function getSubscription()
    {
        if (!isset($this->subscription) && isset($this->data->autobill)) {
            $this->subscription = $this->objectHelper->buildSubscription($this->data->autobill);
        }
        return isset($this->subscription) ? $this->subscription : null;
    }

    /**
     * Get the reference provided by the gateway to represent this subscription.
     *
     * @return string|null
     */
    public function getSubscriptionReference()
    {
        if ($this->getSubscription()) {
            return $this->getSubscription()->getReference();
        }
        return null;
    }

    /**
     * Get the id you (the merchant) provided to represent this subscription.
     *
     * @return string|null
     */
    public function getSubscriptionId()
    {
        if ($this->getSubscription()) {
            return $this->getSubscription()->getId();
        }
        return null;
    }

    /**
     * Get the status of the subscription.
     *
     * @return string|null
     */
    public function getSubscriptionStatus()
    {
        if (isset($this->data->autobill)) {
            return $this->data->autobill->status;
        }
        return null;
    }

    public function getPaymentMethod()
    {
        if (isset($this->paymentMethod)) {
            return $this->paymentMethod;
        }

        if (isset($this->data->paymentMethod)) {
            $this->paymentMethod = $this->objectHelper->buildPaymentMethod($this->data->paymentMethod);
            return $this->paymentMethod;
        }

        // sometimes it's in the account object
        if (!isset($this->data->account) || !isset($this->data->account->paymentMethods)) {
            return null;
        }

        // Vindicia returns all of the payment methods for this account, so we have to find
        // the one that we added in the request. Theoretically we could just return the id
        // that was added in the request, but this way we ensure it is actually returned
        // in the response
        foreach ($this->data->account->paymentMethods as $paymentMethod) {
            if ($paymentMethod->merchantPaymentMethodId === $this->getRequest()->getPaymentMethodId()) {
                $this->paymentMethod = $this->objectHelper->buildPaymentMethod($paymentMethod);
                return $this->paymentMethod;
            }
        }

        return null;
    }

    /**
     * Get the id you (the merchant) provided to represent this payment method.
     * This ID must be provided when creating a create payment method request.
     *
     * @return string|null
     */
    public function getPaymentMethodId()
    {
        if ($this->getPaymentMethod()) {
            return $this->getPaymentMethod()->getId();
        }

        return null;
    }

    /**
     * Get the reference provided by the gateway to represent this payment method.
     *
     * @return string|null
     */
    public function getPaymentMethodReference()
    {
        if ($this->getPaymentMethod()) {
            return $this->getPaymentMethod()->getReference();
        }

        return null;
    }

    public function getRefunds()
    {
        if (!isset($this->refunds) && isset($this->data->refunds)) {
            $refunds = array();
            foreach ($this->data->refunds as $refund) {
                $refunds[] = $this->objectHelper->buildRefund($refund);
            }
            $this->refunds = $refunds;
        }
        return isset($this->refunds) ? $this->refunds : null;
    }

    public function getTransactions()
    {
        if (!isset($this->transactions) && isset($this->data->transactions)) {
            $transactions = array();
            foreach ($this->data->transactions as $transaction) {
                $transactions[] = $this->objectHelper->buildTransaction($transaction);
            }
            $this->transactions = $transactions;
        }
        return isset($this->transactions) ? $this->transactions : null;
    }

    public function getSubscriptions()
    {
        if (!isset($this->subscriptions) && isset($this->data->autobills)) {
            $subscriptions = array();
            foreach ($this->data->autobills as $subscription) {
                $subscriptions[] = $this->objectHelper->buildSubscription($subscription);
            }
            $this->subscriptions = $subscriptions;
        }
        return isset($this->subscriptions) ? $this->subscriptions : null;
    }

    public function getPaymentMethods()
    {
        if (!isset($this->paymentMethods) && isset($this->data->paymentMethods)) {
            $paymentMethods = array();
            foreach ($this->data->paymentMethods as $paymentMethod) {
                $paymentMethods[] = $this->objectHelper->buildPaymentMethod($paymentMethod);
            }
            $this->paymentMethods = $paymentMethods;
        }
        return isset($this->paymentMethods) ? $this->paymentMethods : null;
    }

    public function getChargebacks()
    {
        if (!isset($this->chargebacks) && isset($this->data->chargebacks)) {
            $chargebacks = array();
            foreach ($this->data->chargebacks as $chargeback) {
                $chargebacks[] = $this->objectHelper->buildChargeback($chargeback);
            }
            $this->chargebacks = $chargebacks;
        }
        return isset($this->chargebacks) ? $this->chargebacks : null;
    }

    /**
     * Get the reference provided by the gateway to represent this HOA Web Session.
     * NOTE: Web sessions do not have IDs, only references.
     *
     * @return string|null
     */
    public function getWebSessionReference()
    {
        if (isset($this->data->session)) {
            return $this->data->session->VID;
        }
        return null;
    }

    /**
     * Returns the total sales tax. For use after a CalculateSalesTax request
     *
     * @return float
     */
    public function getSalesTax()
    {
        if (isset($this->data->totalTax)) {
            return $this->data->totalTax;
        }
        return null;
    }

    /**
     * Return the soap ID from the soap response.
     *
     * @return string
     */
    public function getSoapId()
    {
        if (isset($this->data->return)) {
            return $this->data->return->soapId;
        }
        throw new InvalidResponseException('Response has no soap id.');
    }

    /**
     * Gets the risk score for the transaction, that is, the estimated probability that
     * this transaction will result in a chargeback. This number ranges from 0 (best) to
     * 100 (worst). It can also be -1, meaning that Vindicia has no opinion. (-1 indicates
     * a transaction with no originating IP addresses, an incomplete addresses, or both.
     * -2 indicates an error; retry later.)
     *
     * @return int|null
     */
    public function getRiskScore()
    {
        if (isset($this->data->score)) {
            return intval($this->data->score);
        }
        return null;
    }

    /**
     * Override to set return type correctly
     *
     * @return AbstractRequest
     */
    public function getRequest()
    {
        /**
         * @var AbstractRequest
         */
        return parent::getRequest();
    }
}
