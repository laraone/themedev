<?php

namespace laraone\themedev\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File as File;
use JsonSchema\Validator as JsonValidator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class ThemePackageCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'theme:pack';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Package theme files into an installable zip.';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Package theme files command';

    public function handle()
    {
        if (extension_loaded('zip')) {

            // copy views to build directory
            $this->copyDirectory(
                'src' . DIRECTORY_SEPARATOR . 'views', 
                'build' . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'views'
            );

            $theme = base_path() . DIRECTORY_SEPARATOR . 'build'. DIRECTORY_SEPARATOR . 'theme';
            $themeData = json_decode(file_get_contents($theme . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'theme.json'));
            $validator = new JsonValidator;
            $jsonSchema = '..' . DIRECTORY_SEPARATOR . 'scheme.json';
            $validator->validate($themeData, $jsonSchema);

            if ($validator->isValid()) {
                $this->info('The supplied JSON validates against the schema.');
                $buildPath = 'build';
                $mode = 0777;
                File::makeDirectory($buildPath, $mode, false, true);

                // Sanitize target filename
                $zipFileName = $themeData->name;
                $zipFileName = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $zipFileName);
                $zipFileName = mb_ereg_replace("([\.]{2,})", '', $zipFileName);
                $zipFileName = $buildPath . DIRECTORY_SEPARATOR . "{$zipFileName}.zip";
                $zipFileName = strtolower(str_replace(' ', '-', $zipFileName));

                // Create ZipArchive Obj
                $zip = new ZipArchive;
                if ($zip->open($zipFileName, (ZipArchive::CREATE | ZipArchive::OVERWRITE)) === true) {
                    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($theme), RecursiveIteratorIterator::LEAVES_ONLY);
                    $this->info('Packing the theme...');
                    foreach ($files as $name => $file) {
                        // Skip directories (they would be added automatically)
                        if (!$file->isDir()) {
                            // Get real and relative path for current file
                            $filePath = $file->getRealPath();
                            $relativePath = substr($filePath, strlen($theme) + 1);

                            // Add current file to archive
                            $zip->addFile(str_replace('\\', '/', $filePath), str_replace('\\', '/', $relativePath));
                        }
                    }
                    $zip->close();
                    $this->info('Theme has been packed at ' . $zipFileName);
                }
            } else {
                $this->error('Theme json file does not validate. There are violations!');
                foreach ($validator->getErrors() as $error) {
                    $this->error($error['property'] . ': ' . $error['message']);
                }
            }
        } else {
            $this->error('Cant pack the theme, zip extension not installed.');
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
