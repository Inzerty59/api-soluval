<?php

namespace App\Twig;

use App\Controller\Shop\CartController;
use Symfony\Component\HttpFoundation\Session\SessionFactoryInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CartExtension extends AbstractExtension
{
    private $cartController;
    private $sessionFactory;

    public function __construct(CartController $cartController, SessionFactoryInterface $sessionFactory)
    {
        $this->cartController = $cartController;
        $this->sessionFactory = $sessionFactory;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('cart_count', [$this, 'getCartCount']),
        ];
    }

    public function getCartCount(): int
    {
        $session = $this->sessionFactory->createSession();
        return $this->cartController->getCartCount($session);
    }
}