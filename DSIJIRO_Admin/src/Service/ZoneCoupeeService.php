<?php

namespace App\Service;

use App\Doctrine\PolygonType;
use Doctrine\DBAL\Connection;
use App\Entity\ZoneCoupee;
use App\Entity\Coupure;
use App\Entity\Filtre;
use App\Entity\ZoneElectrique;

class ZoneCoupeeService
{
    private Connection $conn;

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    public function fetchZoneCoupee($order): array
    {
        $sql = "
            select * from v_zone_coupee order by date_debut {$order}
        ";
        $results = $this->conn->fetchAllAssociative($sql);

        return $this->toZoneCoupee($results);
    }

    public function fetchZoneCoupeeById($id_string, $order): array
    {
        $sql = "
            select 
                * 
                from v_zone_coupee 
                where id_coupure_zone in ({$id_string})
                order by date_debut {$order}
        ";
        $results = $this->conn->fetchAllAssociative($sql);

        return $this->toZoneCoupee($results);
    }

    public function countResult($sql, $limit) {
        $countSql = "
            SELECT 
                COUNT(*) as total 
            FROM (
                {$sql}
            ) as total_rows
        ";

        $totalCountResult = $this->conn->fetchOne($countSql);
        $totalCount = (int) $totalCountResult;
        
        // Calculate the total number of pages
        $totalPages = ceil($totalCount / $limit);
        return $totalPages;
    }

    public function fetchZoneCoupeeByFilter(Filtre $filtre): array
    {
        $niv = $filtre->getGroupby();
        $motclee = $filtre->getMotclee();
        $order = $filtre->getOrderby();
        $inc = $filtre->getOrderinc();
        $page = $filtre->getPage();
        $limit = $filtre->getLimit();

        $offset = ($page - 1) * $limit;

        $sql = "
            select * from v_zone_coupee
            where {$niv} like '%{$motclee}%' AND {$filtre->getDateIntervalFilter()}
            ORDER BY {$order} {$inc}
        ";

        $count = $this->countResult($sql, $limit);
        $sql_limit = "{$sql} LIMIT {$limit} OFFSET {$offset}";

        // Execute the query and get the results
        $results = $this->conn->fetchAllAssociative($sql_limit);

        return [
            'results' => $this->toZoneCoupee($results),
            'totalPages' => $count,
            'currentPage' => $page
        ];
    }

    public function fetchZoneCoupeeByDate($date): array
    {
        $sql = "
            select * from v_zone_coupee where DATE(date_debut) = ?
        ";
        $results = $this->conn->fetchAllAssociative($sql, [$date]);

        return $this->toZoneCoupee($results);
    }

    public function fetchZoneCoupeeOnBoundsByDate($debut, $fin): array
    {
        $sql = "
            SELECT * 
            FROM v_zone_coupee 
            WHERE date_debut <= ? AND date_fin >= ?
        ";
        $results = $this->conn->fetchAllAssociative($sql, [$fin, $debut]);

        return $this->toZoneCoupee($results);
    }

    public function toZoneCoupee(array $results): array
    {
        $listZones = [];

        foreach ($results as $row) {
            $id = $row['id_coupure_zone'];
            $zone = new ZoneElectrique();
            $zone->init(
                $row['id_zone'],
                $row['ref_secteur'],
                $row['ref_zone'],
                $row['specificite'],
                $row['lieux'],
                $row['postes'],
                $row['degree'],
                PolygonType::convert($row['coord'])
            );
    
            $coupure = new Coupure();
            $coupure->init(
                $row['id_coupure'],
                $row['ref_coupure'],
                $row['confidentialite_id'],
                $row['date_annonce'] !== null ? \DateTime::createFromFormat('Y-m-d H:i:s', $row['date_saisie']) : null,
                $row['date_annonce'] !== null ? \DateTime::createFromFormat('Y-m-d H:i:s', $row['date_annonce']) : null,
                \DateTime::createFromFormat('Y-m-d H:i:s', $row['date_debut']),
                \DateTime::createFromFormat('Y-m-d H:i:s', $row['date_fin']),
                $row['motif'],
                $row['division'],
                $row['sa'],
                $row['employe_id'],
                $row['etat']
            );
    
            $zoneCoupee = new ZoneCoupee($id, $zone, $coupure);
    
            $listZones[] = $zoneCoupee;
        }

        return $listZones;
    }



}
