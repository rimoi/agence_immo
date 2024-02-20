<?php

namespace App\Controller;

use App\Constant\LinkingTypeConstant;
use App\Entity\File as FileEntity;
use App\Entity\Linking;
use App\Entity\Mission;
use App\Form\ShowInfoFreelanceurType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mission")
 */
class MissionController extends AbstractController
{
    /**
     * @Route("/{slug}/show", name="mission_show")
     */
    public function show(Request $request, Mission $mission, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ShowInfoFreelanceurType::class);
        $form->handleRequest($request);

        $showInfo = false;

        if ($form->isSubmitted() && $form->isValid()) {
            $showInfo = true;
        }

        return $this->render('mission/show.html.twig', [
            'form' => $form->createView(),
            'mission' => $mission,
            'show_info' => $showInfo,
            'contacts' => $entityManager->getRepository(Linking::class)->findBy(['scope' => LinkingTypeConstant::CONTACT])
        ]);
    }


    /**
     * @Route("/{id}/consuler", name="front_mission_consulter")
     */
    public function consuler(Request $request, FileEntity $fichier): BinaryFileResponse
    {
        $file = new File($this->getParameter('app.image_directory').'/'.$fichier->getName());

        return $this->file($file, $fichier->getName() , ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
