<?php

namespace App\Entity;

use App\Repository\CoupureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CoupureRepository::class)]
class Coupure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $idCoupure = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $refCoupure = null;

    #[ORM\Column(type: 'integer')]
    private ?int $confidentialiteId = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateSaisie = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateAnnonce = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateDebut = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $dateFin = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $motif = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $division = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $sa = null;

    #[ORM\Column(type: 'integer')]
    private ?int $employeId = null;

    #[ORM\Column(type: 'integer')]
    private ?int $etat = null;


    public function init(
        ?int $idCoupure,
        ?string $refCoupure,
        ?int $confidentialiteId,
        ?\DateTimeInterface $dateSaisie,
        ?\DateTimeInterface $dateAnnonce,
        ?\DateTimeInterface $dateDebut,
        ?\DateTimeInterface $dateFin,
        string $motif,
        ?string $division,
        ?string $sa,
        ?int $employeId,
        ?string $etat
    ): self {
        $this->idCoupure = $idCoupure;
        $this->refCoupure = $refCoupure;
        $this->confidentialiteId = $confidentialiteId;
        $this->dateSaisie = $dateSaisie;
        $this->dateAnnonce = $dateAnnonce;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
        $this->motif = $motif;
        $this->division = $division;
        $this->sa = $sa;
        $this->employeId = $employeId;
        $this->etat = $etat;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->idCoupure;
    }

    public function getRefCoupure(): ?string
    {
        return $this->refCoupure;
    }

    public function setRefCoupure(string $refCoupure): self
    {
        $this->refCoupure = $refCoupure;

        return $this;
    }

    public function getConfidentialiteId(): ?int
    {
        return $this->confidentialiteId;
    }

    public function setConfidentialiteId(int $confidentialiteId): self
    {
        $this->confidentialiteId = $confidentialiteId;

        return $this;
    }

    public function getDateSaisie(): ?\DateTimeInterface
    {
        return $this->dateSaisie;
    }

    public function setDateSaisie(?\DateTimeInterface $dateSaisie): self
    {
        $this->dateSaisie = $dateSaisie;

        return $this;
    }

    public function getDateAnnonce(): ?\DateTimeInterface
    {
        return $this->dateAnnonce;
    }

    public function setDateAnnonce(?\DateTimeInterface $dateAnnonce): self
    {
        $this->dateAnnonce = $dateAnnonce;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): self
    {
        $this->motif = $motif;

        return $this;
    }

    public function getDivision(): ?string
    {
        return $this->division;
    }

    public function setDivision(string $division): self
    {
        $this->division = $division;

        return $this;
    }

    public function getSa(): ?string
    {
        return $this->sa;
    }

    public function setSa(string $sa): self
    {
        $this->sa = $sa;

        return $this;
    }

    public function getEmployeId(): ?int
    {
        return $this->employeId;
    }

    public function setEmployeId(int $employeId): self
    {
        $this->employeId = $employeId;

        return $this;
    }

    public function getEtat(): ?int
    {
        return $this->etat;
    }

    public function setEtat(int $etat): self
    {
        $this->etat = $etat;

        return $this;
    }
}
