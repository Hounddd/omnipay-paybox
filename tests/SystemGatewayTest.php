<?php

namespace Omnipay\Paybox;

use Omnipay\Tests\GatewayTestCase;

class SystemGatewayTest extends GatewayTestCase
{
    /*
     * For techincal informations see :
     * https://www.paybox.com/wp-content/uploads/2022/01/ParametresTestVerifone_Paybox_V8.1_FR-1.pdf
     */

    /**
     * @var SystemGateway
     */
    public $gateway;

    /**
     * HMAC key used for test
     */
    public string $hmackey = '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF';

    /**
     * Test credentials SITE NUMBER
     */
    public int $site = 1999888;

    /**
     * Test credentials RANG
     */
    public int $rang = 32;

    /**
     * Test credentials IDENTIFIANT
     */
    public int $identifiant = 107904482;

    /**
     * Test transacation (order) Id
     */
    private int $transactionID = 3;

    /**
     * Test transaction time
     */
    private string $transactionTime = '2023-12-10T12:00:00+00:00';

    /**
     * Test default purchase data
     */
    private array $defaultPurchaseData = [
        'amount' => '10.00',
        'currency' => 'EUR',
        'card' => [
            'firstName' => 'Test',
            'lastName' => 'PAYBOX',
            'email' => 'test@paybox.com',
        ],
    ];

    /**
     * Test xml string shopping card for 3DS enrolment
     */
    private string $shoopingCartXML = '<?xml version="1.0" encoding="utf-8"?>'
            . '<shoppingcart>'
            . '<total>'
            . '<totalQuantity>5</totalQuantity>'
            . '</total>'
            . '</shoppingcart>';

    /**
     * Test xml string billing for 3DS enrolment
     */
    private string $billingXML = '<?xml version="1.0" encoding="utf-8"?>'
            . '<Billing>'
            . '<Address>'
            . '<FirstName>Test</FirstName>'
            . '<LastName>PAYBOX</LastName>'
            . '<Address1>Paybox street</Address1>'
            . '<Address2></Address2>'
            . '<ZipCode>00000</ZipCode>'
            . '<City>PAYBOX</City>'
            . '<CountryCode>250</CountryCode>'
            . '</Address>'
            . '</Billing>';


    public function setUp(): void
    {
        parent::setUp();

        $this->gateway = new SystemGateway($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testPurchase()
    {
        $request = $this->gateway->purchase(['amount' => '10.00']);

        $this->assertInstanceOf('Omnipay\Paybox\Message\SystemPurchaseRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
        $this->assertSame('https://tpeweb.paybox.com/php', $request->getEndpoint());
    }

    public function testPurchaseTestMode()
    {
        $request = $this->gateway->purchase(['amount' => '10.00', 'testMode' => true]);

        $this->assertInstanceOf('Omnipay\Paybox\Message\SystemPurchaseRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
        $this->assertSame('https://preprod-tpeweb.paybox.com/php', $request->getEndpoint());
    }

    public function testAuthorize()
    {
        $request = $this->gateway->authorize(['amount' => '10.00', 'testMode' => true]);

        $this->assertInstanceOf('Omnipay\Paybox\Message\SystemAuthorizeRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
    }

    public function testCompletePurchase()
    {
        $request = $this->gateway->completePurchase(['amount' => '10.00']);

        $this->assertInstanceOf('Omnipay\Paybox\Message\SystemCompletePurchaseRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
    }

    public function testPurchaseSend()
    {
        $request = $this->gateway
            ->purchase($this->defaultPurchaseData)
            ->send();

        $this->assertInstanceOf('Omnipay\Paybox\Message\SystemResponse', $request);
        $this->assertFalse($request->isTransparentRedirect());
    }

    public function testPurchaseSendWithSiteData()
    {
        $hmac = 'ACFCD463AD654139BCC1A47254A65B9637066CE019A2B6B14B4A549600DC12E0E2226A5C04E95722F7E993C45B86E01ABC4CDEC9A9C67FC41A662ABB1AF773AB';

        $expected_url = $expected_url = $this->getBaseExpectedUrl()
            . "&PBX_HMAC=" . $hmac;

        $gateway = $this->gateway
            ->purchase($this->defaultPurchaseData);

        $gateway->setRang($this->rang)
            ->setSite($this->site)
            ->setIdentifiant($this->identifiant)
            ->setTransactionID($this->transactionID)
            ->setKey($this->hmackey)
            ->setTime($this->transactionTime);

        $request = $gateway->send();

        $this->assertInstanceOf('Omnipay\Paybox\Message\SystemResponse', $request);
        $this->assertFalse($request->isTransparentRedirect());
        $this->assertSame($expected_url, $request->getRedirectUrl());
    }

    public function testPurchaseSendWithSiteData3DSV2OFF()
    {
        $this->set3dsTestAccount();

        $hmac = '75E84EABB8398898DBE1150FDD68F82CB532CD44900F5ADAE1117CB9265E59DECA341DF2D548BDCFE587C3038468AF5FC58EC3CAAB5CB91F69A0DD407FF1482D';

        // Merchant authentication - Frictionless requested.
        $merchantAuthentification = '02';

        $expected_url = $expected_url = $this->getBaseExpectedUrl()
            . "&PBX_SHOPPINGCART=" . urlencode($this->shoopingCartXML)
            . "&PBX_BILLING=" . urlencode($this->billingXML)
            . "&PBX_SOUHAITAUTHENT=". $merchantAuthentification
            . "&PBX_HMAC=" . $hmac;

        $defaultPurchaseData = $this->getDefaultPurchaseData();
        $defaultPurchaseData['enableAuthentification'] = $merchantAuthentification;
        // var_dump($defaultPurchaseData);

        $gateway = $this->gateway->purchase($defaultPurchaseData);

        $gateway->setRang($this->rang)
            ->setSite($this->site)
            ->setIdentifiant($this->identifiant)
            ->setTransactionID($this->transactionID)
            ->setKey($this->hmackey)
            ->setTime($this->transactionTime);

        $request = $gateway->send();

        $this->assertInstanceOf('Omnipay\Paybox\Message\SystemResponse', $request);
        $this->assertFalse($request->isTransparentRedirect());
        $this->assertSame($expected_url, $request->getRedirectUrl());
    }

    public function testPurchaseSendWithSiteData3DSV2ON()
    {
        $this->set3dsTestAccount();

        // Merchant authentication - Challenge required.
        $merchantAuthentification = '04';

        $hmac = 'F6FAB41A0675457F3775A27E6A8B0223AE7FB0E5C3F4D2643F16E4AF96D88F65940359BE7B7E9468BA87B9CA7F034322AADA0C416CDE574B270097C1BDCCC31A';

        $expected_url = $expected_url = $this->getBaseExpectedUrl()
            . "&PBX_SHOPPINGCART=" . urlencode($this->shoopingCartXML)
            . "&PBX_BILLING=" . urlencode($this->billingXML)
            . "&PBX_SOUHAITAUTHENT=". $merchantAuthentification
            . "&PBX_HMAC=" . $hmac;

        $defaultPurchaseData = $this->getDefaultPurchaseData();
        $defaultPurchaseData['enableAuthentification'] = $merchantAuthentification;
        var_dump($defaultPurchaseData);

        $gateway = $this->gateway->purchase($defaultPurchaseData);

        $gateway->setRang($this->rang)
            ->setSite($this->site)
            ->setIdentifiant($this->identifiant)
            ->setTransactionID($this->transactionID)
            ->setKey($this->hmackey)
            ->setTime($this->transactionTime);

        $request = $gateway->send();

        $this->assertInstanceOf('Omnipay\Paybox\Message\SystemResponse', $request);
        $this->assertFalse($request->isTransparentRedirect());
        $this->assertSame($expected_url, $request->getRedirectUrl());
    }


    /**
     * Get the default data for the purchase process
     */
    private function getDefaultPurchaseData(): array
    {
        return array_merge(
            $this->defaultPurchaseData,
            [
                'shoppingCart' => $this->shoopingCartXML,
                'billing' => $this->billingXML
            ]
        );
    }

    /**
     * Change testing accound, use 'Tests Paybox System 3-D Secure' account
     * see: https://www.paybox.com/espace-integrateur-documentation/comptes-de-tests/
     */
    private function set3dsTestAccount(): void
    {
        $this->rang = 43;
        $this->identifiant = '107975626';
    }

    /**
     * Return base expected url from \Omnipay\Common\Message\ResponseInterface request
     */
    private function getBaseExpectedUrl(): string
    {
        return "https://tpeweb.paybox.com/php?"
            . "PBX_SITE=" . $this->site
            . "&PBX_RANG=" . $this->rang
            . "&PBX_IDENTIFIANT=" . $this->identifiant
            . "&PBX_TOTAL=" . ($this->defaultPurchaseData['amount'] * 100)
            . "&PBX_DEVISE=978"
            . "&PBX_CMD=" . $this->transactionID
            . "&PBX_PORTEUR=" . urlencode($this->defaultPurchaseData['card']['email'])
            . "&PBX_RETOUR=Mt%3AM%3BId%3AR%3BRef%3AS%3BErreur%3AE%3Bsign%3AK"
            . "&PBX_TIME=" . urlencode($this->transactionTime);
    }
}
