<?php

namespace App\Command;

use App\Service\User\UserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class UserCreateCommand extends Command
{
    protected static $defaultName = 'user:create';

    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Create User')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $helper = $this->getHelper('question');
        $question = new Question('User [email]');
        $email = $helper->ask($input, $output, $question);

        $helper = $this->getHelper('question');
        $question = new Question('User [ROLE]');
        $role = $helper->ask($input, $output, $question);

        $helper = $this->getHelper('question');
        $question = new Question('User [password]');
        $password = $helper->ask($input, $output, $question);

        $helper = $this->getHelper('question');
        $question = new Question('User [profile name]');
        $profileName = $helper->ask($input, $output, $question);

        $user = $this->userService->createUser($email, $password, [$role]);
        $io->success('User added');

        if ($profileName) {
            if ($this->userService->attachProfile($user, $profileName)) {
                $io->success('Profile added');
            } else {
                $io->warning('Profile not found');
            }
        }

        return Command::SUCCESS;
    }
}
