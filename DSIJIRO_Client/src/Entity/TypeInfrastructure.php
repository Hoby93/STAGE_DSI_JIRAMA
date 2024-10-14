<?php

namespace App\Entity;

use App\Repository\TypeInfrastructureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TypeInfrastructureRepository::class)]
class TypeInfrastructure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_type = null;

    #[ORM\Column(type: 'string', length: 120)]
    private ?string $libelle = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $descr = null;

    public function getId(): ?int
    {
        return $this->id_type;
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

    public function getDescr(): ?string
    {
        return $this->descr;
    }

    public function setDescr(string $descr): self
    {
        $this->descr = $descr;

        return $this;
    }
}
