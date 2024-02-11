<?php

namespace App\Controller\Admin;

use App\Entity\City;
use App\Entity\Country;
use App\Entity\Mission;
use App\Form\MissionType;
use App\Form\ServiceType;
use App\Repository\MissionRepository;
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
            'missions' => $missionRepository->findAll(),
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

        if ($form->isSubmitted() && $form->isValid()) {

            $countryExist = null;
            if ($service->getCountry()) {
                $countryExist = $entityManager->getRepository(Country::class)->findOneBy(['reference' => $service->getCountry()->getReference()]);

                if (!$countryExist) {
                    $entityManager->persist($service->getCountry());
                } else {
                    $service->setCountry($countryExist);
                }
            }

            if ($service->getCity()) {
                $cityExist = $entityManager->getRepository(City::class)->findOneBy(['reference' => $service->getCity()->getReference()]);

                if (!$cityExist) {
                    $entityManager->persist($service->getCity());
                } else {
                    $service->setCity($cityExist);
                }

                if ($service->getCountry()) {
                    $service->getCity()->setCountry($service->getCountry());
                }
            }

            if ($service->getCountry()) {
                $countryExist = $entityManager->getRepository(Country::class)->findOneBy(['reference' => $service->getCountry()->getReference()]);

                if (!$countryExist) {
                    $entityManager->persist($service->getCountry());
                } else {
                    $service->setCountry($countryExist);
                }
            }

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('image')->getData();

            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadMissionImage($uploadedFile, null);
                $service->setImageFile($newFilename);
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
        }


        return $this->render('admin/mission/new.html.twig', [
            'mission' => $service,
            'form' => $form->createView(),
        ]);
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
        UploaderHelper $uploaderHelper
    ): Response
    {
        if (!$mission->isOwner($this->getUser())) {
            throw new AccessDeniedException("Vous n'avez pas le droit d'accèder à cette resource");
        }

        $form = $this->createForm(ServiceType::class, $mission);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $countryExist = null;
            if ($mission->getCountry()) {
                $countryExist = $entityManager->getRepository(Country::class)->findOneBy(['reference' => $mission->getCountry()->getReference()]);

                if (!$countryExist) {
                    $entityManager->persist($mission->getCountry());
                } else {
                    $mission->setCountry($countryExist);
                }
            }

            if ($mission->getCity()) {
                $cityExist = $entityManager->getRepository(City::class)->findOneBy(['reference' => $mission->getCity()->getReference()]);

                if (!$cityExist) {
                    $entityManager->persist($mission->getCity());
                } else {
                    $mission->setCity($cityExist);
                }

                if ($mission->getCountry()) {
                    $mission->getCity()->setCountry($mission->getCountry());
                }
            }

            if ($mission->getCountry()) {
                $countryExist = $entityManager->getRepository(Country::class)->findOneBy(['reference' => $mission->getCountry()->getReference()]);

                if (!$countryExist) {
                    $entityManager->persist($mission->getCountry());
                } else {
                    $mission->setCountry($countryExist);
                }
            }

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('image')->getData();

            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadMissionImage($uploadedFile, null);
                $mission->setImageFile($newFilename);
            }

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
        }

        return $this->render('admin/mission/edited.html.twig', [
            'mission' => $mission,
            'form' => $form->createView(),
        ]);
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
