<?php
/**
 * Created by PhpStorm.
 * User: zhumingliang
 * Date: 2017/9/20
 * Time: 下午3:35
 */

namespace app\api\controller;


use YouZan\lib\YZTokenClient;
use YouZan\YouZanConfig;

class YZService
{

    public function __construct($access_token)
    {
        $this->access_token = $access_token;
    }

    /**
     * 新增商品
     * @param $my_params
     * @return array
     */
    public function addGoods($my_params)
    {
        $client = new YZTokenClient($this->access_token);
        $method = YouZanConfig::$ADD_GOODS;
        $api_version = YouZanConfig::$API_VERSION;
        $my_files = [
        ];
        $post_res = $client->post($method, $api_version, $my_params, $my_files);
        if (isset($post_res['error_response'])) {
            return ['code' => 0, 'msg' => $post_res['error_response']['msg']];
        }
        return ['code' => 1, 'msg' => '成功'];

    }


    /**
     * 获取所有分组列表
     * @return mixed
     */
    public function getTagList()
    {
        $client = new YZTokenClient($this->access_token);

        $method = YouZanConfig::$GET_TAG_LIST;
        $api_version = YouZanConfig::$API_VERSION;

        $my_params = [
            'is_sort' => '1'
        ];
        $tag_arr = array();
        $res = $client->post($method, $api_version, $my_params, array());
        $tags = $res['response']['tags'];
        if (count($tags)) {
            foreach ($tags as $k => $v) {
                $tag_arr[preg_replace('# #', '', $tags[$k]['name'])] = $tags[$k]['id'];
            }

        }
        return $tag_arr;
    }


    /**
     * 新增分组接口
     * @param $name
     * @return array
     */
    public function addTag($name)
    {

        $client = new YZTokenClient($this->access_token);
        $method = YouZanConfig::$ADD_TAG;
        $api_version = YouZanConfig::$API_VERSION;

        $my_params = [
            'name' => $name,
        ];

        $res = $client->post($method, $api_version, $my_params, array());
        if (isset($res['error_response'])) {
            return ['code' => 0, 'msg' => $res['error_response']['msg']];
        }
        return ['code' => 1, 'tag_id' => $res['response']['tag']['id']];
    }

    public function addImage($url)
    {
        $client = new YZTokenClient($this->access_token);

        $method = YouZanConfig::$UP_IMG;
        $api_version = YouZanConfig::$API_VERSION;

        $my_params = [
        ];

        $my_files = [
            [
                'url' => $url,
                'field' => 'image[]',
            ],
        ];
        $res = $client->post($method, $api_version, $my_params, $my_files);
        if (isset($res['error_response'])) {
            return ['code' => 0, 'msg' => $res['error_response']['msg']];
        }
       /* //将图片名称修改
        $path = pathinfo($url);
        $newname = $path['dirname'] . '/' . guid() . '.' . $path['extension'];
        rename($url, $newname);*/
        return ['code' => 1, 'image_id' => $res['response']['image_id']];

    }
}