<?php

namespace app\api\controller;

use think\Controller;
use think\Exception;
use think\Loader;
use think\Request;
use YouZan\lib\YZGetTokenClient;
use YouZan\Token;
use YouZan\YouZanConfig;


class Index extends Controller
{
    public function index()
    {
        $id = Request()->param('id');
        if (!empty($id)) {
            session('id', $id);
        }

        return view();

    }


    public function upFile()
    {
        try {

            $success_msg = "";
            $error_msg = "";

            //检测token
            $token = new Token();
            $token_res = $token->getAccessToken();
            if (!$token_res['res']) {
                //access_token过期，需要点击授权
                return ['ret_code' => 4, 'msg' => 'access_token过期', 'url' => $token_res['url']];
            }
            if ($token_res['res'] == 3) {
                return ['ret_code' => 5, 'msg' => $token_res['msg']];
            }
            $access_token = $token_res['access_token'];
            $service = new YZService($access_token);
            $file_excel = request()->file('excel');
            $file_img = request()->file('img_zip');
            if (is_null($file_img)) {
                return ['ret_code' => 0, 'msg' => '图片不能为空'];
            }

            if (is_null($file_excel)) {
                return ['ret_code' => 0, 'msg' => 'Excel不能为空'];
            }
            //处理excel
            $info = $file_excel->move(ROOT_PATH . 'public' . DS . 'uploads');
            $file_name = $info->getPathname();
            $result_excel = $this->import_excel($file_name);
            //处理图片ZIP
            $this->import_img($file_img);
            if (!count($result_excel)) {
                return ['ret_code' => 0, 'msg' => 'EXCEL为空'];
            }
            //获取所有分组
            $tags = $service->getTagList();
            foreach ($result_excel as $k => $v) {
                if ($k > 1 && !empty(preg_replace('# #', '', $v[0]))) {
                    $params['title'] = $v[0];
                    $params['quantity'] = $v[5];
                    $params['price'] = $v[6] * 100;
                    $params['post_fee'] = $v[7] * 100;
                    $params['item_no'] = $v[8];
                    $params['origin_price'] = $v[9];
                    $params['item_weight'] = $v[11];
                    $params['is_top'] = $v[12] == 1 ? true : false;
                    $params['is_display'] = 0;//$v[13] == 1 ? 1 : 0;
                    $params['desc'] = self::getDes($v[21]) . self::des($v[14]);//$v[14];
                    $params['buy_url'] = $v[15];
                    $params['buy_quota'] = $v[16];
                    $params['fields'] = 'title';
                    $params['is_virtual'] = 0;
                    $params['join_level_discount'] = $v[22];
                    $params['hide_stock'] = $v[20] ? 1 : $v[20];
                    $params['cid'] = $this->getCategoryId($v[17]);


                    $tag_id_res = $this->getTagId($service, $tags, $v[18]);
                    if (!$tag_id_res['code']) {
                        $error_msg .= $v[0] . "--失败原因：" . $tag_id_res['msg'] . ";";
                        continue;
                    }
                    /**
                     * 组装商品sku
                     * 先判断 sku属性是否为空
                     */
                    if (empty($v[1]) || empty($v[2]) || empty($v[3]) || empty($v[4])) {
                        $error_msg .= $v[0] . "--失败原因：" . "sku属性不能为空" . ";";
                        continue;
                    }

                    //$sku = '[{"item_no":3337,"code":"10","price":10000,"quantity":100,"skus":[{"k":"颜色","v":"白色"},{"k":"尺寸","v":"L"}]},{"item_no":3337,"code":"10","price":10000,"quantity":100,"skus":[{"k":"颜色","v":"白色"},{"k":"尺寸","v":"S"}]},{"item_no":3337,"code":"10","price":10000,"quantity":100,"skus":[{"k":"颜色","v":"黑色"},{"k":"尺寸","v":"L"}]},{"item_no":3338,"code":"10","price":10000,"quantity":101,"skus":[{"k":"颜色","v":"黑色"},{"k":"尺寸","v":"S"}]}]';
                    $sku = $this->getSkuJson($v[1], $v[2], $v[3], $v[4]);
                    $params['sku_stocks'] = $sku;

                    /**
                     * 处理商品图片
                     * 1.判断图片是否为空
                     * 2.新增图片，获取图片id
                     */
                    if (empty($v[19])) {
                        $error_msg .= $v[0] . "--失败原因：" . "图片为空" . ";";
                        continue;
                    }

                    $image_res = $this->getImageId($service, $v[19]);

                    if (!$image_res['code']) {
                        $error_msg .= $v[0] . "--失败原因：" . $image_res['msg'] . ";";
                        continue;
                    }
                    $params['image_ids'] = $image_res['image_ids'];
                    //$params['image_ids'] = 869283374;

                    $add_goods_res = $service->addGoods($params);
                    if (!$add_goods_res['code']) {
                        $error_msg .= $v[0] . "--失败原因：" . $add_goods_res['msg'] . ";";
                        continue;
                    }

                    $success_msg .= $v[0] . ";";
                }

            }
            //将今天的图片重命名，防止今天下一次提交替换图片
            $this->fRename();

            return ['ret_code' => 1, "success_msg" => $success_msg, "error_msg" => $error_msg];
        } catch (Exception $e) {
            return ['ret_code' => 0, 'msg' => $e->getMessage()];

        }

    }

    public function callback(Request $request)
    {
        $code = $request->param('code');
        $state = $request->param('state');
        echo $state;
        if (!is_null($code)) {
            $token = new YZGetTokenClient(YouZanConfig::$CLIENT_ID, YouZanConfig::$CLIENT_SECRET);
            $type = 'oauth';//如要刷新access_token，type值为refresh_token
            $keys['code'] = $code;//如要刷新access_token，这里为$keys['refresh_token']
            $keys['redirect_uri'] = YouZanConfig::$REDIRECT_URL;
            $data = $token->get_token($type, $keys);
            if (!$data) {
                //获取处理access_token失败
                $this->error("access_token失败,请重新获取。");
            }
            //处理access_token
            $filename = 'token/' . $state . '.php';
            $token_obj = json_decode(get_php_file($filename));
            $access_token = $data['access_token'];
            if ($access_token) {
                $token_obj->access_token = $access_token;
                $token_obj->expire_time = time() + 60 * 60 * 24 * 6;
                set_php_file($filename, json_encode($token_obj));
            }
            //access_token处理成功，跳转首
            $this->redirect('http://youzan.partywall.cn:8080/youzan-api/public/index.php?id=' . $state);
        }

    }


    public function getURL()
    {
        echo YouZanConfig::$LOGIN_URL . $this->guid();
    }

    private function getImageId($service, $name)
    {

        $name_arr = explode(';', $name);
        $image_ids = '';

        for ($i = 0; $i < count($name_arr); $i++) {
            $filename = ROOT_PATH . 'public' . DS . 'img' . DS . date('Y-m-d') . DS . $name_arr[$i];
            if (!is_file($filename)) {
                $res['code'] = 0;
                $res['msg'] = '图片:' . $name_arr[$i] . '不存在';
                return $res;
            }
            $size = getsize(filesize($filename), 'mb');
            if ($size > 1) {
                $res['code'] = 0;
                $res['msg'] .= '图片' . $name_arr[$i] . '大于1M';
                return $res;
            }

            $url = ROOT_PATH . 'public' . DS . 'img' . DS . date('Y-m-d') . DS . $name_arr[$i];

            $res = $service->addImage($url);
            if (!$res['code']) {
                return ['code' => 1, 'msg' => "图片" . $name_arr[$i] . "上传出错"];
            }
            //拼接图片ids
            $image_id = $res['image_id'];
            $image_ids .= $image_id . ',';
        }

        if (strlen($image_ids)) {
            $image_ids = substr($image_ids, 0, -1);
        }

        return ['code' => 1, "image_ids" => $image_ids];

    }


    /**
     * 导入excel文件
     * @param  string $file excel文件路径
     * @return array        excel文件内容数组
     */
    private function import_excel($file)
    {
        // 判断文件是什么格式
        $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        ini_set('max_execution_time', '0');
        Loader::import('PHPExcel.PHPExcel');
        // 判断使用哪种格式
        if ($extension == 'xlsx') {
            $objReader = new \PHPExcel_Reader_Excel2007();
            $objPHPExcel = $objReader->load($file);
        } else if ($extension == 'xls') {
            $objReader = new \PHPExcel_Reader_Excel5();
            $objPHPExcel = $objReader->load($file);
        } else if ($extension == 'csv') {
            $PHPReader = new \PHPExcel_Reader_CSV();

            //默认输入字符集
            $PHPReader->setInputEncoding('GBK');

            //默认的分隔符
            $PHPReader->setDelimiter(',');

            //载入文件
            $objPHPExcel = $PHPReader->load($file);
        }
        $sheet = $objPHPExcel->getSheet(0);
        // 取得总行数
        $highestRow = $sheet->getHighestRow();
        // 取得总列数
        $highestColumn = $sheet->getHighestColumn();
        //循环读取excel文件,读取一条,插入一条
        $data = array();
        //从第一行开始读取数据
        for ($j = 1; $j <= $highestRow; $j++) {
            //从A列读取数据
            for ($k = 'A'; $k <= $highestColumn; $k++) {
                // 读取单元格
                $data[$j][] = $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
            }
        }
        return $data;
    }

    /**
     * 处理图片zip
     * @param $file
     * @return array
     */
    private function import_img($file)
    {

        try {
            Loader::import('Zip.Zip');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'imgZip');

            $file_name = $info->getPathname();
            $path = ROOT_PATH . 'public' . DS . 'img' . DS . date('Y-m-d');

            if (!is_dir($path)) {
                mkdir($path);
            }
            $path = $path . DS;
            $z = new \Unzip();
            $z->unzip($file_name, $path, true, false);

            if ($z) {
                return ['code' => 1, 'name' => 'img'];
            }
        } catch (Exception $e) {
            return ['code' => 0, 'msg' => '图片压缩包上传失败，请检查。可能原因：过大。'];
        }

    }


    /**
     * 根据用户提供类别获取类别ID
     * @param $key
     * @return mixed
     */
    private function getCategoryId($key)
    {
        $all = array(
            '女人' => 1000000,
            '男人' => 2000000,
            '食品' => 3000000,
            '美妆' => 4000000,
            '亲子' => 5000000,
            '居家' => 6000000,
            '数码家电' => 7000000,
            '其他' => 8000000,
            '礼品鲜花' => 8000001,
            '餐饮外卖' => 8000002,
            '丽人健身' => 8000003,
            '休闲娱乐' => 8000004,
            '酒店客栈' => 8000005,
            '婚庆摄影' => 8000006,
            '汽车养护' => 8000007,
            '家政服务' => 8000008,
            '门票卡券' => 8000009,
            '家装建材' => 80000010,
            '钟表眼镜' => 80000011,
            '宠物' => 80000012,
            '文化收藏' => 80000013,
            '日用百货' => 80000014,
            '教育培训' => 80000015,
            '媒体服务' => 80000017,
            '充值缴费' => 80000018

        );

        $key = preg_replace('# #', '', $key);
        if (array_key_exists($key, $all)) {
            return $all[$key];

        } else {
            return $all['其他'];
        }

    }


    /**
     * 将商品字符串类型SKU转换为Json类型
     * @param $sku_pro
     * @param $sku_pri
     * @param $sku_stock
     * @param $code
     * @return string
     */
    private
    function getSkuJson($sku_pro, $sku_pri, $sku_stock, $code)
    {
        $sku_pro_arr = explode(';', $sku_pro);
        $count = count($sku_pro_arr);
        $sku_pri_arr = explode(';', $sku_pri);
        $sku_stock_arr = explode(';', $sku_stock);
        $code_arr = explode(';', $code);
        $arr_json = array();


        for ($i = 0; $i < $count; $i++) {
            $arr_json[$i]['item_no'] = empty($code_arr[$i]) ? '' : $code_arr[$i];
            $arr_json[$i]['code'] = $i + 1;
        }

        for ($i = 0; $i < $count; $i++) {
            $arr_json[$i]['price'] = empty($sku_pri_arr[$i]) ? 100 : (int)$sku_pri_arr[$i] * 100;
        }

        for ($i = 0; $i < $count; $i++) {
            $arr_json[$i]['quantity'] = empty($sku_stock_arr[$i]) ? 0 : (int)$sku_stock_arr[$i];
        }

        foreach ($sku_pro_arr as $k => $v) {
            $param_array = explode(',', $sku_pro_arr[$k]);
            $d_arr = array();
            for ($j = 0; $j < count($param_array); $j++) {
                $arr = explode(':', $param_array[$j]);
                $d = array('k' => $arr[0], 'v' => $arr[1]);
                $d_arr[$j] = $d;

            }
            $arr_json[$k]['skus'] = $d_arr;
        }

        $json_arr = json_encode($arr_json);
        return $json_arr;

    }

    /**
     * 将用户提交的分组名称转化为分组id
     * @param $service
     * @param $tags
     * @param $name
     * @return array
     */
    private function getTagId($service, $tags, $name)

    {
        $name_arr = explode(';', $name);
        $tag_ids = '';
        for ($i = 0; $i < count($name_arr); $i++) {
            $name_obj = preg_replace('# #', '', $name_arr[$i]);
            if (array_key_exists($name_obj, $tags)) {
                $tag_ids .= $tags[$name_obj] . ',';

            } else {
                //新增
                $add_res = $service->addTag($name_arr[$i]);
                if (!$add_res['code']) {
                    return ['code' => 0, 'msg' => '新增分组"' . $name_arr[$i] . '"失败'];

                }
                $tag_ids .= $add_res['tag_id'] . ',';

            }
        }
        if (strlen($tag_ids)) {
            $tag_ids = substr($tag_ids, 0, -1);
        }

        return ['code' => 1, 'ids' => $tag_ids];


    }


    public function download()
    {
        $type = Request()->param('type');
        if ($type == 1) {
            $filename = ROOT_PATH . 'public' . DS . 'youzan-goods.xlsx';
            $des = '上传商品Excel模版.xlsx';
            header('Content-Type:xlsx'); //指定下载文件类型
        } else if ($type == 2) {
            $filename = ROOT_PATH . 'public' . DS . 'test.zip';
            $des = '上传商品图片模版.zip';
        } else if ($type == 3) {
            $filename = ROOT_PATH . 'public' . DS . 'youzan.docx';
            $des = 'Excel说明文档.docx';
        }

        //header('Content-Type:image/gif'); //指定下载文件类型
        header('Content-Disposition: attachment; filename="' . $des . '"'); //指定下载文件的描述
        header('Content-Length:' . filesize($filename)); //指定下载文件的大小
        readfile($filename);
    }

    private function des($des)
    {
        $des_arr = explode('*', $des);
        $reture_des = '';
        for ($i = 0; $i < count($des_arr); $i++) {

            if (strstr($des_arr[$i], "jpg") || strstr($des_arr[$i], "jpeg")
                || strstr($des_arr[$i], "gif") || strstr($des_arr[$i], "png")
            ) {

                $des_arr[$i] = $this->getImgSrc($des_arr[$i]);
            }

            $reture_des .= $des_arr[$i];
        }

        return $reture_des;
    }

    private function getImgSrc($name)
    {
        // $src = "http://youzan.partywall.cn:8080/imgtest/" . $name;
        $res = "<br/>" . "<img data-origin-width=\"639\" data-origin-height=\"643\"src=\"" . $name . "\" _src=\"" . $name . "\">";
        $res .= "<br/>";
        return $res;
    }

    private function getDes($des)
    {

        if (!strlen($des)) {
            return $des;
        }


        $res = '<ul >';
        $des_arr = explode(';', $des);
        for ($i = 0; $i < count($des_arr); $i++) {
            $res .= '<li><p>● ' . $des_arr[$i] . '</p></li>';

        }
        $res .= '</ul><br/>';
        return $res;

    }

    private function fRename()
    {
        $dirname = $path = ROOT_PATH . 'public' . DS . 'img' . DS . date('Y-m-d');
        $handle = opendir($dirname);
        while (($fn = readdir($handle)) !== false) {

            if ($fn != '.' && $fn != '..') {
                $curDir = $dirname . '/' . $fn;
                $path = pathinfo($curDir);
                $newname = $path['dirname'] . '/' . $this->guid() . '.' . $path['extension'];
                rename($curDir, $newname);


            }
        }
    }

    private function guid()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000);
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);
            $uuid = substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12);
            return $uuid;
        }
    }


}