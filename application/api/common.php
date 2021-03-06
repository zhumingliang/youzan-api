<?php
// 应用公共文件

/**
 * @param $filename
 * @return string
 */
function get_php_file($filename)
{
    //检查文件是否存在
    if (!is_file($filename)) {
        //不存在则创建文件
        fopen($filename, 'w');
        $token_obj = new stdClass();
        $token_obj->expire_time = time() - 60 * 60 * 24 * 7;
        $token_obj->access_token = "e528918012053f678305b28fba068baa";
        set_php_file($filename, json_encode($token_obj));
    }

    return trim(substr(file_get_contents($filename), 15));
}

/**
 * @param $filename
 * @param $content
 */

function set_php_file($filename, $content)
{
    $fp = fopen($filename, "w");
    fwrite($fp, "<?php exit();?>" . $content);
    fclose($fp);
}

/**
 * 生成guid
 * @return string
 */
function guid()
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

