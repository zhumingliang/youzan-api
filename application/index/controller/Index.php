<?php

namespace app\index\controller;

//use youzanlib\Toekn;

use think\Loader;

class Index
{
    public function index()
    {
        Loader::import('youzanlib.Token');
        $token = new \youzanlib\Toekn();

    }
}
