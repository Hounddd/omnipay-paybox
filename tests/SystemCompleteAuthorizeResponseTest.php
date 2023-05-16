<?php

namespace Omnipay\Paybox\Message;

use Omnipay\Tests\TestCase;

class SystemCompleteAuthorizeResponseTest extends TestCase
{
    public function testSuccess()
    {
        $response = new SystemCompleteAuthorizeResponseLocalKey(
            $this->getMockRequest(),
            [
               'Mt' => 100,
               'Id' => 47,
               'idtrans' => 601957,
               'Erreur' => '00000',
               'sign' => 'TVw83RDN4Vbji%2BCkh9djB8ezLIJqDsZmNuliCTrfHiBdK1Frfr5fpY9COixHpzmksT37oGl3ifwoZTL8%2FxLaC9Sk0pArMKRtNPWTY8Ubj82S8vgrxEEDdXOA9aykfjjldfG5e1xzHd3z8dAyjd5gs7DlvQWD1mqXqnOQ6BqzcBQ%3D',
            ]
        );

        $this->assertTrue($response->isSuccessful());
        $this->assertSame(601957, $response->getTransactionReference());
        $this->assertNull($response->getMessage());
    }

    public function testFailure()
    {
        $response = new SystemCompleteAuthorizeResponse(
            $this->getMockRequest(),
            [
                'Mt' => 100,
                'Id' => 45,
                'Erreur' => '00114',
                'sign' => 'opPlzAadVvCor99yZ8oj2NHmE0eAxXkmCZ80C%2BYW8htpF7Wf6krYYFjc1pQnvYHcW7vp3ta3p8Gfh7gAaR6WDOnhe1Xzm39whk11%2BShieXbQCnEKXot4aGkpodxi1cHutXBhh1IBQOLgq1IVM%2BaV9PUeTI%2FGFruSDnA1TExDHZE%3D',
            ]
        );

        $this->assertFalse($response->isSuccessful());
        $this->assertSame(45, $response->getTransactionId());
        $this->assertSame('Transaction failed', $response->getMessage());
    }
}

class SystemCompleteAuthorizeResponseLocalKey extends SystemCompleteAuthorizeResponse {

    /**
     * Get local public key file path.
     *
     * @return string
     *  Full path to local public key for testing
     */
    protected function getPublicKey()
    {
        return  __DIR__ . '/../src/Resources/tests/test_pubkey.pem';
    }

}
