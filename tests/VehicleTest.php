<?php 
use PHPUnit\Framework\TestCase;
use App\Entity\Vehicle;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class VehicleTest extends TestCase
{
    public function testGetAndSetId()
    {
        $vehicle = new Vehicle();
        $this->assertNull($vehicle->getId());
    }

    public function testGetAndSetExternalId()
    {
        $vehicle = new Vehicle();
        $vehicle->setExternalId(123);
        $this->assertEquals(123, $vehicle->getExternalId());
    }

    public function testGetAndSetBrand()
    {
        $vehicle = new Vehicle();
        $vehicle->setBrand('Toyota');
        $this->assertEquals('Toyota', $vehicle->getBrand());
    }

    public function testGetAndSetModel()
    {
        $vehicle = new Vehicle();
        $vehicle->setModel('Corolla');
        $this->assertEquals('Corolla', $vehicle->getModel());
    }

    public function testGetAndSetYear()
    {
        $vehicle = new Vehicle();
        $vehicle->setYear(2020);
        $this->assertEquals(2020, $vehicle->getYear());
    }

    public function testGetAndSetMileage()
    {
        $vehicle = new Vehicle();
        $vehicle->setMileage(15000);
        $this->assertEquals(15000, $vehicle->getMileage());
    }

    public function testGetAndSetFuelType()
    {
        $vehicle = new Vehicle();
        $vehicle->setFuelType('Petrol');
        $this->assertEquals('Petrol', $vehicle->getFuelType());
    }

    public function testGetAndSetTransmission()
    {
        $vehicle = new Vehicle();
        $vehicle->setTransmission('Automatic');
        $this->assertEquals('Automatic', $vehicle->getTransmission());
    }

    public function testGetAndSetColor()
    {
        $vehicle = new Vehicle();
        $vehicle->setColor('Red');
        $this->assertEquals('Red', $vehicle->getColor());
    }

    public function testGetAndSetDoorCount()
    {
        $vehicle = new Vehicle();
        $vehicle->setDoorCount(4);
        $this->assertEquals(4, $vehicle->getDoorCount());
    }

    public function testGetAndSetPrice()
    {
        $vehicle = new Vehicle();
        $vehicle->setPrice('15000.00');
        $this->assertEquals('15000.00', $vehicle->getPrice());
    }

    public function testIsAndSetAvailability()
    {
        $vehicle = new Vehicle();
        $vehicle->setAvailability(true);
        $this->assertTrue($vehicle->isAvailability());
    }

    public function testGetAndSetImages()
    {
        $vehicle = new Vehicle();
        $images = ['image1.jpg', 'image2.jpg'];
        $vehicle->setImages($images);
        $this->assertEquals($images, $vehicle->getImages());
    }

    public function testGetAndSetCreatedAt()
    {
        $vehicle = new Vehicle();
        $date = new \DateTime();
        $vehicle->setCreatedAt($date);
        $this->assertEquals($date, $vehicle->getCreatedAt());
    }

    public function testGetAndSetUpdateAt()
    {
        $vehicle = new Vehicle();
        $date = new \DateTime();
        $vehicle->setUpdateAt($date);
        $this->assertEquals($date, $vehicle->getUpdateAt());
    }

    public function testInitialValues()
    {
        $vehicle = new Vehicle();
        $this->assertNull($vehicle->getId());
        $this->assertNull($vehicle->getExternalId());
        $this->assertNull($vehicle->getBrand());
        $this->assertNull($vehicle->getModel());
        $this->assertNull($vehicle->getYear());
        $this->assertNull($vehicle->getMileage());
        $this->assertNull($vehicle->getFuelType());
        $this->assertNull($vehicle->getTransmission());
        $this->assertNull($vehicle->getColor());
        $this->assertNull($vehicle->getDoorCount());
        $this->assertNull($vehicle->getPrice());
        $this->assertNull($vehicle->isAvailability());
        $this->assertEmpty($vehicle->getImages());
        $this->assertNull($vehicle->getCreatedAt());
        $this->assertNull($vehicle->getUpdateAt());
    }
}
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