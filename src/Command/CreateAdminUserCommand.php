<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:create-admin-user',
    description: 'Créer un premier utilisateur administrateur depuis le terminal.',
)]
class CreateAdminUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('email', InputArgument::REQUIRED, 'Adresse e-mail du compte administrateur.');
        $this->addArgument('password', InputArgument::REQUIRED, 'Mot de passe du compte administrateur.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = mb_strtolower(trim((string) $input->getArgument('email')));
        $plainPassword = (string) $input->getArgument('password');

        if ($this->userRepository->findOneBy(['email' => $email])) {
            $output->writeln('<error>Un compte avec cette adresse e-mail existe déjà.</error>');

            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($plainPassword);

        $violations = $this->validator->validate($user);

        if (count($violations) > 0) {
            $output->writeln('<error>Le mot de passe ou l’adresse e-mail est invalide :</error>');

            foreach ($violations as $violation) {
                $output->writeln(sprintf(' - %s', $violation->getMessage()));
            }

            return Command::FAILURE;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(sprintf(
            '<info>Compte administrateur créé avec succès : %s</info>',
            $user->getEmail(),
        ));

        return Command::SUCCESS;
    }
}
