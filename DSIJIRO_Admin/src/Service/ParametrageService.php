<?php

namespace App\Service;

use Doctrine\DBAL\Connection;

class ParametrageService
{
    private Connection $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function fetchFokontany($motclee): array
    {
        // Pour éviter les injections SQL
        $motclee = addslashes($motclee);
        
        $sql = "
            SELECT * FROM fokontany where libelle like '%{$motclee}%' or id_fokontany = '{$motclee}' limit 5
        ";

        // Exécutez la requête et obtenez les résultats sous forme associative
        $results = $this->conn->fetchAllAssociative($sql);

        return $results;
    }

    public function fetchSecteur($motclee): array
    {
        // Pour éviter les injections SQL
        $motclee = addslashes($motclee);
        
        $sql = "
            SELECT * FROM secteur_electrique where ref_secteur like '%{$motclee}%' limit 5
        ";

        // Exécutez la requête et obtenez les résultats sous forme associative
        $results = $this->conn->fetchAllAssociative($sql);

        return $results;
    }

    public function fetchRegion(): array
    {  
        $sql = "
            SELECT * FROM region
        ";

        // Exécutez la requête et obtenez les résultats sous forme associative
        $results = $this->conn->fetchAllAssociative($sql);

        return $results;
    }
}
