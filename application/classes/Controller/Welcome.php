<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Welcome extends Controller 
{
    public function action_index()
    {
        Template::factory('Welcome/Index', array(
            'pinyin' => "hello world" 
            )
        )->response();
    }
} // End Welcome
