<?php
// 应用公共文件
function get_php_file($filename)
{
    return trim(substr(file_get_contents($filename), 15));
}