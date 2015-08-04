<?php
 

class site_explorer_file
{

	function __construct($theme=''){
		if($theme){
			$this->theme = $theme;
		}
		if(ECAE_MODE==true){
			$this->storager = kernel::single('site_explorer_dbsave');
		}else{
			$this->storager = kernel::single('site_explorer_fssave');
		}
		$this->storager->theme = $theme;
	}
	public function set_theme($theme){
		$this->theme = $theme;
		$this->storager->theme = $theme;
	}
    /*
     * 文件列表
     * @param array $filter
     * @return minxed
     */
    public function file_list($filter){
		return $this->storager->file_list($filter);
                    }

    /*
     * 分析列表
     * @param array $file
     * @return array
     */
    public function parse_filter($file) 
    {
		return $this->storager->parse_filter($file);
    }//End Function

    /*
     * 删除文件
     * @param array $file
     * @return boolean
     */
    public function delete_file($file) 
    {
        return $this->storager->delete_file($file);
    }//End Function

    /*
     * 读取文件
     * @param array $file
     * @return mixed
     */
    public function get_file($file) 
    {
		return $this->storager->get_file($file);
    }//End Function

    /*
     * 取得文件列表
     * @param array $file
     * @param string $fname
     * @return mixed
     */
    public function get_file_baklist($filter, $fname) 
    {
		return $this->storager->get_file_baklist($filter, $fname);
    }//End Function
    
    /*
     * 取得文件列表
     * @param array $file
     * @param string $fname
     * @return mixed
     */
    public function get_file_instancelist($filter, $fname) 
    {
		return $this->storager->get_file_instancelist($filter, $fname);
    }//End Function
        
    /*
     * 备份文件
     * @param array $file
     * @return boolean
     */
    public function backup_file($file) 
    {
       return $this->storager->backup_file($file);
    }//End Function
    
    /*
     * 保存文件源码
     * @param string $file
     * @param string $source
     * @return boolean
     */
    public function save_source($file, $source) 
    {
        return $this->storager->save_source($file, $source);
    }//End Function

    /*
     * 保存图片文件
     * @param string $file
     * @param array $_file
     * @return boolean
     */
    public function save_image($file, $_file) 
    {
        return $this->storager->save_image($file, $_file);
    }//End Function

    /*
     * 还原文件
     * @param string $file
     * @return boolean
     */
    public function recover_file($file) 
    {
		return $this->storager->recover_file($file);
    }//End Function

    public function is_dir_writable($dir) 
    {
		return $this->storager->is_dir_writable($dir);
    }//End Function

    public function is_file_writable($file) 
    {
		return $this->storager->is_file_writable($file);
    }//End Function
}
