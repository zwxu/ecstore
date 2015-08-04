<?php
class site_widget_install {

    public function check_install() 
    {
        $this->check_dir();
        $d = dir(THEME_DIR);
        while (false !== ($entry = $d->read())) {
            if(in_array($entry, array('.', '..', '.svn')))   continue;
            if(is_dir(THEME_DIR . '/' . $entry)&&!ECAE_MODE){
                $themeData = app::get('site')->model('themes')->select()->where('theme = ?', $entry)->instance()->fetch_row();
                if(empty($themeData)){
                    $this->init_theme($entry);
                }
            }
            if(!kernel::single('site_theme_base')->get_default()){
                kernel::single('site_theme_base')->set_default($entry);
            }
        }
        $d->close();
    }//End Function

}