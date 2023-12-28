<?php

namespace App\Controller;

use App\Entity\Tournaments;
use App\Form\TournamentsType;
use App\Handlers\SchedulerClear;
use App\Handlers\SchedulerRandom;
use App\Handlers\SchedulerShow;
use App\Repository\TournamentsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tournaments')]
class TournamentsController extends AbstractController
{
    public function __construct(
        private SchedulerRandom $schedulerRandom,
        private SchedulerClear $schedulerClear
    ) {
    }

    #[Route('/', name: 'app_tournaments_index', methods: ['GET'])]
    public function index(TournamentsRepository $tournamentsRepository): Response
    {
        return $this->render('tournaments/index.html.twig', [
            'tournaments' => $tournamentsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_tournaments_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $tournament = new Tournaments();
        $form = $this->createForm(TournamentsType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tournament);
            $entityManager->flush();

            $this->schedulerRandom->create($tournament, $entityManager);

            return $this->redirectToRoute('app_tournaments_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tournaments/new.html.twig', [
            'tournament' => $tournament,
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'app_tournaments_show', methods: ['GET'])]
    public function show(Tournaments $tournament, SchedulerShow $scheduler): Response
    {
        return $this->render('tournaments/show.html.twig', [
            'tournament' => $tournament,
            'games' => $scheduler->getGrid($tournament),
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_tournaments_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tournaments $tournament, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TournamentsType::class, $tournament);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->schedulerClear->clear($tournament, $entityManager);
            $this->schedulerRandom->create($tournament, $entityManager);

            return $this->redirectToRoute('app_tournaments_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('tournaments/edit.html.twig', [
            'tournament' => $tournament,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_tournaments_delete', methods: ['POST'])]
    public function delete(Request $request, Tournaments $tournament, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$tournament->getId(), $request->request->get('_token'))) {
            $entityManager->remove($tournament);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tournaments_index', [], Response::HTTP_SEE_OTHER);
    }
}
