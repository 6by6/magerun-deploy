<?php

namespace SixBySix\Magerun\Deploy\Test\Command;

use N98\Magento\Command\PHPUnit\TestCase;
use SixBySix\Magerun\Deploy\Helper\Capistrano;

/**
 * Class AbstractCommandTest
 * @package SixBySix\Magerun\Deploy\Test\Command
 */
abstract class AbstractCommandTest extends TestCase
{
    protected function setUp()
    {
        /** @var string $baseDir */
        $baseDir = $this->getApplication()->getMagentoRootFolder();

        $helper = new Capistrano();
        $helper->rmdir("$baseDir/config");
        unlink("$baseDir/Capfile");
        unlink("$baseDir/Gemfile");

        return parent::tearDown();
    }

    protected function getInputStream($content)
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $content);
        rewind($stream);
        return $stream;
    }
}
