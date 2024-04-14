<?php

namespace App\Service;

use App\Entity\Contact;
use App\Entity\Conversation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationService
{
    /**
     * @var MailerInterface
     */
    private $mailer;
    /**
     * @var string
     */
    private $emailSender;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;
    /**
     * @var string|null
     */
    private $emailPerso;

    public function __construct(
        MailerInterface $mailer,
        ?string $emailSender,
        ?string $emailPerso,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator
    )
    {
        $this->mailer = $mailer;
        $this->emailSender = $emailSender;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->emailPerso = $emailPerso;
    }

    public function sendEmailWhenContactAdmin(Contact $contact): void
    {
        $template = 'mailing/contact.html.twig';

        $templateEmail = (new TemplatedEmail())
            ->from(new Address($this->emailSender, 'AGARATI'))
            ->to($this->emailPerso)
            ->subject('AGARATI - Prise de contact ')
            ->htmlTemplate($template)
            ->context([
                'contact' => $contact,
                'homepage' => $this->urlGenerator->generate('home', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ]);

        $this->mailer->send($templateEmail);

    }

    public function newContactReceived(Conversation $conversation): void
    {
        $template = 'mailing/new_contact.html.twig';

        $templateEmail = (new TemplatedEmail())
            ->from(new Address($this->emailSender, 'AGARATI'))
            ->to($conversation->getUser2()->getEmail())
            ->subject(
                sprintf('%s vous a envoyé 1 message', $conversation->getUser1()->nickname())
            )
            ->htmlTemplate($template)
            ->context([
                'conversation' => $conversation,
                'homepage' => $this->urlGenerator->generate('home', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ]);

        $this->mailer->send($templateEmail);

    }
}
