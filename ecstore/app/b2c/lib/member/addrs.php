<?php


class b2c_member_addrs
{
    public function get_receive_addr(&$controller, $addr_id='', $member_id=0, $tpl='site/common/rec_addr.html')
    {
        if ($addr_id)
        {
            $arr_addr_id = explode(':', $addr_id);
            $addr_id = $arr_addr_id[0];
            $last_addr_id = $arr_addr_id[1];

            if ($addr_id != '-1')
            {
                $member_addrs = &$controller->app->model('member_addrs');
                $arr_member_addr = $member_addrs->dump(array('addr_id'=>$addr_id ? $addr_id : $last_addr_id,'member_id'=>$member_id), '*');
                if ($addr_id == '0' && $last_addr_id != '0')
                {
                    unset($arr_member_addr['addr_id']);
                }
            }
            else
            {
                if (!$member_id)
                {
                    $arr = unserialize(stripslashes($_COOKIE['purchase']['addon']));
                    $arr_member_addr['addr_id'] = 0;
                    $arr_member_addr['member_id'] = 0;
                    $arr_member_addr['name'] = $arr['member']['ship_name'];
                    $arr_member_addr['area'] = $arr['member']['ship_area'];
                    $arr_member_addr['addr'] = $arr['member']['ship_addr'];
                    $arr_member_addr['zipcode'] = $arr['member']['ship_zip'];
                    $arr_member_addr['email'] = $arr['member']['ship_email'];
                    $arr_member_addr['day'] = $arr['member']['day'];
                    $arr_member_addr['specal_day'] = $arr['member']['specal_day'];
                    $arr_member_addr['time'] = $arr['member']['time'];
                    $arr_member_addr['phone'] = array(
                        'mobile'=>$arr['member']['ship_mobile'],
                        'telephone'=>$arr['member']['ship_tel'],
                    );
                }
                else
                {
                    $obj_member = $controller->app->model('members');
                    $tmp = $obj_member->getList('addon', array('member_id' => $member_id));
                    $arr_member = $tmp[0];
                    $arr_addon = unserialize($arr_member['addon']);
                    $arr_member_addr = $arr_addon['def_addr'];
                    $arr_member_addr['zipcode'] = $arr_member_addr['zip'];
                    $arr_member_addr['phone'] = array(
                        'mobile' => $arr_member_addr['mobile'],
                        'telephone' => $arr_member_addr['tel'],
                    );
                }
                $controller->pagedata['has_last_def_addr'] = 1;
            }
        }

        $is_rec_addr_edit = 'true';
        $obj_recsave_checkbox = kernel::service('b2c.checkout_recsave_checkbox');
        if ($obj_recsave_checkbox)
        {
			if (method_exists($obj_recsave_checkbox,'check_edit'))
				$obj_recsave_checkbox->check_edit($is_rec_addr_edit);
        }
        $controller->pagedata['is_rec_addr_edit'] = $is_rec_addr_edit;

        $controller->pagedata['addr'] = $arr_member_addr;
        $controller->pagedata['address']['member_id'] = $member_id;
        $controller->pagedata['site_checkout_zipcode_required_open'] = $controller->app->getConf('site.checkout.zipcode.required.open');
        /**
         * 额外设置的地址checkbox是否显示
         */
        $is_recsave_display = 'true';
        $obj_recsave_checkbox = kernel::service('b2c.checkout_recsave_checkbox');
        if ($obj_recsave_checkbox)
        {
			if (method_exists($obj_recsave_checkbox,'check_display'))
				$obj_recsave_checkbox->check_display($is_recsave_display);
        }
        $controller->pagedata['is_recsave_display'] = $is_recsave_display;

		$str_html = $controller->fetch($tpl);
		$obj_ajax_view_help = kernel::single('b2c_view_ajax');
        return $obj_ajax_view_help->get_html($str_html, 'b2c_member_addrs','get_receive_addr');;
    }

	public function use_new_addr(&$controller, $addr_id='', $tpl='site/common/useNewAddr.html'){
		$obj_member_addrs = $controller->app->model('member_addrs');
		$default_addr = $obj_member_addrs->getList('*',array('addr_id'=>$addr_id));
		$this->pagedata['default_addr'] = $default_addr[0];
		//echo '<pre>';print_r($default_addr);exit;
		$str_html = $controller->fetch($tpl);
		$obj_ajax_view_help = kernel::single('b2c_view_ajax');
		return $obj_ajax_view_help->get_html($str_html, 'b2c_member_addrs','use_new_addr');
	}

    public function get_def_addr(&$controller, $arr_delivery=array(), $is_insert=false)
    {
        if ($arr_delivery)
        {
            $arr_def_addr = array(
                'addr_id'=> $arr_delivery['addr_id'] ? $arr_delivery['addr_id'] : 0,
                'addr_region'=> $arr_delivery['ship_area'],
                'addr'=> $arr_delivery['ship_addr_area'].$arr_delivery['ship_addr'],
                'zip' => $arr_delivery['ship_zip'] ? $arr_delivery['ship_zip'] : '',
                'name' => $arr_delivery['ship_name'],
                'mobile' => $arr_delivery['ship_mobile'],
                'tel' => $arr_delivery['ship_tel'] ? $arr_delivery['ship_tel'] : '',
                'day' => $arr_delivery['day'] ? $arr_delivery['day'] : '',
                'specal_day' => $arr_delivery['specal_day'] ? $arr_delivery['specal_day'] : '',
                'time' => $arr_delivery['time'] ? $arr_delivery['time'] : '',
            );

            $controller->pagedata['def_arr_addr'] = $arr_def_addr;
            if ($is_insert)
            {
                $arr_ship_area = explode(':', $arr_delivery['ship_area']);
                $str_ship_area = str_replace('/', '-', $arr_ship_area[1]);
                $controller->pagedata['def_radio'] = "<label><input type=\"radio\" class=\"receiver_radio_addr_id\" value=\"" . $arr_def_addr['addr_id'] . "\" name=\"delivery[addr_id]\"" . ((!$arr_delivery['addr_id'] && $arr_delivery['is_default']) ? " checked=\"checked\"" : "") . ((!$arr_delivery['is_default'] && $arr_delivery['addr_id']) ? " checked=\"checked\"" : "") . ">" . $str_ship_area . $arr_delivery['ship_addr'] . app::get('b2c')->_(" (收货人：") . $arr_delivery['ship_name'];

                if ($arr_delivery['ship_tel'])
                    $controller->pagedata['def_radio'] .= app::get('b2c')->_(" 电话：") . $arr_delivery['ship_tel'];
                if ($arr_delivery['ship_zip'])
                    $controller->pagedata['def_radio'] .= app::get('b2c')->_(" 邮编：") . $arr_delivery['ship_zip'];

                $controller->pagedata['def_radio'] .= ')</label>';
            }

            $controller->pagedata['site_checkout_receivermore_open'] = $controller->app->getConf('site.checkout.receivermore.open');
			$str_html = $controller->fetch('site/common/reciver_def_addr.html');
			$obj_ajax_view_help = kernel::single('b2c_view_ajax');
            return $obj_ajax_view_help->get_html($str_html, 'b2c_member_addrs','get_def_addr');;
        }

        return '';
    }

	public function purchase_save_addr(&$controller, $arr_post=array(),&$msg='')
	{
		$arr_delivery = $arr_post['delivery'];
		if ($arr_delivery)
        {
            $obj_member_addr = $controller->app->model('member_addrs');
            $address['addr_id'] = $arr_delivery['addr_id'] ? $arr_delivery['addr_id'] : '';
            $address['name'] = $arr_delivery['ship_name'];
            $at = explode(':',$arr_delivery['ship_area']);
            $area['area_type'] = $at[0];
            $area['sar'] = explode('/',$at[1]);
            $area['id'] = $at[2];
            $address['area'] = $area; unset($area,$at);
            $address['addr'] = $arr_delivery['ship_addr_area'] . $arr_delivery['ship_addr'];
            if($obj_member_addr->is_exists_addr($address,$arr_post['member_id'])){
			    $msg = app::get('b2c')->_('收货地址重复');
                return false;
		    }
            unset($address);

            if ($arr_post['member_id'])
            {
                // member default addr.
                if ($arr_delivery)
                {
                    if (!$arr_delivery['addr_id'])
                    {
                        $arr_member_addr = array(
                            'member_id' => $arr_post['member_id'],
                            'name' => $arr_delivery['ship_name'],
                            'area' => $arr_delivery['ship_area'],
                            'addr' => $arr_delivery['ship_addr_area'] . $arr_delivery['ship_addr'],
                            'zip' => $arr_delivery['ship_zip'] ? $arr_delivery['ship_zip'] : '',
                            'tel' => $arr_delivery['ship_tel'] ? $arr_delivery['ship_tel'] : '',
                            'mobile' => $arr_delivery['ship_mobile'],
                            'def_addr' => $arr_delivery['is_default'] ? 1 : 0,
                        );

                        if (!$arr_delivery['addr_id'])
                        {
                            $arr_delivery['addr_id'] = $obj_member_addr->set_default_addr($arr_member_addr, 0, $arr_post['member_id'], $msg);
                            if ($arr_delivery['addr_id'])
                                $is_insert = true;
							else
								return false;
                        }
                        elseif ($arr_delivery['addr_id'])
                        {
                            $arr_member_addr['addr_id'] = $arr_delivery['addr_id'];
                            if (!$obj_member_addr->set_default_addr($arr_member_addr, $arr_delivery['addr_id'], 0, $msg))
								return false;
                        }
                        $arr_member_addr['addr_id'] = $arr_delivery['addr_id'];
                    }
                    else
                    {
                        $tmp = $obj_member_addr->getList('*', array('addr_id' => $arr_delivery['addr_id']));
                        $arr_member_addr = $tmp[0];

                        if ($arr_delivery['ship_name'] && $arr_delivery['ship_area'] && $arr_delivery['ship_addr'] && $arr_delivery['ship_mobile'])
                        {
                            $arr_member_addr['name'] = $arr_delivery['ship_name'];
                            $arr_member_addr['area'] = $arr_delivery['ship_area'];
                            $arr_member_addr['addr'] = $arr_delivery['ship_addr_area'] . $arr_delivery['ship_addr'];
                            $arr_member_addr['zip'] = $arr_delivery['ship_zip'];
                            $arr_member_addr['tel'] = $arr_delivery['ship_tel'];
                            $arr_member_addr['mobile'] = $arr_delivery['ship_mobile'];

                            if (!$obj_member_addr->set_default_addr($arr_member_addr, $arr_delivery['addr_id'], 0, $msg))
								return false;
                        }
                        $arr_member_addr['addr_id'] = $addr_id = $arr_delivery['addr_id'];
                    }
                }
                $filter = array(
                    'member_id'=>$arr_post['member_id'],
                );
                $addr_lists = $obj_member_addr->getList('*', $filter);
                if ($is_insert)
                    $other_addr_checked = 1;
                else
                    $other_addr_checked = 0;
                foreach ($addr_lists as &$list)
                {
                    if(empty($list['tel']))
                    {
                        $str_tel = app::get('b2c')->_('手机：').$list['mobile'];
                    }
                    else
                    {
                        $str_tel = app::get('b2c')->_('电话：').$list['tel'];
                    }
                    if (!$other_addr_checked && $arr_member_addr['addr_id'] == $list['addr_id']) $list['def_addr'] = 1;
                    $list['addr_region'] = $list['area'];
                    $list['addr_label'] = $list['addr'].app::get('b2c')->_(' (收货人：').$list['name'].' '.$str_tel.app::get('b2c')->_(' 邮编：').$list['zip'].')';
                }
                $controller->pagedata['addrlist'] = $addr_lists;
                $controller->pagedata['other_addr_checked'] = $other_addr_checked ? 'true' : 'false';

                $str_html = $controller->fetch('site/cart/checkout_recaddr_list.html');
				$obj_ajax_view_help = kernel::single('b2c_view_ajax');
				return $obj_ajax_view_help->get_html($str_html, 'b2c_member_addrs','purchase_save_addr');
            }
        }
	}
}
?>

