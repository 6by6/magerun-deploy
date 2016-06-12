<?php

namespace SixBySix\Magerun\Deploy\Test\Command;

use SixBySix\Magerun\Deploy\Command\GenerateCommand;
use Symfony\Component\Console\Tester\CommandTester;
use N98\Magento\Command\PHPUnit\TestCase;

/**
 * Class SetupCommandTest
 * @package SixBySix\Magerun\Deploy\Test
 */
class SetupCommandTest extends AbstractCommandTest
{
    /** @var  string */
    protected $output;

    /** @var  string */
    protected $baseDir;

    /** @var  string */
    protected $pregBaseDir;

    /**
     * Run command and capture output
     * @return null
     */
    protected function setUp()
    {
        parent::setUp();
        $this->runCommand();
    }

    protected function runCommand()
    {
        $command = $this->getApplication()->find('deploy:setup');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $this->baseDir = $this->getApplication()->getMagentoRootFolder();
        $this->pregBaseDir = preg_quote($this->baseDir, '/');
        $this->output = $commandTester->getDisplay(true);
    }

    /**
     * Test deploy:setup on a completely fresh codebase
     * @test
     */
    public function noCap()
    {
        $this->assertRegExp("/Wrote {$this->pregBaseDir}\\/Gemfile/", $this->output);
        $this->assertRegExp("/Wrote {$this->pregBaseDir}\\/Capfile/", $this->output);
        $this->assertRegExp("/Created {$this->pregBaseDir}\\/config/", $this->output);
        $this->assertRegExp("/Created {$this->pregBaseDir}\\/config\\/deploy/", $this->output);

        $this->assertFileExists("{$this->baseDir}/Gemfile");
        $this->assertFileExists("{$this->baseDir}/Capfile");

        $this->assertFileExists("{$this->baseDir}/config");
        $this->assertTrue(is_dir("{$this->baseDir}/config"));

        $this->assertFileExists("{$this->baseDir}/config/deploy");
        $this->assertTrue(is_dir("{$this->baseDir}/config/deploy"));
    }

    /**
     * Test deploy:setup following :setup in ->noCap test (should fail gracefully)
     * @test
     * @depends noCap
     */
    public function previousCap()
    {
        $this->runCommand();
        $this->assertContains("Found the following files/dirs:", $this->output);
        $this->assertContains(
            "It looks like there is an existing setup. Please run 'deploy:wipe' to remove the above files.",
            $this->output
        );
    }

    /**
     * Delete one file from :setup
     * @test
     * @depends previousCap
     */
    public function corruptedCap()
    {
        unlink("{$this->baseDir}/Capfile");
        $this->runCommand();
        $this->assertContains("Found the following files/dirs:", $this->output);
        $this->assertContains(
            "It looks like there is an existing setup. Please run 'deploy:wipe' to remove the above files.",
            $this->output
        );
    }
}
