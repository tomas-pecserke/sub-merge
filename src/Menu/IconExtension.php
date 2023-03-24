<?php

namespace App\Menu;

use Knp\Menu\Factory\ExtensionInterface;
use Knp\Menu\ItemInterface;

class IconExtension implements ExtensionInterface {
    public function buildOptions(array $options): array {
        return array_merge(['icon' => null], $options);
    }

    public function buildItem(ItemInterface $item, array $options): void {
        if (!empty($options['icon'])) {
            $item->setExtra('icon', $options['icon']);
        }
    }
}
