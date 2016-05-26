<?php

namespace SixBySix\Magerun\Deploy\Command;

use N98\Magento\Command\AbstractMagentoCommand;
use SixBySix\Magerun\Deploy\Exception;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SixBySix\Magerun\Deploy\Helper\Capistrano as CapHelper;

class SetupCommand extends AbstractCommand
{
    /** @var CapHelper */
    protected $helper;

    protected function configure()
    {
        $this
            ->setName('deploy:setup')
            ->setDescription('Setup codebase for capistrano deployments');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->writeHeader("Setup Capistrano", $output);
        $output->setFormatter(new OutputFormatter(true));

        $this->detectMagento($output);

        $this->helper = new CapHelper();

        if ($this->initMagento()) {

            $output->writeln("Inspecting codebase for existing Cap files...");

            /** @var mixed[] $info */
            $info = $this->helper->getSetupInfo();

            if (count($info['found']) > 0) {
                // there are preexisting files, can't automate
                $output->writeln("");
                $output->writeln('<options=bold>Found the following files/dirs:</>');

                /** @var string $filename */
                foreach ($info['found'] as $filename) {
                    $output->writeln(" $this->symArrowRight $filename");
                }

                $output->writeln("");

                $this->writeError("It looks like there is an existing setup. Please run 'deploy:wipe' to remove the above files.", $output);
                return 1;
            }

            try {
                $this->writeGemfile();
                $output->writeln(" <fg=green>$this->symCheck</> Wrote {$this->helper->getGemfileFilename()}");
                $this->writeCapfile();
                $output->writeln(" <fg=green>$this->symCheck</> Wrote {$this->helper->getCapfileFilename()}");
                $this->mkdirs();
                $output->writeln(" <fg=green>$this->symCheck</> Created {$this->helper->getCapDir()}");
                $output->writeln(" <fg=green>$this->symCheck</> Created {$this->helper->getStageDir()}");
                $this->writeConfigFile();
                $output->writeln(" <fg=green>$this->symCheck</> Created {$this->helper->getConfigFilename()}");
            } catch (Exception $e) {
                $output->writeln(" <fg=red>$this->symCross</> {$e->getMessage()}");
            }

            $this->cleanup($output);
        }
    }

    protected function mkdirs()
    {
        /** @var string $dirName */
        $dirName = $this->helper->getStageDir();

        if (!mkdir($dirName, 500, true)) {
            throw new Exception(
                "Cannot create $dirName, possible permissions issue",
                Exception::INVALID_PERMISSIONS_ISSUE
            );
        }
    }

    protected function writeFile($filename, $content)
    {
        /** @var resource $fh */
        $fh = fopen($filename, 'w');

        if (!$fh) {
            throw new Exception(
                "Cannot write $filename, possible permissions issue",
                Exception::INVALID_PERMISSIONS_ISSUE
            );
        }

        fwrite($fh, $content);
        fclose($fh);
    }

    protected function writeGemfile()
    {
        /** @var string $content */
        $content = <<<ruby
source 'http://rubygems.org'

gem 'net-ssh'
gem 'highline'
gem 'magentify'
ruby;

        $this->writeFile($this->helper->getGemfileFilename(), $content);
    }

    protected function writeCapfile()
    {
        $content = <<<ruby
load 'deploy' if respond_to?(:namespace) # cap2 differentiator
Dir['plugins/*/lib/recipes/*.rb'].each { |plugin| load(plugin) }
load Gem.find_files('mage.rb').last.to_s

load 'config/deploy'
ruby;
        $this->writeFile($this->helper->getCapfileFilename(), $content);
    }

    protected function writeConfigFile()
    {
        $content = <<<json
{
}
json;

        $this->writeFile($this->helper->getConfigFilename(), $content);
    }
}