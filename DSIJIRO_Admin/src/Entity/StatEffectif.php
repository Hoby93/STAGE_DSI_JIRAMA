<?php

namespace App\Entity;

use App\Service\StatService;

class StatEffectif
{
    private $tous;
    private string $motclee;
    private string $groupby;
    private string $orderby;
    private string $orderinc;
    private array $results;

    public function __construct($groupby, $motclee, $orderby, $orderinc) {
        $this->groupby = $groupby;
        $this->motclee = $motclee;
        $this->orderby = $orderby;
        $this->orderinc = $orderinc;
    }

    public function init(StatService $statService) {
        $this->tous = $statService->fetchCountOfAll();
        $this->results = $statService->fetchCountInfraGroupByLocation($this->groupby, $this->motclee, $this->orderby, $this->orderinc);
    }

    public function init2(StatService $statService) {
        $this->tous = $statService->fetchCountOfAll();
        $this->results = $statService->fetchCountInfraGroupByLocation($this->groupby, $this->motclee, $this->orderby, $this->orderinc);
    }

    // Getter et Setter pour $tous
    public function getTous() {
        return $this->tous;
    }

    public function setTous($tous): void {
        $this->tous = $tous;
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

    // Getter et Setter pour $results
    public function getResults(): array {
        return $this->results;
    }

    public function setResults(array $results): void {
        $this->results = $results;
    }
}
