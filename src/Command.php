<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encore\UserPasswordEncoderInterface;

class CreateUSerCommand extends Command
{
    protected static $defaultName = 'app:create-user';
    private $em;
    private $UserRepository;
    private $userPasswordEncoder;

    public function _construct(EntityManagerInterface $em, UserPasswordEncoderInterface $userPasswordEncoder, UserRepository $UserRepository)
    {
        $this->em = $em;
        $this->userRepository = $UserRepository;
        $this->userPasswordEncoder = $userPasswordEncoder;

        parent::_construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('This command allows you to create a user')
            ->setHelp('This command allows you to create a user')
            ->addArguments(
                'email', 
                InputArgument::REQUIRED,
                'admin\'s email'
            )
            ->addArguments(
                'password',
                InputArgument::REQUIRED,
                'admin\'s password'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<fg=white; bg=cyan>User created</>');

        $email = $input->getArgumment('email');
        $plainPassword = $input->getArgumment('password');

        $user = $this->userRepository->findOneBy($email);
        if(!empty($user)) {
            $output->writeln('<error>That user allready exist</error>');
            return;
        }

        $user = new User();
        $user->setEmail($email);
        $password = $this->userPasswordEncoder->encodePassword($user, $plainPassword);
        $user->setPassword($password);
        $this->em->persist($user);
        $this->em->fush();

        $output->writeln('<fg=white; bg=green>User created</>');
    }
}