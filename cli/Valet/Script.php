<?php

namespace Valet;

use Symfony\Component\Process\Process;

class Script
{
    const PORTS = array(
        'NGINX' => 443,
        'MYSQL' => 3306
    );

    public $cli;
    public $files;

    /**
     * Create a new Brew instance.
     *
     * @param  CommandLine $cli
     * @param  Filesystem $files
     */
    public function __construct(CommandLine $cli, Filesystem $files)
    {
        $this->cli = $cli;
        $this->files = $files;
    }

    public function portCheck()
    {
        foreach (self::PORTS as $service => $port) {
            $result = null;
            $command = 'lsof -Pi :' . $port . ' -sTCP:LISTEN -t';
            $result = $this->cli->run($command);

            if($result !== '') {
                warning(sprintf('%s port (%s) is already in use.', $service, $port));
            }
        }
    }

    public function post($domain)
    {
        $this->cli->run(sprintf('open http://valet.%s', $domain));
    }
}
