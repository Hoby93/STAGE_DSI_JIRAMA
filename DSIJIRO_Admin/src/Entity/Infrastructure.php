<?php

namespace App\Entity;

use App\Repository\InfrastructureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InfrastructureRepository::class)]
class Infrastructure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_infra = null;

    #[ORM\Column(type: 'string', length: 10, unique: true)]
    private ?string $refInfra = null;

    #[ORM\Column(type: 'integer')]
    private ?int $typeInfra = null;

    #[ORM\Column(type: 'integer')]
    private ?int $fktId = null;

    #[ORM\Column(type: 'string', length: 120)]
    private ?string $libelle = null;

    #[ORM\Column(type: 'string', length: 120)]
    private ?string $adresse = null;

    #[ORM\Column(type: 'string', length: 120)]
    private ?string $contact = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $descr = null;

    #[ORM\Column(type: 'string', length: 255, options: ['default' => 'lun:08:00-17:00;mar:08:00-17:00;mer:08:00-17:00;jeu:08:00-17:00;ven:08:00-17:00;sam:09:00-13:00;'])]
    private ?string $horaire = null;

    #[ORM\Column(type: 'point')]
    private ?array $coord = null;

    #[ORM\ManyToOne(targetEntity: TypeInfrastructure::class)]
    #[ORM\JoinColumn(name: 'type_infra', referencedColumnName: 'id_type')]
    private ?TypeInfrastructure $typeInfrastructure = null;

    public function isThisTypeInfra(int $type) {
        if($this->typeInfra == $type) {
            return "selected";
        }
        return "";
    }

    public function getId(): ?int
    {
        return $this->id_infra;
    }

    public function getRefInfra(): ?string
    {
        return $this->refInfra;
    }

    public function setRefInfra(string $refInfra): self
    {
        $this->refInfra = $refInfra;

        return $this;
    }

    public function getTypeInfra(): ?int
    {
        return $this->typeInfra;
    }

    public function setTypeInfra(int $typeInfra): self
    {
        $this->typeInfra = $typeInfra;

        return $this;
    }

    public function getFktId(): ?int
    {
        return $this->fktId;
    }

    public function setFktId(int $fktId): self
    {
        $this->fktId = $fktId;

        return $this;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(string $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getDescr(): ?string
    {
        return $this->descr;
    }

    public function setDescr(string $descr): self
    {
        $this->descr = $descr;

        return $this;
    }

    public function getHoraire(): ?string
    {
        return $this->horaire;
    }

    public function setHoraire(string $horaire): self
    {
        $this->horaire = $horaire;

        return $this;
    }

    public function getCoord(): ?array
    {
        return $this->coord;
    }

    public function setCoord(array $coord): self
    {
        $this->coord = $coord;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->coord['latitude'] ?? null;
    }

    public function getLongitude(): ?float
    {
        return $this->coord['longitude'] ?? null;
    }

    public function getCoordToPoint() {
        $res = "POINT({$this->coord['latitude']} {$this->coord['longitude']})";
        return $res;
    }

    public function getTypeInfrastructure(): ?TypeInfrastructure
    {
        return $this->typeInfrastructure;
    }

    public function setTypeInfrastructure(?TypeInfrastructure $typeInfrastructure): self
    {
        $this->typeInfrastructure = $typeInfrastructure;

        return $this;
    }
}
