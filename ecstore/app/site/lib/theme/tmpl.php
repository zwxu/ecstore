<?php


class site_theme_tmpl 
{
	function __construct(){
		if(ECAE_MODE){
			$this->storager = kernel::single('site_theme_tmpl_dbsave');
        }else{
			$this->storager = kernel::single('site_theme_tmpl_fssave');
        }
            }
    public function get_default($type, $theme){
        return $this->storager->get_default($type, $theme);
        }
    public function set_default($type, $theme, $value){
        return $this->storager->set_default($type, $theme, $value);
            }
    public function del_default($type, $theme){
        return $this->storager->del_default($type, $theme);
            }
    public function set_all_tmpl_file($theme){
        return $this->storager->set_all_tmpl_file($theme);
        }
    public function get_all_tmpl_file($theme){
        return $this->storager->get_all_tmpl_file($theme);
                }
    public function tmpl_file_exists($tmpl_file, $theme){
        return $this->storager->tmpl_file_exists($tmpl_file, $theme);
                    }
    public function get_edit_list($theme){
        return $this->storager->get_edit_list($theme);
                }
    public function install($theme){
        return $this->storager->install($theme);
            }
    public function update($theme){
        return $this->storager->update($theme);
    }
    public function insert($data){
        return $this->storager->insert($data);
        }
    public function insert_tmpl($data,&$msg){
        return $this->storager->insert_tmpl($data,$msg);
        }
    public function copy_tmpl($tmpl, $theme){
        return $this->storager->copy_tmpl($tmpl, $theme);
            }
    public function delete_tmpl_by_theme(){
        return $this->storager->delete_tmpl_by_theme();
        }
    public function delete_tmpl($tmpl, $theme){
        return $this->storager->delete_tmpl($tmpl, $theme);
            }
    private function __get_all_files($sDir, &$aFile, $loop=true){
        return $this->storager->__get_all_files($sDir, $aFile, $loop=true);
                        }
    public function get_name(){
        return $this->storager->get_name();
    }
    public function get_list_name($name){
        return $this->storager->get_list_name($name);
    }
    private function __get_tmpl_list() {
        return $this->storager->__get_tmpl_list();
                    }
    public function touch_theme_tmpl($theme){
        return $this->storager->touch_theme_tmpl($theme);
                }
    public function touch_tmpl_file($tmpl, $time=null){
        return $this->storager->touch_tmpl_file($tmpl, $time=null);
            }
    public function output_pkg($theme){
        return $this->storager->output_pkg($theme);
        }
    public function make_configfile($theme){
        return $this->storager->make_configfile($theme);
        }

}//End Class
