<?php 
use PHPUnit\Framework\TestCase;
use App\Entity\Vehicle;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VehicleIntegrationTest extends WebTestCase
{
    public function testGetVehicle()
    {
        $client = static::createClient();
        $client->request('GET', '/vehicles/1');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testGetVehiclesCollection()
    {
        $client = static::createClient();
        $client->request('GET', '/vehicles');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testCreateVehicle()
    {
        $client = static::createClient();
        $client->request('POST', '/vehicles', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'externalId' => 123,
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'mileage' => 15000,
            'fuelType' => 'Petrol',
            'transmission' => 'Automatic',
            'color' => 'Red',
            'doorCount' => 4,
            'price' => '15000.00',
            'availability' => true,
            'images' => ['image1.jpg', 'image2.jpg'],
            'createdAt' => (new \DateTime())->format('Y-m-d H:i:s'),
            'updateAt' => (new \DateTime())->format('Y-m-d H:i:s')
        ]));

        $this->assertEquals(201, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testUpdateVehicle()
    {
        $client = static::createClient();
        $client->request('PUT', '/vehicles/1', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([
            'brand' => 'Updated Brand'
        ]));

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testDeleteVehicle()
    {
        $client = static::createClient();
        $client->request('DELETE', '/vehicles/1');

        $this->assertEquals(204, $client->getResponse()->getStatusCode());
    }
}