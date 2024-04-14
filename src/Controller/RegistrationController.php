<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\Login;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register/dont-use-url", name="app_register")
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        MailerInterface $mailer,
        EntityManagerInterface $entityManager,
        UploaderHelper $uploaderHelper,
        string $emailSender
    ): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            if ($role = $form->get('status')->getData()) {
                $user->setRoles([$role]);
            }

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();

            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadUserAvatar($uploadedFile);
                $user->setAvatar($newFilename);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $template = 'registration/confirmation_email.html.twig';

            $templateEmail = (new TemplatedEmail())
                ->from(new Address($emailSender, 'AGARATI'))
                ->to($user->getEmail())
                ->subject('AGARATI - Activez votre compte')
                ->htmlTemplate($template)
                ->context([
                    'name' => $user->nickname(),
                    'signedUrl' => $this->generateUrl('app_confim_account', ['slug' => $user->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
                    'homepage' => $this->generateUrl('home', [], UrlGeneratorInterface::ABSOLUTE_URL)
                ]);

            $mailer->send($templateEmail);

            $notice = sprintf(
                'Vous avez presque terminé. <br/> Nous avons envoyé un email d’activation à l’adresse <b>%s</b>. <br> Ouvrez l’email et cliquez sur « Activer mon compte » pour confirmer votre adresse email.',
                $user->getEmail()
            );
            $this->addFlash('success', $notice);

            // do anything else you need here, like send an email

            return $this->redirectToRoute('home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/confirm/{slug}/account", name="app_confim_account")
     */
    public function confirmAccount(
        Request $request,
        User $user,
        EntityManagerInterface $em,
        Login $login
    ): Response
    {
        if ($user->getIsVerified()) {
            $this->addFlash('danger', 'Votre compte a déjà été activé.');

            return $this->redirectToRoute('home');
        }

        $user->setIsVerified(true);

        $this->addFlash('success', 'Votre compte a été activé avec succès.');

        $em->flush();

        $login->forceConnection($request, $user);

        return $this->redirectToRoute('home');
    }
}
