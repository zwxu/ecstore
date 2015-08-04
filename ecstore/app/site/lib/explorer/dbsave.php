<?php

 

class site_explorer_dbsave
{
    function __construct(){
        $this->fileObj = app::get('site')->model('themes_file');
        $this->ftype = array(
                    'html' =>app::get('site')->_('模板文件'),
                    'gif'=>app::get('site')->_('图片文件'),
                    'jpg'=>app::get('site')->_('图片文件'),
                    'jpeg'=>app::get('site')->_('图片文件'),
                    'png'=>app::get('site')->_('图片文件'),
                    'bmp'=>app::get('site')->_('图片文件'),
                    'css'=>app::get('site')->_('样式表文件'),
                    'js'=>app::get('site')->_('脚本文件'),
                );
    }
    /*
     * 文件列表
     * @param array $filter
     * @return minxed
     */
    public function file_list($filter){
        $key = md5(var_export($filter,1));

        $theme_dir = THEME_DIR.DIRECTORY_SEPARATOR.$this->theme;
        $cur_dir = $filter['dir'];
        $dir = substr($cur_dir,strlen($theme_dir)+1);

        if(!isset($this->_cacheList[$key])){
            
            $aRows = array();
            
            $ftype = $this->ftype;


            $rows = $this->fileObj->getList('*',array('theme'=>$this->theme),0,-1);
           foreach($rows as $row){
                $file_name = $row['filename'];
                if ($file_name!="." && $file_name!=".." && $file_name!="Thumbs.db" && $file_name!="theme.xml"&& $file_name!=".svn"){
                    if(!$filter['show_bak'] && preg_match('/.*\\.bak_[0-9]+\\.[^\\.]+/',$file_name)){
                        continue;
                    }
                    $fext = strtolower(substr($file_name,strrpos($file_name,'.')+1));
                    if($filter['type'] == 'all' || in_array($fext, (array)$filter['type'])){
                        $aRows[$file_name] = array('id'=> ($filter['id'] ? $filter['id'].'-' : '').$file_name,
                                'name' => $file_name,
                                'filetype' => $fext,
                                'memo' => ($ftype[$fext]?$ftype[$fext]:app::get('site')->_('资源文件'))
                            );
                    }
                }
            }
            $tmpRows = array();
            foreach($aRows as $k=>$v){
                $key = "['".str_replace('/',"']['",$k)."']";
                eval("\$tmpRows".$key."=\$v;");

            }
            if(is_array($tmpRows))    ksort($tmpRows);
            if($dir){
                $key = "['".str_replace('/',"']['",$dir)."']";                
                eval("\$tmpRows = \$tmpRows".$key.";");                
            }
            foreach($tmpRows as $key=>$row){
                if(!$row['filetype']){
                    unset($tmpRows[$key]);
                    $tmpRows[$key]['filetype'] = 'Folder';
                    $tmpRows[$key]['name'] = $key;
                    $tmpRows[$key]['memo'] = app::get('site')->_('资源文件');
                }
            }
            $this->_cacheList[$key] = $tmpRows;
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
        if($this->fileObj->delete(array('theme'=>$this->theme,'filename'=>$file))){
            return true;
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
        $rows = $this->fileObj->getList('content',array('theme'=>$this->theme,'filename'=>$file));
        if($rows[0]['content']){
            return $rows[0]['content'];
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
        $dir=$fnameInfo['dirname']!='.'?preg_quote($fnameInfo['dirname'],'/').'\/':'';
        $regex = '/^'.$dir.preg_quote($fnameInfo['filename']).'\.bak_([0-9]+)\.'.preg_quote($fnameInfo['extension']).'$/';
        
        foreach($fileList AS $val){
            if(preg_match($regex, $val['name'])){
                $return[] = $val;
            }
        }
        return $return;
    }//End Function
    public function get_file_instancelist($filter, $fname) 
    {
        if(empty($fname))   return array();
        $fileList = $this->file_list($filter);
        if(!is_array($fileList))    return array();
        $fnameInfo = pathinfo($fname);
        $regex = '/^'.preg_quote($fnameInfo['filename']).'-(%s)\.'.preg_quote($fnameInfo['extension']).'$/';
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
        $loop = 1;
        $fileInfo = pathinfo($file);
        $baklist = $this->get_file_baklist(array('dir'=>dirname(THEME_DIR.DIRECTORY_SEPARATOR.$this->theme.'/'.$file), 'show_bak'=>true, 'type'=>'all'), $file);

        $dir=$fileInfo['dirname']!='.'?$fileInfo['dirname'].'/':'';

        if(is_array($baklist)){
            foreach($baklist AS $val){
                if($val['name'] !==  sprintf('%s.bak_%d.%s', $dir.$fileInfo['filename'], $loop, $fileInfo['extension'])){
                    break;
                }
                $loop++;
            }
        }
        $ftype = $this->ftype;
        $target = sprintf('%s%s.bak_%d.%s', dirname($file)=='.'?'':dirname($file).'/', $fileInfo['filename'], $loop, $fileInfo['extension']);
        $content = $this->get_file($file);

        //如果是css文件,单独做处理
        if($fileInfo['extension']=='css'||$fileInfo['extension']=='jpg'||$fileInfo['extension']=='jpeg'||$fileInfo['extension']=='gif'||$fileInfo['extension']=='png'||$fileInfo['extension']=='bmp'){
            if(constant("ECAE_MODE")) {
                $tmp_file = tempnam(sys_get_temp_dir(),'themecss');
            } else {
                $tmp_file = tempnam(DATA_DIR,'themecss');
            }
            $initial_url = explode('|',$content);//修复恢复备份的css错误的问题@lujy
            $source = file_get_contents(reset($initial_url));
            file_put_contents($tmp_file,$source);
            $storager = kernel::single('base_storager');
            $addons = array('name'=>basename($target),'path'=>dirname('/theme/'.$this->theme.'/'.$target).'/');
            $file_indent = $storager->save($tmp_file,'image',$addons);
            if(is_file($tmp_file)) @unlink($tmp_file);
            $content = $file_indent;
        }

        $data = array(
            'filename'=>$target,
            'fileuri'=>$this->theme.':'.$target,
            'theme'=>$this->theme,
            'filetype'=>$fileInfo['extension'],
            'memo'=>$ftype[$fileInfo['extension']],
            'content'=>$content,
            );
        if($this->fileObj->save($data)){
            return true;
        }else{
            return false;
        }
    }//End Function
    
    /*
     * 保存文件源码
     * @param string $file
     * @param string $source
     * @return boolean
     */
    public function save_source($file, $source) 
    {
        $ftype = $this->ftype;
        $fileInfo = pathinfo($file);
        $filter = array(
            'fileuri'=>$this->theme.':'.$file,
            'theme'=>$this->theme,
            );
        $data['content'] = base64_encode($source);
        
        //如果是css文件,单独做处理,只更新远程的文件内容即可。
        if($fileInfo['extension']=='css'){
            if(constant("ECAE_MODE")) {
                $tmp_file = tempnam(sys_get_temp_dir(),'themecss');
            } else {
                $tmp_file = tempnam(DATA_DIR,'themecss');
            }
            $indent = $this->get_file($file);
            file_put_contents($tmp_file,$source);
            $storager = kernel::single('base_storager');
            $file_indent = $storager->replace($tmp_file,$indent);
            if(is_file($tmp_file)) @unlink($tmp_file);
            return true;
        }

        if($this->fileObj->update($data,$filter)){
            return true;
        }else{
            return false;
        }

    }//End Function

    /*
     * 保存图片文件
     * @param string $file
     * @param array $_file
     * @return boolean
     */
    public function save_image($file, $_file) 
    {
        if ($_file['size'] > 0){
            if ((substr($_file['type'],0,5)=="image") ){
                $indent = $this->get_file($file);
                $storager = kernel::single('base_storager');
                if($storager->replace($_file['tmp_name'],$indent)){
                    return true;                
                }else{
                    return false;
                }
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
        $fileInfo = pathinfo($file);
        $fname = basename($file);
        $regex = '/^(.*)\.bak_([0-9]+)\.(.*)$/';
        preg_match_all($regex, $fname, $match);
        if(!count($match[0]))    return false;

        $target = sprintf('%s%s.%s', dirname($file)=='.'?'':dirname($file).'/', $match[1][0], $match[3][0]);

        $content = $this->get_file($file);

        //如果是css或image文件,单独做处理
        if($fileInfo['extension']!='xml'&&$fileInfo['extension']!='html'){
            $storager = kernel::single('base_storager');
            $target_indent = $this->get_file($target);
            $src_indent = $storager->parse($content);

            $http = kernel::single('base_httpclient');
            $http->set_timeout(10);
            $file_content = $http->action(__FUNCTION__,$src_indent['url'],null,null,array());
            if(constant("ECAE_MODE")) {
                $tmp_file = tempnam(sys_get_temp_dir(),'ts');
            } else {
                $tmp_file = tempnam(DATA_DIR,'ts');
            }
            file_put_contents($tmp_file,$file_content);
            $storager->replace($tmp_file,$target_indent);
            if(is_file($tmp_file)) @unlink($tmp_file);
            return true;
        }

        $data = array(
            'content'=>base64_encode($content),
            );
        $filter = array(
            'fileuri'=>$this->theme.':'.$target,
            'theme'=>$this->theme,
            );
        if($this->fileObj->update($data,$filter)){
            return true;
        }else{
            return false;
        }

        return copy($file, $target);
    }//End Function

}
