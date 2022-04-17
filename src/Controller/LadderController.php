<?php

namespace App\Controller;

use App\Entity\Prediction;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LadderController extends AbstractController
{
    #[Route('/ladder', name: 'ladder')]
    public function show(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        // Add number of correct predictions to users
        $ranking = \array_map(function (User $user) {
            $predictions = $user->getPredictions();

            return [
                'user' => $user,
                'correct_predictions' => \array_reduce(
                    $predictions->toArray(),
                    fn (int $carry, Prediction $prediction): int => $prediction->isRealised() ? ++$carry : $carry,
                    0
                ),
                'total_predictions' => $predictions->count(),
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
        ]);
    }
}
