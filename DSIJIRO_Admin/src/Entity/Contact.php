<?php

namespace App\Entity;

use App\Repository\ContactRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id_contact = null;

    #[ORM\Column]
    private ?string $nom = null;

    #[ORM\Column]
    private ?string $prenom = null;

    #[ORM\Column]
    private ?string $fonction = null;

    #[ORM\Column]
    private ?string $email = null;

    public function init(
        ?string $nom,
        ?string $prenom,
        ?string $fonction,
        ?string $email
    ): self {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->fonction = $fonction;
        $this->email = $email;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id_contact;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getFonction(): ?string
    {
        return $this->fonction;
    }

    public function setFonction(string $fonction): self
    {
        $this->fonction = $fonction;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
}
