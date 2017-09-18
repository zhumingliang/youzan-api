<?php
// 应用公共文件

/**
 * @param $filename
 * @return string
 */
function get_php_file($filename)
{
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