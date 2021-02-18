<?php declare(strict_types=1);

namespace Valet;

class Logs
{
    /** @var CommandLine */
    private $cli;

    /**
     * @param CommandLine $cli
     */
    public function __construct(CommandLine $cli)
    {
        $this->cli = $cli;
    }

    /**
     * @param string $file
     */
    public function open(string $file): void
    {
        $this->cli->passthru('open ' . $this->resolvePath($file));
    }

    /**
     * @param string $file
     *
     * @return bool
     */
    public function exists(string $file): bool
    {
        $file = $this->resolvePath($file);

        return file_exists($file);
    }

    /**
     * @param string $file
     *
     * @return string
     */
    private function resolvePath(string $file): string
    {
        return str_replace('$HOME', $_SERVER['HOME'], $file);
    }
}
