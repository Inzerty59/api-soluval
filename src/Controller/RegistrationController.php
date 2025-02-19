<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Form\FormError;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use App\Service\SiretVerificationService;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        SiretVerificationService $siretVerificationService
    ): Response {
        $user = new User();

        $form = $this->createForm(RegistrationFormType::class, $user, [
            'siret_verification_service' => $siretVerificationService,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
                $form->get('email')->addError(new FormError('Cette adresse e-mail est déjà utilisée.'));
            } else {
                $plainPassword = $form->get('password')->getData();
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);

                $entityManager->persist($user);
                $entityManager->flush();

                $dsn = $_ENV['MAILER_DSN'];
                $transport = Transport::fromDsn($dsn);
                $mailer = new Mailer($transport);

                $email = (new Email())
                    ->from('florent.devynck@groupevitaminet.com')
                    ->to($user->getEmail())
                    ->subject('Confirmation de création de compte')
                    ->text('Votre compte a bien été créé.');

                $mailer->send($email);

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
