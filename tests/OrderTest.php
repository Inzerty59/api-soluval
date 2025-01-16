<?php 
use PHPUnit\Framework\TestCase;
use App\Entity\Order;
use App\Entity\User;
use App\Entity\Product;

class OrderTest extends TestCase
{
    public function testGetId()
    {
        $order = new Order();
        $this->assertNull($order->getId());
    }

    public function testSetAndGetExternalId()
    {
        $order = new Order();
        $order->setExternalId(123);
        $this->assertEquals(123, $order->getExternalId());
    }

    public function testSetAndGetAmount()
    {
        $order = new Order();
        $order->setAmount(99.99);
        $this->assertEquals(99.99, $order->getAmount());
    }

    public function testSetAndGetStatus()
    {
        $order = new Order();
        $order->setStatus('Pending');
        $this->assertEquals('Pending', $order->getStatus());
    }

    public function testSetAndGetInvoicePath()
    {
        $order = new Order();
        $order->setInvoicePath('/path/to/invoice');
        $this->assertEquals('/path/to/invoice', $order->getInvoicePath());
    }

    public function testSetAndGetCreatedAt()
    {
        $order = new Order();
        $date = new \DateTime();
        $order->setCreatedAt($date);
        $this->assertEquals($date, $order->getCreatedAt());
    }

    public function testSetAndGetUpdatedAt()
    {
        $order = new Order();
        $date = new \DateTime();
        $order->setUpdatedAt($date);
        $this->assertEquals($date, $order->getUpdatedAt());
    }

    public function testSetAndGetUser()
    {
        $order = new Order();
        $user = new User();
        $order->setUser($user);
        $this->assertEquals($user, $order->getUser());
    }

    public function testAddAndRemoveProduct()
    {
        $order = new Order();
        $product = new Product();
        
        $order->addProduct($product);
        $this->assertTrue($order->getProducts()->contains($product));
        
        $order->removeProduct($product);
        $this->assertFalse($order->getProducts()->contains($product));
    }
}