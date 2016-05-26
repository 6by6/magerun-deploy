<?php

namespace SixBySix\Magerun\Deploy\Helper;

class Capistrano
{
    public function getCapfileFilename()
    {
        return \Mage::getBaseDir() . DS . 'Capfile';
    }

    public function capfileExists()
    {
        return file_exists($this->getCapfileFilename());
    }

    public function getGemfileFilename()
    {
        return \Mage::getBaseDir() . DS . 'Gemfile';
    }

    public function gemfileExists()
    {
        return file_exists($this->getGemfileFilename());
    }

    public function getCapDir()
    {
        return \Mage::getBaseDir() . DS . 'config';
    }

    public function capDirExists()
    {
        return is_dir($this->getCapDir());
    }

    public function getStageDir()
    {
        return $this->getCapDir() . DS . 'deploy';
    }

    public function stageDirExists()
    {
        return is_dir($this->getCapDir());
    }

    public function getConfigFilename()
    {
        return $this->getCapDir() . DS . 'sixbysix-deploy.json';
    }

    public function configFileExists()
    {
        return file_exists($this->getConfigFilename());
    }

    public function getStages()
    {
        /** @var string[] $stages */
        $stages = [];

        if (!$this->stageDirExists()) {
            return $stages;
        }

        /** @var string $globPattern */
        $globPattern = $this->getStageDir() . DS . "*.rb";

        /** @var string $stageFilename */
        foreach (glob($globPattern) as $stageFilename) {

            /** @var string $stageName */
            $stageName = basename($stageFilename, ".rb");

            $stages[$stageName] = $stageFilename;
        }

        return $stages;
    }

    public function getSetupInfo()
    {
        /** @var mixed[] $info */
        $info = [
            'found' => [],
            'missing' => [],
            'stages' => [],
        ];

        /** @var boolean[] $paths */
        $paths = [
            $this->getGemfileFilename() => $this->gemfileExists(),
            $this->getCapfileFilename() => $this->capfileExists(),
            $this->getStageDir() => $this->stageDirExists(),
            $this->getConfigFilename() => $this->configFileExists(),
            $this->getCapDir() => $this->capDirExists(),
        ];

        /**
         * @var string $filename
         * @var boolean $exists
         */
        foreach ($paths as $filename => $exists) {
            $info[(($exists) ? 'found' : 'missing')][] = $filename;
        }

        $info['stages'] = $this->getStages();

        return $info;
    }

    public function getConfigSkeleton()
    {
        return [
            'version' => '0.0.1',
            'name' => "My Application",
            'scm' => "git",
            'repository' => "",
            'app_symlinks' => ["/media", "/var", "/sitemaps", "/staging"],
            'app_shared_dirs' => ["/app/etc", "/sitemaps", "/media", "/var", "/staging"],
            'app_shared_files' => ["/app/etc/local.xml", "/robots.txt"],
            'keep_releases' => 3,
            'stages' => [],
        ];
    }

    public function getStageSkeleton()
    {
        return [
            'version' => '0.0.1',
            'deploy_to' => '',
            'branch' => '',
            'ssh_user' => '',
            'host' => '',
        ];
    }
}