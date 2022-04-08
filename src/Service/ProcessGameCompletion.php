<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Entity\Prediction;
use Doctrine\ORM\EntityManagerInterface;

class ProcessGameCompletion
{
    public function process(Game $game, EntityManagerInterface $em): void
    {
        // Update all predictions related to the match
        /** @var Prediction[] $predictions */
        $predictions = $game->getPredictions();
        $result = $game->getWinner();

        foreach ($predictions as $prediction) {
            $prediction->setRealised($prediction->getTeam() === $result);
        }

        $em->flush();
    }
}
