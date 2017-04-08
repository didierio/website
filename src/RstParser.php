<?php

class RstParser
{
    const DELIMITER_PATTERN = '=-`:\'"~';

    protected $keys;

    public function __construct()
    {
        $this->keys = array();
    }

    public function getExcerpt()
    {
        return $this->excerpt;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function parse(\SplFileObject $file)
    {
        $file->setFlags(\SplFileObject::DROP_NEW_LINE | \SplFileObject::READ_AHEAD);
        $previousLine = null;
        $data = array();
        $key = null;
        $content = array();

        while (!$file->eof()) {
            $line = $file->current();

            if ($this->isDelimiter($line, $previousLine)) {
                if (null !== $key) {
                    array_pop($content);

                    $data[$key] = implode("\n", $content);
                    $content = array();
                }

                $key = $previousLine;
            } else if (null !== $key) {
                $content[] = $line;
            }

            $previousLine = $line;

            $file->next();
        }

        if (0 < count($content)) {
            $data[$key] = implode("\n", $content);
        }

        return $data;
    }

    protected function isDelimiter($line, $previousLine = null)
    {
        if (null === $previousLine) {
            return false;
        }

        if (strlen($line) === 0) {
            return false;
        }

        $pattern = self::DELIMITER_PATTERN;
        $length = strlen($pattern);

        for ($i = 0; $i < $length; $i++) {
            if ($line === str_repeat($pattern[$i], strlen($previousLine))) {
                return true;
            }
        }

        return false;
    }
}
