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
                'Ref' => 601957,
                'Erreur' => '00000',
                'sign' => 'Lw%2FQgRrw570C1F2pOU%2B8h2INbTeBEkodjcec%2Fvpnrb4EyHBI4JPlnBHnx3CTdZIDr8HSQgAvuyowijKLG7UlFc6dOuLrN3MgcDnbIBx0dHtMc6%2Bojacp3LR0AbZI2hd73HeZGUM8i0xCkj9IQGjGyNnvqLxuvskQrKVtAKo6JiU%3D',
            ]
        );

        $this->assertTrue($response->isSuccessful());
        $this->assertSame(601957, $response->getTransactionReference());
        $this->assertSame(47, $response->getTransactionId());
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
        $this->assertNull($response->getTransactionReference());
        $this->assertSame('Transaction failed', $response->getMessage());
    }


    /**
     * @throws InvalidResponseException
     */
    public function testMissingSign()
    {
        $this->expectException(\Omnipay\Common\Exception\InvalidResponseException::class);

        $response = new SystemCompleteAuthorizeResponse(
            $this->getMockRequest(),
            [
                'Mt' => 100,
                'Id' => 47,
                'idtrans' => 601957,
                'Erreur' => '00000',
            ]
        );

        $this->assertFalse($response->isSuccessful());
        $this->assertSame(47, $response->getTransactionId());
        $this->assertSame(601957, $response->getTransactionReference());
        $this->assertSame('Unsigned response', $response->getMessage());
    }


    /**
     * @throws InvalidResponseException
     */
    public function testInvalidSign()
    {
        $this->expectException(\Omnipay\Common\Exception\InvalidResponseException::class);

        $response = new SystemCompleteAuthorizeResponse(
            $this->getMockRequest(),
            [
                'Mt' => 100,
                'Id' => 47,
                'idtrans' => 601957,
                'Erreur' => '00000',
                'sign' => 'opPlzAadVvCor99yZ8oj2NHmE0eAxXkmCZ80C%2BYW8htpF7Wf6krYYFjc1pQnvYHcW7vp3ta3p8Gfh7gAaR6WDOnhe1Xzm39whk11%2BShieXbQCnEKXot4aGkpodxi1cHutXBhh1IBQOLgq1IVM%2BaV9PUeTI%2FGFruSDnA1TExDHZE%3D',
            ]
        );

        $this->assertFalse($response->isSuccessful());
        $this->assertSame(47, $response->getTransactionId());
        $this->assertSame(601957, $response->getTransactionReference());
        $this->assertSame('Signature is invalid.', $response->getMessage());
    }
}


class SystemCompleteAuthorizeResponseLocalKey extends SystemCompleteAuthorizeResponse
{
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
