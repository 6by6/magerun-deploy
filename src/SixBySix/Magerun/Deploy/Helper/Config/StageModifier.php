<?php

namespace SixBySix\Magerun\Deploy\Helper\Config;

use SixBySix\Magerun\Deploy\Exception;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Validator\Constraints\UrlValidator;

class StageModifier extends ArrayModifier
{
    /** @var  \stdClass */
    protected $skeleton;

    public function __construct(InputInterface $input, OutputInterface $output, $helper, array $list)
    {
        parent::__construct($input, $output, $helper, $list);

        $this->list = (array) $list;
    }

    /**
     * @return \stdClass
     */
    public function getSkeleton()
    {
        return $this->skeleton;
    }

    /**
     * @param \stdClass $skeleton
     */
    public function setSkeleton(\stdClass $skeleton)
    {
        $this->skeleton = $skeleton;
    }

    public function addFlow()
    {
        /** @var stdClass $skel */
        $skel = (object) $this->getSkeleton();

        $this->output->writeln('');
        $this->output->writeln('<style=bold>Adding new stage...</>');

        try {
            $q = new Question("<fg=blue> #1 Enter name: </>");
            $name = $this->helper->ask($this->input, $this->output, $q);
            $skel->name = $name;

            if (isset($this->list->$name)) {
                throw new Exception("{$skel->name} already exists");
            }

            if (!preg_match('/^[A-Za-z0-9]+$/i', $skel->name)) {
                throw new Exception("{$skel->name} must be alphanumeric");
            }

            $q = new Question("<fg=blue> #2 Enter SSH host: </>");
            $skel->host = $this->helper->ask($this->input, $this->output, $q);

            if (!strlen($skel->host)) {
                throw new Exception("Host must be provided");
            }

            $q = new Question("<fg=blue> #3 Enter SSH username: </>");
            $skel->user = $this->helper->ask($this->input, $this->output, $q);

            if (!preg_match('/^[a-z_][a-z0-9_-]{0,31}$/', $skel->user)) {
                throw new Exception("{$skel->user} is an invalid username");
            }

            $q = new Question("<fg=blue> #4 Enter SCM branch: </>");
            $skel->branch = $this->helper->ask($this->input, $this->output, $q);

            if (!strlen($skel->branch)) {
                throw new Exception("SCM branch must be provided");
            }

            $q = new Question("<fg=blue> #4 Enter target directory: </>");
            $skel->deploy_to = $this->helper->ask($this->input, $this->output, $q);

            if (!strlen($skel->deploy_to)) {
                throw new Exception("Target directory must be provided");
            }

        } catch (Exception $e) {
            $this->output->writeln("<fg=red>{$e->getMessage()}</>");
            return false;
        }

        $this->list[$name] = $skel;
    }

    protected function removeFlow()
    {
        $q = new ChoiceQuestion("Please enter value to remove: ", $this->getChoices());
        $value = $this->helper->ask($this->input, $this->output, $q);

        if (isset($this->list[$value])) {
            unset($this->list[$value]);
        }
    }

    protected function getChoices()
    {
        $list = [];

        /** @var \stdClass $stage */
        foreach ($this->list as $stage) {
            $list[] = $stage->name;
        }

        return $list;
    }

    protected function printListItem($value)
    {
        $this->output->writeln("");
        $this->output->writeln(" <bg=white;fg=black;style=bold>    {$value->name}    </>");
        $this->output->writeln("   <style=bold>Host</> {$value->user}@{$value->host}");
        $this->output->writeln("   <style=bold>Branch</> {$value->branch}");
        $this->output->writeln("   <style=bold>Dir</> {$value->deploy_to}");
        $this->output->writeln("");
    }
}