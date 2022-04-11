<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Repository\GameRepository;
use App\Repository\PredictionRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoryController extends AbstractController
{
    #[Route('/history', name: 'history')]
    public function show(GameRepository $gameRepository, PredictionRepository $predictionRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $games = $gameRepository->findBy(['state' => Game::STATE_COMPLETED], ['date' => 'DESC']);

        $predictions = [];
        foreach ($games as $key => $game) {
            $prediction = $predictionRepository->findOneBy(['user' => $user, 'game' => $game]);

            if (null !== $prediction) {
                $predictions[$key] = $prediction->getTeam();
            } else {
                $predictions[$key] = null;
            }
        }

        return $this->render('page/history.html.twig', [
            'games' => $games,
            'predictions' => $predictions,
        ]);
    }

    #[Route('/{id}/history', name: 'user-history')]
    #[ParamConverter('user', User::class)]
    public function showOther(User $user, GameRepository $gameRepository, PredictionRepository $predictionRepository): Response
    {
        $games = $gameRepository->findBy(['state' => Game::STATE_COMPLETED], ['date' => 'DESC']);

        $predictions = [];
        foreach ($games as $key => $game) {
            $prediction = $predictionRepository->findOneBy(['user' => $user, 'game' => $game]);

            if (null !== $prediction) {
                $predictions[$key] = $prediction->getTeam();
            } else {
                $predictions[$key] = null;
            }
        }

        return $this->render('page/history.html.twig', [
            'games' => $games,
            'predictions' => $predictions,
        ]);
    }
}
