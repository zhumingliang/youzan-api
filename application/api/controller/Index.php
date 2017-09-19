<?php

namespace app\api\controller;

use think\Controller;
use think\Exception;
use think\Loader;
use think\Request;
use YouZan\lib\YZGetTokenClient;
use YouZan\lib\YZTokenClient;
use YouZan\Token;
use YouZan\YouZanConfig;


class Index extends Controller
{
    public function index()
    {
        try {
            return view();
        } catch (Exception $e) {
            echo $e->getMessage();
        }


    }


    public function upFile()
    {
        /*$file_excel = request()->file('excel');
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
        $result = $this->import_excel($file_name);*/
        $res = $this->addGoods();
        return ['ret_code' => 4, 'msg' => 'access_token过期', 'url' => $res['url']];

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
            if (!$data) {
                //获取处理access_token失败
            }
            //处理access_token
            $token_obj = json_decode(get_php_file('access_token.php'));
            $access_token = $data['access_token'];
            if ($access_token) {
                $token_obj->expire_time = time() + 7000;
                $token_obj->access_token = $access_token;
                set_php_file("access_token.php", json_encode($token_obj));
            }
            //access_token处理成功，跳转首
            return view();

        }

    }


    public function addGoods()
    {
        $token = new Token();
        $token_res = $token->getAccessToken();
        if (!$token_res['res']) {
            //access_token过期，需要点击授权
            return $token_res;
        }
        $access_token = $token_res['access_token'];
        echo $access_token;
        /*        $client = new YZTokenClient($token);

                $method = 'youzan.item.create'; //要调用的api名称
                $api_version = '3.0.0'; //要调用的api版本号

                $my_params = [
                    'title' => 'aaatest-standard5566',
                    'price' => '10000',
                    'image_ids' => '845910482',
                    'desc' => 'http://n.sinaimg.cn/mil/8_img/upload/f8bc40b5/20170710/-Bec-fyhwret0362019.jpg',
                    'item_no' => '6933285600396',
                    'sku_images' => '[{"v":"22","img_url":"https://img.yzcdn.cn/upload_files/2016/09/24/1a6004ee7c5ecd970affba1999c7e0e1.jpg"}]',
                    'sku_stocks' => '[{"sku_id":3337,"code":"sdsfdsfs","price":10000,"quantity":100,"skus":[{"k":"颜色","kid":1,"v":"22","vid":1196}]}]',
                    'auto_listing_time' => '222112332',
                ];

                $my_files = [
                ];

                echo '<pre>';
                var_dump(
                    $client->post($method, $api_version, $my_params, $my_files)
                );
                echo '</pre>';*/
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
        Loader::import('PHPExcel.PHPExcel');
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

}