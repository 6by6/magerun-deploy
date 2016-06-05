<?php

namespace SixBySix\Magerun\Deploy\Command;

use N98\Magento\Command\AbstractMagentoCommand;
use SixBySix\Magerun\Deploy\Exception;
use SixBySix\Magerun\Deploy\Helper\Config;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArrayInput;
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
 * Class ConfigStageCommand
 * @package SixBySix\Magerun\Deploy\Command
 */
class ConfigStageCommand extends ConfigCommand
{
    const ARG_ACTION = 'action';
    const ARG_ACTION_LIST = 'list';
    const ARG_ACTION_EDIT = 'edit';
    const ARG_ACTION_ADD = 'add';
    const ARG_ACTION_DELETE = 'delete';

    const OPTION_NAME = 'name';
    const OPTION_RENAME = 'rename';
    const OPTION_DEPLOY_TO = 'deploy_to';
    const OPTION_BRANCH = 'branch';
    const OPTION_USER = 'user';
    const OPTION_HOST = 'host';

    protected function configure()
    {
        $this
            ->setName('deploy:config:stage')
            ->setDescription('Capistrano multistage configuration')
            ->addArgument(
                self::ARG_ACTION,
                InputArgument::REQUIRED
            )
            ->addOption(
                self::OPTION_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Name of stage to modify'
            )
            ->addOption(
                self::OPTION_RENAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Update existing stage to this name'
            )
            ->addOption(
                self::OPTION_HOST,
                null,
                InputOption::VALUE_REQUIRED,
                'Hostname to deploy to'
            )
            ->addOption(
                self::OPTION_USER,
                null,
                InputOption::VALUE_REQUIRED,
                'Username to SSH into target machine'
            )
            ->addOption(
                self::OPTION_DEPLOY_TO,
                null,
                InputOption::VALUE_REQUIRED,
                'Dirname to deploy app to on host'
            )
            ->addOption(
                self::OPTION_BRANCH,
                null,
                InputOption::VALUE_REQUIRED,
                'SCM branch name to deploy'
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

        if ($this->initMagento()) {

            /** @var ConfigHelper $config */
            $config = new ConfigHelper();

            /** @var array[] $stages */
            $stages = (array) $config->getStages();

            /** @var string $action */
            $action = $input->getArgument(self::ARG_ACTION);

            if (in_array($action, [self::ARG_ACTION_ADD, self::ARG_ACTION_EDIT])) {

                /** @var string $name */
                /** @var mixed[] $stage */
                if ($action == self::ARG_ACTION_EDIT) {
                    $name = $input->getOption(self::OPTION_NAME);

                    if (!isset($stages[$name])) {
                        throw new Exception("Stage '{$name}' does not exist'");
                    }

                    $stage = (array) $stages[$name];

                    /** @var string $rename */
                    if ($rename = $input->getOption(self::OPTION_RENAME)) {
                        $stage['name'] = $rename;
                    }

                } else {
                    $stage = $config->getStageSkeleton();

                    $name = $input->getOption(self::OPTION_NAME);

                    if (isset($stages[$name])) {
                        throw new Exception("Stage '{$name}' already exists");
                    }

                    $stage['name'] = $name;
                }

                if ($host = $input->getOption(self::OPTION_HOST)) {
                    $stage['host'] = $host;
                }

                if ($user = $input->getOption(self::OPTION_USER)) {
                    $stage['ssh_user'] = $user;
                }

                if ($branch = $input->getOption(self::OPTION_BRANCH)) {
                    $stage['branch'] = $branch;
                }

                if ($deployTo = $input->getOption(self::OPTION_DEPLOY_TO)) {
                    $stage['deploy_to'] = $deployTo;
                }


                if (isset($rename)) {
                    unset($stages[$name]);
                    $name = $rename;
                }

                if (!strlen($name)) {
                    throw new Exception('Stage name cannot be empty');
                }

                $stages[$name] = $stage;
                $config->setStages($stages);
                $config->save();

            } elseif ($action == self::ARG_ACTION_DELETE) {

                /** @var string $name */
                $name = $input->getOption(self::OPTION_NAME);

                if (!isset($stages[$name])) {
                    throw new Exception("Stage '{$name}' does not exist'");
                }

                unset($stages[$name]);
                $config->setStages($stages);
                $config->save();
            }

            $cmd = $this->getApplication()->find('deploy:config');
            $cmd->run(new ArrayInput(['']), $output);
        }
    }
}