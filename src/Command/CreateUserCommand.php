<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Créer un nouvel utilisateur',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $io->ask('Email de l\'utilisateur');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error("Email invalide : $email");
            return Command::FAILURE;
        }

        $name = $io->ask('Nom de l\'utilisateur (max 10 caractères)');
        if (strlen($name) > 10) {
            $io->error("Le nom doit faire maximum 10 caractères.");
            return Command::FAILURE;
        }

        $plainPassword = $io->askHidden('Mot de passe (ne s\'affichera pas)');
        if (!$plainPassword) {
            $io->error("Le mot de passe ne peut pas être vide.");
            return Command::FAILURE;
        }

        $rolesInput = $io->ask('Rôles (séparés par des virgules, ex: ROLE_ADMIN,ROLE_MANAGER)', 'ROLE_USER');

        $user = new User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setRoles(array_map('trim', explode(',', $rolesInput)));
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $plainPassword)
        );

        $this->em->persist($user);
        $this->em->flush();

        $io->success("Nouvel utilisateur créé avec succès : {$user->getEmail()}");

        return Command::SUCCESS;
    }
}
