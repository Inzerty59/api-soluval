<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\SiretVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    private $siretVerificationService;

    public function __construct(SiretVerificationService $siretVerificationService)
    {
        $this->siretVerificationService = $siretVerificationService;
    }

    /**
     * @Route("/mes-informations", name="user_informations")
     */
    public function informations(UserInterface $user): Response
    {
        return $this->render('user/informations.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route(path: '/mes-commandes', name: 'user_orders')]
    public function orders(UserInterface $user): Response
    {
        return $this->render('user/orders.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/update-informations", name="user_update_informations", methods={"POST"})
     */
    public function updateInformations(Request $request, EntityManagerInterface $entityManager, UserInterface $user, ValidatorInterface $validator, MailerInterface $mailer): Response
    {
        $surname = $request->request->get('surname');
        $name = $request->request->get('name');
        $email = $request->request->get('email');
        $companyName = $request->request->get('companyName');
        $siretNumber = $request->request->get('siretNumber');
        $vatNumber = $request->request->get('vatNumber');

        $errors = [];
        $hasChanges = false;

        // Validate surname
        $surnameConstraint = new Assert\NotBlank(['message' => 'Le nom ne doit pas être vide.']);
        $surnameErrors = $validator->validate($surname, $surnameConstraint);
        if (count($surnameErrors) > 0) {
            $errors[] = $surnameErrors[0]->getMessage();
        } elseif ($user->getSurname() !== $surname) {
            $user->setSurname($surname);
            $hasChanges = true;
        }

        // Validate name
        $nameConstraint = new Assert\NotBlank(['message' => 'Le prénom ne doit pas être vide.']);
        $nameErrors = $validator->validate($name, $nameConstraint);
        if (count($nameErrors) > 0) {
            $errors[] = $nameErrors[0]->getMessage();
        } elseif ($user->getName() !== $name) {
            $user->setName($name);
            $hasChanges = true;
        }

        // Validate email
        $emailConstraints = [
            new Assert\NotBlank(['message' => 'L\'adresse email ne doit pas être vide.']),
            new Assert\Email(['message' => 'L\'adresse email n\'est pas valide.'])
        ];
        $emailErrors = $validator->validate($email, $emailConstraints);
        if (count($emailErrors) > 0) {
            $errors[] = $emailErrors[0]->getMessage();
        } elseif ($user->getEmail() !== $email) {
            $user->setEmail($email);
            $hasChanges = true;
        }

        if ($user->getAccountType() == 'professionnel') {
            if (!empty($companyName) && $user->getCompanyName() !== $companyName) {
                $user->setCompanyName($companyName);
                $hasChanges = true;
            }

            $siretConstraint = new Assert\Regex([
                'pattern' => '/^\d{14}$/',
                'message' => 'Le numéro SIRET doit contenir exactement 14 chiffres.',
            ]);
            $siretErrors = $validator->validate($siretNumber, $siretConstraint);
            if (count($siretErrors) > 0) {
                $errors[] = $siretErrors[0]->getMessage();
            } elseif ($user->getSiretNumber() !== $siretNumber) {
                if (!$this->siretVerificationService->verifySiret($siretNumber)) {
                    $errors[] = 'Le numéro SIRET est invalide.';
                } else {
                    $user->setSiretNumber($siretNumber);
                    $hasChanges = true;
                }
            }

            $vatConstraint = new Assert\Regex([
                'pattern' => '/^[A-Z]{2}[A-Z0-9]{2,12}$/',
                'message' => 'Le numéro de TVA doit commencer par deux lettres suivies de 2 à 12 caractères alphanumériques.',
            ]);
            $vatErrors = $validator->validate($vatNumber, $vatConstraint);
            if (count($vatErrors) > 0) {
                $errors[] = $vatErrors[0]->getMessage();
            } elseif ($user->getVatNumber() !== $vatNumber) {
                $user->setVatNumber($vatNumber);
                $hasChanges = true;
            }
        }

        if (count($errors) > 0) {
            return $this->render('user/informations.html.twig', [
                'user' => $user,
                'errors' => $errors,
            ]);
        }

        if ($hasChanges) {
            $entityManager->persist($user);
            $entityManager->flush();

            $dsn = $_ENV['MAILER_DSN'];
            $transport = Transport::fromDsn($dsn);
            $mailer = new Mailer($transport);

            $email = (new Email())
                ->from('florent.devynck@groupevitaminet.com')
                ->to($user->getEmail())
                ->subject('Mise à jour de vos informations')
                ->text('Vos informations ont été mises à jour avec succès.');

            $mailer->send($email);

            $this->addFlash('success', 'Vos informations ont été mises à jour avec succès.');
        }

        return $this->redirectToRoute('user_informations');
    }

    /**
     * @Route("/update-password", name="user_update_password", methods={"POST"})
     */
    public function updatePassword(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, UserInterface $user, MailerInterface $mailer): Response
    {
        $newPassword = $request->request->get('new_password');
        $confirmPassword = $request->request->get('confirm_password');

        if ($newPassword !== $confirmPassword) {
            $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
            return $this->redirectToRoute('user_informations');
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $newPassword)) {
            $this->addFlash('error', 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.');
            return $this->redirectToRoute('user_informations');
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($hashedPassword);
        $entityManager->flush();

        $dsn = $_ENV['MAILER_DSN'];
        $transport = Transport::fromDsn($dsn);
        $mailer = new Mailer($transport);

        $email = (new Email())
            ->from('florent.devynck@groupevitaminet.com')
            ->to($user->getEmail())
            ->subject('Mise à jour de votre mot de passe')
            ->text('Votre mot de passe a été mis à jour avec succès.');

        $mailer->send($email);

        $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès.');

        return $this->redirectToRoute('user_informations');
    }
}