<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // Ajoute un filtre 'floatval' qui utilise la fonction PHP floatval
            new TwigFilter('floatval', 'floatval'),
        ];
    }
}
