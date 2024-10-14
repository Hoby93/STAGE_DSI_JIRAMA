<?php

namespace App\Entity;

use App\Repository\SecteurElectriqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SecteurElectriqueRepository::class)]
class SecteurElectrique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_secteur = null;

    #[ORM\Column(type: 'string', length: 10, unique: true)]
    private ?string $refSecteur = null;

    #[ORM\Column(type: 'integer')]
    private ?int $region_id = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $region = null;

    public function getId(): ?int
    {
        return $this->id_secteur;
    }

    public function setId(string $id): self
    {
        $this->id_secteur = $id;

        return $this;
    }

    public function getRefSecteur(): ?string
    {
        return $this->refSecteur;
    }

    public function setRefSecteur(string $refSecteur): self
    {
        $this->refSecteur = $refSecteur;

        return $this;
    }

    public function getRegionId(): ?string
    {
        return $this->region_id;
    }

    public function setRegionId(string $region_id): self
    {
        $this->region_id = $region_id;

        return $this;
    }

    public function getRegion(): ?string
    {
        return $this->region;
    }

    public function setRegion(string $region): self
    {
        $this->region = $region;

        return $this;
    }
}
