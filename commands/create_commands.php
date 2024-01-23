<?php
// create_user_command.php
define('__ROOT__', dirname(dirname(__FILE__)));

require 'vendor/autoload.php';

require_once(__ROOT__ . '/config/EnvLoader.php');
require_once(__ROOT__ . '/controllers/UserController.php');
require_once(__ROOT__ . '/controllers/EventController.php');

$config = new EnvLoader();

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

        UserController::create($email, $password, $role);

        $output->writeln('User created successfully!');
        return Command::SUCCESS;
    }
}

class CreateEventCommand extends Command
{
    protected static $defaultName = 'create:event';

    protected function configure()
    {
        $this
            ->setDescription('Create a new event with title, arrival, departure, leader, email, phone, and notes')
            ->addArgument('title', InputArgument::REQUIRED, 'The event title')
            ->addArgument('arrival', InputArgument::REQUIRED, 'The arrival date')
            ->addArgument('departure', InputArgument::REQUIRED, 'The departure date')
            ->addArgument('leader', InputArgument::REQUIRED, 'The event leader')
            ->addArgument('email', InputArgument::REQUIRED, 'The leader\'s email')
            ->addArgument('phone', InputArgument::REQUIRED, 'The leader\'s phone')
            ->addArgument('notes', InputArgument::OPTIONAL, 'Additional notes for the event');       
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $title = $input->getArgument('title');
        $arrival = $input->getArgument('arrival');
        $departure = $input->getArgument('departure');
        $leader = $input->getArgument('leader');
        $email = $input->getArgument('email');
        $phone = $input->getArgument('phone');
        $notes = $input->getArgument('notes');

        // Call the EventController to create the event with the retrieved arguments
        EventController::create($title, $arrival, $departure, $leader, $email, $phone, $notes);


        // New command logic here
        $output->writeln('Event created succesfully!');
        return Command::SUCCESS;
    }
}

$application = new Application();
$application->add(new CreateUserCommand());
$application->add(new CreateEventCommand());
$application->run();