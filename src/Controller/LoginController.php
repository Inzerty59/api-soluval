<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class LoginController extends AbstractController
{
    #[Route('/connexion', name: 'app_login')]
    public function login(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        SessionInterface $session
    ): Response {
        $form = $this->createForm(LoginFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $email = $data['email'];
            $password = $data['password'];

            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
                $this->addFlash('error', 'Identifiants incorrects.');
                return $this->redirectToRoute('app_login');
            }

            $clientHttp = HttpClient::create();
            $response = $clientHttp->request('POST', 'http://localhost:9000/api/login', [
                'json' => [
                    'grant_type' => 'password',
                    'client_id' => $user->getClientId(),
                    'client_secret' => $user->getSecretId(),
                    'username' => $email,
                    'password' => $password,
                    'scope' => 'email',
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $responseData = $response->toArray();
                $accessToken = $responseData['access_token'];

                $session->set('api_token', $accessToken);
                $user->setApiToken($accessToken);
                $user->setTokenExpiresAt((new \DateTime())->modify('+14 days'));
                $entityManager->flush();

                return $this->redirectToRoute('app_home');
            } else {
                $this->addFlash('error', 'Échec de la connexion à l\'API.');
            }
        }

        return $this->render('login/login.html.twig', [
            'loginForm' => $form->createView(),
        ]);
    }
}
