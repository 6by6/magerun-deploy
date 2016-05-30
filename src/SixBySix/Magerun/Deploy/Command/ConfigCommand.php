<?php

namespace SixBySix\Magerun\Deploy\Command;

use N98\Magento\Command\AbstractMagentoCommand;
use SixBySix\Magerun\Deploy\Exception;
use SixBySix\Magerun\Deploy\Helper\Config;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use SixBySix\Magerun\Deploy\Helper\Capistrano as CapHelper;
use SixBySix\Magerun\Deploy\Helper\Config as ConfigHelper;
use SixBySix\Magerun\Deploy\Helper\Config\ArrayModifier;
use SixBySix\Magerun\Deploy\Helper\Config\StageModifier;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class ConfigCommand extends AbstractCommand
{
    const OPTION_WIZARD = 'wizard';
    const OPTION_WIZARD_SHORTCUT = 'w';

    /** @var CapHelper */
    protected $helper;

    /** @var  Config */
    protected $config;

    protected function configure()
    {
        $this
            ->setName('deploy:config')
            ->setDescription('Wipe all capistrano files')
            ->addOption(self::OPTION_WIZARD, self::OPTION_WIZARD_SHORTCUT);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->writeHeader("Capistrano Configuration", $output);

        $this->detectMagento($output);
        if ($this->initMagento()) {

            $this->helper = new CapHelper();

            try {
                $this->config = new ConfigHelper();

                if ($input->getOption(self::OPTION_WIZARD)) {
                    $this->runWizard($input, $output, $this->config);
                }

            } catch (Exception $e) {
                if ($e->getCode() == Exception::CONFIG_NOT_FOUND) {
                    $message = "Please ensure you've run 'deploy:setup' first";
                } else {
                    $message = $e->getMessage();
                }

                $output->writeln("");
                $output->writeln("<fg=red>$this->symCross</> $message ({$e->getCode()})");
                $this->cleanup($output);
                return 1;
            }
        }
    }

    protected function runWizard(InputInterface $input, OutputInterface $output, ConfigHelper $config)
    {
        $helper = $this->getHelper('question');

        $q = new Question(
            $this->formatQuestionText("What is your application called?", $config->getApplicationName()),
            $config->getApplicationName()
        );

        $name = $helper->ask($input, $output, $q);
        $this->config->setApplicationName($name);

        $q = new ChoiceQuestion(
            $this->formatQuestionText("Which version control system are you using?", $config->getScm()),
            $config->getScmChoices(),
            $config->getScm()
        );

        $scm = $helper->ask($input, $output, $q);
        $this->config->setScm($scm);

        $q = new Question(
            $this->formatQuestionText("Please enter your VC repo?", $config->getRepositoryUrl()),
            $config->getRepositoryUrl()
        );
        $url = $helper->ask($input, $output, $q);
        $this->config->setRepositoryUrl($url);

        $q = new Question(
            $this->formatQuestionText("How many releases should be kept?", $config->getReleaseLimit()),
            $config->getReleaseLimit()
        );

        $keepReleases = $helper->ask($input, $output, $q);
        $this->config->setReleaseLimit($keepReleases);

        $output->writeln('');
        $output->writeln('<style=bold>-- Shared Directories --</>');
        $output->writeln('Shared directories are used for dirs that should');
        $output->writeln('persist between deploys e.g. sessions, logs, word');
        $output->writeln('press config etc.');

        $modProc = new ArrayModifier($input, $output, $helper, $config->getSharedDirs());
        $sharedDirs = $modProc->run();
        $this->config->setSharedDirs($sharedDirs);

        $output->writeln('');
        $output->writeln('<bg=blue;fg=white;style=bold>   Shared Files   </>');
        $output->writeln('Shared files are used for files that should');
        $output->writeln('persist between deploys e.g. robots.txt, ');
        $output->writeln('database settings etc.');

        $modProc = new ArrayModifier($input, $output, $helper, $config->getSharedFiles());
        $sharedFiles = $modProc->run();
        $this->config->setSharedFiles($sharedFiles);

        $output->writeln('');
        $output->writeln('<fg=blue;style=bold>Stages</>');
        $output->writeln('<fg=blue;style=bold>----------</>');
        $output->writeln('A stage is a target for deployments');

        $modProc = new StageModifier($input, $output, $helper, $config->getStages());
        $modProc->setSkeleton($config->getStageSkeleton());
        $stages = $modProc->run();
        $this->config->setStages($stages);

        $q = new ConfirmationQuestion(
            $this->formatQuestionText("Please enter your VC repo?", $config->getRepositoryUrl()),
            $config->getRepositoryUrl()
        );

        $helper->ask($output, $input, $q);

        $this->config->save();
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
}