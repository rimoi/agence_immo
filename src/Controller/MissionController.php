<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Form\ShowInfoFreelanceurType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mission", name="mission_")
 */
class MissionController extends AbstractController
{
    /**
     * @Route("/{slug}/show", name="show")
     */
    public function show(Request $request, Mission $mission): Response
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
            'show_info' => $showInfo
        ]);
    }
}
