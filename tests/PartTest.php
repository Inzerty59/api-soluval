<?php 
use PHPUnit\Framework\TestCase;
use App\Entity\Part;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PartTest extends TestCase
{
    public function testGetAndSetId()
    {
        $part = new Part();
        $this->assertNull($part->getId());
    }

    public function testGetAndSetExternalId()
    {
        $part = new Part();
        $part->setExternalId(123);
        $this->assertEquals(123, $part->getExternalId());
    }

    public function testGetAndSetName()
    {
        $part = new Part();
        $part->setName('Test Part');
        $this->assertEquals('Test Part', $part->getName());
    }

    public function testGetAndSetDescription()
    {
        $part = new Part();
        $part->setDescription('This is a test part.');
        $this->assertEquals('This is a test part.', $part->getDescription());
    }

    public function testGetAndSetPrice()
    {
        $part = new Part();
        $part->setPrice('99.99');
        $this->assertEquals('99.99', $part->getPrice());
    }

    public function testIsAndSetAvailability()
    {
        $part = new Part();
        $part->setAvailability(true);
        $this->assertTrue($part->isAvailability());
    }

    public function testGetAndSetStockQuantity()
    {
        $part = new Part();
        $part->setStockQuantity(50);
        $this->assertEquals(50, $part->getStockQuantity());
    }

    public function testGetAndSetCategory()
    {
        $part = new Part();
        $part->setCategory('Engine');
        $this->assertEquals('Engine', $part->getCategory());
    }

    public function testGetAndSetBrand()
    {
        $part = new Part();
        $part->setBrand('BrandX');
        $this->assertEquals('BrandX', $part->getBrand());
    }

    public function testGetAndSetManufacturerPartNumber()
    {
        $part = new Part();
        $part->setManufacturerPartNumber('MPN12345');
        $this->assertEquals('MPN12345', $part->getManufacturerPartNumber());
    }

    public function testGetAndSetVehicleCompatibility()
    {
        $part = new Part();
        $vehicleCompatibility = ['CarModel1', 'CarModel2'];
        $part->setVehicleCompatibility($vehicleCompatibility);
        $this->assertEquals($vehicleCompatibility, $part->getVehicleCompatibility());
    }

    public function testGetAndSetImages()
    {
        $part = new Part();
        $images = ['image1.jpg', 'image2.jpg'];
        $part->setImages($images);
        $this->assertEquals($images, $part->getImages());
    }

    public function testGetAndSetCreatedAt()
    {
        $part = new Part();
        $createdAt = new \DateTime();
        $part->setCreatedAt($createdAt);
        $this->assertEquals($createdAt, $part->getCreatedAt());
    }

    public function testGetAndSetUpdatedAt()
    {
        $part = new Part();
        $updatedAt = new \DateTime();
        $part->setUpdatedAt($updatedAt);
        $this->assertEquals($updatedAt, $part->getUpdatedAt());
    }
public function testInitialValues()
    {
    $part = new Part();
    $this->assertNull($part->getId());
    $this->assertNull($part->getExternalId());
    $this->assertNull($part->getName());
    $this->assertNull($part->getDescription());
    $this->assertNull($part->getPrice());
    $this->assertNull($part->isAvailability());
    $this->assertNull($part->getStockQuantity());
    $this->assertNull($part->getCategory());
    $this->assertNull($part->getBrand());
    $this->assertNull($part->getManufacturerPartNumber());
    $this->assertEmpty($part->getVehicleCompatibility());
    $this->assertEmpty($part->getImages());
    $this->assertNull($part->getCreatedAt());
    $this->assertNull($part->getUpdatedAt());
    }
}
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