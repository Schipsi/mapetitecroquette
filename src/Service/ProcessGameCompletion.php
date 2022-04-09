<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Game;
use App\Entity\Prediction;
use Doctrine\ORM\EntityManagerInterface;

class ProcessGameCompletion
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function process(Game $game): void
    {
        // Update all predictions related to the match
        /** @var Prediction[] $predictions */
        $predictions = $game->getPredictions();
        $result = $game->getWinner();

        foreach ($predictions as $prediction) {
            $prediction->setRealised($prediction->getTeam() === $result);
        }

        $this->em->flush();
    }
}
