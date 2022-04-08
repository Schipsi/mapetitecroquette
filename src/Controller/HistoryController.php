<?php

namespace App\Controller;

use App\Entity\Game;
use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoryController extends AbstractController
{
    #[Route('/history', name: 'history')]
    public function show(GameRepository $gameRepository): Response
    {
        $games = $gameRepository->findBy(['state' => Game::STATE_COMPLETED], ['date' => 'DESC']);

        return $this->render('page/history.html.twig', [
            'games' => $games,
        ]);
    }
}
