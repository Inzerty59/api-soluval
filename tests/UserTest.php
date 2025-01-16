<?php 

use PHPUnit\Framework\TestCase;
use App\Entity\User;
use Symfony\Component\Uid\Uuid;

class UserTest extends TestCase
{
    public function testUserConstructor()
    {
        $user = new User();
        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->getCreatedAt());
        $this->assertNotNull($user->getUpdatedAt());
        $this->assertNotNull($user->getClientId());
        $this->assertNotNull($user->getSecretId());
        $this->assertEquals('ROLE_USER', $user->getRoles()[0]);
    }

    public function testSetName()
    {
        $user = new User();
        $user->setName('John');
        $this->assertEquals('John', $user->getName());
    }

    public function testSetSurname()
    {
        $user = new User();
        $user->setSurname('Doe');
        $this->assertEquals('Doe', $user->getSurname());
    }

    public function testSetEmail()
    {
        $user = new User();
        $user->setEmail('john.doe@example.com');
        $this->assertEquals('john.doe@example.com', $user->getEmail());
    }

    public function testSetPassword()
    {
        $user = new User();
        $user->setPassword('password123');
        $this->assertEquals('password123', $user->getPassword());
    }

    public function testSetRoles()
    {
        $user = new User();
        $roles = ['ROLE_ADMIN'];
        $user->setRoles($roles);
        $this->assertEquals($roles, $user->getRoles());
    }

    public function testSetAccountType()
    {
        $user = new User();
        $user->setAccountType('premium');
        $this->assertEquals('premium', $user->getAccountType());
    }

    public function testSetCompanyName()
    {
        $user = new User();
        $user->setCompanyName('Acme Corp');
        $this->assertEquals('Acme Corp', $user->getCompanyName());
    }

    public function testSetSiretNumber()
    {
        $user = new User();
        $user->setSiretNumber('12345678901234');
        $this->assertEquals('12345678901234', $user->getSiretNumber());
    }

    public function testSetVatNumber()
    {
        $user = new User();
        $user->setVatNumber('FR12345678901');
        $this->assertEquals('FR12345678901', $user->getVatNumber());
    }

    public function testRefreshApiToken()
    {
        $user = new User();
        $user->refreshApiToken();
        $this->assertNotNull($user->getApiToken());
        $this->assertNotNull($user->getTokenExpiresAt());
    }

    public function testSetTokenExpiresAt()
    {
        $user = new User();
        $date = new \DateTime();
        $user->setTokenExpiresAt($date);
        $this->assertEquals($date, $user->getTokenExpiresAt());
    }

    public function testPasswordHashing()
    {
        $user = new User();
        $plainPassword = 'password123';
        $user->setPassword(password_hash($plainPassword, PASSWORD_BCRYPT));
        $this->assertTrue(password_verify($plainPassword, $user->getPassword()));
    }

    public function testProfessionalAttributes()
    {
        $user = new User();
        $user->setAccountType('professional');
        $user->setCompanyName('Acme Corp');
        $user->setSiretNumber('12345678901234');
        $user->setVatNumber('FR12345678901');
        
        $this->assertEquals('professional', $user->getAccountType());
        $this->assertEquals('Acme Corp', $user->getCompanyName());
        $this->assertEquals('12345678901234', $user->getSiretNumber());
        $this->assertEquals('FR12345678901', $user->getVatNumber());
    }

    public function testNonProfessionalAttributes()
    {
        $user = new User();
        $user->setAccountType('standard');
        
        $this->assertEquals('standard', $user->getAccountType());
        $this->assertNull($user->getCompanyName());
        $this->assertNull($user->getSiretNumber());
        $this->assertNull($user->getVatNumber());
    }

    public function testProfessionalAttributes2()
    {
        $user = new User();
        $user->setCompanyName('Acme Corp');
        $user->setSiretNumber('12345678901234');
        $user->setVatNumber('FR12345678901');
        
        $this->assertEquals('Acme Corp', $user->getCompanyName());
        $this->assertEquals('12345678901234', $user->getSiretNumber());
        $this->assertEquals('FR12345678901', $user->getVatNumber());
    }
}