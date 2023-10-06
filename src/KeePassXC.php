<?php

namespace App;

use Symfony\Component\Process\InputStream;
use Symfony\Component\Process\Process;

class KeePassXC
{
    private $exec;

    private $filename;

    private $password;

    private $timeout;

    /**
     * Console input prefix
     * @var string
     */
    private $prefix;

    public function __construct($filename, $password, $timeout = 10, $exec='keepassxc-cli')
    {
        $this->exec = $exec;
        $this->filename = $filename;
        $this->password = $password;
        $this->timeout = $timeout;

        $this->prefix = $this->filename . '> ';
    }

    public function getList($path = ''): array
    {
        $input = new InputStream();
        $process = $this->connect($input);
        $query = "ls \"$path\"\n";
        $input->write($query);

        $list = [];

        $process->waitUntil(function ($type, $output) use (&$list, $query): bool {
            $lines = array_filter(explode("\n", $output));

            if (str_starts_with(reset($lines), $this->prefix) && count($lines) > 1) {
                array_shift($lines);
            }

            foreach ($lines as $line) {
                if ($line === trim($query)) {
                    continue;
                }

                if ($line === $this->prefix) {
                    return true;
                }

                $list[] = $line;
            }

            return false;
        });

        $input->write("quit\n");
        $input->close();

        return $list;
    }

    public function show($path): array
    {
        $input = new InputStream();
        $process = $this->connect($input);
        $query = "show \"$path\" -s\n";
        $input->write($query);

        $process->waitUntil(function ($type, $data) use ($query) {
            if ($data === trim($query)) {
                return false;
            }

            return true;
        });

        $input->write("quit\n");
        $input->close();

        $entries = array_filter(explode("\n", $process->getOutput()));

        $result = [];
        foreach ($entries as $entry) {
            if (!str_contains($entry, ":")) {
                continue;
            }

            $entryParts = explode(":", $entry, 2);

            $result[$entryParts[0]] = $entryParts[1];
        }

        return $result;
    }

    protected function connect(InputStream $input): Process
    {
        $process = new Process([$this->exec, 'open', $this->filename, '-q']);
        $process->setTimeout($this->timeout);
        $process->setWorkingDirectory(getcwd() . '/..');
        $process->setInput($input);
        $process->start();

        $input->write("$this->password\n");

        $process->waitUntil(function ($type, $output): bool {
            return $output === $this->prefix;
        });

        return $process;
    }
}