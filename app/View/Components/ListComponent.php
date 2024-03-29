<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ListComponent extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public array|null $list = []
    ) {
        // dd($list);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.list-component.index');
    }
}
