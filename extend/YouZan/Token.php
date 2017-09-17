<?php
/**
 * Created by PhpStorm.
 * User: zhumingliang
 * Date: 2017/9/15
 * Time: 下午4:13
 */

namespace YouZan;


use think\Db;
use think\Exception;
use YouZan\lib\YZGetTokenClient;

class Token
{
    public function getToken($code)
    {
        try {
            $token = new YZGetTokenClient(YouZanConfig::$CLIENT_ID, YouZanConfig::$CLIENT_SECRET);
            $type = 'oauth';//如要刷新access_token，type值为refresh_token
            $keys['code'] = $code;//如要刷新access_token，这里为$keys['refresh_token']
            $keys['redirect_uri'] = '';


            $t = $token->get_token($type, $keys);
            $data = array(
                'content' => $t,
                'create_time' => date("Y-m-d H:i:s"),
            );
            Db::table('t_test')->insert($data);

        } catch (Exception $e) {
            echo $e->getMessage();
        }

    }


}