<?php

namespace App\Entity;

class ZoneCoupee
{
    private int $id;
    private ZoneElectrique $zone;
    private Coupure $coupure;

    public function __construct(int $id, ZoneElectrique $zone = null, Coupure $coupure = null)
    {
        $this->id = $id;
        $this->zone = $zone ?? new ZoneElectrique();
        $this->coupure = $coupure ?? new Coupure();
    }

    // Getter pour la propriété $Id
    public function getId(): int
    {
        return $this->id;
    }

    // Setter pour la propriété $Id
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    // Getter pour la propriété $zone
    public function getZone(): ZoneElectrique
    {
        return $this->zone;
    }

    // Setter pour la propriété $zone
    public function setZone(ZoneElectrique $zone): self
    {
        $this->zone = $zone;
        return $this;
    }

    // Getter pour la propriété $coupure
    public function getCoupure(): Coupure
    {
        return $this->coupure;
    }

    // Setter pour la propriété $coupure
    public function setCoupure(Coupure $coupure): self
    {
        $this->coupure = $coupure;
        return $this;
    }
}
