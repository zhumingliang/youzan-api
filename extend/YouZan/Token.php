<?php
/**
 * Created by PhpStorm.
 * User: zhumingliang
 * Date: 2017/9/15
 * Time: 下午4:13
 */

namespace YouZan;

class Token
{


    public function getAccessToken()
    {
        $id = session('id');
        $filename = 'token/' . $id . '.php';
        if (is_null($id)) {
            return ['res' => 3, 'msg' => '链接不正确，请在浏览器输入正确链接：http://youzan.partywall.cn:8080/youzan-api/public/index.php/?id=xxxxxxx'];
        }
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例

        $data = json_decode(get_php_file($filename));

        if ($data->expire_time < time()) {
            // access_token过期，需要管理员授权获取code
            //1.生成管理员授权链接
            $url = "https://open.youzan.com/oauth/authorize?client_id=" . YouZanConfig::$CLIENT_ID . "&response_type=code&state=" . $id . "&redirect_uri=" . YouZanConfig::$REDIRECT_URL;
            return ['res' => 0, 'url' => $url];
        }

        $access_token = $data->access_token;
        return ['res' => 1, 'access_token' => $access_token];
    }


}