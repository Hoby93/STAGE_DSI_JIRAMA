<?php
namespace App\Entity;

use App\Repository\EmployeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: EmployeRepository::class)]
class Employe implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_employe = null;

    #[ORM\Column(type: 'string')]
    private ?string $nom = null;

    #[ORM\Column(type: 'string')]
    private ?string $prenom = null;

    #[ORM\Column(type: 'string')]
    private ?string $email = null;

    #[ORM\Column(type: 'string')]
    private ?string $mdp = null;

    #[ORM\Column(type: 'integer')]
    private ?int $profil = null;

    #[ORM\Column(type: 'string')]
    private ?string $position = null;

    public function getId(): ?int
    {
        return $this->id_employe;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function init(
        ?string $nom,
        ?string $prenom,
        ?string $email,
        ?string $mdp,
        ?int $profil,
        ?string $position
    ): self {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->mdp = $mdp;
        $this->profil = $profil;
        $this->position = $position;
        return $this;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getMdp(): ?string
    {
        return $this->mdp;
    }

    public function setMdp(string $mdp): self
    {
        $this->mdp = $mdp;
        return $this;
    }

    public function getProfil(): ?int
    {
        return $this->profil;
    }

    public function setProfil(int $profil): self
    {
        $this->profil = $profil;
        return $this;
    }

    // Méthodes requises par UserInterface

    public function getRoles(): array
    {
        // Load and decode the JSON file
        $data = json_decode(file_get_contents(__DIR__ . '/../../public/json/role.json'), true);
        
        // Ensure the profil is set and exists in the JSON data
        if (isset($data[$this->profil])) {
            // Decode the Roles string to get it as an array
            return $data[$this->profil]['Roles'];
        }
        return ['ROLE_USER'];
    }

    public function getPassword(): ?string
    {
        return $this->mdp;
    }

    public function getSalt(): ?string
    {
        // Le sel n'est pas nécessaire si vous utilisez bcrypt ou argon2i dans Symfony
        return null;
    }

    public function getUsername(): string
    {
        return $this->nom;
    }

    public function getUserIdentifier(): string{
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // Efface les données sensibles temporaires, si nécessaire
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): self
    {
        $this->position = $position;
        return $this;
    }
}
