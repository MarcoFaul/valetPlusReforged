<?php declare(strict_types=1);

namespace Valet;

use DomainException;

//@TODO: refactor paths
class Brew
{

    public $cli;
    public $files;

    /**
     * Create a new Brew instance.
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
     * Determine if the given formula is installed.
     *
     * @param string $formula
     *
     * @return bool
     */
    public function installed(string $formula): bool
    {
        return in_array($formula, explode(PHP_EOL, $this->cli->runAsUser('brew list | grep ' . $formula)));
    }

    /**
     * Determine if a compatible nginx version is Homebrewed.
     *
     * @return bool
     */
    public function hasInstalledNginx(): bool
    {
        return $this->installed('nginx')
            || $this->installed('nginx-full');
    }

    /**
     * Return name of the nginx service installed via Homebrewed.
     *
     * @return string
     */
    public function nginxServiceName(): string
    {
        return $this->installed('nginx-full') ? 'nginx-full' : 'nginx';
    }

    /**
     * Remove the "sudoers.d" entry for running Brew.
     *
     * @return void
     */
    function removeSudoersEntry(): void
    {
        $this->cli->quietly('rm /etc/sudoers.d/brew');
    }

    /**
     * Create the "sudoers.d" entry for running Brew.
     *
     * @return void
     */
    function createSudoersEntry(): void
    {
        $this->files->ensureDirExists('/etc/sudoers.d');

        $this->files->put('/etc/sudoers.d/brew', 'Cmnd_Alias BREW = /usr/local/bin/brew *
%admin ALL=(root) NOPASSWD:SETENV: BREW' . PHP_EOL);
    }

    /**
     * Ensure that the given formula is installed.
     *
     * @param string $formula
     * @param array $options
     * @param array $taps
     *
     * @return void
     */
    public function ensureInstalled(string $formula, array $options = [], array $taps = []): void
    {
        if (!$this->installed($formula)) {
            $this->installOrFail($formula, $options, $taps);
        }
    }

    /**
     * Ensure that the given formula is uninstalled.
     *
     * @param string $formula
     * @param array $options
     * @param array $taps
     *
     * @return void
     */
    public function ensureUninstalled(string $formula, array $options = [], array $taps = []): void
    {
        if ($this->installed($formula)) {
            $this->uninstallOrFail($formula, $options, $taps);
        }
    }

    /**
     * Install the given formula and throw an exception on failure.
     *
     * @param string $formula
     * @param array $options
     * @param array $taps
     *
     * @return void
     */
    public function installOrFail(string $formula, array $options = [], array $taps = []): void
    {
        info('[' . $formula . '] Installing');

        if (count($taps) > 0) {
            $this->tap($taps);
        }

        $this->cli->runAsUser(
            trim('brew install ' . $formula . ' ' . implode(' ', $options)),
            function ($exitCode, $errorOutput) use ($formula) {
                output($errorOutput);

                throw new DomainException('Brew was unable to install [' . $formula . '].');
            }
        );
    }

    /**
     * Uninstall the given formula and throw an exception on failure.
     *
     * @param string $formula
     * @param array $options
     * @param array $taps
     *
     * @return void
     */
    public function uninstallOrFail(string $formula, array $options = [], array $taps = []): void
    {
        info('[' . $formula . '] Uninstalling');

        if (count($taps) > 0) {
            $this->tap($taps);
        }

        $this->cli->runAsUser(
            trim('brew uninstall ' . $formula . ' ' . implode(' ', $options)),
            function ($exitCode, $errorOutput) use ($formula) {
                output($errorOutput);

                throw new DomainException('Brew was unable to uninstall [' . $formula . '].');
            }
        );
    }

    /**
     * Tap the given formulas.
     *
     * @param mixed $formula
     *
     * @return void
     */
    public function tap($formulas): void
    {
        $formulas = is_array($formulas) ? $formulas : func_get_args();

        foreach ($formulas as $formula) {
            $this->cli->passthru('sudo -u ' . user() . ' brew tap ' . $formula);
        }
    }

    /**
     * Untap the given formulas.
     *
     * @param mixed $formula
     *
     * @return void
     */
    public function unTap($formulas): void
    {
        $formulas = is_array($formulas) ? $formulas : func_get_args();

        foreach ($formulas as $formula) {
            $this->cli->passthru('sudo -u ' . user() . ' brew untap ' . $formula);
        }
    }

    /**
     * Check if brew has the given tap.
     *
     * @param string $formula
     *
     * @return bool
     */
    public function hasTap(string $formula): bool
    {
        return strpos($this->cli->runAsUser("brew tap | grep $formula"), $formula) !== false;
    }

    /**
     * Restart the given Homebrew services.
     *
     * @param mixed $services
     */
    public function restartService($services)
    {
        $services = is_array($services) ? $services : func_get_args();

        foreach ($services as $service) {
            if ($this->installed($service)) {
                info('[' . $service . '] Restarting');

                $this->cli->quietly('sudo brew services stop ' . $service);
                $this->cli->quietly('sudo brew services start ' . $service);
            }
        }
    }

    /**
     * Stop the given Homebrew services.
     *
     * @param mixed $services
     */
    public function stopService($services): void
    {
        $services = is_array($services) ? $services : func_get_args();

        foreach ($services as $service) {
            if ($this->installed($service)) {
                info('[' . $service . '] Stopping');

                $this->cli->quietly('sudo brew services stop ' . $service);
            }
        }
    }

    /**
     * Checks wether the requested services is running.
     *
     * @param string $formula
     *
     * @return bool
     */
    public function isStartedService(string $formula): bool
    {
        $info = explode(' ', trim(str_replace($formula, '', $this->cli->runAsUser('brew services list | grep ' . $formula))));
        $state = array_shift($info);

        return ($state === 'started');
    }
}
