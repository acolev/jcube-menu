<?php

use jCube\Models\Menu;

function getMenu($parent = null)
{
    $menus = Menu::with(['allSubItems'])
        ->where('parent_id', $parent)
        ->orderBy('position')
        ->get();

    return $menus;
}
