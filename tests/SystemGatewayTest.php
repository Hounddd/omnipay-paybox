<?php

namespace Omnipay\Paybox;

use Omnipay\Tests\GatewayTestCase;

class SystemGatewayTest extends GatewayTestCase
{
    /**
     * Key for test site - see http://www1.paybox.com/wp-content/uploads/2014/02/PayboxTestParameters_V6.2_EN.pdf
     * @var string
     */
    public $key = '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF';

    /**
     * @var SystemGateway
     */
    public $gateway;

    /**
     * Test credentials site number.
     *
     * @var int
     */
    public $site = 1999888;

    /**
     * Test credentials RANG.
     *
     * @var int
     */
    public $rang = 32;

    public $identifiant = '107904482';

    public function setUp() : void
    {
        parent::setUp();

        $this->gateway = new SystemGateway($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testPurchase()
    {
        $request = $this->gateway->purchase(array('amount' => '10.00'));

        $this->assertInstanceOf('Omnipay\Paybox\Message\SystemPurchaseRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
        $this->assertSame('https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi', $request->getEndpoint());
    }

    public function testPurchaseTestMode()
    {
        $request = $this->gateway->purchase(array('amount' => '10.00', 'testMode' => true));

        $this->assertInstanceOf('Omnipay\Paybox\Message\SystemPurchaseRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
        $this->assertSame('https://preprod-tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi', $request->getEndpoint());
    }

    public function testCompletePurchase()
    {
        $request = $this->gateway->completePurchase(array('amount' => '10.00'));

        $this->assertInstanceOf('Omnipay\Paybox\Message\SystemCompletePurchaseRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
    }
/*
    public function testCompleteAuthorize()
    {
        $options = array(
            'amount' => '10.00',
            'transactionId' => '45',
            'returnUrl' => 'https://www.example.com/return',
        );
        $signature = 'opPlzAadVvCor99yZ8oj2NHmE0eAxXkmCZ80C%2BYW8htpF7Wf6krYYFjc1pQnvYHcW7vp3ta3p8Gfh7gAaR6WDOnhe1Xzm39whk11%2BShieXbQCnEKXot4aGkpodxi1cHutXBhh1IBQOLgq1IVM%2BaV9PUeTI%2FGFruSDnA1TExDHZE%3D';

        $this->getHttpRequest()->request->replace(
            array(
                'Mt' => 100,
                'Id' => 45,
                'Erreur' => '00114',
                'sign' => $signature,
            )
        );

        $response = $this->gateway->completeAuthorize($options)->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertSame(45, $response->getTransactionReference());
    }
*/
    public function testPurchaseSend()
    {
        $request = $this->gateway->purchase(array('amount' => '10.00', 'currency' => 'USD', 'card' => array(
            'firstName' => 'Pokemon',
            'lastName' => 'The second',
            'email' => 'test@paybox.com',
        )))->send();

        $this->assertInstanceOf('Omnipay\Paybox\Message\SystemResponse', $request);
        $this->assertFalse($request->isTransparentRedirect());
    }


    public function testPurchaseSendWithSiteData()
    {
        $gateway = $this->gateway->purchase(array('amount' => '10.00', 'currency' => 'EUR', 'card' => array(
            'firstName' => 'Pokemon',
            'lastName' => 'The second',
            'email' => 'test@paybox.com',
        )));

        $gateway->setRang($this->rang);
        $gateway->setSite($this->site);
        $gateway->setIdentifiant($this->identifiant);
        $gateway->setTransactionID(3);
        $gateway->setTime("2014-12-09T22:37:34+00:00");
        $gateway->setKey($this->key);
        $request = $gateway->send();

        $this->assertInstanceOf('Omnipay\Paybox\Message\SystemResponse', $request);
        $this->assertFalse($request->isTransparentRedirect());
        $expected_url = "https://tpeweb.paybox.com/cgi/MYchoix_pagepaiement.cgi?PBX_SITE=1999888&PBX_RANG=32";
        $expected_url .= "&PBX_IDENTIFIANT=107904482&PBX_TOTAL=1000&PBX_DEVISE=978&PBX_CMD=3&PBX_PORTEUR=test%40paybox.com";
        $expected_url .= "&PBX_RETOUR=Mt%3AM%3BId%3AR%3Bidtrans%3AS%3BErreur%3AE%3Bsign%3AK&PBX_TIME=2014-12-09T22%3A37%3A34%2B00%3A00";
        $hmac = '62E903153A5E9603B2C497F3A74D8B4EC7172CDA405B134B11EFE15F031BCDEB6A9C8F8655307A34D1BAF95D2FFB7624F7631F3FBBAA23C6FB8EE3C1B5D2AEBC';
        $expected_url .= "&PBX_HMAC=" . $hmac;
        $this->assertSame($expected_url, $request->getRedirectUrl());
    }
}
