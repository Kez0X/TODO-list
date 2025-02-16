<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    // Injection des dépendances : EntityManager pour la base de données et PasswordHasher pour le hashage du mot de passe
    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    // Configure la commande, ici on définit son nom et sa description
    protected function configure(): void
    {
        $this->setName('app:create-user')
            ->setDescription('Créer un nouvel utilisateur');
    }

    // Exécution de la commande
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Demander l'email de l'utilisateur
        $email = $io->ask('Entrez l\'email de l\'utilisateur');
        if (!$email) {
            $io->error('L\'email est requis.');
            return Command::FAILURE;
        }

        // Demander le mot de passe de l'utilisateur
        $password = $io->askHidden('Entrez le mot de passe de l\'utilisateur');
        if (!$password) {
            $io->error('Le mot de passe est requis.');
            return Command::FAILURE;
        }

        // Création de l'utilisateur
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));  // Hash du mot de passe
        $user->setRoles(['ROLE_USER']);  // Définition du rôle par défaut

        // Sauvegarde de l'utilisateur en base de données
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Message de succès
        $io->success('L\'utilisateur a été créé avec succès !');

        return Command::SUCCESS;
    }
}
