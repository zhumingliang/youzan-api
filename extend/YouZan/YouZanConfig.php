<?php
/**
 * Created by PhpStorm.
 * User: zhumingliang
 * Date: 2017/9/15
 * Time: 下午4:09
 */

namespace YouZan;


class YouZanConfig
{
    //有赞云控制台的应用client_id
    public static $CLIENT_ID = '83010f53b9a9c0ed35';
    //有赞云控制台的应用client_secret
    public static $CLIENT_SECRET = 'e183b9b6588bcac248edc6cf9235f8a';
    //开发者后台所填写的回调地址
    public static $REDIRECT_URL = 'http://youzan.partywall.cn:8080/index.php/api/index/callback';

}