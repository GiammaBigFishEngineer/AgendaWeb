<?php
// create_user_command.php
define('__ROOT__', dirname(dirname(__FILE__)));

require 'vendor/autoload.php';

require_once(__ROOT__ . '/config/EnvLoader.php');
require_once(__ROOT__ . '/controllers/UserController.php');

$config = new EnvLoader();

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'create:user',
    description: 'Creates a new user.',
    hidden: false,
)]
class CreateUserCommand extends Command
{
    protected static $defaultName = 'create:user';

    protected function configure()
    {
        $this
            ->setDescription('Create a new user')
            ->addArgument('email', InputArgument::REQUIRED, 'The user\'s email')
            ->addArgument('password', InputArgument::REQUIRED, 'The user\'s password')
            ->addArgument('role', InputArgument::OPTIONAL, 'The user\'s role (Default: Utente)');

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $role = $input->getArgument('role');

        // Your user creation logic here
        // Example: create a user with the provided email and password
        UserController::create($email, $password, $role);

        $output->writeln('User created successfully.');
        return Command::SUCCESS;
    }
}

$application = new Application();
$application->add(new CreateUserCommand());
$application->run();