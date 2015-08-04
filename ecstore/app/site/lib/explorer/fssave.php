<?php


class site_explorer_fssave
{

    /*
     * 文件列表
     * @param array $filter
     * @return minxed
     */
    public function file_list($filter){
        $key = md5(var_export($filter,1));

        if(!isset($this->_cacheList[$key])){
            
            $aRows = array();
            
            $dir = $filter['dir'];

            if(!is_dir($dir))   return false;

            $dirhandle=@opendir($dir);
            $ftype = array(
                    'html' =>app::get('site')->_('模板文件'),
                    'gif'=>app::get('site')->_('图片文件'),
                    'jpg'=>app::get('site')->_('图片文件'),
                    'jpeg'=>app::get('site')->_('图片文件'),
                    'png'=>app::get('site')->_('图片文件'),
                    'bmp'=>app::get('site')->_('图片文件'),
                    'css'=>app::get('site')->_('样式表文件'),
                    'js'=>app::get('site')->_('脚本文件'),
                );

            while($file_name=@readdir($dirhandle)){
                if ($file_name!="." && $file_name!=".." && $file_name!="Thumbs.db" && $file_name!="theme.xml"&& $file_name!=".svn"){
                    if(!$filter['show_bak'] && preg_match('/.*\\.bak_[0-9]+\\.[^\\.]+/',$file_name)){
                        continue;
                    }
                    if(!is_dir(realpath($dir.'/'.$file_name)))
                        $fext = strtolower(substr($file_name,strrpos($file_name,'.')+1));
                    else
                        $fext = 'Folder';
                    if($filter['type'] == 'all' || in_array($fext, (array)$filter['type'])){
                        $aRows[$file_name] = array('id'=> ($filter['id'] ? $filter['id'].'-' : '').$file_name,
                                'name' => $file_name,
                                'filetype' => $fext,
                                'memo' => ($ftype[$fext]?$ftype[$fext]:app::get('site')->_('资源文件'))
                            );
                    }
                }
            }
            @closedir($dirhandle);
            if(is_array($aRows))    ksort($aRows);
            $this->_cacheList[$key] = $aRows;
        }

        return  $this->_cacheList[$key];
    }

    /*
     * 分析列表
     * @param array $file
     * @return array
     */
    public function parse_filter($file) 
    {
        if(is_array($file)){
            foreach($file as $k=>$v){
                $name=explode('.',$k);
                if(substr($name[1], 0, 4)=='bak_')
                unset($file[$k]);
                if($v['filetype']=='Folder'){
                unset($file[$k]);
                array_push($file,$v);
                }
            }
            return $file;
        }
        return array();
    }//End Function

    /*
     * 删除文件
     * @param array $file
     * @return boolean
     */
    public function delete_file($file) 
    {
        $this->is_file_writable($file);
        if(is_file($file)){
            return unlink($file);
        }
        return false;
    }//End Function

    /*
     * 读取文件
     * @param array $file
     * @return mixed
     */
    public function get_file($file) 
    {
        if(is_file($file)){
            return file_get_contents($file);
        }
        return false;
    }//End Function

    /*
     * 取得文件列表
     * @param array $file
     * @param string $fname
     * @return mixed
     */
    public function get_file_baklist($filter, $fname) 
    {
        if(empty($fname))   return array();
        $fileList = $this->file_list($filter);
        if(!is_array($fileList))    return array();
        $fnameInfo = pathinfo($fname);
        $regex = '/^'.preg_quote($fnameInfo['filename']).'\.bak_([0-9]+)\.'.preg_quote($fnameInfo['extension']).'$/';
        foreach($fileList AS $val){
            if(preg_match($regex, $val['name'])){
                $return[] = $val;
            }
        }
        return $return;
    }//End Function
    
    /*
     * 备份文件
     * @param array $file
     * @return boolean
     */
    public function backup_file($file) 
    {
        $this->is_dir_writable(dirname($file));
        if(is_file($file)){
            $loop = 1;
            $fileInfo = pathinfo($file);
            $baklist = $this->get_file_baklist(array('dir'=>dirname($file), 'show_bak'=>true, 'type'=>'all'), basename($file));
            if(is_array($baklist)){
                foreach($baklist AS $val){
                    if($val['name'] !==  sprintf('%s.bak_%d.%s', $fileInfo['filename'], $loop, $fileInfo['extension'])){
                        break;
                    }
                    $loop++;
                }
            }
            $target = sprintf('%s/%s.bak_%d.%s', dirname($file), $fileInfo['filename'], $loop, $fileInfo['extension']);
            return copy($file, $target);
        }
        return false;
    }//End Function
    
    /*
     * 保存文件源码
     * @param string $file
     * @param string $source
     * @return boolean
     */
    public function save_source($file, $source) 
    {
        $this->is_file_writable($file);
        if(is_file($file)){
            return file_put_contents($file, $source);
        }
        return false;
    }//End Function

    /*
     * 保存图片文件
     * @param string $file
     * @param array $_file
     * @return boolean
     */
    public function save_image($file, $_file) 
    {
        $this->is_file_writable($file);
        if ($_file['size'] > 0){
            if ((substr($_file['type'],0,5)=="image") ){

                if (move_uploaded_file($_file['tmp_name'], $file)) {
                    chmod($file, 0644);
                    return true;
                }
                return false;
            }
        }
        return false;
    }//End Function

    /*
     * 还原文件
     * @param string $file
     * @return boolean
     */
    public function recover_file($file) 
    {
        $fname = basename($file);
        $regex = '/^(.*)\.bak_([0-9]+)\.(.*)$/';
        preg_match_all($regex, $fname, $match);
        if(!count($match[0]))    return false;
        $target = sprintf('%s/%s.%s', dirname($file), $match[1][0], $match[3][0]);
        $this->is_file_writable($target);
        return copy($file, $target);
    }//End Function

    public function is_dir_writable($dir) 
    {
        $dir = realpath($dir);
        $file = $dir . '/wrieable_touch.test';
        if($handle = @fopen($file, 'a+')){
            if(@fwrite($handle, 'test version')){
                fclose($handle);
                unlink($file);
                return true;
            }else{
                trigger_error(app::get('site')->_('目标目录不可写'), E_USER_ERROR);
                return false;
            }
        }else{
            trigger_error(app::get('site')->_('目标目录不可写'), E_USER_ERROR);
            return false;
        }
    }//End Function

    public function is_file_writable($file) 
    {
        if(is_writable($file)){
            return true;
        }else{
            trigger_error(app::get('site')->_('目标文件不可写'), E_USER_ERROR);
            return false;
        }
    }//End Function
}
