<?php

namespace App\Entity;

use App\Repository\PredictionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PredictionRepository::class)]
#[ORM\Table(name: '`prediction`')]
class Prediction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'predictions')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private $user;

    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: 'predictions')]
    #[ORM\JoinColumn(name: 'game_id', referencedColumnName: 'id')]
    private $game;

    #[ORM\Column(type: 'string')]
    private $team;

    #[ORM\Column(type: 'integer', nullable: true)]
    private $losingTeamScore;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $realised;

    public function __construct(
        User $user,
        Game $game,
        string $team,
        ?int $losingTeamScore = null,
    ) {
        $this->user = $user;
        $this->game = $game;
        $this->team = $team;
        $this->losingTeamScore = $losingTeamScore;
    }

    public function getId(): int
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

    public function getTeam(): string
    {
        return $this->team;
    }

    public function setTeam(string $team): void
    {
        $this->team = $team;
    }

    public function getLosingTeamScore(): ?int
    {
        return $this->losingTeamScore;
    }

    public function setLosingTeamScore(?int $losingTeamScore): void
    {
        $this->losingTeamScore = $losingTeamScore;
    }

    public function isRealised(): ?bool
    {
        return $this->realised;
    }

    public function setRealised(?bool $realised): void
    {
        $this->realised = $realised;
    }
}
