<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @method User getUser()
 */
class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="profile")
     */
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        UploaderHelper $uploaderHelper,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response
    {
        $user = $this->getUser();
        $user = $entityManager->getRepository(User::class)->find($user->getId());
        $form = $this->createForm(ProfileType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($form->get('plainPassword')->getData()) {
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('imageFile')->getData();

            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadUserAvatar($uploadedFile);
                $user->setAvatar($newFilename);
            }

            $this->addFlash('success', 'Mise à jour de votre profil effectuée avec succès.');

            $entityManager->flush();

            return $this->redirectToRoute('profile');
        }

        return $this->render('profile/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
