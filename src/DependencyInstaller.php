<?php
/**
 * Copyright MediaCT. All rights reserved.
 * https://www.mediact.nl
 */

namespace Mediact\Composer\DependencyInstaller;

use Composer\Command\ConfigCommand;
use Composer\Command\RequireCommand;
use Composer\Console\Application;
use Composer\Factory;
use Composer\Json\JsonFile;
use Symfony\Component\Console\Input\ArrayInput;

class DependencyInstaller
{
    /**
     * @var array
     */
    private $definition;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $composerFile     = Factory::getComposerFile();
        $composerJson     = new JsonFile($composerFile);
        $this->definition = $composerJson->read();
    }

    /**
     * Install a repository.
     *
     * @param string $name
     * @param string $type
     * @param string $url
     *
     * @return void
     */
    public function installRepository(string $name, string $type, string $url)
    {
        if (array_key_exists($name, $this->definition['repositories'])) {
            return;
        }

        $application = new Application();
        $command     = new ConfigCommand();

        $definition = clone $application->getDefinition();
        $definition->addArguments($command->getDefinition()->getArguments());
        $definition->addOptions($command->getDefinition()->getOptions());

        $input = new ArrayInput(
            [
                'command' => 'config',
                'setting-key' => 'repositories.' . $name,
                'setting-value' => [
                    $type,
                    $url
                ]
            ],
            $definition
        );

        $application->run($input);
    }

    /**
     * Install a composer package.
     *
     * @param string $name
     * @param string $version
     *
     * @return void
     */
    public function installPackage(string $name, string $version)
    {
        if (array_key_exists($name, $this->definition['require-dev'])) {
            return;
        }

        $application = new Application();
        $command     = new RequireCommand();

        $definition = clone $application->getDefinition();
        $definition->addArguments($command->getDefinition()->getArguments());
        $definition->addOptions($command->getDefinition()->getOptions());

        $input = new ArrayInput(
            [
                'command' => 'require',
                'packages' => [$name . ':' . $version],
                '--dev' => true,
                '--no-scripts' => true,
                '--no-interaction' => true,
                '--no-plugins' => true,
            ],
            $definition
        );

        $application->run($input);
    }
}
