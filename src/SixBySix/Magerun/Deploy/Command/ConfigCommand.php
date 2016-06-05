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
    const ARG_UPDATE = 'update';

    const OPTION_SET_NAME = 'name';
    const OPTION_SET_SCM = 'scm';
    const OPTION_SET_REPO = 'repo';
    const OPTION_SET_KEEP_RELEASES = 'keep_releases';
    const OPTION_INC_DEFAULT_SHARED_DIRS = 'default_shared_dirs';
    const OPTION_SET_SHARED_DIRS = 'shared_dirs';
    const OPTION_INC_DEFAULT_SHARED_FILES = 'default_shared_files';
    const OPTION_SET_SHARED_FILES = 'shared_files';
    const OPTION_FORCE_SAVE = 'force_save';

    /** @var CapHelper */
    protected $helper;

    /** @var  Config */
    protected $config;

    protected function configure()
    {
        $this
            ->setName('deploy:config')
            ->setDescription('Show Capistrano configuration')
            ->addArgument(
                self::ARG_UPDATE,
                InputArgument::OPTIONAL,
                'Update configuration values',
                false
            )
            ->addOption(
                self::OPTION_SET_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Set application name'
            )
            ->addOption(
                self::OPTION_FORCE_SAVE,
                'f',
                InputOption::VALUE_NONE,
                'Save changes without confirming'
            )
            ->addOption(
                self::OPTION_SET_SCM,
                's',
                InputOption::VALUE_REQUIRED,
                'Set SCM'
            )
            ->addOption(
                self::OPTION_SET_REPO,
                'r',
                InputOption::VALUE_REQUIRED,
                'Set repository URL'
            )
            ->addOption(
                self::OPTION_SET_KEEP_RELEASES,
                'k',
                InputOption::VALUE_REQUIRED,
                'Number of releases to keep'
            )
            ->addOption(
                self::OPTION_INC_DEFAULT_SHARED_DIRS,
                null,
                InputOption::VALUE_OPTIONAL,
                'Include default shared directories',
                true
            )
            ->addOption(
                self::OPTION_SET_SHARED_DIRS,
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Set shared directories'
            )
            ->addOption(
                self::OPTION_INC_DEFAULT_SHARED_FILES,
                null,
                InputOption::VALUE_OPTIONAL,
                'Include default shared files',
                true
            )
            ->addOption(
                self::OPTION_SET_SHARED_FILES,
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Set shared files'
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
        $this->setStyles($output);

        /** @var QuestionHelper $question */
        $question = $this->getHelper('question');

        $this->detectMagento($output);
        if ($this->initMagento()) {
            $this->config = new ConfigHelper();

            if ($input->getArgument(self::ARG_UPDATE)) {
                if ($name = $input->getOption(self::OPTION_SET_NAME)) {
                    $this->config->setApplicationName($name);
                }

                if ($scm = $input->getOption(self::OPTION_SET_SCM)) {
                    $this->config->setScm($scm);
                }

                if ($repo = $input->getOption(self::OPTION_SET_REPO)) {
                    $this->config->setRepositoryUrl($repo);
                }

                if ($keepReleases = $input->getOption(self::OPTION_SET_KEEP_RELEASES)) {
                    $this->config->setReleaseLimit($keepReleases);
                }

                $sharedDirs = $input->getOption(self::OPTION_SET_SHARED_DIRS);
                if ($input->getOption(self::OPTION_INC_DEFAULT_SHARED_DIRS)) {
                    $sharedDirs = array_merge($sharedDirs, $this->config->getDefaultSharedDirs());
                    $sharedDirs = array_unique($sharedDirs);
                }
                $this->config->setSharedDirs($sharedDirs);

                $sharedFiles = $input->getOption(self::OPTION_SET_SHARED_FILES);
                if ($input->getOption(self::OPTION_INC_DEFAULT_SHARED_FILES)) {
                    $sharedFiles = array_merge($sharedFiles, $this->config->getDefaultSharedFiles());
                    $sharedFiles = array_unique($sharedFiles);
                }
                $this->config->setSharedFiles($sharedFiles);
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
            if (sizeof($this->config->getSharedFiles())) {
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
                        "{$stage->ssh_user}@{$stage->host}:{$stage->deploy_to}"
                    );
                }
            } else {
                $output->writeln("<warning>No stages found</>");
            }

            $output->writeln("");


            if ($input->getArgument(self::ARG_UPDATE)) {
                if (!$input->getOption(self::OPTION_FORCE_SAVE)) {
                    $confirm = new ConfirmationQuestion('Save this configuration?');

                    if (!$question->ask($input, $output, $confirm)) {
                        return;
                    }
                }

                $this->config->save();
            }
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