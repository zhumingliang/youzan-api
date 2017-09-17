<?php

namespace app\index\controller;

//use youzanlib\Toekn;

use think\Loader;
use YouZan\Token;

class Index
{
    public function index()
    {
        Loader::import('YouZan\Token', EXTEND_PATH);
       $token=new Token();
    }
}
