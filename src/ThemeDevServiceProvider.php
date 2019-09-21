<?php

namespace laraone\themedev;

use Illuminate\Support\ServiceProvider;
use mysteryreloaded\laraonetheme\Commands\SampleCommand;

class ThemeDevServiceProvider extends ServiceProvider
{

    public function boot()
    {

    }

    public function register()
    {
        $this->commands([
            Commands\ThemePackageCommand::class,
            Commands\ThemeSyncCommand::class,
            Commands\ThemeValidateCommand::class,
        ]);
    }
}
