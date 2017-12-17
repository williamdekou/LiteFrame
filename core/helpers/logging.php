<?php

use LiteFrame\Logger;

/**
 * Log error to file.
 *
 * @param type $exception
 */
function logger($exception)
{
    (new Logger($exception, Logger::MEDIUM_FILE))->log();
}

function exceptionHandler($exception)
{
    $outputMedium = config('app.env') === 'local' ?
            Logger::MEDIUM_STDOUT_FILE :
            Logger::MEDIUM_FILE;
    //Log
    (new Logger($exception, $outputMedium))->log();
}

function errorHandler($errno, $errstr, $errfile, $errline)
{
    if ($errno && error_reporting()) {
        $date = date('Y-m-d H:i:s');
        $message = "Error ($errno): [$date] $errfile line $errline\n"
                .$errstr.PHP_EOL;
        logger($message);

        if (config('app.env') === 'local') {
            echo nl2br("$errstr at $errfile line $errline\n");
        }
    }
}

function shutdownHandler()
{
    $err = error_get_last();
    if (empty($err)) {
        return;
    }

    $handledErrors = [
        E_USER_ERROR => 'USER ERROR',
        E_ERROR => 'ERROR',
        E_PARSE => 'PARSE',
        E_CORE_ERROR => 'CORE_ERROR',
        E_CORE_WARNING => 'CORE_WARNING',
        E_COMPILE_ERROR => 'COMPILE_ERROR',
        E_COMPILE_WARNING => 'COMPILE_WARNING',
    ];

    // If our last error wasn't fatal then this must be a normal shutdown.
    if (!isset($handledErrors[$err['type']])) {
        return;
    }

    $date = date('Y-m-d H:i:s');
    $message = "Shutdown Error: [$date] {$err['file']} line {$err['line']}\n"
            .$err['message'].PHP_EOL;
    logger($message);

    if (!headers_sent()) {
        abort(500);
    }
}

function getErrorName($errno)
{
    switch ($errno) {

        case 1: $e_type = 'E_ERROR';
            $exit_now = true;
            break;

        case 2: $e_type = 'E_WARNING';
            break;

        case 4: $e_type = 'E_PARSE';
            break;

        case 8: $e_type = 'E_NOTICE';
            break;

        case 16: $e_type = 'E_CORE_ERROR';
            $exit_now = true;
            break;

        case 32: $e_type = 'E_CORE_WARNING';
            break;

        case 64: $e_type = 'E_COMPILE_ERROR';
            $exit_now = true;
            break;

        case 128: $e_type = 'E_COMPILE_WARNING';
            break;

        case 256: $e_type = 'E_USER_ERROR';
            $exit_now = true;
            break;

        case 512: $e_type = 'E_USER_WARNING';
            break;

        case 1024: $e_type = 'E_USER_NOTICE';
            break;

        case 2048: $e_type = 'E_STRICT';
            break;

        case 4096: $e_type = 'E_RECOVERABLE_ERROR';
            $exit_now = true;
            break;

        case 8192: $e_type = 'E_DEPRECATED';
            break;

        case 16384: $e_type = 'E_USER_DEPRECATED';
            break;

        case 30719: $e_type = 'E_ALL';
            $exit_now = true;
            break;

        default: $e_type = 'E_UNKNOWN';
            break;
    }

    return $e_type;
}
