<?php declare(strict_types=1);

namespace Valet;

use Symfony\Component\Process\Process;

class CommandLine
{
    /**
     * Simple global function to run commands.
     *
     * @param string $command
     *
     * @return void
     */
    public function quietly(string $command): void
    {
        $this->runCommand($command . ' > /dev/null 2>&1');
    }

    public function getRunAsUserPrefix(): string
    {
        return 'sudo -u ' . user();
    }

    /**
     * Simple global function to run commands.
     *
     * @param string $command
     *
     * @return void
     */
    public function quietlyAsUser(string $command): void
    {
        $this->quietly('sudo -u ' . user() . ' ' . $command . ' > /dev/null 2>&1');
    }

    /**
     * Pass the command to the command line and display the output.
     *
     * @param string $command
     *
     * @return void
     */
    public function passthru(string $command): void
    {
        passthru($command);
    }

    /**
     * Run the given command as the non-root user.
     *
     * @param string $command
     * @param callable $onError
     *
     * @return string
     */
    public function run(string $command, callable $onError = null): string
    {
        return $this->runCommand($command, $onError);
    }

    /**
     * Run the given command.
     *
     * @param string $command
     * @param callable $onError
     *
     * @return string
     */
    public function runAsUser(string $command, callable $onError = null): string
    {
        return $this->runCommand('sudo -u ' . user() . ' ' . $command, $onError);
    }

    /**
     * Run the given command.
     *
     * @param string $command
     * @param callable|null $onError
     *
     * @return string
     */
    public function runCommand(string $command, callable $onError = null): string
    {
        $onError = $onError ?: static function () {};

        $process = Process::fromShellCommandline($command);

        $processOutput = '';
        $process->setTimeout(null)->run(function ($type, $line) use (&$processOutput) {
            $processOutput .= $line;
        });

        $process->run();


        if (!$process->isSuccessful()) {
            $onError($process->getExitCode(), $processOutput);
        }

        return $processOutput;
    }
}
