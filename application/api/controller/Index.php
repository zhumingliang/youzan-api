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

            return view();
           /* $token = new Token();
            $r = $token->getAccessToken();*/


        } catch (Exception $e) {
            echo $e->getMessage();
        }


    }


    public function upFile(){
        $file = request()->param('import');
        dump($file);
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
            //access_token处理成功，跳转首页
            return view();

        }

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