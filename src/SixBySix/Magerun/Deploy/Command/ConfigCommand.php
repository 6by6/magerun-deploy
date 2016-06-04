<?php

namespace SixBySix\Magerun\Deploy\Command;

use N98\Magento\Command\AbstractMagentoCommand;
use SixBySix\Magerun\Deploy\Exception;
use SixBySix\Magerun\Deploy\Helper\Config;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use SixBySix\Magerun\Deploy\Helper\Capistrano as CapHelper;
use SixBySix\Magerun\Deploy\Helper\Config as ConfigHelper;
use SixBySix\Magerun\Deploy\Helper\Config\ArrayModifier;
use SixBySix\Magerun\Deploy\Helper\Config\StageModifier;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class ConfigCommand
 * @package SixBySix\Magerun\Deploy\Command
 */
class ConfigCommand extends AbstractCommand
{
    const OPTION_SET_NAME = 'name';
    const OPTION_SET_SCM = 'scm';


    /** @var CapHelper */
    protected $helper;

    /** @var  Config */
    protected $config;

    protected function configure()
    {
        $this
            ->setName('deploy:config')
            ->setDescription('Show Capistrano configuration')
            ->addOption(self::OPTION_SET_NAME, null, InputOption::VALUE_OPTIONAL, 'Set application name')
            ->addOption(
                self::OPTION_SET_SCM,
                null,
                InputOption::VALUE_OPTIONAL,
                'Set SCM'
            )
            ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->writeHeader("Capistrano Configuration", $output);
        $this->setStyles($output);

        $this->detectMagento($output);
        if ($this->initMagento()) {
            $this->config = new ConfigHelper();

            if ($name = $input->getOption(self::OPTION_SET_NAME)) {
                $this->config->setApplicationName($name);
            }

            if ($scm = $input->getOption(self::OPTION_SET_SCM)) {
                $this->config->setScm($scm);
            }


            $output->writeln("<label>Name:</>\t\t" . $this->config->getApplicationName());
            $output->writeln("<label>SCM:</>\t\t" . $this->config->getScm());
            $output->writeln("<label>Repository:</>\t" . $this->config->getRepositoryUrl());
            $output->writeln("<label>Keep Releases:</>\t" . $this->config->getReleaseLimit());

            $output->writeln("\n<subtitle>Shared Directories</>");
            if (sizeof($this->config->getSharedDirs())) {
                $output->writeln(implode(" ", $this->config->getSharedDirs()));
            } else {
                $output->writeln("<warning>No entries found</>");
            }

            $output->writeln("\n<subtitle>Shared Files</>");
            if (sizeof($this->config->getSharedDirs())) {
                $output->writeln(implode(" ", $this->config->getSharedFiles()));
            } else {
                $output->writeln("<warning>No entries found</>");
            }

            $output->writeln("\n<subtitle>Stages</>");
            if (sizeof($this->config->getStages())) {
                /** @var \stdClass $stage */
                foreach ($this->config->getStages() as $stage) {
                    $output->writeln(
                        "<label>{$stage->name}:</label> " .
                        "({$stage->branch}) " .
                        "{$stage->user}@{$stage->host}:{$stage->deploy_to}"
                    );
                }
            } else {
                $output->writeln("<warning>No stages found</>");
            }

            $output->writeln("");
        }
    }

    protected function formatQuestionText($question, $currentValue)
    {
        /** @var string $text */
        $text = "$question : ";
        if (strlen($currentValue)) {
            $text .= "($currentValue) ";
        }

        return $text;
    }

    protected function setStyles(OutputInterface $output)
    {
        $style = new OutputFormatterStyle('white', null, array('bold'));
        $output->getFormatter()->setStyle('label', $style);

        $style = new OutputFormatterStyle('blue', null, array('bold', 'underscore'));
        $output->getFormatter()->setStyle('subtitle', $style);

        $style = new OutputFormatterStyle('yellow', null, array('bold'));
        $output->getFormatter()->setStyle('warning', $style);
    }
}