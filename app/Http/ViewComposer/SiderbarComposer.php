<?php

namespace App\Http\ViewComposer;

use App\Models\Module;
use Illuminate\View\View;
use Route;

class SiderbarComposer
{

    /**
     * @return mixed
     */
    final function getModuleList() {
        return Module::where('parent',0)->orderBy('sort')->with('children')->get()->toArray();
    }

    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $view->with('modules', $this->getModuleList());
        $view->with('current_route_name', Route::currentRouteName());
    }
}