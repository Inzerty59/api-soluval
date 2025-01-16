<?php 
use PHPUnit\Framework\TestCase;
use App\Entity\Part;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PartIntegrationTest extends WebTestCase
{    
public function testGetPart()
    {
        $client = static::createClient();
        $client->request('GET', '/parts/1');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testGetPartsCollection()
    {
        $client = static::createClient();
        $client->request('GET', '/parts');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testCreatePart()
    {
        $client = static::createClient();
        $client->request('POST', '/parts', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'externalId' => 123,
            'name' => 'Test Part',
            'description' => 'This is a test part.',
            'price' => '99.99',
            'availability' => true,
            'stockQuantity' => 50,
            'category' => 'Engine',
            'brand' => 'BrandX',
            'manufacturerPartNumber' => 'MPN12345',
            'vehicleCompatibility' => ['CarModel1', 'CarModel2'],
            'images' => ['image1.jpg', 'image2.jpg'],
            'createdAt' => (new \DateTime())->format('Y-m-d H:i:s'),
            'updatedAt' => (new \DateTime())->format('Y-m-d H:i:s')
        ]));

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testUpdatePart()
    {
        $client = static::createClient();
        $client->request('PUT', '/parts/1', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'name' => 'Updated Test Part'
        ]));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testDeletePart()
    {
        $client = static::createClient();
        $client->request('DELETE', '/parts/1');

        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }
}