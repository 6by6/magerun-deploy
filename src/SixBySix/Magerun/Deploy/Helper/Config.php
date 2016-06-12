<?php

namespace SixBySix\Magerun\Deploy\Helper;

use SixBySix\Magerun\Deploy\Exception;

class Config extends \ArrayObject
{
    const SCM_GIT = 'git';
    const SCM_HG = 'hg';
    const SCM_SVN = 'svn';

    /** @var  stdObject */
    protected $data;

    /** @var  integer */
    protected $mtime;

    /** @var  Capistrano */
    protected $helper;

    public function __construct()
    {
        parent::__construct();

        $this->helper = new Capistrano();
        $this->load();
    }

    public function loadSkeleton()
    {
        $this->data = $this->getConfigSkeleton();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function load()
    {
        if (!$this->helper->configFileExists()) {
            throw new Exception(null, Exception::CONFIG_NOT_FOUND);
        }

        /** @var string $filename */
        $filename = $this->helper->getConfigFilename();

        /** @var string $content */
        $content = file_get_contents($filename);

        if (strlen($content) < 1) {
            $this->loadSkeleton();
            $this->save();
            return;
        }

        /** @var array $config */
        $config = json_decode($content);

        if (!$config) {
            throw new Exception(null, Exception::CONFIG_INVALID_FORMAT);
        }

        $this->data = $config;
        $this->mtime = filemtime($filename);
        // @todo need version checker + merger here
    }

    public function setApplicationName($name)
    {
        if (!strlen($name)) {
            throw new Exception('', Exception::CONFIG_INVALID_VALUE);
        }

        $this->data->name = $name;
    }

    public function getApplicationName()
    {
        return $this->data->name;
    }

    public function setScm($scm)
    {
        /** @var string[] $choices */
        $choices = $this->getScmChoices();

        if (!isset($choices[$scm])) {
            throw new Exception("$scm is not a valid SCM", Exception::CONFIG_INVALID_VALUE);
        }

        $this->data->scm = $scm;
    }

    public function getScm()
    {
        return $this->data->scm;
    }

    public function setRepositoryUrl($url)
    {
        $this->data->repository = $url;
    }

    public function getRepositoryUrl()
    {
        return $this->data->repository;
    }

    public function getScmChoices()
    {
        return [
            self::SCM_GIT => "Git",
            self::SCM_SVN => "Subversion",
            self::SCM_HG => "Mercurial",
        ];
    }

    public function getSharedDirs()
    {
        return array_values((array) $this->data->app_shared_dirs);
    }

    public function setSharedDirs(array $dirs)
    {
        $this->data->app_shared_dirs = (array) $dirs;
    }

    public function getSharedFiles()
    {
        return array_values((array) $this->data->app_shared_files);
    }

    public function setSharedFiles(array $files)
    {
        $this->data->app_shared_files = (array) $files;
    }

    public function getReleaseLimit()
    {
        return $this->data->keep_releases;
    }

    public function setReleaseLimit($limit)
    {
        $this->data->keep_releases = (int) $limit;
    }

    public function getStages()
    {
        return $this->data->stages;
    }

    public function setStages(array $stages)
    {
        $this->data->stages = $stages;
    }

    public function getStageNames()
    {
        return array_keys((array) $this->data->stages);
    }

    public function save()
    {
        if (!$this->helper->configFileExists()) {
            throw new Exception(null, Exception::CONFIG_NOT_FOUND);
        }

        /** @var string $filename */
        $filename = $this->helper->getConfigFilename();

        if ($this->mtime !== null && filemtime($filename) > $this->mtime) {
            throw new Exception("$filename has been modified since read", Exception::CONFIG_OUT_OF_DATE);
        }

        /** @var resource $fh */
        $fh = fopen($filename, 'w');

        if (!$fh) {
            throw new Exception("Unable to read {$filename}", Exception::INVALID_PERMISSIONS_ISSUE);
        }

        /** @var  $json */
        $json = json_encode($this->data);

        if (fwrite($fh, $json) === false) {
            throw new Exception("Unable to write {$filename}", Exception::INVALID_PERMISSIONS_ISSUE);
        }

        $this->mtime = filemtime($filename);
    }

    public function getConfigSkeleton()
    {
        return [
            'version' => '0.0.1',
            'name' => "My Application",
            'scm' => "git",
            'repository' => "",
            'app_shared_dirs' => $this->getDefaultSharedDirs(),
            'app_shared_files' => $this->getDefaultSharedFiles(),
            'keep_releases' => 3,
            'stages' => [],
        ];
    }

    public function getStageSkeleton()
    {
        return [
            'version' => '0.0.1',
            'name' => '',
            'deploy_to' => '',
            'branch' => '',
            'ssh_user' => '',
            'host' => '',
        ];
    }

    public function getDefaultSharedDirs()
    {
        return ["/app/etc", "/sitemaps", "/media", "/var", "/staging"];
    }

    public function getDefaultSharedFiles()
    {
        return ["/app/etc/local.xml", "/robots.txt"];
    }
}