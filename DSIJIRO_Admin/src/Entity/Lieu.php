<?php

namespace App\Entity;

use App\Repository\LieuRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LieuRepository::class)]
class Lieu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_lieu = null;

    #[ORM\Column(type: 'string', length: 120)]
    private ?string $refOsm = null;

    #[ORM\Column(type: 'integer')]
    private ?int $secteurId = null;

    #[ORM\Column(type: 'string', length: 120)]
    private ?string $place = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $nom = null;

    public function getId(): ?int
    {
        return $this->id_lieu;
    }

    public function getRefOsm(): ?string
    {
        return $this->refOsm;
    }

    public function setRefOsm(string $refOsm): self
    {
        $this->refOsm = $refOsm;

        return $this;
    }

    public function getSecteurId(): ?int
    {
        return $this->secteurId;
    }

    public function setSecteurId(int $secteurId): self
    {
        $this->secteurId = $secteurId;

        return $this;
    }

    public function getPlace(): ?string
    {
        return $this->place;
    }

    public function setPlace(string $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }
}
