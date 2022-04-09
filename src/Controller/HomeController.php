<?php

namespace App\Controller;

use App\Entity\Prediction;
use App\Entity\User;
use App\Form\UsernameFormType;
use App\Repository\GameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(
        Request $request,
        GameRepository$gameRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $usernameForm = $this->createForm(UsernameFormType::class, $user);
        $usernameForm->handleRequest($request);

        if ($usernameForm->isSubmitted() && $usernameForm->isValid()) {
            $entityManager->flush();
        }

        $predictions = $user->getPredictions();
        $correctPredictions = \array_reduce(
            $predictions->toArray(),
            fn (int $carry, Prediction $prediction): int => $prediction->isRealised() ? ++$carry : $carry,
            0
        );
        $incorrectPredictions = \array_reduce(
            $predictions->toArray(),
            fn (int $carry, Prediction $prediction): int => $prediction->isRealised() === false ? ++$carry : $carry,
            0
        );
        $pendingPredictions = \count(\array_filter(
            $predictions->toArray(),
            fn (Prediction $prediction): bool => null === $prediction->isRealised(),
        ));
        $completedPrediction = $incorrectPredictions + $correctPredictions;
        $missedPredictions = \count($gameRepository->findAll()) - $predictions->count();

        return $this->render('page/home.html.twig', [
            'user' => $user,
            'correct_predictions' => $correctPredictions,
            'incorrect_predictions' => $incorrectPredictions,
            'pending_predictions' => $pendingPredictions,
            'missed_predictions' => $missedPredictions,
            'total_predictions' => $completedPrediction,
            'usernameForm' => $usernameForm->createView(),
        ]);
    }
}
