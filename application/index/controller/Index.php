<?php

namespace app\index\controller;

//use youzanlib\Toekn;

use think\Loader;
use think\log\driver\Test;


//use YouZan\Token;

class Index
{
    public function index()
    {
        Loader::import('YouZanLib.YZApiProtocol');
        $t = new \YZApiProtocol();
    }
}
