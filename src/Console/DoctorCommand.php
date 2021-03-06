<?php

namespace SilverStripe\Upgrader\Console;

use BadMethodCallException;
use InvalidArgumentException;
use SilverStripe\Upgrader\Util\ConfigFile;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DoctorCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('doctor')
            ->setDescription('Run all cleanup tasks configured for this project')
            ->setDefinition([
                new InputOption(
                    'root-dir',
                    'd',
                    InputOption::VALUE_REQUIRED,
                    'Specify project root dir, if not the current directory',
                    '.'
                )
            ]);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $settings = array_merge($input->getOptions(), $input->getArguments());
        $rootPath = $this->realPath($settings['root-dir']);

        // Sanity check input
        if (!is_dir($rootPath)) {
            $rootPath = $settings['root-dir'];
            throw new InvalidArgumentException("No silverstripe project found in root-dir \"{$rootPath}\"");
        }

        // Load the code to be upgraded and run the upgrade process

        $config = ConfigFile::loadCombinedConfig($rootPath);
        $tasks = isset($config['doctorTasks']) ? $config['doctorTasks']: [];
        if (empty($tasks)) {
            $output->writeln("No tasks configured for this installation");
            return null;
        }

        $count = count($tasks);
        $output->writeln("Running {$count} doctor tasks on \"{$rootPath}\"");

        foreach ($tasks as $class => $path) {
            if (!file_exists($path)) {
                $relative = substr($path, strlen($rootPath));
                throw new BadMethodCallException("No task in {$relative} found");
            }
            require_once $path;
            $task = new $class;
            if (!is_callable($task)) {
                throw new BadMethodCallException(
                    "Class {$class} could not be invoked. Perhaps a missing __invoke() method?"
                );
            }
            // Invoke
            $output->writeln("Running task <info>{$class}</info>...");
            $task($input, $output, $rootPath);
        }

        $output->writeln("All tasks run");
        return null;
    }
}
