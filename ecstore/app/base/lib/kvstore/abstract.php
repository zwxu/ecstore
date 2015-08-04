<?php

/*
 * @package base
 * @author edwin.lzh@gmail.com
 * @license 
 */
abstract class base_kvstore_abstract 
{
    
    /*
     * 生成经过处理的唯一key
     * @var string $key
     * @access public
     * @return string
     */
    public function create_key($key) 
    {
        return md5(base_kvstore::kvprefix() . $this->prefix . $key);
    }//End Function

}//End Class