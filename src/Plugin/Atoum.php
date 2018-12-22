<?php

namespace PHPCensor\Plugin;

use PHPCensor\Builder;
use PHPCensor\Model\Build;
use PHPCensor\Plugin;

/**
 * Atoum plugin, runs Atoum tests within a project.
 */
class Atoum extends Plugin
{
    /**
     * Allows you to provide a path to the Atom binary (defaults to PHP Censor root)
     *
     * @var string
     */
    protected $executable;

    /**
     * @var string
     */
    protected $args;

    /**
     * @var string
     */
    protected $config;

    /**
     *
     * @var This option lets you specify the tests directory to run.
     */
    protected $directory;

    /**
     * @return string
     */
    public static function pluginName()
    {
        return 'atoum';
    }

    /**
     * {@inheritdoc}
     */
    public function __construct(Builder $builder, Build $build, array $options = [])
    {
        parent::__construct($builder, $build, $options);

        $this->directory = $this->getWorkingDirectory($options);

        $this->executable = $this->findBinary('atoum');

        if (isset($options['args'])) {
            $this->args = $options['args'];
        }

        if (isset($options['config'])) {
            $this->config = $options['config'];
        }
    }

    /**
     * Run the Atoum plugin.
     *
     * @return bool
     */
    public function execute()
    {
        $cmd = $this->executable;

        if (null !== $this->args) {
            $cmd .= " {$this->args}";
        }

        if (null !== $this->config) {
            $cmd .= " -c '{$this->config}'";
        }

        if (null !== $this->directory) {
            $cmd .= " --directories '{$this->directory}'";
        }

        chdir($this->builder->buildPath);

        $status = true;

        $this->builder->executeCommand($cmd);

        $output = $this->builder->getLastOutput();

        if (count(preg_grep("/Success \(/", $output)) == 0) {
            $status = false;
            $this->builder->log($output);
        }

        if (count($output) == 0) {
            $status = false;
            $this->builder->log('No tests have been performed.');
        }

        return $status;
    }
}
