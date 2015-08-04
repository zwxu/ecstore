<?php

 
/*
 * @package base
 * @author edwin.lzh@gmail.com
 * @license 
 */

interface base_charset_interface{
    
    public function local2utf($strFrom,$charset='zh');

    public function utf2local($strFrom,$charset='zh');

    public function u2utf8($str);

    public function utf82u($str);

    public function replace_utf8bom($str);

    public function is_utf8($word);
}