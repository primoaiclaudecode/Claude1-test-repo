<?php

namespace UniSharp\LaravelFilemanager\LaravelFilemanager\Controllers;

/**
 * Class DemoController.
 */
class DemoController extends LfmController
{
    /**
     * @return mixed
     */
    public function index()
    {
        return view('laravel-filemanager::demo');
    }
}
