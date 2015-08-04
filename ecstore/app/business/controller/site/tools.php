<?php

 
class business_ctl_site_tools extends site_controller{

    function __construct($app) {        
        $this->app = $app;
        $this->b2c = app::get('b2c');
        parent::__construct($this->b2c);
    }
    function get_subcat_list($cat_id){
        $objCat = &$this->b2c->model('goods_cat');
        $row = $objCat->dump($cat_id);
        
        $path_id = explode(',',$row['cat_path']);
        array_shift($path_id);
        array_pop($path_id);
        $path_id[] = $cat_id;
        $cat_path = array();
        if($path_id){
            $filter = array('cat_id'=>$path_id);
            $cat_path = $objCat->getList('*',$filter);
        }
        $list = $objCat->get_subcat_list($cat_id);
        foreach($list as $key=>&$val){
            if($val['child_count'] > 0){
                $val['isParent'] = 'isParent';
            }else{
                //unset($list['isParent']);
            }
        }


        $count = $objCat->get_subcat_count($cat_id);
        $this->pagedata['cats'] = json_encode($list);
        if(is_array($cat_path)){
            foreach($cat_path as $ck=>$cv){
                $catPath[] = $cv['cat_id'];
            }
        }
        echo json_encode($list);
       
    }

    function get_subcat($cat_id){
        //$cat_id=$_GET['cat_id'];
        if(empty($cat_id)){
             $cat_id = 0;
        }

        $objCat = app::get('b2c')->model('goods_cat');
        $row = $objCat->dump($cat_id);
        
        $path_id = explode(',',$row['cat_path']);
        array_shift($path_id);
        array_pop($path_id);
        $path_id[] = $cat_id;
        $cat_path = array();
        if($path_id){
            $filter = array('cat_id'=>$path_id);
            $cat_path = $objCat->getList('*',$filter);
        }
        $list = $objCat->get_subcat_list(0);
        foreach($list as $key=>&$val){
            if($val['child_count'] > 0){
                $val['isParent'] = 'isParent';
            }else{
                //unset($list['isParent']);
            }
        }
        $newCat = $objCat->get_new_cat(10);
        foreach($newCat as $nk=>&$nv){
            $nv['cat_path'] = substr($nv['cat_path'],1);
            $nv['cat_path'] = $nv['cat_path'].$nv['cat_id'];
        }

        $count = $objCat->get_subcat_count($cat_id);
        $list[]['cat_id'] = 0;
        $list[]['cat_name'] = '分类不限';
        $this->pagedata['cats'] = json_encode($list);
        if(is_array($cat_path)){
            foreach($cat_path as $ck=>$cv){
                $catPath[] = $cv['cat_id'];
            }
        }
        $this->pagedata['catPath'] = implode(',',$catPath);
        $this->pagedata['newCat'] = $newCat;
        $this->page('site/tools/cat_list.html', true, 'business');
        
    }
    function get_substorecat_list($cat_id){
        $objCat = app::get('business')->model('storecat');
        //$row = $objCat->getList('*',array('parent_id'=>$cat_id));
        $row=$objCat->dump($cat_id);
        $path_id = explode(',',$row['cat_path']);
        array_shift($path_id);
        array_pop($path_id);
        $path_id[] = $cat_id;
        $cat_path = array();
        if($path_id){
            $filter = array('cat_id'=>$path_id);
            $cat_path = $objCat->getList('*',$filter);
        }
        $list = $objCat->get_subcat_list($cat_id);
        foreach($list as $key=>&$val){
            if($val['child_count'] > 0){
                $val['isParent'] = 'isParent';
            }else{
                //unset($list['isParent']);
            }
        }


        $count = $objCat->get_subcat_count($cat_id);
        $this->pagedata['cats'] = json_encode($list);
        if(is_array($cat_path)){
            foreach($cat_path as $ck=>$cv){
                $catPath[] = $cv['cat_id'];
            }
        }
        echo json_encode($list);
       
    }
    function get_substorecat($cat_id){
        if(empty($cat_id)){
             $cat_id = 0;
        }

        $objCat = app::get('business')->model('storecat');
        $row=$objCat->dump($cat_id);
        //$row = $objCat->getList('*',array('parent_id'=>$cat_id));
        //print_r($row);
        $path_id = explode(',',$row['cat_path']);
        array_shift($path_id);
        array_pop($path_id);
        $path_id[] = $cat_id;
        $cat_path = array();
        if($path_id){
            $filter = array('cat_id'=>$path_id);
            $cat_path = $objCat->getList('*',$filter);
        }
        $list = $objCat->get_subcat_list(0);
        foreach($list as $key=>&$val){
            if($val['child_count'] > 0){
                $val['isParent'] = 'isParent';
            }else{
                //unset($list['isParent']);
            }
        }
        $newCat = $objCat->get_new_cat(10);
        foreach($newCat as $nk=>&$nv){
            $nv['cat_path'] = substr($nv['cat_path'],1);
            $nv['cat_path'] = $nv['cat_path'].$nv['cat_id'];
        }
        $count = $objCat->get_subcat_count($cat_id);
        $list[]=array('cat_id' => 0,'cat_name'=> '分类不限');
        
        $this->pagedata['cats'] = json_encode($list);
        if(is_array($cat_path)){
            foreach($cat_path as $ck=>$cv){
                $catPath[] = $cv['cat_id'];
            }
        }
        $this->pagedata['catPath'] = implode(',',$catPath);
        $this->pagedata['newCat'] = $newCat;

         
        $this->page('site/tools/store/cat_list.html', true, 'business');
        
    }
    function alertpages(){
        $goto=urldecode($_GET['goto']);
        
        if(strpos($goto,"?")===false){
            $goto=$goto.'?';
        }else{
            $goto=$goto.'&';
        }
        //echo $goto;exit;
        $this->pagedata['goto'] = $goto;
        $this->singlepage('site/tools/loadpage.html','business');
    }
    function singlepage($view, $app_id=''){

        $page = $this->fetch($view, $app_id);
        $this->pagedata['_PAGE_PAGEDATA_'] = $this->_vars;

        $re = '/<script([^>]*)>(.*?)<\/script>/is';
        $this->__scripts = '';
        $page = preg_replace_callback($re,array(&$this,'_singlepage_prepare'),$page)
            .'<script type="text/plain" id="__eval_scripts__" >'.$this->__scripts.'</script>';

        //后台singlepage页面增加自定义css引入到head标签内的操作--@lujy-start
        $recss = '/<link([^>]*)>/is';
        $this->__link_css = '';
        $page = preg_replace_callback($recss,array(&$this,'_singlepage_link_prepare'),$page);
        $this->pagedata['singleappcss'] = $this->__link_css;
        //--end

        $this->pagedata['statusId'] = $this->app->getConf('b2c.wss.enable');
        $this->pagedata['session_id'] = kernel::single('base_session')->sess_id();
        $this->pagedata['desktop_path'] = app::get('desktop')->res_url;
        $this->pagedata['shopadmin_dir'] = dirname($_SERVER['PHP_SELF']).'/';
        $this->pagedata['shop_base'] = $this->app->base_url();
        $this->pagedata['desktopresurl'] = app::get('desktop')->res_url;
        $this->pagedata['desktopresfullurl'] = app::get('desktop')->res_full_url;


        $this->pagedata['_PAGE_'] = &$page;
        $this->display('site/tools/singlepage.html','business');
    }
    function _singlepage_prepare($match){
        if($match[2] && !strpos($match[1],'src') && !strpos($match[1],'hold')){
            $this->__scripts.="\n".$match[2];
            return '';
        }else{
            return $match[0];
        }
    }

    //处理singlepage页面的css的preg_replace_callback的回调替换函数--@lujy-start
    function _singlepage_link_prepare($matches){
        $this->__link_css .= $matches[0];
        return '';
    }
    function get_store_id(){
        $member_info = kernel::single('b2c_frontpage')->get_current_member();
        $sto= kernel::single("business_memberstore",$member_info['member_id']);
        $sto->process($member_info['member_id']);
        $data = $sto->storeinfo;
        if($sto->isshoper == 'true'){
            $store_id = $data['store_id']?intval($data['store_id']):0;
        }elseif($sto->isshopmember == 'true'){
            $store_id = $data[0]['store_id']?intval($data[0]['store_id']):0;
        }else{
            $store_id = 0;
        }
        return $store_id;
    }
    /**
     * 图片浏览器
     * @param int 第几页的页面
     * @return string html内容
     */
    function image_broswer($page=1){

        $pagelimit = 10;
        $store_id=$this->get_store_id();
        
        $otag = app::get('desktop')->model('tag');
        $oimage = app::get('image')->model('image');
        $tags = $otag->getList('*',array('tag_type'=>'image','store_id'=>$store_id));
        
        $this->pagedata['type'] = $_GET['type'];
        $this->pagedata['tags'] = $tags;
        $this->display('site/tools/image_broswer.html','business');
    
    }
    function image_lib($tag='',$page=1){
        $pagelimit = 12;
        if($_GET['p']){
            $tag=$_GET['p'][0];
            $page=intval($_GET['p'][1]);
        }
        //$otag = $this->app->model('tag');
        $oimage =app::get('image')->model('image');

        //$tags = $otag->getList('*',array('tag_type'=>'image'));
        
        
        $store_id=$this->get_store_id();
        
        if($store_id==0||empty($store_id)){
           $this->pagedata['images']=array();
           $this->display('site/tools/image_lib.html','business');
           return;
        }
       
        $filter = array();
        if($tag){
            $filter = array('tag'=>array($tag));
        }
        $filter['store_id']=intval($store_id);
        $images = $oimage->getList('*',$filter,$pagelimit*($page-1),$pagelimit);
        $count = $oimage->count($filter);

        $limitwidth = 100;
        foreach($images as $key=>$row){
            $maxsize = max($row['width'],$row['height']);
            if($maxsize>$limitwidth){
                $size ='width=';
                $size.=$row['width']-$row['width']*(($maxsize-$limitwidth)/$maxsize);
                $size.=' height=';
                $size.=$row['height']-$row['height']*(($maxsize-$limitwidth)/$maxsize);
            }else{
                $size ='width='.$row['width'].' height='.$row['height'];
            }
            $row['size'] = $size;
            $images[$key] = $row;
        }
        $url=app::get('site')->router()->gen_url(array('app'=>'business','ctl'=>'site_tools','act'=>'image_lib'));
        $this->pagedata['images'] = $images;
        $ui = new base_component_ui($this->app);
        $this->pagedata['pagers'] = $ui->pager(array(
            'current'=>$page,
            'total'=>ceil($count/$pagelimit),
            'link'=>$url.'?p[0]='.$tag.'&p[1]=%d',
            ));
         $this->display('site/tools/image_lib.html','business');

     }
     /**
     * 图片上传的接口
     * @param null
     * @return string 上传的消息
     */
    function image_upload(){
       
        
        $store_id=$this->get_store_id();
       
       $mdl_img   = app::get('business')->model('image');
       $image_name = $_FILES['upload_item']['name'];
       $image_id  = $mdl_img->store($_FILES['upload_item']['tmp_name'],null,null,$image_name,false,$store_id);
       if(!$image_id) {
            header('Content-Type:text/html; charset=utf-8');
            echo "{error:'".app::get('image')->_('图片上传失败')."',splash:'true'}";
            exit;
       }
       //非商品图片不生成小中大图。
       //$mdl_img->rebuild($image_id,array('L','M','S'),true,$store_id);     
     
       if(isset($_REQUEST['type'])){
            $type=$_REQUEST['type'];
       }else{
            $type='s';
       }
           
       $image_src = base_storager::image_path($image_id,$type);
      
       $this->_set_tag($image_id,$store_id);
       if($callback = $_REQUEST['callbackfunc']){
            
             $_return = "<script>try{parent.$callback('$image_id','$image_src')}catch(e){}</script>";
       
       }
       
       $_return.="<script>parent.MessageBox.success('".app::get('image')->_('图片上传成功')."');</script>";

       echo $_return;
    
    }
    /**
     * 设置图片的tag-本类私有方法
     * @param null
     * @return null
     */
    function _set_tag($image_id,$store_id){
       $tagctl   = app::get('desktop')->model('tag');
       $tag_rel   = app::get('desktop')->model('tag_rel');
       $data['rel_id'] = $image_id;
       $tags = explode(' ',$_POST['tag']['name']);
       $data['tag_type'] = 'image';
       $data['app_id'] = 'image';
       $data['store_id']=$store_id;
       foreach($tags as $key=>$tag){
           if(!$tag) continue;
            $data['tag_name'] = $tag;
            $tagctl->save($data);
            if($data['tag_id']){
                $data2['tag']['tag_id'] = $data['tag_id'];
                $data2['rel_id'] = $image_id;
                $data2['tag_type'] = 'image';
                $data2['app_id'] = 'image';
                $tag_rel->save($data2);
                unset($data['tag_id']);
            }
       }
    }

    /**
     * 上传网络图片地址-本类私有方法
     * @param null
     * @return string html内容
     */
    function image_www_uploader(){
        if($_POST['upload_item']){
          
            
            $store_id=$this->get_store_id();
      
            $image = app::get('business')->model('image');
            $image_name = substr(strrchr($_POST['upload_item'],'/'),1);
            $image_id = $image->store($_POST['upload_item'],null,null,$image_name,false,$store_id);
            $image_src = base_storager::image_path($image_id);
            $this->_set_tag($image_id,$store_id);
            if($callback = $_REQUEST['callbackfunc']){
                
                 $_return = "<script>try{parent.$callback('$image_id','$image_src')}catch(e){}</script>";

            }

            $_return.="<script>parent.MessageBox.success('".app::get('image')->_('图片上传成功')."');</script>";

            echo $_return;
            echo <<<EOF
<div id="upload_remote_image"></div>
<script>
try{
    if($('upload_remote_image').getParent('.dialog'))
    $('upload_remote_image').getParent('.dialog').retrieve('instance').close();
}catch(e){}
</script>
EOF;
        }else{
            $html  ='<div class="division"><h5>'.app::get('image')->_('网络图片地址：').'</h5>';
            $ui = new base_component_ui($this);
            $html .= $ui->form_start(array('method'=>'post'));
            $html .= $ui->input(array(

                'type'=>'url',
                'name'=>'upload_item',
                'value'=>'http://',
                
                'style'=>'width:70%'
                ));
            $html .='</div>';
            $html .= $ui->form_end();
            echo $html."";

        }
    }
    function table(){
        echo '
            <div class="htmltableform" style=" background:none">
                 <table cellspacing="0" cellpadding="0" border="0">

                    <tr><th>'.app::get('desktop')->_('行数：').'</th>

                        <td>&nbsp;<input id="txtRows" type="text" maxlength="3" size="2" value="3" name="txtRows" /></td>

                  <th>'.app::get('desktop')->_('列数:').'</th>

                        <td>&nbsp;<input id="txtColumns" type="text" maxlength="2" size="2" value="2" name="txtColumns" /></td>
                    </tr>


                    <tr><th>'.app::get('desktop')->_('边框粗细:').'</th>

                        <td>&nbsp;<input id="txtBorder" type="text" maxlength="2" size="2" value="1" name="txtBorder" /></td>

                    <th>'.app::get('desktop')->_('对齐标题:').'</th>

                        <td><select id="selAlignment" name="selAlignment">
                          <option value="" selected="selected">'.app::get('desktop')->_('默认').'</option>
                          <option value="left">'.app::get('desktop')->_('左边').'</option>
                          <option value="center">'.app::get('desktop')->_('中间').'</option>
                          <option value="right">'.app::get('desktop')->_('右边').'</option>
                        </select></td>
                    </tr>


                <tr><th>'.app::get('desktop')->_('表格宽度:').'</th>
                    <td><input id="txtWidth" type="text" maxlength="4" size="3" value="200" name="txtWidth" />
                    <select id="selWidthType" name="selWidthType">

                      <option value="px" selected="selected">'.app::get('desktop')->_('像素').'</option>
                      <option value="%">'.app::get('desktop')->_('百分比').'</option>
                    </select></td>
                    <th>'.app::get('desktop')->_('表格宽度:').'</th>
                    <td><input id="txtHeight" type="text" maxlength="4" size="3" name="txtHeight" />'.app::get('desktop')->_('像素').'</td>
                </tr>



                <tr><th>'.app::get('desktop')->_('单元格边距').'</th>

                    <td>&nbsp;<input id="txtCellSpacing" type="text" maxlength="2" size="2" value="1" name="txtCellSpacing" /></td>

                <th>'.app::get('desktop')->_('单元格间距').'</th>

                    <td>&nbsp;<input id="txtCellPadding" type="text" maxlength="2" size="2" value="1" name="txtCellPadding" /></td>

        </tr></table>
        <div class="mainFoot"><div class="table-action">
            <button type="button" class="btn" id="mce_dlg_ok"><span><span>'.app::get('desktop')->_('确定').'</span></span></button>
            <button type="button" class="btn" isclosedialogbtn="true"><span><span>'.app::get('desktop')->_('取消').'</span></span></button>
        </div></div>
</div>
        <script>
           $("mce_dlg_ok").addEvent("click",function(){
                var ret = "<table "+($("selAlignment").value?("align=\""+$("selAlignment").value+"\" "):"")+"width=\""+$("txtWidth").value+$("selWidthType").value+"\" "+($("txtHeight").value?("height=\""+$("txtHeight").value+"\" "):"")+"border=\""+$("txtBorder").value+"\" cellspacing=\""+$("txtCellSpacing").value+"\" cellpadding=\""+$("txtCellPadding").value+"\">";
                var row="";
                for(var i=$("txtColumns").value.toInt();i>0;i--){
                row+="<td>&nbsp;</td>";
                }
                for(var i=$("txtRows").value.toInt();i>0;i--){
                ret+="<tr>"+row+"</tr>";
                }
                ret+="</table>";
              window.curEditor.exec.bind(window.curEditor)("insertHTML",ret);
            });
        </script>
';
    }

    function link(){

$html='
<div class="htmltableform"  id="dlg_lnk_base">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr><th width="100">'.app::get('desktop')->_('文本：').'</th>
          <td><textarea cols="50" rows="4" name="text" vtype="required">'.$_POST['text'].'</textarea> <span style="color:red;">*</span></td>
        </tr>
        <tr><th>'.app::get('desktop')->_('标题：').'</th>
          <td><input type="text" size="30" name="title" value="'.$_POST['title'].'" /><br />'.app::get('desktop')->_('当鼠标移至链接上时，会显示链接标题。').'</td>
        </tr>
        <tr><th>&nbsp;</th>';
$html.='<td><input type="checkbox" name="_blank" value="true"';
$html.($_POST['target']=='_blank')?' checked="checked" ':' ';
$html.='id="lnkInNewWindow"><label for="lnkInNewWindow">'.app::get('desktop')->_('在新窗口中打开链接').'</label></td>';
$html.='
        </tr>
        <tr><th>'.app::get('desktop')->_('链接到：').'</th>
        <td><input type="radio" name="type" value="url" id="lnkToUrl"';
$html.=(!$_POST[type] || $_POST[type]=='url')?' checked="checked" ':' ';
$html.='><label for="lnkToUrl">'.app::get('desktop')->_('链接').'</label>';
$html.='<input type="radio" name="type" value="email" id="lnkToMail"';
$html.=($_POST[type]=='email')?' checked="checked" ':' ';
$html.='><label for="lnkToMail">'.app::get('desktop')->_('电子邮件').'</label></td>
        </tr>
    </table>
    <div id="dolink">
        <table id="dolnkToUrl"';
$html.=($_POST[type] && $_POST[type]!='url')?' style="display:none" ':'';
$html.='>';
$html.='
        <tr><th >'.app::get('desktop')->_('链接地址:').'</th>

          <td><input type="text" size="30" name="url" value="';
$html.=(!$_POST[type] || $_POST[type]=='url')?$_POST[href]:' ';
$html.='" /></td>
        </tr>
    </table>
';
$html.='<table  id="dolnkToMail"';
$html.=($_POST[type]!='email')?' style="display:none" ':'';
$html.='
><tr><th>'.app::get('desktop')->_('邮件地址:').'</th>
                              <td><input type="text" size="30" name="email" value="{$_POST[email]}" /></td>
                            </tr>
                        </table>
                    </div></div>

                <div class="mainFoot">
                <div class="table-action">
                <button type="button" class="btn" id="mce_dlg_ok"><span><span>'.app::get('desktop')->_('确定').'</span></span></button>
                <button type="button" class="btn" isclosedialogbtn="true"><span><span>'.app::get('desktop')->_('取消').'</span></span></button>
                </div></div>
';
        $html.="
<script>
\$('lnkToUrl').addEvent('click',function(){
  \$ES('table','dolink').setStyle('display','none');
  \$('dolnkToUrl').setStyle('display','');
});
\$('lnkToMail').addEvent('click',function(){
\$ES('table','dolink').setStyle('display','none');
  \$('dolnkToMail').setStyle('display','');
});


\$('mce_dlg_ok').addEvent('click',function(){
  var setting = \$('dlg_lnk_base').getValues();
  var addon=[' ','type=\"'+setting.type+'\"',' '];
  switch(setting.type){
    case 'goods':
      setting.url = setting.goods;
      break;

    case 'page':
      setting.url = setting.page;
      break;

    case 'article':
      setting.url = setting.article;
      break;

    case 'email':
      setting.url = 'mailto:'+setting.email;
      break;

  }
   setting.url=decodeURI(setting.url);
  if(setting.title){
    addon.push('title=\"'+setting.title+'\"');
  }
  if(setting._blank){
    addon.push('target=\"_blank\"');
  }
  var link_uid='link'+(Native.UID++);

  var linkHtml = new String('<a href=\"{1}\" thref=\"{1}\"  {2} id=\"'+link_uid+'\">{0}</a>').format(setting.text,setting.url,addon.join(''));
  try{
    window.curEditor.exec.bind(window.curEditor)('insertHTML',linkHtml);
  }catch(e){}

    var  alink=window.curEditor.inc.win.document.getElementById(link_uid);

    if(alink){
         alink.href=alink.getAttribute('thref');
         alink.removeAttribute('thref');
         alink.removeAttribute('id');
         }
});

</script>

";
        echo $html;

    }
    function find(){
        $p=$_GET['p'];
        $type=$p[0];
        $keywords=$p[1];
        if(!$keywords){
            echo app::get('desktop')->_('请输入关键字。');
            return;
        }
        if($type=='goods'){
            $mod = &app::get('b2c')->model('goods');
            foreach($mod->getList('goods_id,name',array('name'=>$keywords)) as $k=>$r){
                $list[] = array(
                    'url'=>app::get('site')->router()->gen_url(array('app'=>'b2c','ctl'=>'site_product','full'=>1,'act'=>'index','arg'=>$r['goods_id']))
                    ,'label'=>$r['name']);
            }
            $this->pagedata['list'] = $list;
        }elseif($type=='article'){
            $mod = &app::get('content')->model('article_indexs');
            print_r($mod->getList('*'));
            foreach($mod->getList('article_id,title',array('title'=>$keywords)) as $k=>$r){
                var_dump($r);
                $list[] = array(
                    'url'=>app::get('site')->router()->gen_url(array('app'=>'content','ctl'=>'site_article','full'=>1,'act'=>'index','arg'=>$r['article_id'] ) ),
                    'label'=>$r['title']);
            }
            $this->pagedata['list'] = $list;
        }
        if(count($list)>0){
            $this->pagedata['type'] = $type;
            $this->display('site/tools/editor/dlg_result.html','business');
        }else{
            echo app::get('desktop')->_('没有符合条件"')."<b>".$keywords."<b>".app::get('desktop')->_('"的记录。');
        }
    }
    
    public function showRegionTreeList($serid,$multi=false,$textid=null,$hiddenid=null)
    {
        $serid = $_GET['serid'];
        $multi = $_GET['multi'];
         if ($serid)
         {
            $this->pagedata['sid'] = $serid;
         }
         else
         {
            $this->pagedata['sid'] = substr(time(),6,4);
            //$this->pagedata['sid'] = time();
         }
         if($textid && $hiddenid){
         	$this->pagedata['textid'] = $textid;
         	$this->pagedata['hiddenid'] = $hiddenid;
         }

         $this->pagedata['multi'] =  $multi;
         $this->singlepage('site/tools/regionSelect.html','business');
    }
    
    public function getRegionById($pregionid)
    {
        $pregionid = $_GET['p'];
        //$oDlyType = &$this->app->model('regions');
        $obj_regions_op = kernel::service('ectools_regions_apps', array('content_path'=>'ectools_regions_operation'));
        echo json_encode($obj_regions_op->getRegionById($pregionid));
    }
    function object_rows(){
        if($_POST['data']){
            if($_POST['app_id'])
                $app = app::get($_POST['app_id']);
            else
                $app = $this->b2c;
            $obj = $app->model($_POST['object']);
            $schema = $obj->get_schema();
            $textColumn = $_POST['textcol']?$_POST['textcol']:$schema['textColumn'];
            $textColumn = explode(',',$textColumn);
            $_textcol = $textColumn;
            $textColumn = $textColumn[0];

            $keycol = $_POST['key']?$_POST['key']:$schema['idColumn'];

            //统一做掉了。
            $all_filter = !empty($obj->__all_filter) ? $obj->__all_filter : array();
            $filter = !empty($_POST['filter']) ? $_POST['filter'] : $all_filter;
            $arr_filter = array();
            if( $_POST['data'][0]==='_ALL_' ) {
                if (isset($filter['advance'])&&$filter['advance']){
                    $arr_filters = explode(',',$filter['advance']);
                    foreach ($arr_filters as $obj_filter){
                        $arr = explode('=',$obj_filter);
                        $arr_filter[$arr[0]] = $arr[1];
                    }
                    unset($filter['advance']);
                }
                $arr_filter = array_merge($filter,$arr_filter); 
            }else{
                $arr_filter = array_merge($filter,array($keycol=>$_POST['data']));
            }

            $items = $obj->getList('*', $arr_filter);
            $name = $items[0][$textColumn];
            if($_POST['type']=='radio'){
                if(strpos($textColumn,'@')!==false){
                    list($field,$table,$app_) = explode('@',$textColumn);
                    if($app_){
                        $app = app::get($app_);
                    }
                    $mdl = $app->model($table);
                    $schema = $mdl->get_schema();
                    $row = $mdl->getList('*',array($schema['idColumn']=>$items[0][$keycol]));
                    $name = $row[0][$field];

                }
                echo json_encode(array('id'=>$items[0][$keycol],'name'=>$name));
                exit;
            }

            $this->pagedata['_input'] = array('items'=>$items,
                                                'idcol' => $schema['idColumn'],
                                                'keycol' => $keycol,
                                                'textcol' => $textColumn,
                                                '_textcol' => $_textcol,
                                                'name'=>$_POST['name']
                                                );
            $this->pagedata['_input']['view_app'] = 'desktop';
            $this->pagedata['_input']['view'] = $_POST['view'];
            if($_POST['view_app']){
                $this->pagedata['_input']['view_app'] =  $_POST['view_app'];
            }

            if(strpos($_POST['view'],':')!==false){
                list($view_app,$view) = explode(':',$_POST['view']);
                $this->pagedata['_input']['view_app'] = $view_app;
                $this->pagedata['_input']['view'] = $view;

            }
            $this->pagedata['domid'] = "list_datas_".$_POST['domid'];
            $this->pagedata['desktop_res_url'] = app::get('desktop')->res_url;
            $this->display('site/tools/input-row.html','business');
        }
    }
}