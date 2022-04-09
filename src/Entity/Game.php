<?php

namespace App\Entity;

use App\Repository\GameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GameRepository::class)]
#[ORM\Table(name: '`game`')]
class Game
{
    public const STATE_UNSTARTED = 'unstarted';
    public const STATE_STARTED = 'started';
    public const STATE_COMPLETED = 'completed';

    public const OUTCOME_WIN = 'win';
    public const OUTCOME_LOSS = 'loss';

    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private string $id;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $date;

    #[ORM\Column(type: 'string')]
    private string $nameTeam1;

    #[ORM\Column(type: 'string')]
    private string $nameTeam2;

    #[ORM\Column(type: 'string')]
    private string $codeTeam1;

    #[ORM\Column(type: 'string')]
    private string $codeTeam2;

    #[ORM\Column(type: 'string')]
    private string $imageTeam1;

    #[ORM\Column(type: 'string')]
    private string $imageTeam2;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $outcomeTeam1;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $outcomeTeam2;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $scoreTeam1;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $scoreTeam2;

    #[ORM\Column(type: 'string')]
    private string $state;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $pingSent;

    #[ORM\OneToMany(mappedBy: 'game', targetEntity: 'Prediction')]
    private $predictions;

    public function __construct(
        string $id,
        \DateTimeImmutable $date,
        string $nameTeam1,
        string $nameTeam2,
        string $codeTeam1,
        string $codeTeam2,
        string $imageTeam1,
        string $imageTeam2,
        ?string $state = self::STATE_UNSTARTED,
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->nameTeam1 = $nameTeam1;
        $this->nameTeam2 = $nameTeam2;
        $this->codeTeam1 = $codeTeam1;
        $this->codeTeam2 = $codeTeam2;
        $this->imageTeam1 = $imageTeam1;
        $this->imageTeam2 = $imageTeam2;
        $this->state = $state;

        $this->pingSent = false;
        $this->predictions = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function getNameTeam1(): string
    {
        return $this->nameTeam1;
    }

    public function setNameTeam1(string $nameTeam1): void
    {
        $this->nameTeam1 = $nameTeam1;
    }

    public function getNameTeam2(): string
    {
        return $this->nameTeam2;
    }

    public function setNameTeam2(string $nameTeam2): void
    {
        $this->nameTeam2 = $nameTeam2;
    }

    public function getCodeTeam1(): string
    {
        return $this->codeTeam1;
    }

    public function setCodeTeam1(string $codeTeam1): void
    {
        $this->codeTeam1 = $codeTeam1;
    }

    public function getCodeTeam2(): string
    {
        return $this->codeTeam2;
    }

    public function setCodeTeam2(string $codeTeam2): void
    {
        $this->codeTeam2 = $codeTeam2;
    }

    public function getImageTeam1(): string
    {
        return $this->imageTeam1;
    }

    public function setImageTeam1(string $imageTeam1): void
    {
        $this->imageTeam1 = $imageTeam1;
    }

    public function getImageTeam2(): string
    {
        return $this->imageTeam2;
    }

    public function setImageTeam2(string $imageTeam2): void
    {
        $this->imageTeam2 = $imageTeam2;
    }

    public function getOutComeTeam1(): ?string
    {
        return $this->outcomeTeam1;
    }

    public function setOutComeTeam1(?string $outcomeTeam1): void
    {
        $this->outcomeTeam1 = $outcomeTeam1;
    }

    public function getOutComeTeam2(): ?string
    {
        return $this->outcomeTeam2;
    }

    public function setOutComeTeam2(?string $outcomeTeam2): void
    {
        $this->outcomeTeam2 = $outcomeTeam2;
    }

    public function getScoreTeam1(): ?int
    {
        return $this->scoreTeam1;
    }

    public function setScoreTeam1(?int $scoreTeam1): void
    {
        $this->scoreTeam1 = $scoreTeam1;
    }

    public function getScoreTeam2(): ?int
    {
        return $this->scoreTeam2;
    }

    public function setScoreTeam2(?int $scoreTeam2): void
    {
        $this->scoreTeam2 = $scoreTeam2;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): void
    {
        $this->state = $state;
    }

    public function isPingSent(): bool
    {
        return $this->pingSent;
    }

    public function setPingSent(bool $pingSent): void
    {
        $this->pingSent = $pingSent;
    }

    public function getPredictions(): Collection
    {
        return $this->predictions;
    }

    public function getWinner(): ?string
    {
        if (null === $this->outcomeTeam1 || null === $this->outcomeTeam2) {
            return null;
        }

        if (self::OUTCOME_WIN === $this->outcomeTeam1) {
            return $this->getCodeTeam1();
        } else {
            return $this->getCodeTeam2();
        }
    }
}
