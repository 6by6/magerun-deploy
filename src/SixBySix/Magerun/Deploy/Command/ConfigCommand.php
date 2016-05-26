<?php

namespace SixBySix\Magerun\Deploy\Command;

use N98\Magento\Command\AbstractMagentoCommand;
use SixBySix\Magerun\Deploy\Exception;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SixBySix\Magerun\Deploy\Helper\Capistrano as CapHelper;

class ConfigCommand extends AbstractCommand
{
    /** @var CapHelper */
    protected $helper;

    protected function configure()
    {
        $this
            ->setName('deploy:config')
            ->setDescription('Wipe all capistrano files')
            ->addOption('wizard', 'w');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        var_dump($input->getOption('wizard'));
    }
}