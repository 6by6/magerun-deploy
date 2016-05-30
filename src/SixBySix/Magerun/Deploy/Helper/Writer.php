<?php

namespace SixBySix\Magerun\Deploy\Helper;

use N98\Util\Template\Twig;
use SixBySix\Magerun\Deploy\Exception;

class Writer
{
    /** @var Config */
    protected $configHelper;

    /** @var Capistrano */
    protected $capHelper;

    /** @var Twig  */
    protected $twig;

    public function __construct(Config $config, Capistrano $capistrano)
    {
        $this->configHelper = $config;
        $this->capHelper = $capistrano;
        $this->twig = new Twig([
            __DIR__ . DIRECTORY_SEPARATOR . 'Writer',
        ]);
    }

    public function writeDeployRb()
    {
        /** @var string $content */
        $content = $this->twig->render('deploy.rb.twig', [
            'config' => $this->configHelper,
        ]);

        /** @var string $filename */
        $filename = $this->capHelper->getDeployRbFilename();

        $this->writeToFile($filename, $content);
    }

    public function flushStageFiles()
    {
        /** @var string $pattern */
        $pattern = $this->capHelper->getStageDir() . DIRECTORY_SEPARATOR . "*.rb";

        foreach (glob($pattern) as $filename) {
            unlink($filename);
        }

    }

    public function writeStageFiles()
    {
        /** @var \stdClass[] $stages */
        $stages = (array) $this->configHelper->getStages();

        /** @var \stdClass $stage */
        foreach ($stages as $stage)
        {
            /** @var string $filename */
            $filename = $this->capHelper->getStageDir() . DIRECTORY_SEPARATOR . $stage->name . ".rb";

            /** @var string $content */
            $content = $this->twig->render('stage.rb.twig', [
                'stage' => $stage,
            ]);

            $this->writeToFile($filename, $content);
        }
    }

    protected function writeToFile($filename, $content)
    {
        $fh = fopen($filename, 'w');

        if (!$fh) {
            throw new Exception('Unable to write '. $filename, Exception::IO_ERROR);
        }

        fwrite($fh, $content);
        fclose($fh);
    }
}