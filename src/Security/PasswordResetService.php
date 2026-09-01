<?php

namespace App\Security;

use App\Entity\PasswordResetToken;
use App\Entity\User;
use App\Repository\PasswordResetTokenRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PasswordResetService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly PasswordResetTokenRepository $passwordResetTokenRepository,
    ) {
    }

    public function createToken(User $user): string
    {
        $activeTokens = $this->passwordResetTokenRepository->findActiveByUser($user);

        foreach ($activeTokens as $activeToken) {
            $this->entityManager->remove($activeToken);
        }

        $token = bin2hex(random_bytes(32));

        $passwordResetToken = new PasswordResetToken();

        $passwordResetToken->setToken($token);
        $passwordResetToken->setUser($user);
        $passwordResetToken->setExpiresAt(
            new \DateTimeImmutable('+1 hour')
        );

        $this->entityManager->persist($passwordResetToken);
        $this->entityManager->flush();

        $resetUrl = $this->urlGenerator->generate(
            'app_reset_password',
            ['token' => $token],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new Email())
            ->from('boatech38@gmail.com')
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->html("
                <h2>Réinitialisation du mot de passe</h2>
                <p>Vous avez demandé à réinitialiser votre mot de passe.</p>
                <p>Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe :</p>
                <p>
                    <a href=\"{$resetUrl}\">
                        Réinitialiser mon mot de passe
                    </a>
                </p>
                <p>Ce lien est valable pendant 1 heure.</p>
                <p>Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet email.</p>
            ");

        $this->mailer->send($email);

        return $token;
    }
}