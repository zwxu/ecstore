<?php


class desktop_ctl_roles extends desktop_controller{

    var $workground = 'desktop_ctl_system';

    public function __construct($app)
    {
        parent::__construct($app);
        $this->obj_roles = kernel::single('desktop_roles');
        header("cache-control: no-store, no-cache, must-revalidate");
    }

    function index(){
        $this->finder('desktop_mdl_roles',array(
            'title'=>app::get('desktop')->_('角色'),
            'actions'=>array(
                            array('label'=>app::get('desktop')->_('新建角色'),'href'=>'index.php?ctl=roles&act=addnew','target'=>'dialog::{title:\''.app::get('desktop')->_('新建角色').'\'}'),
                        )
            ));
    }

    function addnew(){

        $workgrounds = app::get('desktop')->model('menus')->getList('*',array('menu_type'=>'workground','disabled'=>'false','display'=>'true'));
        $this->pagedata['workgrounds'] = $workgrounds;
        $widgets = app::get('desktop')->model('menus')->getList('*',array('menu_type'=>'widgets'));
        $this->pagedata['widgets'] = $widgets;
        
        //大类权限
        $obj_cat=app::get('b2c')->model('goods_cat');
        $cat=$obj_cat->getList('cat_id,cat_name',array('parent_id'=>'0','disabled'=>'false'));
        $this->pagedata['cat']=$cat;
        
        $account_id=$this->user->get_user_cat(true);
        //print_r($obj_cat->get_allsubcat($account_id));
        foreach($workgrounds as $k => $v)
        {
            $workgrounds[$k]['permissions'] = $this->obj_roles->get_permission_per($v['menu_id'],array());
        }

        $this->pagedata['workgrounds'] = $workgrounds;
        $this->pagedata['adminpanels'] = $this->obj_roles->get_adminpanel(null,array());
        //$this->pagedata['others'] = $this->obj_roles->get_others();

        //桌面挂件权限
        $html1 = '';
        foreach($this->pagedata['widgets'] as $key1=>$val1){
            if($val1['checked']){
                $html1 .= "<li style='padding-left:25px;text-align:left;float:left;'><input  class='leaf ' type='checkbox' checked='checked' name='workground[]' value=".$val1['addon'].">".$val1['menu_title']."</li>";
            }else{
                $html1 .= "<li style='padding-left:25px;text-align:left;float:left;'><input  class='leaf ' type='checkbox' name='workground[]' value=".$val1['addon'].">".$val1['menu_title']."</li>";
            }
        }
        $this->pagedata['menus1'] = "<ul><li><input class='parent' type=\"checkbox\">全选<ul>".$html1."</ul></li></ul>";

        //控制面板权限
        $html2 = '';
        foreach($this->pagedata['adminpanels'] as $key2=>$val2){
            if($val2['checked']){
                $html2 .= "<li style='padding-left:25px;text-align:left;float:left;'><input  class='leaf ' type='checkbox' checked='checked' name='workground[]' value=".$val2['permission'].">".$val2['menu_title']."</li>";
            }else{
                $html2 .= "<li style='padding-left:25px;text-align:left;float:left;'><input  class='leaf ' type='checkbox' name='workground[]' value=".$val2['permission'].">".$val2['menu_title']."</li>";
            }
        }
        $this->pagedata['menus2'] = "<ul><li><input class='parent' type=\"checkbox\">全选<ul>".$html2."</ul></li></ul>";

        //业务权限
        $treedata=array();
        foreach($this->pagedata['workgrounds'] as $key3=>$val3){
            $mgrpname['mgrpname'][] = $val3['menu_title'];
            $treedata[] = $this->getTree($val3['permissions'],'0');
        }
        foreach($treedata as $kmgrp=>$vmgrp){
            $treedata[$kmgrp][0]['mgrpname'] = $mgrpname['mgrpname'][$kmgrp];
        }
		
		//begin
		$cats = app::get('b2c')->model('goods_cat')->getList('*',array('parent_id'=>0));
		$cat_ids = array();
		foreach($cats as $v){
			$cat_ids[] = $v['cat_id'];
		}
		$types = app::get('site')->model('themes_tmpl')->getList('*');
		$list_type = kernel::single('site_theme_tmpl')->get_name();
		foreach($treedata as $tk=>$tv){
			foreach($tv as $k=>$v){
				if($v['menu_title']=='模板管理'){
					$item = $v;
					$item['menu_title'] = '模板页面管理';
					$item['permission'] = 'tpl';
					$treedata[$tk][10] = $item;
					foreach($types as $value){
						$item['menu_id'] = $value['id'];
						$item['menu_title'] = $list_type[$value['tmpl_type']]."_".$value['tmpl_name'];
						$item['permission'] = 'cat_'.$value['id'];
						if(in_array($value['id'],$cat_type)){
							$item['checked'] = '1';
						}else{
							$item['checked'] = 0;
						}
						$treedata[$tk][10]['parent'][] = $item;
					}
				}
			}
		}
		
        foreach($treedata as $item){
            $this->pagedata['menus3'][]= $this->procHTML($item);
        }

        /*其他权限
        #$vv3 = $this->getTree($this->pagedata['others'],'0');
        #$base_v3 = array('property'=>array('name'=>'其他', 'hasCheckbox'=>false), 'children'=>$vv3);
        */

        $this->page('users/add_roles.html');
    }

    function save()
    {
        $this->begin();
        $roles = $this->app->model('roles');
        if($roles->validate($_POST,$msg))
        {
            if($roles->save($_POST))
                $this->end(true,app::get('desktop')->_('保存成功'));
            else
                $this->end(false,app::get('desktop')->_('保存失败'));

        }
        else
        {
            $this->end(false,$msg);
        }
    }

    function getTree($data, $pId){
        $tree = '';
        foreach($data as $k => $v){
           if($v['parent'] == $pId){         //父亲找到儿子
               $v['parent'] = $this->getTree($data, $v['permission']);
               $tree[] = $v;
               //unset($data[$k]);
           }
        }
        return $tree;
    }

    function edit($roles_id){
        $param_id = $roles_id;
        $this->begin();
        if($_POST){
            if($_POST['role_name']==''){
                 $this->end(false,app::get('desktop')->_('工作组名称不能为空'));
            }
            if(!$_POST['workground']){
                //$_POST['workground'] = '';
                $this->end(false,app::get('desktop')->_('请至少选择一个权限'));
            }
            $opctl = &$this->app->model('roles');
            $result = $opctl->check_gname($_POST['role_name']);
            if($result && ($result!=$_POST['role_id'])) {$this->end(false,app::get('desktop')->_('该工作组名称已存在'));}
            if($opctl->save($_POST)){
                 $this->end(true,app::get('desktop')->_('保存成功'));
            }else{
               $this->end(false,app::get('desktop')->_('保存失败'));
            }

            }
        else{
            $opctl = &$this->app->model('roles');
            $menus = $this->app->model('menus');
            $sdf_roles = $opctl->dump($param_id);
            $this->pagedata['roles'] = $sdf_roles;
            $workground = unserialize($sdf_roles['workground']);
            $cat_arr=array();
            if($sdf_roles['cat'])
            $cat_arr = unserialize($sdf_roles['cat']);
			//begin 
			foreach($workground as $key=>$value){
				if(substr($value,0,4)=='cat_'){
					$cat_type[] = substr($value,4);
					unset($workground[$key]);
				}
			}
			//echo '<pre>';print_r($cat_type);exit;
			
            foreach((array)$workground as $v){
                #$sdf = $menus->dump($v);
                $menuname = $menus->getList('*',array('menu_type' =>'menu','permission' => $v));
                foreach($menuname as $val){
                    $menu_workground[] = $val['workground'];
                }
            }
            $menu_workground = array_unique((array)$menu_workground);
            $workgrounds = app::get('desktop')->model('menus')->getList('*',array('menu_type'=>'workground','disabled'=>'false','display'=>'true'));
            foreach($workgrounds as $k => $v){
                $workgrounds[$k]['permissions'] = $this->obj_roles->get_permission_per($v['menu_id'],$workground);
                if(in_array($v['workground'],(array)$menu_workground)){
                    $workgrounds[$k]['checked'] = 1;

                }
            }

            $widgets = app::get('desktop')->model('menus')->getList('*',array('menu_type'=>'widgets'));

            foreach($widgets as $key=>$widget){
                if(in_array($widget['addon'],$workground))
                    $widgets[$key]['checked'] = true;
            }

            $this->pagedata['widgets'] = $widgets;
            $this->pagedata['workgrounds'] = $workgrounds;
            $this->pagedata['adminpanels'] = $this->obj_roles->get_adminpanel($param_id,$workground);#print_r($workgrounds);exit;
            //$this->pagedata['others'] = $this->obj_roles->get_others($workground);

            //桌面挂件权限
            $html1 = '';
            foreach($this->pagedata['widgets'] as $key1=>$val1){
                if($val1['checked']){
                    $html1 .= "<li style='padding-left:25px;text-align:left;float:left;'><input  class='leaf ' type='checkbox' checked='checked' name='workground[]' value=".$val1['addon'].">".$val1['menu_title']."</li>";
                }else{
                    $html1 .= "<li style='padding-left:25px;text-align:left;float:left;'><input  class='leaf ' type='checkbox' name='workground[]' value=".$val1['addon'].">".$val1['menu_title']."</li>";
                }
            }
            $this->pagedata['menus1'] = "<ul><li><input class='parent' type=\"checkbox\">全选<ul>".$html1."</ul></li></ul>";

            //控制面板权限
            $html2 = '';
            foreach($this->pagedata['adminpanels'] as $key2=>$val2){
                if($val2['checked']){
                    $html2 .= "<li style='padding-left:25px;text-align:left;float:left;'><input  class='leaf ' type='checkbox' checked='checked' name='workground[]' value=".$val2['permission'].">".$val2['menu_title']."</li>";
                }else{
                    $html2 .= "<li style='padding-left:25px;text-align:left;float:left;'><input  class='leaf ' type='checkbox' name='workground[]' value=".$val2['permission'].">".$val2['menu_title']."</li>";
                }
            }
            $this->pagedata['menus2'] = "<ul><li><input class='parent' type=\"checkbox\">全选<ul>".$html2."</ul></li></ul>";
            
            //大类权限
            $cat=app::get('b2c')->model('goods_cat')->getList('cat_id,cat_name',array('parent_id'=>'0','disabled'=>'false'));
            foreach($cat as $key=>$c){
               if(in_array($c['cat_id'],$cat_arr))
                    $cat[$key]['checked'] = true;
            }
            $this->pagedata['cat']=$cat;
            
            //业务权限
            $treedata=array();
            foreach($this->pagedata['workgrounds'] as $key3=>$val3){//原始权限信息列表
                $mgrpname['mgrpname'][] = $val3['menu_title'];
                $treedata[] = $this->getTree($val3['permissions'],'0');
            }
            foreach($treedata as $kmgrp=>$vmgrp){//权限分组信息
                $treedata[$kmgrp][0]['mgrpname'] = $mgrpname['mgrpname'][$kmgrp];
            }
			//begin
			$cats = app::get('b2c')->model('goods_cat')->getList('*',array('parent_id'=>0));
			$cat_ids = array();
			foreach($cats as $v){
				$cat_ids[] = $v['cat_id'];
			}
			$types = app::get('site')->model('themes_tmpl')->getList('*');
			$list_type = kernel::single('site_theme_tmpl')->get_name();
		
			foreach($treedata as $tk=>$tv){
				foreach($tv as $k=>$v){
					if($v['menu_title']=='模板管理'){
						$item = $v;
						$item['menu_title'] = '模板页面管理';
						$item['permission'] = 'tpl';
						$treedata[$tk][10] = $item;
						foreach($types as $value){
							$item['menu_id'] = $value['id'];
							if($value['type']){
								$item['menu_title'] = '频道页'."_".$value['tmpl_name'];
							}else{
								$item['menu_title'] = $list_type[$value['tmpl_type']]."_".$value['tmpl_name'];
							}
							$item['permission'] = 'cat_'.$value['id'];
							if($cat_type&&in_array($value['id'],$cat_type)){
								$item['checked'] = '1';
							}else{
								$item['checked'] = 0;
							}
							$treedata[$tk][10]['parent'][] = $item;
						}
					}
				}
			}
			//echo '<pre>';print_r($treedata);exit;
            foreach($treedata as $item){//权限列表生成
                $this->pagedata['menus3'][]= $this->procHTML($item);
            }
            /*其他权限
            #$vv3 = $this->getTree($this->pagedata['others'],'0');
            #$base_v3 = array('property'=>array('name'=>'其他', 'hasCheckbox'=>false), 'children'=>$vv3);
            */

            $this->page('users/edit_roles.html');
            }
    }


    function procHTML($tree){
        $html = '';
        foreach($tree as $k=>$t){
            if($t['mgrpname']){
                $html .= "<li style='text-align:left;font-weight:bold;font-style:italic;'>".$t['mgrpname'];
            }
            if($t['parent'] == ''){
                if($t['checked']){
                $html .= "<li style='padding-left:25px;text-align:left;'><input  class='leaf'  type='checkbox' checked='checked' name='workground[]' value=".$t['permission'].">".$t['menu_title'];
                }else{
                $html .= "<li style='padding-left:25px;text-align:left;'><input   class='leaf' type='checkbox' name='workground[]' value=".$t['permission'].">{$t['menu_title']}</li>";
                }
            }else{
                if($t['checked']){
                $html .= "<li style='padding-left:25px;text-align:left;'><input  class='parent leaf'  type='checkbox' checked='checked' name='workground[]' value=".$t['permission'].">".$t['menu_title'];
                }else{
                $html .= "<li style='padding-left:25px;text-align:left;'><input  class='parent leaf'  type='checkbox' name='workground[]' value=".$t['permission'].">".$t['menu_title'];
                }
                $html .= $this->procHTML($t['parent']);
                $html = $html."</li>";
            }
        }
        //return $html ? "<ul>".$html."</ul>" : $html;
        return "<ul>".$html."</ul>";
    }

}
