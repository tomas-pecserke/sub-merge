<?php

namespace App\Util;

use PhpParser\Builder\EnumCase;
use ReflectionEnum;

class Enums {
    private function __construct() {
    }

    public static function enumNames(string $enumClass): array {
        $reflection = new ReflectionEnum($enumClass);
        return array_map(fn($case) => $case->name, $reflection->getCases());
    }
}
