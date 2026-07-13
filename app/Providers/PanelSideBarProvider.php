<?php

namespace App\Providers;

use App\Http\ViewComposer\SiderbarComposer;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

class PanelSideBarProvider extends ServiceProvider
{
    public function __construct($app)
    {
        $this->sidebarProvider = new SiderbarComposer();
        parent::__construct($app);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        view()->composer('*' , function(View $view) {
            $this->sidebarProvider->compose($view);
        });
    }
}
