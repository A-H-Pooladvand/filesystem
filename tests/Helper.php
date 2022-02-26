<?php

use Symfony\Component\VarDumper\VarDumper;

if (!function_exists('dd')) {
    /**
     * @param  mixed  ...$vars
     *
     * @return void
     */
    function dd(...$vars): void
    {
        if (!in_array(\PHP_SAPI, ['cli', 'phpdbg'], true) && !headers_sent()) {
            header('HTTP/1.1 500 Internal Server Error');
        }

        foreach ($vars as $v) {
            VarDumper::dump($v);
        }

        exit(1);
    }
}