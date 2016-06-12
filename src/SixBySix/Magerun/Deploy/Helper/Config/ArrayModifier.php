<?php

namespace SixBySix\Magerun\Deploy\Helper\Config;

use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ArrayModifier
{
    const ACTION_ADD = 'add';
    const ACTION_REMOVE = 'remove';
    const ACTION_LIST = 'list';
    const ACTION_CONTINUE = 'save';
    const ACTION_EDIT = 'edit';
    const ACTION_IMPORT_DEFAULTS = 'use_defaults';

    /** @var array  */
    protected $list;

    /** @var  InputInterface */
    protected $input;

    /** @var  OutputInterface */
    protected $output;

    /** @var  mixed[] */
    protected $defaults;

    /** @var   */
    protected $helper;

    public function __construct(
        InputInterface $input,
        OutputInterface $output,
        $helper,
        array $list = [],
        array $defaults = []
    ) {
        $this->list = $list;
        $this->input = $input;
        $this->output = $output;
        $this->helper = $helper;
        $this->defaults = $defaults;
    }

    public function run()
    {
        $this->showList();

        while (true) {
            $choice = $this->promptAction();

            if ($choice == self::ACTION_CONTINUE) {
                break;
            } elseif ($choice == self::ACTION_ADD) {
                $this->addFlow();
            } elseif ($choice == self::ACTION_EDIT) {
                $this->editFlow();
            } elseif (sizeof($this->defaults) && $choice == self::ACTION_IMPORT_DEFAULTS) {
                $this->list = array_merge($this->list, $this->defaults);
            } elseif ($choice == self::ACTION_REMOVE) {
                $this->removeFlow();
            } elseif ($choice == self::ACTION_LIST) {
                $this->showList();
            }
        }

        return $this->list;
    }

    protected function showList()
    {
        $this->output->writeln('');

        if (sizeof($this->list)) {
            $this->output->writeln("Current:");
            foreach ($this->list as $value) {
                $this->printListItem($value);
            }
        } else {
            $this->output->writeln(" <fg=red>No entries found</>");
        }

        if (sizeof($this->defaults)) {
            $this->output->writeln("");
            $this->output->writeln(sprintf("(defaults: %s)", implode(", ", $this->defaults)));
        }
    }

    protected function getChoices()
    {
        return $this->list;
    }

    protected function addFlow()
    {
        $q = new Question("Please enter value to add: ");
        $value = $this->helper->ask($this->input, $this->output, $q);
        if (!in_array($value, $this->getChoices())) {
            $this->list[] = $value;
        } else {
            $this->output->writeln("$value is already included");
        }
    }

    protected function editFlow()
    {
        $q = new ChoiceQuestion("Please enter value to edit: ", $this->getChoices());
        $value = $this->helper->ask($this->input, $this->output, $q);
        if (($idx = array_search($value, $this->getChoices())) !== false) {
            unset($this->list[$idx]);
        }
    }

    protected function removeFlow()
    {
        $q = new ChoiceQuestion("Please enter value to remove: ", $this->getChoices());
        $value = $this->helper->ask($this->input, $this->output, $q);
        if (($idx = array_search($value, $this->getChoices())) !== false) {
            unset($this->list[$idx]);
        }
    }

    protected function printListItem($value)
    {
        $this->output->writeln(" * $value");
    }

    protected function promptAction()
    {
        $this->output->writeln('');

        /** @var string[] $actions */
        $actions = [
            self::ACTION_ADD => "Add entry/entries",
            self::ACTION_EDIT => "Edit entry/entries",
            self::ACTION_REMOVE => "Remove entry/entries",
            self::ACTION_LIST => "Show current entries",
        ];

        if (sizeof($this->defaults)) {
            $actions[self::ACTION_IMPORT_DEFAULTS] = sprintf("Import Defaults (%s)", implode(", ", $this->defaults));
        }
        $actions[self::ACTION_CONTINUE] = "Continue...";

        $q = new ChoiceQuestion("Perform action: ", $actions);

        return $this->helper->ask($this->input, $this->output, $q);
    }
}