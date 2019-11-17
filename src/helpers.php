<?php
if(!function_exists('copyDirectory')) {
    function copyDirectory($from, $to, $rewrite = true) {
        if (is_dir($from)) {
            @mkdir($to);
            $d = dir($from);
            while (false !== ($entry = $d->read())) {
                if ($entry == "." || $entry == "..") {
                    continue;
                }

                copyDirectory("$from/$entry", "$to/$entry", $rewrite);
            }
            $d->close();
        } else {
            if (!file_exists($to) || $rewrite) {
                copy($from, $to);
            }
        }
    }
}

?>