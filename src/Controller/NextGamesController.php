<?php

namespace App\Controller;

use App\Entity\Game;
use App\Repository\GameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NextGamesController extends AbstractController
{
    #[Route('/next-games', name: 'next-games')]
    public function show(GameRepository $gameRepository): Response
    {
        $games = $gameRepository->findBy(['state' => [Game::STATE_UNSTARTED, Game::STATE_STARTED]], ['date' => 'ASC']);

        return $this->render('page/next_games.html.twig', [
            'games' => $games,
        ]);
    }
}
