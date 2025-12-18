<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('euro_date', [$this, 'formatEuroDate']),
        ];
    }

    public function formatEuroDate(?\DateTimeInterface $date, string $format = 'd/m/Y'): string
    {
        if (!$date) {
            return '';
        }
        
        return $date->format($format);
    }
}