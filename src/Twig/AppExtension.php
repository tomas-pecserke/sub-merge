<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension {
    private const SUFFIX = ['', 'k', 'M', 'G'];

    public function getFilters(): array {
        return [
            new TwigFilter('file_size', fn($value) => self::formatSize($value)),
        ];
    }



    public static function formatSize($size): string {
        $suffix = 0;
        while ($size >= 1000.0 && $suffix < count(self::SUFFIX)) {
            $suffix++;
            $size /= 1000.0;
        }

        return round($size, 1) . ' ' . self::SUFFIX[$suffix] . 'B';
    }
}
