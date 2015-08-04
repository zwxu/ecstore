<?php
class business_ctl_admin_default extends desktop_controller{
    public function index() 
    {
        $this->pagedata['website_url'] = app::get('business')->getConf('website.url');        
        $this->page('admin/index.html');
    }//End Function

    public function save() 
    {
        $this->begin();
        app::get('business')->setConf('website.url', $_POST['url']);
        $this->end(true, '保存成功');
    }//End Function
    public function img_index() 
    {
        $this->pagedata['website_url'] = app::get('business')->getConf('website.img_url');        
        $this->page('admin/img_index.html');
    }//End Function

    public function img_save() 
    {
        $this->begin();
        app::get('business')->setConf('website.img_url', $_POST['url']);
        $this->end(true, '保存成功');
    }//End Function
   
    public function finder_object_select(){
        $this->get_finder_object_items();
        $this->pagedata['res_url'] = app::get('business')->res_url;
        $this->display('admin/object/object_select.html');
    }
    
    private function get_finder_object_items(){
        $arr_widgets = array();
        if($_POST['widgets']){
          $arr_widgets = json_decode($_POST['widgets'],true);
        }
        $this->pagedata['onlyone'] = 0;
        if(isset($_POST['onlyone']) && $_POST['onlyone']){
            $this->pagedata['onlyone'] = 1;
        }
        $name = trim(urldecode($_GET['name']));
        $tab = trim(urldecode($_GET['tab']));
        $tab = $tab?$tab:'A';
        $this->pagedata['tab'] = $tab;
        $voc = explode(' ',"A B C D E F G H I J K L M N O P Q R S T U V W X Y Z");
        $voc[] = '0';
        $this->pagedata['voclist'] = $voc;
        try{
            $object = $_POST['object'];
            $app_id = $_POST['app_id'];
            $app = app::get($app_id);        
            $o = $app->model($object);
            $dbschema = $o->get_schema();
            
            $sql  = " select {$_POST['textcol']},{$_POST['idcol']} as self_id,{$dbschema['textColumn']} as self_name from ";
            $sql .= $o->table_name(1)." where 1=1 ";
            if($name){
                $sql .= " and {$dbschema['textColumn']} link '%{$name}%' ";
            }
            if($_POST['filter']){
                $where = kernel::single('dbeav_filter')->dbeav_filter_parser($_POST['filter'],null,null,$o);
                $sql .= " and ".$where;
            }
            if($arr_widgets){
                $arr_id = array_map('current',$arr_widgets);
            }
            foreach((array)$o->db->select($sql) as $item){
                $item['self_tab'] = $this->getinitial($item['self_name']);
                if($arr_id && in_array($item[self_id],$arr_id)){
                    $this->pagedata['info']['right'][] = $item;
                }else{
                    $this->pagedata['info']['left'][] = $item;
                }
            }
        }catch (Exception $e) {   
            print $e->getMessage();   
            exit();   
        }
    }
    
    private function getinitial($str){
        $str = iconv("UTF-8","gb2312", $str);
        $asc=ord(substr($str,0,3));
        if ($asc<160){ //非中文
            if ($asc>=48 && $asc<=57){
                return '0'; //数字
            }elseif ($asc>=65 && $asc<=90){
                return chr($asc); // A--Z
            }elseif ($asc>=97 && $asc<=122){
                return chr($asc-32); // a--z
            }else{
                return '0'; //其他
            }
        }else{ //中文
            $asc=$asc*1000+ord(substr($str,1,1));
            //获取拼音首字母A--Z
            if ($asc>=176161 && $asc<176197){
                return 'A';
            }elseif ($asc>=176197 && $asc<178193){
                return 'B';
            }elseif ($asc>=178193 && $asc<180238){
                return 'C';
            }elseif ($asc>=180238 && $asc<182234){
                return 'D';
            }elseif ($asc>=182234 && $asc<183162){
                return 'E';
            }elseif ($asc>=183162 && $asc<184193){
                return 'F';
            }elseif ($asc>=184193 && $asc<185254){
                return 'G';
            }elseif ($asc>=185254 && $asc<187247){
                return 'H';
            }elseif ($asc>=187247 && $asc<191166){
                return 'J';
            }elseif ($asc>=191166 && $asc<192172){
                return 'K';
            }elseif ($asc>=192172 && $asc<194232){
                return 'L';
            }elseif ($asc>=194232 && $asc<196195){
                return 'M';
            }elseif ($asc>=196195 && $asc<197182){
                return 'N';
            }elseif ($asc>=197182 && $asc<197190){
                return 'O';
            }elseif ($asc>=197190 && $asc<198218){
                return 'P';
            }elseif ($asc>=198218 && $asc<200187){
                return 'Q';
            }elseif ($asc>=200187 && $asc<200246){
                return 'R';
            }elseif ($asc>=200246 && $asc<203250){
                return 'S';
            }elseif ($asc>=203250 && $asc<205218){
                return 'T';
            }elseif ($asc>=205218 && $asc<206244){
                return 'W';
            }elseif ($asc>=206244 && $asc<209185){
                return 'X';
            }elseif ($asc>=209185 && $asc<212209){
                return 'Y';
            }elseif ($asc>=212209){
                return 'Z';
            }else{
                return '0';
            }
        }
    } 
   
}