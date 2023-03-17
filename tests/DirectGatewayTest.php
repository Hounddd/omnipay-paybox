<?php

namespace Omnipay\Paybox;

use Omnipay\Tests\GatewayTestCase;

class DirectGatewayTest extends GatewayTestCase
{
    /**
     * @var
     */
    protected $gateway;

    public function setUp(): void
    {
        parent::setUp();

        $this->gateway = new DirectGateway($this->getHttpClient(), $this->getHttpRequest());
    }

    public function testPurchase()
    {
        $request = $this->gateway->purchase(['amount' => '10.00']);

        $this->assertInstanceOf('Omnipay\Paybox\Message\DirectPurchaseRequest', $request);
        $this->assertSame('10.00', $request->getAmount());
    }

    public function testCompletePurchaseSend()
    {
        $request = $this->gateway->purchase([
            'amount' => '10.00',
            'currency' => 'USD',
            'card' => [
                'firstName' => 'Pokemon',
                'lastName' => 'The second',
                'email' => 'test@paybox.com',
            ]
        ])->send();

        $this->assertInstanceOf('Omnipay\Paybox\Message\DirectResponse', $request);
        $this->assertTrue($request->isTransparentRedirect());
    }
}
