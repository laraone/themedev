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
    protected $description = 'Command description';

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
            copy('build/' . $themeName . '.zip', $phoenixPath . '/storage/themes/' . $themeName . '.zip');
            $this->info('Zip has been synced with Phoenix');
        }

        if ($foldersOption) {
            $this->copyDirectory('theme/views', $phoenixPath . '/resources/themes/' . $themeName);
            $this->copyDirectory('theme/assets', $phoenixPath . '/public/themes/' . $themeName);
            $this->info('Working directories has been synced with Phoenix');
        }
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
        $theme = base_path() . DIRECTORY_SEPARATOR . 'theme';
        $themeData = json_decode(file_get_contents($theme . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'theme.json'));
        return $themeData;
    }

    private function copyDirectory($from, $to, $rewrite = true)
    {
        if (is_dir($from)) {
            @mkdir($to);
            $d = dir($from);
            while (false !== ($entry = $d->read())) {
                if ($entry == "." || $entry == "..") {
                    continue;
                }

                $this->copyDirectory("$from/$entry", "$to/$entry", $rewrite);
            }
            $d->close();
        } else {
            if (!file_exists($to) || $rewrite) {
                copy($from, $to);
            }

        }
    }
}
