<?php


namespace App\EventListener;


use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Security;

class ExceptionListener
{
    private $mailer;
    private $security;
    /**
     * @var string
     */
    private $emailSender;
    /**
     * @var string
     */
    private $emailPerso;
    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct(
        MailerInterface $mailer,
        string $emailSender,
        string $emailPerso,
        KernelInterface $kernel,
        Security $security
    )
    {
        $this->mailer = $mailer;
        $this->security = $security;
        $this->emailSender = $emailSender;
        $this->emailPerso = $emailPerso;
        $this->kernel = $kernel;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        if (
            !$exception instanceof HttpExceptionInterface
            && $this->kernel->getEnvironment() === 'prod'
        ) {

            $user = $this->security->getUser();

            $email = '';
            if ($user) {
                $email = sprintf(
                    '<b>L\'utilisateur concerné par cette erreur est</b> :  %s ( id: %d ) <br><br>',
                    $user->getEmail(),
                    $user->getId()
                );
            }

            $message = sprintf(
                "<b>[ AGARATI ] Le message d'erreur</b> :<br/><br><code>%s</code>",
                $exception->getTraceAsString()
            );


            $message = $email . $message;

//             $this->sendEmail($message);
        }
    }

    private function sendEmail(string $message): void
    {
        $email = (new Email())
            ->from($this->emailSender)
            ->to($this->emailPerso)
            ->subject("Une erreur s'est produite")
            ->html($message)
        ;

        $this->mailer->send($email);
    }
}
