<?php
use PHPUnit\Framework\TestCase;
use App\Entity\OAuth2\AccessToken;

class AccessTokenTest extends TestCase
{
    public function testGetAndSetIdentifier()
    {
        $accessToken = new AccessToken();
        $accessToken->setIdentifier('test_identifier');
        $this->assertEquals('test_identifier', $accessToken->getIdentifier());
    }

    public function testGetAndSetExpiry()
    {
        $accessToken = new AccessToken();
        $expiry = new \DateTime('2023-12-31');
        $accessToken->setExpiry($expiry);
        $this->assertEquals($expiry, $accessToken->getExpiry());
    }

    public function testGetAndSetClientIdentifier()
    {
        $accessToken = new AccessToken();
        $accessToken->setClientIdentifier('client_123');
        $this->assertEquals('client_123', $accessToken->getClientIdentifier());
    }

    public function testGetAndSetScopes()
    {
        $accessToken = new AccessToken();
        $scopes = ['scope1', 'scope2'];
        $accessToken->setScopes($scopes);
        $this->assertEquals($scopes, $accessToken->getScopes());
    }

    public function testGetAndSetUserIdentifier()
    {
        $accessToken = new AccessToken();
        $accessToken->setUserIdentifier(123);
        $this->assertEquals(123, $accessToken->getUserIdentifier());

        $accessToken->setUserIdentifier(null);
        $this->assertNull($accessToken->getUserIdentifier());
    }

    public function testIdentifierFormat()
    {
        $accessToken = new AccessToken();
        $identifier = str_repeat('a', 100); // Create a string with 100 'a' characters
        $accessToken->setIdentifier($identifier);
        $this->assertEquals($identifier, $accessToken->getIdentifier());
        $this->assertIsString($accessToken->getIdentifier());
        $this->assertEquals(100, strlen($accessToken->getIdentifier()));
    }

    public function testTokenExpiryAfter14Days()
    {
        $accessToken = new AccessToken();
        $expiry = new \DateTime();
        $expiry->modify('+14 days');
        $accessToken->setExpiry($expiry);
        $this->assertEquals($expiry, $accessToken->getExpiry());
    }
}