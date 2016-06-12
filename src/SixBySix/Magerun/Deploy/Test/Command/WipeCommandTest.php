<?php

namespace SixBySix\Magerun\Deploy\Test\Command;

use SixBySix\Magerun\Deploy\Command\GenerateCommand;
use Symfony\Component\Console\Tester\CommandTester;
use N98\Magento\Command\PHPUnit\TestCase;

/**
 * Class WipeCommandTest
 * @package SixBySix\Magerun\Deploy\Test\Command
 */
class WipeCommandTest extends AbstractCommandTest
{
    /** @var  string */
    protected $output;

    /** @var  string */
    protected $baseDir;

    /** @var  string */
    protected $pregBaseDir;

    protected function runSetupCommand()
    {
        // create a cap structure
        $command = $this->getApplication()->find('deploy:setup');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['command' => true]);
    }

    /**
     * @test
     */
    public function startWipeOnEmptyCodebase()
    {
        $command = $this->getApplication()->find('deploy:wipe');
        $commandTester = new CommandTester($command);

        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream("y\n")); // simulate "N" to "Do you want to continue? (N/y):"

        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        /** @var string $output */
        $output = $commandTester->getDisplay(true);

        $expectedOutput = "This is a clean project, nothing was found to delete\n" .
            "\n";

        $this->assertEquals($expectedOutput, $output);
    }

    /**
     * @test
     */
    public function startWipeButBail()
    {
        $this->runSetupCommand();

        $command = $this->getApplication()->find('deploy:wipe');
        $commandTester = new CommandTester($command);

        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $command->getHelper('question');
        $helper->setInputStream($this->getInputStream("n\n")); // simulate "N" to "Do you want to continue? (N/y):"

        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        /** @var string $output */
        $output = $commandTester->getDisplay(true);

        $expectedOutput = "WARNING\n" .
            "This command will wipe all Capistrano configuration (including stages)\n" .
            "\n" .
            "Do you want to continue? (N/y): \n";

        $this->assertEquals($expectedOutput, $output);
    }

    /**
     * @test
     */
    public function startAndConfirmWipe()
    {
        $this->runSetupCommand();

        $command = $this->getApplication()->find('deploy:wipe');
        $commandTester = new CommandTester($command);

        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $command->getHelper('question');

        /** @var string $baseDir */
        $baseDir = $this->getApplication()->getMagentoRootFolder();

        touch("{$baseDir}/config/deploy/prod.rb");

        $helper->setInputStream($this->getInputStream("Y\n")); // simulate "Y" to "Do you want to continue? (N/y):"

        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        /** @var string $output */
        $output = $commandTester->getDisplay(true);

        $expectedOutput = "WARNING\n" .
            "This command will wipe all Capistrano configuration (including stages)\n" .
            "\n".
            "Do you want to continue? (N/y): \n" .
            " ✔ Deleted {$baseDir}/Capfile\n".
            " ✔ Deleted {$baseDir}/Gemfile\n".
            " ✔ Deleted {$baseDir}/config\n".
            "\n";

        $this->assertEquals($expectedOutput, $output);
    }
}
