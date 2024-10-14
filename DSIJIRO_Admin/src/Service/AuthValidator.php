<?php

namespace App\Service;

use App\Entity\Employe;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AuthValidator
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function validateUser(string $requiredRole): bool
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return false;
        }
        $user = $token->getUser();
        return $user instanceof Employe && $user->getProfil() === $requiredRole;
    }
}
