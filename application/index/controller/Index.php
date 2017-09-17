<?php

namespace app\index\controller;

use think\Db;
use think\Exception;
use think\Request;
use youzan\YouZanConfig;

class Index
{
    public function index()
    {
        try {
            $token = new \youzan\Token();
            $url = "https://open.youzan.com/oauth/authorize?client_id=83010f53b9a9c0ed35&response_type=code&state=teststate&redirect_uri=http://youzan.partywall.cn:8080/index.php/Index/index/callback";
            echo $url;
        } catch (Exception $e) {
            echo $e->getMessage();
        }


    }


    public function callback(Request $request)
    {
        try {
            echo 1;
            $token = new Token();
            $data = array(
                'content' => $request->param('code'),
                'create_time' => date("Y-m-d H:i:s"),
            );
            Db::table('t_test')->insert($data);
            $token->getToken($request->param('code'));
        } catch (Exception $e) {
            echo $e->getMessage();
        }

    }

    private function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }
}
