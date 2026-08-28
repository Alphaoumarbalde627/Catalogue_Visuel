<?php

namespace App\Controller;

use App\Form\LoginFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Form\ForgotPasswordFormType;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Security\PasswordResetService;
use App\Form\ResetPasswordFormType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\PasswordResetTokenRepository;
use Doctrine\ORM\EntityManagerInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginFormType::class, null, [
            'action' => $this->generateUrl('app_login'),
        ]);
        $form->get('email')->setData($lastUsername);
    
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'login_form' => $form->createView(),
            'recaptcha_site_key' => $_ENV['RECAPTCHA_SITE_KEY'],
        ]);
    }

    #[Route(path: '/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(
        Request $request,
        UserRepository $userRepository,
        PasswordResetService $passwordResetService
    ): Response {
        $form = $this->createForm(ForgotPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $email = $form->get('email')->getData();

            $user = $userRepository->findOneBy([
                'email' => $email,
            ]);

            if ($user) {
                 $passwordResetService->createToken($user);
            }

            $this->addFlash(
                'success',
                'Un lien de réinitialisation vous a été envoyé.'
            );

            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/forgot_password.html.twig', [
            'form' => $form->createView(),
        ]);
    } 
    
    #[Route(path: '/reset-password/{token}',name: 'app_reset_password')]
    public function resetPassword(
        string $token,
        Request $request,
        PasswordResetTokenRepository $passwordResetTokenRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // Recherche du token dans la base de données
        $passwordResetToken = $passwordResetTokenRepository->findOneBy([
            'token' => $token,
        ]);

        // Token inexistant
        if (!$passwordResetToken) {
            $this->addFlash(
                'error',
                'Le lien de réinitialisation est invalide.'
            );

            return $this->redirectToRoute('app_forgot_password');
        }

        // Token expiré
        if ($passwordResetToken->getExpiresAt() < new \DateTimeImmutable()) {
            // Suppression du token expiré
            $entityManager->remove($passwordResetToken);
            $entityManager->flush();

            $this->addFlash(
                'error',
                'Le lien de réinitialisation a expiré. Veuillez effectuer une nouvelle demande.'
            );

            return $this->redirectToRoute('app_forgot_password');
        }

        // Récupération de l'utilisateur associé au token
        $user = $passwordResetToken->getUser();

        // Vérification de l'utilisateur
        if (!$user) {
            $entityManager->remove($passwordResetToken);
            $entityManager->flush();

            $this->addFlash(
                'error',
                'Impossible de trouver le compte associé à ce lien.'
            );

            return $this->redirectToRoute('app_forgot_password');
        }

        // Création du formulaire
        $form = $this->createForm(ResetPasswordFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $form->get('password')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            // Vérification de la confirmation du mot de passe
            if ($password !== $confirmPassword) {
                $this->addFlash(
                    'error',
                    'Les deux mots de passe ne correspondent pas.'
                );

                return $this->render('security/reset_password.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            // Hashage du nouveau mot de passe
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $password
            );

            $user->setPassword($hashedPassword);

            // Suppression du token pour empêcher sa réutilisation
            $entityManager->remove($passwordResetToken);

            // Enregistrement du nouveau mot de passe
            $entityManager->flush();

            // Message de succès
            $this->addFlash(
                'success',
                'Votre mot de passe a été réinitialisé avec succès.'
            );

            // Retour vers la connexion
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route(path: '/admin/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

}