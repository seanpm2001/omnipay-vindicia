<?php

namespace Omnipay\Vindicia;


class ApplePayGateway extends AbstractVindiciaGateway
{
    /**
     * Get the gateway name.
     *
     * @return string
     */
    public function getName()
    {
        return 'Vindicia Apple Pay';
    }

    /**
     * Authorize an Apple Pay purchase.
     *
     * @param array $parameters
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function authorize(array $parameters = array())
    {
        /**
         * @var \Omnipay\Common\Message\AbstractRequest
         */
        return $this->createRequest('\Omnipay-Vindicia\Message\ApplePayAuthorizeRequest', $parameters);
    }

    /**
     * Makes request to Apple to set up session between Vimeo and Apple 
     * (instead of extending the usual AbstractRequest).
     *
     * @param array $parameters
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function completeAuthorize(array $parameters = array())
    {
        /**
         * @var  \Omnipay\Vindicia\Message\AuthorizeRequest
         */
        return $this->createRequest('\Omnipay\Vindicia\Message\AuthorizeRequest', $parameters);
    }

    /**
     * Capture an Apple Pay purchase.
     *
     * @param array $parameters
     * @return \Omnipay\Vindicia\Message\CaptureRequest
     */
    public function capture(array $parameters = array())
    {
        /**
         * @var \Omnipay\Vindicia\Message\CaptureRequest
         */
        return $this->createRequest('\Omnipay\Vindicia\Message\CaptureRequest', $parameters);
    }

    // see AbstractVindiciaGateway for more functions and documentation
}
