<?php

namespace App\Command;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateAdminCommand extends Command
{
    protected static $defaultName = 'app:create-admin';

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }
    protected function configure(): void
    {
        $this
            ->setDescription('Create a new admin')
            ->setHelp('This command allows you to create an admin...');
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'User email');
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'User name');
        $this
            ->addArgument('password', InputArgument::REQUIRED, 'User password');
        // $this
        //     ->addArgument('userType', InputArgument::REQUIRED, 'User type');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $email = $input->getArgument('email');
        $name = $input->getArgument('name');
        $password = $input->getArgument('password');
        // $user_type = $input->getArgument('userType');
        $user = new Admin();
        $user->setEmail($email);
        $user->setName($name);
        $user->setPassword(password_hash($password, PASSWORD_BCRYPT));
        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        // $user->set($user_type);
        $this->em->persist($user);
        $output->writeln('Admin created successfully!');

        return Command::SUCCESS;
    }
}
