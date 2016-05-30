<?php

namespace SixBySix\Magerun\Deploy\Command;

use N98\Magento\Command\AbstractMagentoCommand;
use SixBySix\Magerun\Deploy\Exception;
use SixBySix\Magerun\Deploy\Helper\Capistrano;
use SixBySix\Magerun\Deploy\Helper\Config;
use SixBySix\Magerun\Deploy\Helper\Writer;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            ->setName('deploy:generate')
            ->setDescription('Generate all capistrano files from config');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->writeHeader("Generate Capistrano Files", $output);
        $output->setFormatter(new OutputFormatter(true));

        if ($this->initMagento()) {
            $this->detectMagento($output);

            $writer = new Writer(new Config(), new Capistrano());

            try {
                $writer->writeDeployRb();
                $writer->flushStageFiles();
                $writer->writeStageFiles();
            } catch (Exception $e) {
                $output->writeln('FAIL!: ' . $e->getMessage());
                return 1;
            }

        }

        $output->writeln("");
    }
}