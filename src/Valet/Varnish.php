<?php declare(strict_types=1);

namespace Valet;

class Varnish extends AbstractService
{
    public $brew;
    public $cli;
    public $files;
    public $site;

    /**
     * Create a new instance.
     *
     * @param Brew $brew
     * @param CommandLine $cli
     * @param Filesystem $files
     * @param Configuration $configuration
     * @param Site $site
     */
    public function __construct(Brew $brew, CommandLine $cli, Filesystem $files, Configuration $configuration, Site $site)
    {
        $this->cli = $cli;
        $this->brew = $brew;
        $this->site = $site;
        $this->files = $files;
        parent::__construct($configuration);
    }

    /**
     * Install the service.
     *
     * @return void
     */
    public function install(): void
    {
        if ($this->installed()) {
            info('[varnish] already installed');
        } else {
            $this->brew->installOrFail('varnish');
            $this->cli->quietly('sudo brew services stop varnish');
        }
        $this->setEnabled(self::STATE_ENABLED);
        $this->restart();
    }

    /**
     * Returns whenever varnish is installed or not.
     *
     * @return bool
     */
    public function installed(): bool
    {
        return $this->brew->installed('varnish');
    }

    /**
     * Restart the service.
     *
     * @return void
     */
    public function restart(): void
    {
        if (!$this->installed() || !$this->isEnabled()) {
            return;
        }

        info('[varnish] Restarting');
        $this->cli->quietlyAsUser('brew services restart varnish');
    }

    /**
     * Stop the service.
     *
     * @return void
     */
    public function stop(): void
    {
        if (!$this->installed()) {
            return;
        }

        info('[varnish] Stopping');
        $this->cli->quietlyAsUser('brew services stop varnish');
    }

    /**
     * Wrapper to stop
     *
     * @return void
     */
    public function uninstall()
    {
        $this->stop();
    }
}
