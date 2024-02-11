<?php

namespace App\Controller;

use App\Repository\MissionRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /** @Route("/", name="home") */
    public function index(
        Request $request,
        MissionRepository $missionRepository,
        PaginatorInterface $pagination
    ) {
        $qb = $missionRepository->getMissiosQueryBuilder(
            $request->get('q'),
            $request->get('prices')
        );

        return $this->render('home/index.html.twig', [
            'missions' => $pagination->paginate(
                $qb,
                $request->get('page', 1),
                6
            )
        ]);
    }
}
