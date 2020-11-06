<?php

/**
 * Check the system's compatibility with Valet.
 */
$inTestingEnvironment = strpos($_SERVER['SCRIPT_NAME'], 'phpunit') !== false;

if (PHP_OS !== 'Darwin' && !$inTestingEnvironment) {
    echo 'Valet only supports the Mac operating system.' . PHP_EOL;

    exit(1);
}

//@TODO: do we really need this check?
if (PHP_VERSION_ID <= 70200) {
    echo 'Valet requires PHP 7.2 or later.';

    exit(1);
}

if (exec('which brew') == '' && !$inTestingEnvironment) {
    echo 'Valet requires Homebrew to be installed on your Mac.';

    exit(1);
}
