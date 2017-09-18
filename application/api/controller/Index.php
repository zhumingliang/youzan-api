<?php

namespace app\api\controller;

use think\Controller;
use think\Exception;
use think\Request;
use YouZan\lib\YZGetTokenClient;
use YouZan\Token;
use YouZan\YouZanConfig;


class Index extends Controller
{
    public function index()
    {
        try {

            $token = new Token();
            $r = $token->getAccessToken();
            echo $r;

        } catch (Exception $e) {
            echo $e->getMessage();
        }


    }


    public function callback(Request $request)
    {
        $code = $request->param('code');
        if (!is_null($code)) {
            $token = new YZGetTokenClient(YouZanConfig::$CLIENT_ID, YouZanConfig::$CLIENT_SECRET);
            $type = 'oauth';//如要刷新access_token，type值为refresh_token
            $keys['code'] = $code;//如要刷新access_token，这里为$keys['refresh_token']
            $keys['redirect_uri'] = YouZanConfig::$REDIRECT_URL;
            $data = $token->get_token($type, $keys);
            if (is_null($data['access_token'])) {
                echo $data['access_token'];
            }


        }

    }
}