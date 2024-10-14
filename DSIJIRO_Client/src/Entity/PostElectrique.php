<?php

namespace App\Entity;

use App\Repository\PostElectriqueRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostElectriqueRepository::class)]
class PostElectrique
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_post = null;

    #[ORM\Column(type: 'string', length: 10, unique: true)]
    private ?string $refPost = null;

    #[ORM\Column(type: 'integer')]
    private ?int $secteurId = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $secteur = null;

    public function getId(): ?int
    {
        return $this->id_post;
    }

    public function getRefPost(): ?string
    {
        return $this->refPost;
    }

    public function setRefPost(string $refPost): self
    {
        $this->refPost = $refPost;

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

    public function getSecteur(): ?string
    {
        return $this->secteur;
    }

    public function setSecteur(string $secteur): self
    {
        $this->secteur = $secteur;

        return $this;
    }
}
