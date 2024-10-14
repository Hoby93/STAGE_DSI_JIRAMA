<?php

namespace App\Entity;

use Twig\Node\Expression\Test\NullTest;

class Filtre
{
    private int $page;
    private int $limit;
    private string $motclee;
    private string $groupby;
    private string $orderby;
    private string $orderinc;
    private string $dategroupe;
    private string $datevalue;

    private string $datedebut = '0000-01-01';
    private string $datefin = '9999-12-31';


    public function __construct($page, $limit, $groupby, $motclee, $orderby, $orderinc, $dategroupe = null, $datevalue = null) {
        $this->page = addslashes($page);
        $this->limit = addslashes($limit);
        $this->groupby = addslashes($groupby);
        $this->motclee = addslashes($motclee);
        $this->orderby = addslashes($orderby);
        $this->orderinc = addslashes($orderinc);
        $this->dategroupe = $dategroupe;
        $this->datevalue = $datevalue;
    }

    // Getter et Setter pour $page
    public function getPage(): string {
        return $this->page;
    }

    public function setPage(string $page): void {
        $this->page = $page;
    }

    // Getter et Setter pour $limit
    public function getLimit(): string {
        return $this->limit;
    }

    public function setLimit(string $limit): void {
        $this->limit = $limit;
    }

    // Getter et Setter pour $motclee
    public function getMotclee(): string {
        return $this->motclee;
    }

    public function setMotclee(string $motclee): void {
        $this->motclee = $motclee;
    }

    // Getter et Setter pour $groupby
    public function getGroupby(): string {
        return $this->groupby;
    }

    public function setGroupby(string $groupby): void {
        $this->groupby = $groupby;
    }

    // Getter et Setter pour $orderby
    public function getOrderby(): string {
        return $this->orderby;
    }

    public function setOrderby(string $orderby): void {
        $this->orderby = $orderby;
    }

    // Getter et Setter pour $orderinc
    public function getOrderinc(): string {
        return $this->orderinc;
    }

    public function setOrderinc(string $orderinc): void {
        $this->orderinc = $orderinc;
    }

    public function getDateValue(): string {
        return $this->datevalue;
    }

    public function setDateInterval($datedebut, $datefin): void {
        if (!empty($datedebut)) {
            $this->datedebut = $datedebut;
        }
    
        if (!empty($datefin)) {
            $this->datefin = $datefin;
        }
    }

    public function getDateFilter(): string {
        if(empty($this->datevalue)) {
            return "TRUE";
        }
        if($this->dategroupe == 'jour') {
            return "date_coupure = '{$this->datevalue}'";
        }
        else if($this->dategroupe == 'mois') {
            return "DATE_FORMAT(date_coupure, '%Y-%m') = '{$this->datevalue}'";
        }
        return "YEAR(date_coupure) = {$this->datevalue}";
    }

    public function getDateFilterOptions(): array {
        if($this->dategroupe == 'jour') {
            return [
                'filterby' => "date_coupure",
                'filterof' => "date_coupure"
            ];
        }
        else if($this->dategroupe == 'mois') {
            return [
                'filterby' => "DATE_FORMAT(date_coupure, '%Y-%m')",
                'filterof' => "date_coupure"
            ];
        }
        return [
            'filterby' => "DATE_FORMAT(date_coupure, '%Y')",
            'filterof' => "DATE_FORMAT(date_coupure, '%Y-%m')"
        ];
    }  
    
    public function getDateIntervalFilter(): string
    {
        return "
            date_debut >= '{$this->datedebut}' AND date_debut <= '{$this->datefin}'
        ";
    }
}
