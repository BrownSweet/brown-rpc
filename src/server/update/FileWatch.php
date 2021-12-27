<?php

/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2021/12/13 18:13
 */
namespace brown\server\update;
use Swoole\Timer;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
class FileWatch
{
    protected $finder;

    protected $files = [];

    /**
     * @param $directory
     * @param $exclude
     * @param $name
     */
    public function __construct($directory, $exclude, $name)
    {
        $this->finder = new Finder();
        $this->finder->files()
            ->name($name)
            ->in($directory)
            ->exclude($exclude);
    }

    /**
     * @return array
     * @author Brown 2021/12/23 18:15
     */
    protected function findFiles()
    {
        $files = [];
        /** @var SplFileInfo $f */
        foreach ($this->finder as $f) {
            $files[$f->getRealpath()] = $f->getMTime();
        }
        return $files;
    }

    /**
     * @param callable $callback
     * @author Brown 2021/12/23 18:15
     */
    public function watch(callable $callback)
    {
        $this->files = $this->findFiles();

        Timer::tick(1000, function () use ($callback) {

            $files = $this->findFiles();

            foreach ($files as $path => $time) {
                if (empty($this->files[$path]) || $this->files[$path] != $time) {
                    call_user_func($callback);
                    break;
                }
            }

            $this->files = $files;
        });
    }

}