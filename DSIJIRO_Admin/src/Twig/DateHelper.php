<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class DateHelper extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('getDay', [$this, 'getDay']),
            new TwigFilter('getDateInterval', [$this, 'getDateInterval']),
        ];
    }

    public function getDay(\DateTimeInterface $date): string
    {
        $days = [
            'Monday' => 'Lundi',
            'Tuesday' => 'Mardi',
            'Wednesday' => 'Mercredi',
            'Thursday' => 'Jeudi',
            'Friday' => 'Vendredi',
            'Saturday' => 'Samedi',
            'Sunday' => 'Dimanche',
        ];

        return $days[$date->format('l')];
    }

    public function getDateInterval(\DateTimeInterface $startDate, \DateTimeInterface $endDate): string
    {
        $startMonth = $startDate->format('F');
        $startYear = $startDate->format('Y');
        $endMonth = $endDate->format('F');
        $endYear = $endDate->format('Y');

        if ($startYear !== $endYear) {
            return sprintf('du %s %s au %s %s %s', 
                $startDate->format('d'),
                $startMonth,
                $endDate->format('d'),
                $endMonth,
                $endYear
            );
        } elseif ($startMonth !== $endMonth) {
            return sprintf('du %s %s au %s %s %s', 
                $startDate->format('d'),
                $startMonth,
                $endDate->format('d'),
                $endMonth,
                $endYear
            );
        } else {
            return sprintf('du %s au %s %s', 
                $startDate->format('d'),
                $endDate->format('d'),
                $endMonth
            );
        }
    }
}
