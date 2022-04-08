<?php

namespace App\Entity;

use App\Repository\BetRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BetRepository::class)]
#[ORM\Table(name: '`bet`')]
class Bet
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: 'User', inversedBy: 'bets')]
    private $user;

    #[ORM\ManyToOne(targetEntity: 'Game', inversedBy: 'bets')]
    private $game;

    #[ORM\Column(type: 'string')]
    private $prediction;

    public function __construct(
        User $user,
        Game $game,
        string $prediction,
    ) {
        $this->user = $user;
        $this->game = $game;
        $this->prediction = $prediction;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function setGame(Game $game): void
    {
        $this->game = $game;
    }

    public function getPrediction(): string
    {
        return $this->prediction;
    }

    public function setPrediction(string $prediction): void
    {
        $this->prediction = $prediction;
    }
}
