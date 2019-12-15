<?php

namespace laraone\themedev\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;

class ThemeSyncCommand extends Command
{
    /**
     * This command is used when developing, to push the compiled theme zip file to phoenix backend
     * so we do not have to manually copy theme.
     *
     * @var string
     */
    protected $signature = 'theme:sync {--folders} {--zip}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Synchronize working directory with Phoenix where theme is installed. Used during development only.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Theme sync command';

    public function handle()
    {
        $foldersOption = $this->option('folders');
        $zipOption = $this->option('zip');
        $themeData = $this->getThemeData();
        $themeName = $themeData->name;
        $phoenixPath = config('app.phoenix_path');

        if ($zipOption) {
            if(file_exists('build' . DIRECTORY_SEPARATOR . $themeName . '.zip')) {
                copy(
                    'build' . DIRECTORY_SEPARATOR . $themeName . '.zip', 
                    $phoenixPath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $themeName . '.zip'
                );
                $this->info('Zip has been synced with Phoenix');
            } else {
                exit('Theme has not been packed into a zip file yet. Run theme:pack command first.');
            }
        }

        // sync views
        if(file_exists('src' . DIRECTORY_SEPARATOR . 'views')) {
            copyDirectory(
                'src' . DIRECTORY_SEPARATOR . 'views', 
                $phoenixPath . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $themeName
            );
        } else {
            exit('Folder src\view is missing, there is nothing to sync.');
        }

        // sync assets
        if(file_exists('build' . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'assets')) {
            copyDirectory(
                'build' . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'assets', 
                $phoenixPath . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . $themeName
            );
        } else {
            exit('Assets are missing, make sure you compile the assets first with npm run dev or yarn run dev.');
        }

        $this->info('Theme has been synced to Phoenix.');
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }

    private function getThemeData()
    {
        $src = base_path() . DIRECTORY_SEPARATOR . 'src';
        $themeData = json_decode(file_get_contents($src . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'theme.json'));
        return $themeData;
    }
}
