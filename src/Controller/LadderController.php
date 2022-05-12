<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\League;
use App\Entity\Prediction;
use App\Entity\User;
use App\Form\LeagueFormType;
use App\Repository\LeagueRepository;
use App\Repository\PredictionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LadderController extends AbstractController
{
    #[Route('/ladder', name: 'ladder')]
    public function show(
        Request $request,
        UserRepository $userRepository,
        LeagueRepository $leagueRepository,
        PredictionRepository $predictionRepository
    ): Response {
        $leagues = $leagueRepository->findAll();
        $defaultLeague = $leagueRepository->findOneBy(['active' => true]);

        $form = $this->createForm(LeagueFormType::class, ['league' => $defaultLeague]);
        $form->handleRequest($request);

        /** @var League $filterLeague */
        $filterLeague = $form->get('league')->getData();
        $users = $userRepository->findAll();

        // Add number of correct predictions to users
        $ranking = \array_map(function (User $user) use ($filterLeague, $predictionRepository) {
            $predictions = $predictionRepository->getUserPredictionForLeague($user, $filterLeague);

            return [
                'user' => $user,
                'correct_predictions' => \array_reduce(
                    $predictions,
                    fn (int $carry, Prediction $prediction): int => $prediction->isRealised() ? ++$carry : $carry,
                    0
                ),
                'total_predictions' => \array_reduce(
                    $predictions,
                    fn (int $carry, Prediction $prediction): int => $prediction->getGame()->getState() === Game::STATE_COMPLETED ? ++$carry : $carry,
                    0
                ),
            ];
        }, $users);

        // Sort user by correct predictions
        \usort($ranking, function ($a, $b) {
            if ($a['correct_predictions'] === $b['correct_predictions']) {
                return 0;
            }

            return $a['correct_predictions'] < $b['correct_predictions'] ? 1 : -1;
        });

        // Add final ranking in case of equality
        $rank = 1;
        $displayedRank = 1;
        foreach ($ranking as $key => $rankedUser) {
            $ranking[$key]['rank'] = $displayedRank;

            ++$rank;
            if (\array_key_exists($key + 1, $ranking) && $rankedUser['correct_predictions'] > $ranking[$key + 1]['correct_predictions']) {
                $displayedRank = $rank;
            }
        }

        return $this->render('page/ladder.html.twig', [
            'ranking' => $ranking,
            'leagues' => $leagues,
            'default_league' => $defaultLeague,
            'form' => $form->createView(),
        ]);
    }
}
