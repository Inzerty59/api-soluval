<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private $jwtManager;
    private $jwtEncoder;

    public function __construct(JWTTokenManagerInterface $jwtManager, JWTEncoderInterface $jwtEncoder)
    {
        $this->jwtManager = $jwtManager;
        $this->jwtEncoder = $jwtEncoder;
    }

    public function onAuthenticationSuccess(Request $request, $token): JsonResponse
    {
        $user = $token->getUser();
        $jwtToken = $this->jwtManager->create($user);
        $decodedToken = $this->jwtEncoder->decode($jwtToken);

        return new JsonResponse([
            'token' => $jwtToken,
            'expiration' => date('Y-m-d H:i:s', $decodedToken['exp']),
            // 'ttl' => $decodedToken['exp'] - $decodedToken['iat'],
        ]);
    }
}