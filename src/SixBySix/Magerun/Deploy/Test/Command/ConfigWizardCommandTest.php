<?php

namespace SixBySix\Magerun\Deploy\Test\Command;

use SixBySix\Magerun\Deploy\Command\GenerateCommand;
use Symfony\Component\Console\Tester\CommandTester;
use N98\Magento\Command\PHPUnit\TestCase;

/**
 * Class ConfigWizardCommandTest
 * @package SixBySix\Magerun\Deploy\Test\Command
 */
class ConfigWizardCommandTest extends AbstractCommandTest
{
    /**
     * @test
     */
    public function noSetup()
    {
        $command = $this->getApplication()->find('deploy:config:wizard');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        /** @var string $output */
        $output = $commandTester->getDisplay();

        $this->assertContains("Please ensure you've run 'deploy:setup' first (200)", $output);
    }

    /**
     * @test
     */
    public function normalFlow()
    {
        $command = $this->getApplication()->find('deploy:setup');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $command = $this->getApplication()->find('deploy:config:wizard');
        $commandTester = new CommandTester($command);

        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $command->getHelper('question');
        $helper->setInputStream(
            $this->getInputStream(
                "magento-ce-1.9.2.4\n" .                             // set application name
                "0\n" .                                              // use git for SCM
                "https://github.com/OpenMage/magento-mirror.git\n" . // use mage mirror
                "3\n" .                                              // keep 3 releases

                "add\n" .                                              // skip shared dirs
                "/media /var\n" .
                "add\n" .

                "s\n" .                                              // skip files
                "s\n"                                                // skip stages
            )
        );

        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        /** @var string $output */
        $output = $commandTester->getDisplay();

        var_dump($output);
        exit;
    }
}
