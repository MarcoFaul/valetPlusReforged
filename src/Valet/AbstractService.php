<?php declare(strict_types=1);

namespace Valet;

use ReflectionClass;
use ReflectionException;

abstract class AbstractService
{
    const STATE_DISABLED = false;
    const STATE_ENABLED = true;

    public $configuration;
    public $configClassName;

    /**
     * AbstractService constructor.
     *
     * @param Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Returns the short class name in lowercase.
     *
     * @return string
     */
    public function getConfigClassName(): string
    {
        if (!$this->configClassName) {
            try {
                $this->configClassName = strtolower((new ReflectionClass($this))->getShortName());
            } catch (ReflectionException $reflectionException) {
                echo 'Ohoh reflection exception';
                die();
            }
        }

        return $this->configClassName;
    }

    /**
     * Returns wether the service is enabled or not.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        $config = $this->configuration->read();
        $name = $this->getConfigClassName();

        return (isset($config[$name]) && isset($config[$name]['enabled']) && $config[$name]['enabled'] == self::STATE_ENABLED);
    }

    /**
     * Stores the active state in the configuration.
     *
     * @param $state
     */
    public function setEnabled(bool $state): void
    {
        $config = $this->configuration->read();
        $name = $this->getConfigClassName();
        if (!isset($config[$name])) {
            $config[$name] = [];
        }
        $config[$name]['enabled'] = $state;
        $this->configuration->write($config);
    }

    /**
     * Stops the service and stores in configuration it should not be started.
     */
    public function disable(): void
    {
        $this->stop();
        $this->setEnabled(self::STATE_DISABLED);
    }

    /**
     * Installs the service if not installed, restarts it and stores in configuration it should be started.
     */
    public function enable(): void
    {
        $this->setEnabled(self::STATE_ENABLED);
        if ($this->installed()) {
            $this->restart();

            return;
        }
        $this->install();
    }

    /**
     * Implement installation of the service.
     */
    abstract public function install();

    /**
     * Implement wether or not the service is installed.
     *
     * @return bool
     */
    abstract public function installed();

    /**
     * Implement stopping the service.
     */
    abstract public function stop();

    /**
     * Implement restarting the service.
     */
    abstract public function restart();
}
