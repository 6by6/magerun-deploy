<?php

namespace SixBySix\Magerun\Deploy\Command;

use N98\Magento\Command\AbstractMagentoCommand;
use SixBySix\Magerun\Deploy\Exception;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use SixBySix\Magerun\Deploy\Helper\Capistrano as CapHelper;

class WipeCommand extends AbstractCommand
{
    /** @var CapHelper */
    protected $helper;

    protected function configure()
    {
        $this
            ->setName('deploy:wipe')
            ->setDescription('Wipe all capistrano files');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->writeHeader("Wipe Capistrano", $output);
        $output->setFormatter(new OutputFormatter(true));

        $this->detectMagento($output);

        $this->helper = new CapHelper();

        if ($this->initMagento()) {
            /** @var mixed $info */
            $info = $this->helper->getSetupInfo();

            if (count($info['found']) < 1) {
                $output->writeln("This is a clean project, nothing was found to delete");
                $this->cleanup($output);
                return;
            }

            $output->writeln("<fg=red;style=bold>WARNING</>");
            $output->writeln("This command will wipe all Capistrano configuration (including stages)");
            $output->writeln("");

            /** @var ConfirmationQuestion $confirm */
            $confirm = new ConfirmationQuestion("<style=bold>Do you want to continue? (N/y): </>", false);

            if (!$this->getHelper('question')->ask($input, $output, $confirm)) {
                $this->cleanup($output);
                return 1;
            }

            $output->writeln("");

            /** @var string $path */
            foreach ($info['found'] as $path) {

                if (!file_exists($path)) {
                    continue;
                }

                /** @var boolean $result */
                $result = (is_dir($path) ? rmdir($path) : unlink($path));

                if ($result) {
                    $output->writeln(" <fg=green;style=bold>$this->symCheck</> Deleted $path");
                } else {
                    $output->writeln(" <fg=red;style=bold>$this->symCross</> Could not delete $path");
                }
            }

        }

        $output->writeln("");
    }
}