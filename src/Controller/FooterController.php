<?php

namespace App\Controller;

use App\Constant\LinkingTypeConstant;
use App\Repository\LinkingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FooterController extends AbstractController
{
    /**
     * @Route("/footer", name="app_footer")
     */
    public function index(LinkingRepository $linkingRepository): Response
    {
        return $this->render('footer/index.html.twig', [
            'contacts' => $linkingRepository->findBy(['scope' => LinkingTypeConstant::FLUX_RSS])
        ]);
    }
}
