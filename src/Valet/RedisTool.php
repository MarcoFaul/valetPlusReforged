<?php declare(strict_types=1);

namespace Valet;

class RedisTool extends AbstractService
{
    const REDIS_CONF = '/usr/local/etc/redis.conf';
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
    public function __construct(
        Brew $brew,
        CommandLine $cli,
        Filesystem $files,
        Configuration $configuration,
        Site $site
    )
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
            info('[redis] already installed');
        } else {
            $this->brew->installOrFail('redis');
            $this->cli->quietly('sudo brew services stop redis');
        }

        $this->installConfiguration();
        $this->setEnabled(self::STATE_ENABLED);
        $this->restart();
    }

    /**
     * Returns wether redis is installed or not.
     *
     * @return bool
     */
    public function installed(): bool
    {
        return $this->brew->installed('redis');
    }

    /**
     * Install the configuration file.
     *
     * @return void
     */
    public function installConfiguration(): void
    {
        $this->files->copy(__DIR__ . '/../stubs/redis.conf', static::REDIS_CONF);
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

        info('[redis] Restarting');
        $this->cli->quietlyAsUser('brew services restart redis');
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

        info('[redis] Stopping');
        $this->cli->quietly('sudo brew services stop redis');
        $this->cli->quietlyAsUser('brew services stop redis');
    }

    /**
     * Prepare for uninstallation.
     *
     * @return void
     */
    public function uninstall(): void
    {
        $this->stop();
    }
}
