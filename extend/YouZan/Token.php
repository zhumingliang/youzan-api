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


    public function getAccessToken()
    {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode(get_php_file('access_token.php'));

        if ($data->expire_time < time()) {
            // access_token过期，需要管理员授权获取code
            /**
             * 1.生成管理员授权链接
             */
            $url = "https://open.youzan.com/oauth/authorize?client_id=" . YouZanConfig::$CLIENT_ID . "&response_type=code&state=teststate&redirect_uri=" . YouZanConfig::$REDIRECT_URL;
            return $url;
        }
        
        $access_token = $data->access_token;
        return $access_token;
    }


    public function getToken($code)
    {
        try {
            //检测token是否过期

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