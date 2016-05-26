<?php

namespace SixBySix\Magerun\Deploy\Command;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand extends AbstractMagentoCommand
{
    protected $symArrowRight = "\xE2\x87\x92";
    protected $symCross = "\xE2\x9C\x96";
    protected $symCheck = "\xE2\x9C\x94";
    protected $symWarning = "\xE2\x9D\x97";

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
}