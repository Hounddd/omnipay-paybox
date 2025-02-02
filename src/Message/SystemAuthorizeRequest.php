<?php

namespace Omnipay\Paybox\Message;

/**
 * Paybox System Authorize Request
 */
class SystemAuthorizeRequest extends AbstractRequest
{
    /**
     * Authorization without capture
     *
     * @var boolean
     */
    protected $onlyAuthorize = true;

    /**
     * Transaction time in timezone format e.g 2011-02-28T11:01:50+01:00.
     *
     * @var string
     */
    protected $time;

    /**
     * Get time of the transaction.
     *
     * @return string
     */
    public function getTime()
    {
        return (!empty($this->time)) ? $this->time : date('c');
    }

    /**
     * Setter for time (of transaction).
     *
     * @param string $time
     *  Time in 'c' format - e.g 2011-02-28T11:01:50+01:00
     */
    public function setTime($time)
    {
        $this->time = $time;
    }

    public function getData()
    {
        foreach ($this->getRequiredCoreFields() as $field) {
            $this->validate($field);
        }
        $this->validateCardFields();
        $data = $this->getBaseData() + $this->getTransactionData() + $this->getURLData();
        if ($this->onlyAuthorize) {
            $data['PBX_AUTOSEULE'] = 'O';
        }

        $data['PBX_HMAC'] = $this->generateSignature($data);
        return $data;
    }

    public function sendData($data)
    {
        return $this->response = new SystemAuthorizeResponse($this, $data, $this->getEndpoint());
    }

    public function getSite()
    {
        return $this->getParameter('site');
    }

    public function setSite($value)
    {
        return $this->setParameter('site', $value);
    }

    public function getRang()
    {
        return $this->getParameter('rang');
    }

    public function setRang($value)
    {
        return $this->setParameter('rang', $value);
    }

    public function getIdentifiant()
    {
        return $this->getParameter('identifiant');
    }

    public function setIdentifiant($value)
    {
        return $this->setParameter('identifiant', $value);
    }


    public function getShoppingCart()
    {
        return $this->getParameter('shoppingCart');
    }

    public function setShoppingCart($value)
    {
        return $this->setParameter('shoppingCart', $value);
    }

    public function getBilling()
    {
        return $this->getParameter('billing');
    }

    public function setBilling($value)
    {
        return $this->setParameter('billing', $value);
    }

    public function getEnableAuthentification()
    {
        return $this->getParameter('enableAuthentification');
    }

    public function setEnableAuthentification($value)
    {
        return $this->setParameter('enableAuthentification', $value);
    }

    public function getRequiredCoreFields()
    {
        return [
            'amount',
            'currency',
        ];
    }

    public function getRequiredCardFields()
    {
        return [
            'email',
        ];
    }

    public function getTransactionData()
    {
        $data = [
            'PBX_TOTAL' => $this->getAmountInteger(),
            'PBX_DEVISE' => $this->getCurrencyNumeric(),
            'PBX_CMD' => $this->getTransactionId(),
            'PBX_PORTEUR' => $this->getCard()->getEmail(),
            'PBX_RETOUR' => 'Mt:M;Id:R;Ref:S;Erreur:E;sign:K',
            'PBX_TIME' => $this->getTime(),
        ];
        if ($this->getShoppingCart()) {
            $data['PBX_SHOPPINGCART'] = $this->getShoppingCart();
        }
        if ($this->getShoppingCart()) {
            $data['PBX_BILLING'] = $this->getBilling();
        }
        if ($this->getEnableAuthentification()) {
            $data['PBX_SOUHAITAUTHENT'] = $this->getEnableAuthentification();
        }
        return $data;
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'PBX_SITE' => $this->getSite(),
            'PBX_RANG' => $this->getRang(),
            'PBX_IDENTIFIANT' => $this->getIdentifiant(),
        ];
    }

    /**
     * Get values for IPN and browser return urls.
     *
     * Browser return urls should all be set or non set.
     */
    public function getURLData()
    {
        $data = [];
        if ($this->getNotifyUrl()) {
            $data['PBX_REPONDRE_A'] = $this->getNotifyUrl();
        }
        if ($this->getReturnUrl()) {
            $data['PBX_EFFECTUE'] = $this->getReturnUrl();
            $data['PBX_REFUSE'] = $this->getReturnUrl();
            $data['PBX_ANNULE'] = $this->getReturnUrl();
            $data['PBX_ATTENTE'] = $this->getReturnUrl();
        }
        if ($this->getCancelUrl()) {
            $data['PBX_ANNULE'] = $this->getCancelUrl();
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getUniqueID()
    {
        return uniqid();
    }

    /**
     * @return string
     * https://www.paybox.com/wp-content/uploads/2022/01/ManuelIntegrationVerifone_PayboxSystem_V8.1_FR.pdf
     * see: 12.6 URL D’APPEL ET ADRESSES IP
     */
    public function getEndpoint()
    {
        if ($this->getTestMode()) {
            return 'https://preprod-tpeweb.paybox.com/php';
        } else {
            return 'https://tpeweb.paybox.com/php';
        }
    }

    public function getPaymentMethod()
    {
        return 'card';
    }

    public function getTransactionType()
    {
        return '00001';
    }
}
