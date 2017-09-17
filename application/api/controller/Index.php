<?php
/**
 * Created by PhpStorm.
 * User: zhumingliang
 * Date: 2017/5/7
 * Time: 下午1:41
 */

namespace app\api\controller;

use think\Controller;
use think\Exception;
use think\Loader;

class Index extends Controller
{
    public function index()
    {
        Loader::import('YouZanLib.YZSignClient');
        $appId = '5a6f9470c23e160ea4'; //请填入你有赞店铺后台-营销-有赞API的AppId
        $appSecret = '888373e35860e94098f43349ae340f57';//请填入你有赞店铺后台-营销-有赞API的AppSecret
        $client = new \YZSignClient($appId, $appSecret);
    }


}