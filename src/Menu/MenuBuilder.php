<?php

namespace App\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;

class MenuBuilder {
    private readonly MenuFactory $factory;

    public function __construct(FactoryInterface $factory) {
        $this->factory = $factory;
    }

    public function createMainMenu(array $options): ItemInterface {
        $menu = $this->factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav flex-column');

        $menu->addChild('Dashboard', ['route' => 'dashboard.index', 'icon' => 'home']);
        $menu->addChild('Videos', ['route' => 'video.list', 'icon' => 'film']);
        $menu->addChild('Activity', ['route' => 'index', 'icon' => 'clock']);
        $menu->addChild('Queue', ['route' => 'index', 'icon' => 'list']);
        $menu->addChild('Settings', ['route' => 'settings.index', 'icon' => 'settings']);
        $menu->addChild('System', ['route' => 'index', 'icon' => 'monitor']);

        // you can also add sub levels to your menus as follows
        //$menu->addChild('About Me', ['route' => 'view_profile');
        //$menu['About Me']->addChild('Edit profile', ['route' => 'edit_profile']);

        return $menu;
    }
}
