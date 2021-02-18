<?php declare(strict_types=1);

namespace Valet;

use Httpful\Exception\ConnectionErrorException;
use Httpful\Request;
use function is_dir;
use function sprintf;

class Valet
{
    private const GITHUB_LATEST_RELEASE_URL = 'https://api.github.com/repos/MarcoFaul/valetPlusReforged/releases/latest';
    private const SUDOERS_PATH = '/etc/sudoers.d';
    public $cli;
    public $files;
    public $valetBin = '/usr/local/bin/valet';

    /**
     * Create a new Valet instance.
     *
     * @param CommandLine $cli
     * @param Filesystem $files
     */
    public function __construct(CommandLine $cli, Filesystem $files)
    {
        $this->cli = $cli;
        $this->files = $files;
    }

    /**
     * Symlink the Valet Bash script into the user's local bin.
     *
     * @return void
     */
    public function symlinkToUsersBin(): void
    {
        $this->cli->quietlyAsUser('rm ' . $this->valetBin);

        $this->cli->runAsUser('ln -s ' . realpath(__DIR__ . '/../../valet') . ' ' . $this->valetBin);
    }

    /**
     * Get the paths to all of the Valet extensions.
     *
     * @return array
     */
    public function extensions(): array
    {
        if (!$this->files->isDir(VALET_HOME_PATH . '/Extensions')) {
            return [];
        }

        return collect($this->files->scandir(VALET_HOME_PATH . '/Extensions'))
            ->reject(function ($file) {
                return is_dir($file);
            })
            ->map(function ($file) {
                return VALET_HOME_PATH . '/Extensions/' . $file;
            })
            ->values()->all();
    }

    /**
     * Create the "sudoers.d" entry for running Valet.
     *
     * @return void
     */
    function createSudoersEntry(): void
    {
        $this->files->ensureDirExists(self::SUDOERS_PATH);

        $this->files->put(self::SUDOERS_PATH . '/valet', sprintf('Cmnd_Alias VALET = %s *
%%admin ALL=(root) NOPASSWD:SETENV: VALET %s', $this->valetBin, PHP_EOL));
    }

    /**
     * Remove the "sudoers.d" entry for running Valet.
     *
     * @return void
     */
    function removeSudoersEntry(): void
    {
        $this->cli->quietly(sprintf('rm %s/valet', self::SUDOERS_PATH));
    }

    /**
     * Determine if this is the latest version of Valet.
     *
     * @param string $currentVersion
     *
     * @return bool|int
     * @throws ConnectionErrorException
     */
    public function onLatestVersion($currentVersion)
    {
        $response = Request::get(self::GITHUB_LATEST_RELEASE_URL)->send();

        return version_compare($currentVersion->getVersion(), $response->body->tag_name, '>=');
    }
}
