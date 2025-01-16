<?php 
use PHPUnit\Framework\TestCase;
use App\Entity\Product;

class ProductTest extends TestCase
{
    public function testGetAndSetId()
    {
        $product = new Product();
        $this->assertNull($product->getId());
    }

    public function testGetAndSetExternalId()
    {
        $product = new Product();
        $product->setExternalId(123);
        $this->assertEquals(123, $product->getExternalId());
    }

    public function testGetAndSetName()
    {
        $product = new Product();
        $product->setName('Test Product');
        $this->assertEquals('Test Product', $product->getName());
    }

    public function testGetAndSetDescription()
    {
        $product = new Product();
        $product->setDescription('This is a test product.');
        $this->assertEquals('This is a test product.', $product->getDescription());
    }

    public function testGetAndSetPrice()
    {
        $product = new Product();
        $product->setPrice(99.99);
        $this->assertEquals(99.99, $product->getPrice());
    }

    public function testGetAndSetStock()
    {
        $product = new Product();
        $product->setStock(50);
        $this->assertEquals(50, $product->getStock());
    }

    public function testGetAndSetImages()
    {
        $product = new Product();
        $images = ['image1.jpg', 'image2.jpg'];
        $product->setImages($images);
        $this->assertEquals($images, $product->getImages());
    }

    public function testGetAndSetCreatedAt()
    {
        $product = new Product();
        $createdAt = new \DateTime();
        $product->setCreatedAt($createdAt);
        $this->assertEquals($createdAt, $product->getCreatedAt());
    }

    public function testGetAndSetUpdateAt()
    {
        $product = new Product();
        $updateAt = new \DateTime();
        $product->setUpdateAt($updateAt);
        $this->assertEquals($updateAt, $product->getUpdateAt());
    }

    public function testGetAndSetStripePaymentId()
    {
        $product = new Product();
        $product->setStripePaymentId(456);
        $this->assertEquals(456, $product->getStripePaymentId());
    }

    public function testGetAndSetPaymentStatus()
    {
        $product = new Product();
        $product->setPaymentStatus('Paid');
        $this->assertEquals('Paid', $product->getPaymentStatus());
    }

    public function testGetAndSetTotalAmount()
    {
        $product = new Product();
        $product->setTotalAmount('150.00');
        $this->assertEquals('150.00', $product->getTotalAmount());
    }
}