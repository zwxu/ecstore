<?php

/*
 * @package base
 * @author edwin.lzh@gmail.com
 * @license 
 */
class base_storage_ecaesystem implements base_interface_storager
{
    private $_tmpfiles = array();

    function __construct()
    {
        //todo;
    }//End Function

    public function save($file, &$url, $type, $addons, $ext_name="")
    {
        if($type=='public'){
            $group_id = constant("ECAE_SITE_NAME").'-public'; 
        }elseif($type=='private'){
            $group_id = constant("ECAE_SITE_NAME").'-private'; 
        }else{
            $group_id = constant("ECAE_SITE_NAME").'-images'; 
        }
		$filename = $this->_get_ident($file,$type,$addons,$url,$path,$ext_name);
        //$filename = basename($file) . $ext_name;
        $ident = ecae_file_save($group_id, $file, array('name'=>$filename,"path"=>$path));
        if($ident){
            $url = ecae_file_url($ident);
            return $ident;
        }else{
            return false;
        }
    }//End Function
	
	// 生成文件名
    public function _get_ident($file,$type,$addons,$url,&$path,$ext_name=""){
		$ident = md5(rand(0,9999).microtime());
		// 路径
		if(isset($addons['path']) && $addons['path']) {
			$path = $addons['path'];
		} else {
			$path = '/'.$ident{0}.$ident{1}.'/'.$ident{2}.$ident{3}.'/'.$ident{4}.$ident{5}.'/'.substr($ident,6);
		}
		// 文件名
		if(isset($addons['name']) && $addons['name']) {
			$uri = $addons['name'];
		} else {
			$uri = substr(md5(($addons?$addons:$file).microtime()),0,6);
		}
		// 后缀
        if($ext_name) {
            if(strrpos($uri,".")) $uri = substr($uri,0,strrpos($uri,".")).$ext_name;
            else $uri .= $ext_name;
        }
        return $uri;
	} // end function _get_ident

    public function replace($file, $id)
    {
        return ecae_file_replace($id, $file);
    }//End Function


    public function remove($id)
    {
        if($id){
            return ecae_file_delete($id);
        }else{
            return false;
        }
    }//End Function

    public function getFile($id, $type)
    {
        $tmpfile = tempnam(sys_get_temp_dir(), 'ecaesystem');
        array_push($this->_tmpfiles, $tmpfile);
        if($id && ecae_file_fetch($id, $tmpfile)){
            return $tmpfile;
        }else{
            return false;
        }
    }//End Function

    function __destruct() 
    {
        foreach($this->_tmpfiles AS $tmpfile){
            @unlink($tmpfile);
        }//todo unlink tmpfiles;
    }//End Function

}//End Class
