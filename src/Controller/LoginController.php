<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use App\Service\OAuth2LoginService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoginController extends AbstractController
{
    #[Route('/connexion', name: 'app_login')]
    public function login(
        Request $request,
        EntityManagerInterface $entityManager,
        OAuth2LoginService $loginService,
        SessionInterface $session
    ): Response {
        $form = $this->createForm(LoginFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $email = $data['email'];
            $password = $data['password'];

            try {
                $accessToken = $loginService->login($email, $password);

                $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
                if (!$user) {
                    throw new \Exception("Utilisateur non trouvé.");
                }

                $this->addFlash('success', 'Connexion réussie.');

                $session->set('api_token', $accessToken);

                return $this->redirectToRoute('app_home');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('login/login.html.twig', [
            'loginForm' => $form->createView(),
        ]);
    }
}
