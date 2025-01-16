<?php
use PHPUnit\Framework\TestCase;
use App\Entity\OAuth2\AccessToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AccessTokenWebTest extends WebTestCase
{
    public function testAuthenticationAndRetrieveAccessToken()
    {
        $client = static::createClient();
        $client->request('POST', '/oauth2/token', [
            'grant_type' => 'password',
            'client_id' => 'client_123',
            'client_secret' => 'secret',
            'username' => 'user',
            'password' => 'password',
        ]);

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('access_token', $data);
        $this->assertNotEmpty($data['access_token']);

        return $data['access_token'];
    }

    /**
     * @depends testAuthenticationAndRetrieveAccessToken
     */
    public function testValidateAccessToken(string $accessToken)
    {
        $client = static::createClient();
        $client->request('GET', '/api/resource', [], [], [
            'HTTP_Authorization' => 'Bearer ' . $accessToken,
        ]);

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }
}