<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Repository\GameRepository;
use App\Repository\PredictionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NextGamesController extends AbstractController
{
    #[Route('/next-games', name: 'next-games')]
    public function show(GameRepository $gameRepository, PredictionRepository $predictionRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $currentGame = $gameRepository->findOneBy(['state' => Game::STATE_STARTED], ['date' => 'ASC']);
        $games = $gameRepository->findBy(['state' => Game::STATE_UNSTARTED], ['date' => 'ASC']);

        $predictions = [];
        foreach ($games as $key => $game) {
            $prediction = $predictionRepository->findOneBy(['user' => $user, 'game' => $game]);

            if (null !== $prediction) {
                $predictions[$key]['team'] = $prediction->getTeam();
                $predictions[$key]['losing_team_score'] = $prediction->getLosingTeamScore();
            } else {
                $predictions[$key]['team'] = null;
                $predictions[$key]['losing_team_score'] = null;
            }
        }

        $currentGamePrediction = $predictionRepository->findOneBy(['user' => $user, 'game' => $currentGame]);

        return $this->render('page/next_games.html.twig', [
            'games' => $games,
            'currentGame' => $currentGame,
            'predictions' => $predictions,
            'currentGamePrediction' => [
                'team' => $currentGamePrediction?->getTeam(),
                'losing_team_score' => $currentGamePrediction?->getLosingTeamScore()
            ],
        ]);
    }
}
