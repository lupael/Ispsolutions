<?php

namespace App\View\Components;

use App\Services\MenuService;
use Illuminate\View\Component;
use Illuminate\View\View;

class RoleBasedMenu extends Component
{
    /**
     * The menu items.
     *
     * @var array
     */
    public $menuItems;

    /**
     * Create a new component instance.
     */
    public function __construct(MenuService $menuService)
    {
        $this->menuItems = $menuService->generateMenu();
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.role-based-menu');
    }
}
