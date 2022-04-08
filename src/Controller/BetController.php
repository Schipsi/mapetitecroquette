<?php

namespace App\Controller;

use App\Entity\Bet;
use App\Entity\User;
use App\Repository\BetRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BetController extends AbstractController
{
    #[Route('/bet', name: 'bet', methods: 'POST')]
    public function bet(
        Request $request,
        GameRepository $gameRepository,
        BetRepository $betRepository,
        EntityManagerInterface $em,
    ): Response
    {
        $requestContent = \json_decode($request->getContent(), true);
        $gameId = $requestContent['game_id'];
        $prediction = $requestContent['team'];

        $game = $gameRepository->find($gameId);
        /** @var User $user */
        $user = $this->getUser();
        $bet = $betRepository->findOneBy(['game' => $game, 'user' => $user]);

        if (null !== $prediction) {
            if (null == $bet) {
                $bet = new Bet($user, $game, $prediction);
                $betRepository->add($bet);
            } else {
                $bet->setPrediction($prediction);
                $em->flush();
            }
        } elseif (null !== $bet) {
            $prediction = $bet->getPrediction();
        }

        return $this->json(['success' => true, 'prediction' => $prediction]);
    }
}
