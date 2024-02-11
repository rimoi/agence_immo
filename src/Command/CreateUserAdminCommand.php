<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateUserAdminCommand extends Command
{
    protected static $defaultName = 'app:create-user-admin';
    protected static $defaultDescription = 'Create user admin';
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('email', InputArgument::OPTIONAL, 'Argument description')
        ;
    }

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder
    )
    {
        parent::__construct(null);
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        if (!$email) {
            $io->note('You need passed email for user');

            return 1;
        }

        $user = new User();
        $user->setPhone('0605992481');
        $user->setFirstName('Jean');
        $user->setLastName('Dupond');
        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                'toto'
            )
        );
        $user->setRoles(['ROLE_ADMIN']);
        $user->setEmail($email);

        $this->entityManager->persist($user);
        $this->entityManager->flush();


        $io->success('Tout est bon !');

        return 0;
    }
}
