<?php

namespace Omnipay\Paybox\Message;

use Omnipay\Paybox\Message\AbstractRequest;

/**
 * Paybox Authorize Request
 */
class DirectAuthorizeRequest extends AbstractRequest
{

    public function getData()
    {
        $this->validate('currency', 'amount');

        $data = $this->getBaseData() + $this->getTransactionData();
        $data['CVV'] = $this->getCard()->getCvv();
        $data['TYPECARTE'] = $this->getCard()->getBrand();
        $data['PORTEUR'] = $this->getCard()->getNumber();
        $data['DATEVAL'] = $this->getCard()->getExpiryDate('m-Y');
        return $data;
    }

    public function sendData($data)
    {
        $httpResponse = $this->httpClient->request(
            'POST',
            $this->getEndpoint(),
            ["Content-Type" => "application/x-www-form-urlencoded"],
            http_build_query($data)
        );
        return $this->createResponse($httpResponse);
    }

    protected function createResponse($data)
    {
        return $this->response = new DirectResponse($this, $data);
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

    public function getRequiredFields()
    {
        $extraFields = $this->getIsUsOrCanada() ? $this->getRequiredFieldsUsAndCanada() : [];
        return array_merge([
            'amount',
            'city',
            'country',
            'address1',
            'email',
            'firstName',
            'lastName',
        ], $extraFields);
    }

    public function getRequiredFieldsUsAndCanada()
    {
        return [
            'postcode',
            'billingState',
        ];
    }

    public function getTransactionData()
    {
        return [
            'REFERENCE' => $this->getTransactionId(),
            'MONTANT' => $this->getAmount(),
            'DEVISE' => $this->getCurrency(),
        ];
    }

    /**
     * @return array
     */
    public function getBaseData()
    {
        return [
            'SITE' => $this->getSite(),
            'RANG' => $this->getRang(),
            //@todo where should this be set ?
            // 00103 for Paybox Direct
            // 00104 for Paybox Direct Plus
            'VERSION' => '00103',
            'TYPE' => $this->getTransactionType(),
            'NUMQUESTION' => substr(uniqid(), 0, 10),
            'DATEQ' => date('dmYhis')
        ];
    }

    /**
     * @return string
     */
    public function getUniqueID()
    {
        return uniqid();
    }

    public function getEndpoint()
    {
        return 'https://ppps.paybox.com/PPPS.php';
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
