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
     * Key for test
     * @var string
     */
    public $key = '0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF0123456789ABCDEF';

    /**
     * Test credentials SITE NUMBER.
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

    /**
     * Test credentials IDENTIFIANT.
     *
     * @var int
     */
    public $identifiant = '110647233';


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
        $request = $this->gateway->purchase([
            'amount' => '10.00',
            'currency' => 'USD',
            'card' => [
                'firstName' => 'Pokemon',
                'lastName' => 'The second',
                'email' => 'test@paybox.com',
            ],
        ])->send();

        $this->assertInstanceOf('Omnipay\Paybox\Message\SystemResponse', $request);
        $this->assertFalse($request->isTransparentRedirect());
    }

    public function testPurchaseSendWithSiteData()
    {
        $site = 1999888;
        $rang = 32;
        $identifiant = '107904482';

        $gateway = $this->gateway->purchase([
            'amount' => '10.00',
            'currency' => 'EUR',
            'card' => [
                'firstName' => 'Pokemon',
                'lastName' => 'The second',
                'email' => 'test@paybox.com',
            ],
        ]);

        $gateway->setRang($rang);
        $gateway->setSite($site);
        $gateway->setIdentifiant($identifiant);
        $gateway->setTransactionID(3);
        $gateway->setTime("2023-03-11T12:00:00+00:00");
        $gateway->setKey($this->key);
        $request = $gateway->send();

        $this->assertInstanceOf('Omnipay\Paybox\Message\SystemResponse', $request);
        $this->assertFalse($request->isTransparentRedirect());

        $hmac = '0128E2C57D2795F6556941979B2891A9169E61312C38DB64C90FE1A83C7EE1A880ED993EFB20389DE294274A308912E42904BC48917CFD1C98BC131CFE0DF89F';

        $expected_url = "https://tpeweb.paybox.com/php?"
                    ."PBX_SITE=1999888"
                    ."&PBX_RANG=32"
                    ."&PBX_IDENTIFIANT=107904482"
                    ."&PBX_TOTAL=1000"
                    ."&PBX_DEVISE=978"
                    ."&PBX_CMD=3"
                    ."&PBX_PORTEUR=test%40paybox.com"
                    ."&PBX_RETOUR=Mt%3AM%3BId%3AR%3BRef%3AS%3BErreur%3AE%3Bsign%3AK"
                    ."&PBX_TIME=2023-03-11T12%3A00%3A00%2B00%3A00"
                    ."&PBX_HMAC=" . $hmac;

        $this->assertSame($expected_url, $request->getRedirectUrl());
    }

    public function testPurchaseSendWithSiteData3DSV2()
    {

        $site = 1999888;
        $rang = 43;
        $identifiant = '107975626';


        $gateway = $this->gateway->purchase([
            'amount' => '10.00',
            'currency' => 'EUR',
            'card' => [
                'firstName' => 'Pokemon',
                'lastName' => 'The second',
                'email' => 'test@paybox.com',
            ],
        ]);

        $gateway->setRang($rang);
        $gateway->setSite($site);
        $gateway->setIdentifiant($identifiant);
        $gateway->setTransactionID(3);
        $gateway->setTime("2023-03-11T12:00:00+00:00");
        $gateway->setKey($this->key);
        $request = $gateway->send();

        $this->assertInstanceOf('Omnipay\Paybox\Message\SystemResponse', $request);
        $this->assertFalse($request->isTransparentRedirect());

        $hmac = '46C2D10BC6A1812656DA98361B2A8054900ADAFC46EE6729CF0EB5405A6CD1B997F19C3A135CB637490CDBF51499BF33DC553E9B618CA94ACF904AAF7D031745';

        $expected_url = "https://tpeweb.paybox.com/php?"
                    ."PBX_SITE=1999888"
                    ."&PBX_RANG=43"
                    ."&PBX_IDENTIFIANT=107975626"
                    ."&PBX_TOTAL=1000"
                    ."&PBX_DEVISE=978"
                    ."&PBX_CMD=3"
                    ."&PBX_PORTEUR=test%40paybox.com"
                    ."&PBX_RETOUR=Mt%3AM%3BId%3AR%3BRef%3AS%3BErreur%3AE%3Bsign%3AK"
                    ."&PBX_TIME=2023-03-11T12%3A00%3A00%2B00%3A00"
                    ."&PBX_HMAC=" . $hmac;

        $this->assertSame($expected_url, $request->getRedirectUrl());
    }
}
