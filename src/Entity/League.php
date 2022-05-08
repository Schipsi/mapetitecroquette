<?php

namespace App\Entity;

use App\Repository\LeagueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LeagueRepository::class)]
#[ORM\Table(name: '`league`')]
class League
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private string $id;

    #[ORM\Column(type: 'string')]
    private string $fullName;

    #[ORM\Column(type: 'string')]
    private string $shortName;

    #[ORM\Column(type: 'string')]
    private string $image;

    #[ORM\Column(type: 'boolean')]
    private bool $active;

    #[ORM\OneToMany(mappedBy: 'league', targetEntity: Game::class)]
    private $games;

    public function __construct(
        string $id,
        string $fullName,
        string $shortName,
        string $image,
        bool $active = true,
    ) {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->shortName = $shortName;
        $this->image = $image;
        $this->active = $active;

        $this->games = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): void
    {
        $this->shortName = $shortName;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getGames(): Collection
    {
        return $this->games;
    }
}
