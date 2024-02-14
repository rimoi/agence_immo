<?php

namespace App\Controller\Admin;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\District;
use App\Entity\File;
use App\Entity\Image;
use App\Entity\Mission;
use App\Form\MissionType;
use App\Form\ServiceType;
use App\helper\FormHelper;
use App\Repository\MissionRepository;
use App\Service\QualificationService;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/mission", name="admin_mission_")
 */
class MissionController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index(MissionRepository $missionRepository): Response
    {
        return $this->render('admin/mission/index.html.twig', [
            'missions' => $missionRepository->findBy([], ['id' => 'DESC']),
        ]);
    }

    /**
     * @Route("/new", name="new", methods={"GET","POST"})
     */
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UploaderHelper $uploaderHelper
    ): Response
    {

        $service = new Mission();
        $form = $this->createForm(ServiceType::class, $service);

        $form->handleRequest($request);

        $errors = null;

        if ($form->isSubmitted() && $form->isValid()) {


            foreach ($service->getImages() as $image) {
                if ($uploadedFile = $image->getFile()) {
                    $newFilename = $uploaderHelper->uploadMissionImage($uploadedFile, null);

                    $file = new File();
                    $file->setName($newFilename);

                    $entityManager->persist($file);

                    $image->setFile($file);
                }

                $service->addImage($image);
                $image->setMission($service);

                $entityManager->persist($image);
            }


            $service->setUser($this->getUser());

            $this->addFlash('success', 'Votre service a été créé avec succès');

            $message = 'Félicitations 🎉, Votre annonce est bel et bien publiée.';
            if (!$service->getPublished()) {
                $message = "Votre annonce n'est pas encore publiée. Pour la rendre visible, veuillez vous rendre à l'annonce et cocher la case 'Publier l'annonce'.";
                $this->addFlash('info', $message);
            } else {
                $this->addFlash('success', $message);
            }

            $entityManager->persist($service);
            $entityManager->flush();

            return $this->redirectToRoute('admin_mission_index');
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $errors = FormHelper::getErrorsFromForm($form, true);
        }
        $params = [
            'form' => $form->createView(),
            'errors' => $errors,
            'mission' => $service,
            'show_updaded_image' => !!$errors,
        ];

        return $this->render('admin/mission/new.html.twig', $params);
    }

    /**
     * @Route("/{id}/show", name="show", methods={"GET"})
     */
    public function show(Mission $mission): Response
    {
        return $this->render('admin/mission/show.html.twig', [
            'mission' => $mission,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="edit", methods={"GET","POST"})
     */
    public function edit(
        Request $request,
        Mission $mission,
        EntityManagerInterface $entityManager,
        UploaderHelper $uploaderHelper,
        QualificationService $qualificationService

    ): Response
    {
        if (!$mission->isOwner($this->getUser())) {
            throw new AccessDeniedException("Vous n'avez pas le droit d'accèder à cette resource");
        }

        $form = $this->createForm(ServiceType::class, $mission);

        $form->handleRequest($request);

        $errors = null;

        if ($form->isSubmitted() && $form->isValid()) {

            $qualificationService->addExperience($form, 'images');

            $mission->setUser($this->getUser());

            $this->addFlash('success', 'Votre service a été modifié avec succès');

            $message = 'Félicitations 🎉, Votre annonce est bel et bien publiée.';
            if (!$mission->getPublished()) {
                $message = "Votre annonce n'est pas encore publiée. Pour la rendre visible, veuillez vous rendre à l'annonce et cocher la case 'Publier l'annonce'.";
                $this->addFlash('info', $message);
            } else {
                $this->addFlash('success', $message);
            }

            $entityManager->persist($mission);
            $entityManager->flush();


            return $this->redirectToRoute('admin_mission_index');
        } elseif ($form->isSubmitted() && !$form->isValid()) {
            $errors = FormHelper::getErrorsFromForm($form, true);
        }

        $params = [
            'form' => $form->createView(),
            'errors' => $errors,
            'mission' => $mission,
            'show_updaded_image' => !!$errors,
        ];

        return $this->render('admin/mission/edited.html.twig', $params);
    }

    /**
     * @Route("/{id}/delete", name="delete", methods={"DELETE"})
     */
    public function delete(Request $request, Mission $mission, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mission->getId(), $request->request->get('_token'))) {
            $entityManager->remove($mission);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_mission_index');
    }
}
