<?php

namespace UniSharp\LaravelFilemanager\LaravelFilemanager\Events;

class ImageWasDeleted
{
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function path()
    {
        return $this->path;
    }
}
