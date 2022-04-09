<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Prediction;
use App\Entity\User;
use App\Repository\GameRepository;
use App\Repository\PredictionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PredictionController extends AbstractController
{
    #[Route('/prediction', name: 'prediction', methods: 'POST')]
    public function predict(
        Request $request,
        GameRepository $gameRepository,
        PredictionRepository $predictionRepository,
        EntityManagerInterface $em,
        HttpClientInterface $httpClient,
    ): Response
    {
        $requestContent = \json_decode($request->getContent(), true);
        $gameId = $requestContent['game_id'];
        $team = $requestContent['team'];

        $game = $gameRepository->find($gameId);
        /** @var User $user */
        $user = $this->getUser();
        $prediction = $predictionRepository->findOneBy(['game' => $game, 'user' => $user]);

        // Return current prediction if no team is given
        if (null === $team) {
            return $this->json(['success' => true, 'prediction' => $prediction?->getTeam()]);
        }

        // Check if the game has not started
        /*$response = $httpClient->request(
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
        }*/

        // Update or create the prediction
        if (null === $prediction) {
            $prediction = new Prediction($user, $game, $team);
            $predictionRepository->add($prediction);
        } else {
            $prediction->setTeam($team);
            $em->flush();
        }

        return $this->json(['success' => true, 'prediction' => $team]);
    }
}
