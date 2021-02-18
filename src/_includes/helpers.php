<?php declare(strict_types=1);

use Illuminate\Container\Container;
use SebastianBergmann\Version;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Define the ~/.valet path as a constant.
 */
define('VALET_HOME_PATH', $_SERVER['HOME'] . '/.valet');
define('VALET_SERVER_PATH', realpath(__DIR__ . '/../../server.php'));
define('VALET_STATIC_PREFIX', '41c270e4-5535-4daa-b23e-c269744c2f45');

/**
 * Output the given text to the console.
 *
 * @param string $output
 *
 * @return void
 */
function success(string $output)
{
    output('<fg=green>' . $output . '</>');
}

/**
 * Output the given text to the console.
 *
 * @param string $output
 *
 * @return void
 */
function info(string $output)
{
    output('<info>' . $output . '</info>');
}

/**
 * Output the given text to the console.
 *
 * @param string $output
 *
 * @return void
 */
function warning(string $output)
{
    output('<fg=yellow>' . $output . '</>');
}

/**
 * Output the given text to the console.
 *
 * @param string $output
 *
 * @return void
 */
function error(string $output)
{
    output('<fg=red>' . $output . '</>');
}

/**
 * get current version based on git describe and tags
 *
 * @return Version
 */
function version(): Version
{
    $version = trim(file_get_contents(__DIR__ . '/../../version'));

    return new Version($version, __DIR__ . '/../../');
}

/**
 * Output a table to the console.
 *
 * @param array $headers
 * @param array $rows
 *
 * @return void
 */
function table(array $headers = [], array $rows = [])
{
    $table = new Table(new ConsoleOutput);

    $table->setHeaders($headers)->setRows($rows);

    $table->render();
}

/**
 * Output the given text to the console.
 *
 * @param string $output
 *
 * @return void
 */
function output($output)
{
    if (isset($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'testing') {
        return;
    }

    (new ConsoleOutput)->writeln($output);
}

if (!function_exists('resolve')) {
    /**
     * Resolve the given class from the container.
     *
     * @param string $class
     *
     * @return mixed
     */
    function resolve($class)
    {
        return Container::getInstance()->make($class);
    }
}

/**
 * Swap the given class implementation in the container.
 *
 * @param string $class
 * @param mixed $instance
 *
 * @return void
 */
function swap(string $class, $instance): void
{
    Container::getInstance()->instance($class, $instance);
}

if (!function_exists('retry')) {
    /**
     * Retry the given function N times.
     *
     * @param int $retries
     * @param $fn
     * @param int $sleep
     *
     * @return mixed
     * @throws Exception
     */
    function retry(int $retries, $fn, int $sleep = 0)
    {
        beginning:
        try {
            return $fn();
        } catch (Exception $e) {
            if (!$retries) {
                throw $e;
            }

            $retries--;

            if ($sleep > 0) {
                usleep($sleep * 1000);
            }

            goto beginning;
        }
    }
}

/**
 * Verify that the script is currently running as "sudo".
 *
 * @return void
 * @throws Exception
 */
function should_be_sudo(): void
{
    if (!isset($_SERVER['SUDO_USER'])) {
        throw new Exception('This command must be run with sudo.');
    }
}

if (!function_exists('tap')) {
    /**
     * Tap the given value.
     *
     * @param mixed $value
     * @param callable $callback
     *
     * @return mixed
     */
    function tap($value, callable $callback)
    {
        $callback($value);

        return $value;
    }
}

if (!function_exists('ends_with')) {
    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string $haystack
     * @param string|array $needles
     *
     * @return bool
     */
    function ends_with(string $haystack, $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string)$needle) {
                return true;
            }
        }

        return false;
    }
}

/**
 * Get the user
 */
function user(): string
{
    if (isset($_SERVER['SUDO_USER']) && $_SERVER['SUDO_USER'] !== null) {
        return $_SERVER['SUDO_USER'];
    }

    if (isset($_SERVER['USER']) && $_SERVER['USER'] !== null) {
        return $_SERVER['USER'];
    }

    return '';
}
