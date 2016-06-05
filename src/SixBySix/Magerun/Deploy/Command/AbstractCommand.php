<?php

namespace SixBySix\Magerun\Deploy\Command;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends AbstractMagentoCommand
{
    protected $symArrowRight = "\xE2\x87\x92";
    protected $symCross = "\xE2\x9C\x96";
    protected $symCheck = "\xE2\x9C\x94";
    protected $symWarning = "\xE2\x9D\x97";
    protected $symBranch = "\xE2\x8E\x87";

    protected function writeHeader($title, OutputInterface $output)
    {
        /** @var integer $padding */
        $padding = 5;

        /** @var integer $width */
        $width = strlen($title) + ($padding * 2);

        /** @var string $title */
        $title = str_pad($title, $width, " ", STR_PAD_BOTH);

        $output->writeln("");
        $output->writeln("<bg=blue;fg=white>". str_repeat(" ", $width));
        $output->writeln($title);
        $output->writeln(str_repeat(" ", $width) ."</>");
        $output->writeln("");
    }

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