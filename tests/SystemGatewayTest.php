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

        $shoppingCart = $this->getShoppingCartXml();
        $billing = $this->getBillingXml();

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

        $gateway->setShoppingCart($shoppingCart);
        $gateway->setBilling($billing);
        $gateway->setEnableAuthentification(2);

        $gateway->setKey($this->key);
        $request = $gateway->send();

        $this->assertInstanceOf('Omnipay\Paybox\Message\SystemResponse', $request);
        $this->assertFalse($request->isTransparentRedirect());

        $hmac = 'A03A9028C9CD947AC5C1E262F02C7D8CEFCF356C31BB813C102D3D60FD035875AF02FC0F53BA3BB116AA568718F8610F93D61306E4B4261272BBA41232810B0D';

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
                    ."&PBX_SHOPPINGCART=". urlencode($shoppingCart)
                    ."&PBX_BILLING=". urlencode($billing)
                    ."&PBX_SOUHAITAUTHENT=2"
                    ."&PBX_HMAC=" . $hmac;

        $this->assertSame($expected_url, $request->getRedirectUrl());
    }

    // public function testPurchaseSendWithSiteData3DSV2()
    // {

    //     $site = 1999888;
    //     $rang = 43;
    //     $identifiant = '107975626';


    //     $gateway = $this->gateway->purchase([
    //         'amount' => '10.00',
    //         'currency' => 'EUR',
    //         'card' => [
    //             'firstName' => 'Pokemon',
    //             'lastName' => 'The second',
    //             'email' => 'test@paybox.com',
    //         ],
    //     ]);

    //     $gateway->setRang($rang);
    //     $gateway->setSite($site);
    //     $gateway->setIdentifiant($identifiant);
    //     $gateway->setTransactionID(3);
    //     $gateway->setTime("2023-03-11T12:00:00+00:00");
    //     $gateway->setKey($this->key);

    //     $gateway->setBilling($this->key);

    //     $request = $gateway->send();

    //     $this->assertInstanceOf('Omnipay\Paybox\Message\SystemResponse', $request);
    //     $this->assertFalse($request->isTransparentRedirect());

    //     $hmac = '46C2D10BC6A1812656DA98361B2A8054900ADAFC46EE6729CF0EB5405A6CD1B997F19C3A135CB637490CDBF51499BF33DC553E9B618CA94ACF904AAF7D031745';

    //     $expected_url = "https://tpeweb.paybox.com/php?"
    //                 ."PBX_SITE=1999888"
    //                 ."&PBX_RANG=43"
    //                 ."&PBX_IDENTIFIANT=107975626"
    //                 ."&PBX_TOTAL=1000"
    //                 ."&PBX_DEVISE=978"
    //                 ."&PBX_CMD=3"
    //                 ."&PBX_PORTEUR=test%40paybox.com"
    //                 ."&PBX_RETOUR=Mt%3AM%3BId%3AR%3BRef%3AS%3BErreur%3AE%3Bsign%3AK"
    //                 ."&PBX_TIME=2023-03-11T12%3A00%3A00%2B00%3A00"
    //                 ."&PBX_HMAC=" . $hmac;

    //     $this->assertSame($expected_url, $request->getRedirectUrl());
    // }


    protected function getShoppingCartXml()
    {
        return '<?xml version="1.0" encoding="utf-8"?><shoppingcart><total><totalQuantity>5</totalQuantity></total></shoppingcart>';
    }

    protected function getBillingXml()
    {
        return '<?xml version="1.0" encoding="utf-8"?><Billing><Address><FirstName>Test</FirstName><LastName>PAYBOX</LastName><Address1>Paybox street</Address1><Address2></Address2><ZipCode>00000</ZipCode><City>PAYBOX</City><CountryCode>250</CountryCode></Address></Billing>';
    }
}
