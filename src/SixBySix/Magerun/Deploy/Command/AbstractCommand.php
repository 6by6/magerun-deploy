<?php

namespace SixBySix\Magerun\Deploy\Command;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractCommand
 * @package SixBySix\Magerun\Deploy\Command
 */
abstract class AbstractCommand extends AbstractMagentoCommand
{
    protected $symArrowRight = "\xE2\x87\x92";
    protected $symCross = "\xE2\x9C\x96";
    protected $symCheck = "\xE2\x9C\x94";
    protected $symWarning = "\xE2\x9D\x97";
    protected $symBranch = "\xE2\x8E\x87";

    protected function writeError($message, OutputInterface $output)
    {
        $output->writeln("<fg=red;options=bold>$this->symCross $message</>");
        $output->writeln("");
    }

    protected function cleanup(OutputInterface $output)
    {
        $output->writeln("");
    }

    protected function setStyles(OutputInterface $output)
    {
        $style = new OutputFormatterStyle('white', null, array('bold'));
        $output->getFormatter()->setStyle('label', $style);

        $style = new OutputFormatterStyle('blue', null, array('bold', 'underscore'));
        $output->getFormatter()->setStyle('subtitle', $style);

        $style = new OutputFormatterStyle('yellow', null, array('bold'));
        $output->getFormatter()->setStyle('warning', $style);

        $style = new OutputFormatterStyle('green', null, array());
        $output->getFormatter()->setStyle('success', $style);

        $style = new OutputFormatterStyle('white', 'red', array('bold'));
        $output->getFormatter()->setStyle('error', $style);
    }
}