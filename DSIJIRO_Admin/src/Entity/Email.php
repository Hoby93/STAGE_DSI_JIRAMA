<?php

namespace App\Entity;

class Email
{
    private string $email;
    private string $objet;
    private string $contenu;

    public function init($type): void {
        $data = json_decode(file_get_contents(__DIR__ . '/../../public/json/email.json'), true);
        $this->email = $data[$type]['emetteur'] ?? '';
        $this->objet = $data[$type]['objet'] ?? '';
        $this->contenu = $data[$type]['contenu'] ?? '';
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getObjet(): string
    {
        return $this->objet;
    }

    public function setObjet(string $objet): self
    {
        $this->objet = $objet;
        return $this;
    }

    public function getContenu(): string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;
        return $this;
    }

    public function setContenuOption(string $option, string $value): self
    {
        $this->contenu = str_replace($option, $value, $this->contenu);
        return $this;
    }
}
