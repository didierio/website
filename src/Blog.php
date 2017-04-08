<?php

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class Blog
{
    private $dir;

    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    public function isReadable()
    {
        return is_readable($this->dir);
    }

    public function all()
    {
        $finder = new Finder();
        $results = null;

        $files = $finder
            ->files()
            ->name('/\.rst$/')
            ->in($this->dir)
            ->sort(
                function (SplFileInfo $a, SplFileInfo $b) {
                   return ($b->getMTime() - $a->getMTime());
                }
            )
        ;

        foreach ($files as $file) {
            $result = $this->parseFile($file);
            $directory = str_replace('/', '-', $file->getRelativePath());
            $result['slug'] = sprintf('%s-%s', $directory, $file->getBasename('.rst'));

            $results[] = $result;
        }

        return $results;
    }

    public function find($slug)
    {
        foreach ($this->all() as $result) {
            if ($slug === $result['slug']) {
                return $result;
            }
        }

        throw new \LogicException(sprintf('No result found for slug "%s"', $slug));
    }

    public function parseFile(SplFileInfo $file)
    {
        $parser = new RstParser();
        $result = $parser->parse($file->openFile());
        $result['updated_at'] = \DateTime::createFromFormat('U', $file->getMTime());

        return $result;
    }
}
