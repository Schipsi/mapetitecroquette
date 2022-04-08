<?php

namespace App\Controller;

use App\Entity\Bet;
use App\Entity\Game;
use App\Entity\User;
use App\Repository\BetRepository;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BetController extends AbstractController
{
    #[Route('/bet', name: 'bet', methods: 'POST')]
    public function bet(
        Request $request,
        GameRepository $gameRepository,
        BetRepository $betRepository,
        EntityManagerInterface $em,
        HttpClientInterface $httpClient,
    ): Response
    {
        $requestContent = \json_decode($request->getContent(), true);
        $gameId = $requestContent['game_id'];
        $prediction = $requestContent['team'];

        $game = $gameRepository->find($gameId);
        /** @var User $user */
        $user = $this->getUser();
        $bet = $betRepository->findOneBy(['game' => $game, 'user' => $user]);

        // Return current prediction if no prediction is given
        if (null === $prediction) {
            return $this->json(['success' => true, 'prediction' => $bet?->getPrediction()]);
        }

        // Check if the game has not started
        $response = $httpClient->request(
            'GET',
            \sprintf('https://esports-api.lolesports.com/persisted/gw/getEventDetails?hl=fr-FR&id=%s', $gameId),
            [
                'headers' => [
                    'x-api-key' => '0TvQnueqKa5mxJntVWt0w4LpLfEkrV1Ta8rQBb9Z',
                ],
            ]
        );

        $decodedResponse = \json_decode($response->getContent(), true);
        if (Game::STATE_UNSTARTED !== $decodedResponse['data']['event']['match']['games'][0]['state']) {
            return $this->json(['success' => false, 'prediction' => null]);
        }

        // Update or create the prediction
        if (null === $bet) {
            $bet = new Bet($user, $game, $prediction);
            $betRepository->add($bet);
        } else {
            $bet->setPrediction($prediction);
            $em->flush();
        }

        return $this->json(['success' => true, 'prediction' => $prediction]);
    }
}
