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
    /**
     * 有赞云控制台的应用client_id
     * @var string
     */
    public static $CLIENT_ID = '83010f53b9a9c0ed35';
    /**
     * 有赞云控制台的应用client_secret
     * @var string
     */
    public static $CLIENT_SECRET = 'e183b9b6588bcac248edc6cf9235f8af';
    /**
     * 开发者后台所填写的回调地址
     * @var string
     */
    public static $REDIRECT_URL = 'http://youzan.partywall.cn:8080/index.php/api/index/callback';

    /**
     * 接口版本
     * @var string
     */
    public static $API_VERSION = '3.0.0';

    /**
     * 新增商品方法
     * @var string
     */
    public static $ADD_GOODS = 'youzan.item.create';

    /**
     *
     * @var string
     */
    public static $ADD_TAG = 'youzan.itemcategories.tag.add';

    /**
     * @var string
     */
    public static $GET_TAG_LIST='youzan.itemcategories.taglist.search';

    /**
     * @var string
     */
    public static $UP_IMG='youzan.materials.storage.platform.img.upload';


}