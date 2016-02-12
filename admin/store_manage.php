<?php

/**
 * ECSHOP 仓库管理文件
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
$exc = new exchange($ecs->table('store_main'), $db, 'store_id', 'store_name');

/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}

/*------------------------------------------------------ */
//-- 列出所有主仓库
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    admin_priv('store_manage');

    /* 取得参数 */
	$keyword = empty($_REQUEST['keyword']) ? 0 : trim($_REQUEST['keyword']);
    $parent_id = empty($_REQUEST['pid']) ? 0 : intval($_REQUEST['pid']);
    $smarty->assign('parent_id',    $parent_id);

    /* 获取仓库列表 */
    $store_arr = store_list($keyword);
    $smarty->assign('store_arr',   $store_arr);

    /* 当前的地区名称 */
    if ($region_id > 0)
    {
        $area_name = $exc->get_name($region_id);
        $area = '[ '. $area_name . ' ] ';
        if ($region_arr)
        {
            $area .= $region_arr[0]['type'];
        }
    }
    else
    {
        $area = $_LANG['country'];
    }
    $smarty->assign('area_here',    $area);

    /* 返回上一级的链接 */
    if ($region_id > 0)
    {
        $parent_id = $exc->get_name($region_id, 'parent_id');
        $action_link = array('text' => $_LANG['back_page'], 'href' => 'area_manage.php?act=list&&pid=' . $parent_id);
    }
    else
    {
        $action_link = '';
    }
    $smarty->assign('action_link',  $action_link);

    /* 赋值模板显示 */
    $smarty->assign('ur_here',      $_LANG['01_store_manage']);
    $smarty->assign('full_page',    1);

    assign_query_info();
    $smarty->display('store_list.htm');
}

/* 区域管理 */
if ($_REQUEST['act'] == 'shipping_area' )
{
	admin_priv('store_manage');
    $store_id = $_REQUEST['id'] ?  intval($_REQUEST['id']) : 0;
	$store_name= $db->getOne("select store_name from ". $ecs->table('store_main') ." where store_id='$store_id' ");
	$smarty->assign('store_name',    $store_name);
	$smarty->assign('store_id',    $store_id);
	$smarty->assign('ur_here',      $_LANG['manage_area2'].' ['.  $store_name .']');
	$smarty->assign('action_link', array('href' => 'store_manage.php?act=list', 'text' => $_LANG['store_main_list']));
    $smarty->assign('full_page',    1);

	$area_arr = store_area_list($store_id);
    $smarty->assign('area_arr',   $area_arr);

	$smarty->assign('countries',        get_regions());

	/* 运费模板列表 */
	$sql="select * from ".$ecs->table('store_shipping_demo')."  where store_type_id=0 ORDER BY demo_id DESC";  
	$res = $db->query($sql);
	$demo_list = array();
    while ($rows = $db->fetchRow($res))
    {
		//$rows['demo_name'] = $rows['shipping_name'].($rows['fee_compute_mode']=='by_number' ? '按商品件数' : '按重量'). $rows['base_fee'].'元';
		//$rows['compute_mode_name'] =  $rows['fee_compute_mode']=='by_number' ? '按商品件数' : '按重量';
		//$rows['fee_desc'] = $rows['fee_compute_mode']=='by_number' ? ('单件商品费用：'.$rows['item_fee'].'元') : ('1000克以内费用：'.$rows['base_fee'].'元，续重每1000克或其零数的费用：'.$rows['step_fee'].'元');
		//$rows['fee_desc'] .='，免费额度：'.$rows['free_money'].'元';
		$rows['configure'] = unserialize($rows['configure']);
		$rows['fee_desc'] ='不足免费额度运费需支付'.$rows['configure']['shipping_fee'].'元';
		$rows['fee_desc'] .='，免费额度：'.$rows['configure']['free_money'].'元';
        $demo_list[] = $rows;
    }
	$smarty->assign('demo_list',   $demo_list);

	assign_query_info();
    $smarty->display('store_shipping_area.htm');
}
/*增加区域1*/
elseif($_REQUEST['act'] == 'add_shipping_area_new'){
	$array = array(1=>'province',2=>'city',3=>'district',4=>'xiangcun');
	
	$citys = $_POST['city'];
	$parent_id      = intval($_POST['parent_id']);
	$insert = array();
	$linshi = array();
	if(count($citys)<=0){
		sys_msg('请返回重新选择具体城市区域', 1);
	}
	//获取有五级区域的四级城市
	$five_city = is_have_five_city();
	foreach($citys as $key => $val){
		$where = '';
		unset($linshi);
		$arr = explode('.',$val);
		if(count($arr) == 4 && !empty($five_city)){
			//判断此四级下面是否有五级城市
			if(isset($five_city[$arr[3]])){
				continue;//不保存有五级区域的四级区域节点
			}
		}
		if(count($arr)>3){
			foreach($arr as $k=>$v){
				if($k>0){
					$where .= " and ".$array[$k]."=".$v;
					$linshi[$array[$k]] = $v;
				}
			}
			
			$linshi['store_id'] = $parent_id;
			$sql="select count(*) from ". $ecs->table('store_shipping_region') ." where store_id='$parent_id' ".$where." and store_type_id=0 ";
			if ($db->getOne($sql))
			{
				unset($linshi);
			}else{
				$insert[] = $linshi;
			}
		}
	}

	if($insert){
		$save = array();
		foreach($insert as $ki=>$vi){
			$db->autoExecute($ecs->table('store_shipping_region'), $vi, 'INSERT');
			unset($save);
		}
	}

	$link[0]['text'] = '返回配送区域';
    $link[0]['href'] = 'store_manage.php?act=shipping_area&id='.$parent_id;
    sys_msg('添加配送区域成功', 0, $link);
}
/* 增加区域 */
elseif ($_REQUEST['act'] == 'add_shipping_area')
{
    check_authz_json('store_manage');

    $parent_id      = intval($_POST['parent_id']);
    $province    = json_str_iconv(trim($_POST['province']));
	$city    = json_str_iconv(trim($_POST['city']));
	$district    = json_str_iconv(trim($_POST['district']));
	$xiangcun    = json_str_iconv(trim($_POST['xiangcun']));

    if (empty($province))
    {
        make_json_error($_LANG['province_empty']);
    }

    /* 查看是否重复 */
	$sql="select count(*) from ". $ecs->table('store_shipping_region') ." where store_id='$parent_id' and province='$province' and city='$city' and district='$district' and xiangcun='$xiangcun' and store_type_id=0 ";
    if ($db->getOne($sql))
    {
        make_json_error($_LANG['shipping_area_exist']);
    }	

    $sql = "INSERT INTO " . $ecs->table('store_shipping_region') . " (store_id, province, city, district, xiangcun) ".
           "VALUES ('$parent_id', '$province', '$city', '$district', '$xiangcun')";
    if ($GLOBALS['db']->query($sql, 'SILENT'))
    {
        /* 获取区域列表 */
        $area_arr = store_area_list($parent_id);
        $smarty->assign('area_arr',   $area_arr);
		$smarty->assign('store_id',   $parent_id);
        $smarty->assign('countries',        get_regions());

        make_json_result($smarty->fetch('store_shipping_area.htm'));
    }
    else
    {
        make_json_error($_LANG['add_area_error']);
    }
}

/* 删除配送区域 */
if ($_REQUEST['act'] == 'shipping_area_remove' )
{
		$store_id = $_REQUEST['store_id'] ?  intval($_REQUEST['store_id']) : 0;
		$rec_id = $_REQUEST['id'] ?  intval($_REQUEST['id']) : 0;

		$sql="delete from ". $ecs->table('store_shipping_region') ." where rec_id='$rec_id' ";
		$db->query($sql);
		$sql="delete from ". $ecs->table('store_shipping_fee') ." where shipping_region_id='$rec_id' ";
		$db->query($sql);

	   /* 清除缓存 */
		//clear_cache_files();

		$link[0]['text'] = '返回列表页';
		$link[0]['href'] = 'store_manage.php?act=shipping_area&id='.$store_id;

		sys_msg('删除成功', 0, $link);
}

/* 配送区域运费设置 */
if ($_REQUEST['act'] == 'shipping_area_fee' )
{
	admin_priv('store_manage');

	$shipping_region_id = $_REQUEST['id'] ?  intval($_REQUEST['id']) : 0;
	$sql="select r.store_id, r.province, r.city, r.district, r.xiangcun, s.store_name from ". $ecs->table('store_shipping_region') ." AS r".
				" left join ". $ecs->table('store_main') ." AS s on r.store_id=s.store_id".
				" where r.rec_id='$shipping_region_id' ";
	$store_row= $db->getRow($sql);
	$mudidi = get_region_name($store_row['province']).get_region_name($store_row['city']).get_region_name($store_row['district']).get_region_name($store_row['xiangcun']);
	$smarty->assign('store_name',    $store_row['store_name']);
	$smarty->assign('store_id',    $store_row['store_id']);
	$smarty->assign('shipping_region_id',    $shipping_region_id);
	$smarty->assign('mudidi',    $mudidi);


	$smarty->assign('ur_here',      '配送区域运费设置'.' ['.  $store_row['store_name'] .'--'. $mudidi .']');
	$smarty->assign('action_link', array('href' => 'store_manage.php?act=shipping_area&id='.$store_row['store_id'], 'text' => '返回上一级'));
	$smarty->assign('form_action',    'shipping_area_fee_insert');
    $smarty->assign('full_page',    1);


	$sql="select f.fee_id, f.shipping_id, f.configure, s.shipping_name from ". $ecs->table('store_shipping_fee') ." AS f ".
			 " left join ". $ecs->table('shipping') ." AS s on f.shipping_id=s.shipping_id ".
			"  where shipping_region_id = $shipping_region_id";
	$res = $db->query($sql);
	$fee_arr =array();
    while ($row = $db->fetchRow($res))
    {
        $fee_arr[] = $row;
    }
    $smarty->assign('fee_arr',   $fee_arr);

	/* 运费模板列表 */
	$sql="select * from ".$ecs->table('store_shipping_demo')." where store_type_id=0 ORDER BY demo_id DESC";  
	$res = $db->query($sql);
	$demo_list = array();
    while ($rows = $db->fetchRow($res))
    {
		//$rows['demo_name'] = $rows['shipping_name'].($rows['fee_compute_mode']=='by_number' ? '按商品件数' : '按重量'). $rows['base_fee'].'元';
		//$rows['compute_mode_name'] =  $rows['fee_compute_mode']=='by_number' ? '按商品件数' : '按重量';
		//$rows['fee_desc'] = $rows['fee_compute_mode']=='by_number' ? ('单件商品费用：'.$rows['item_fee'].'元') : ('1000克以内费用：'.$rows['base_fee'].'元，续重每1000克或其零数的费用：'.$rows['step_fee'].'元');
		//$rows['fee_desc'] .='，免费额度：'.$rows['free_money'].'元';
		$rows['configure'] = unserialize($rows['configure']);
		$rows['fee_desc'] ='不足免费额度运费需支付'.$rows['configure']['shipping_fee'].'元';
		$rows['fee_desc'] .='，免费额度：'.$rows['configure']['free_money'].'元';
        $demo_list[] = $rows;
    }
	$smarty->assign('demo_list',   $demo_list);
    
	/* 物流列表 */
	$sql="select shipping_id, shipping_code, shipping_name from ". $ecs->table('shipping') ." where enabled=1 ";
	$shipping_list=$db->getAll($sql);
	$smarty->assign('shipping_list', $shipping_list);

	//assign_query_info();
    $smarty->display('store_shipping_fee.htm');
}

/* 配送区域__保存运费 */
if ($_REQUEST['act'] == 'shipping_area_fee_insert' )
{
	  
	  $shipping_region_id = $_REQUEST['shipping_region_id'] ?  intval($_REQUEST['shipping_region_id']) : 0;
	  $shipping_id = $_REQUEST['shipping_id'] ?  intval($_REQUEST['shipping_id']) : 0;
	  //$fee_compute_mode = $_REQUEST['fee_compute_mode'] ?  trim($_REQUEST['fee_compute_mode']) : '';
	  $free_money = $_REQUEST['free_money'] ? floatval($_REQUEST['free_money']) : 0;
	$shipping_fee = $_REQUEST['shipping_fee'] ? floatval($_REQUEST['shipping_fee']) : 0;
	$configure =  serialize(array('shipping_fee'=>$shipping_fee,'free_money'=>$free_money));
	  $sql="select fee_id from ". $ecs->table('store_shipping_fee') ." where shipping_region_id='$shipping_region_id' AND shipping_id='$shipping_id'  ";
	  $not_only=$db->getOne($sql);
	  if ($not_only)
	  {
			sys_msg('对不起，该物流已经被添加过了！', 1);
	  }
	  $sql = "insert into ".$ecs->table('store_shipping_fee')." (shipping_region_id, shipping_id, configure )".
				" values('$shipping_region_id', '$shipping_id', '$configure')";
	  $db->query($sql);

	 /* 清除缓存 */
     clear_cache_files();

     $link[0]['text'] = '返回列表页';
     $link[0]['href'] = 'store_manage.php?act=shipping_area_fee&id='.$shipping_region_id;

     sys_msg('添加成功', 0, $link);
}

/* 区域运费编辑 */
if ($_REQUEST['act'] == 'shipping_area_fee_edit' )
{
		$fee_id = $_REQUEST['fee_id'] ?  intval($_REQUEST['fee_id']) : 0;
		$smarty->assign('fee_id', $fee_id);
		$sql="select * from ". $ecs->table('store_shipping_fee') ." where fee_id='$fee_id' ";
		$fee_info = $db->getRow($sql);

		$fee_info['configure'] = unserialize($fee_info['configure']);

		$shipping_region_id = $fee_info['shipping_region_id'];
		$sql="select r.store_id, r.province, r.city, r.district, r.xiangcun, s.store_name from ". $ecs->table('store_shipping_region') ." AS r".
				" left join ". $ecs->table('store_main') ." AS s on r.store_id=s.store_id".
				" where r.rec_id='$shipping_region_id' ";
		$store_row = $db->getRow($sql);
		$mudidi = get_region_name($store_row['province']).get_region_name($store_row['city']).get_region_name($store_row['district']).get_region_name($store_row['xiangcun']);
		$smarty->assign('store_name',    $store_row['store_name']);
		$smarty->assign('store_id',    $store_row['store_id']);
		$smarty->assign('shipping_region_id',    $shipping_region_id);
		$smarty->assign('mudidi',    $mudidi);


		$smarty->assign('ur_here',      '配送区域运费修改'.' ['.  $store_row['store_name'] .'--'. $mudidi .']');
		$smarty->assign('action_link', array('href' => 'store_manage.php?act=shipping_area_fee&id='.$shipping_region_id, 'text' => '返回上一级'));
		$smarty->assign('form_action',    'shipping_area_fee_update');

		$smarty->assign('fee_info',    $fee_info);

		/* 物流列表 */
		$sql="select shipping_id, shipping_code, shipping_name from ". $ecs->table('shipping') ." where enabled=1 ";
		$shipping_list=$db->getAll($sql);
		$smarty->assign('shipping_list', $shipping_list);

		assign_query_info();
       $smarty->display('store_shipping_fee.htm');
}

/* 配送区域__运费_通过模板批量导入 */
if ($_REQUEST['act'] == 'shipping_area_fee_batch' )
{
	$shipping_region_id = $_REQUEST['shipping_region_id'] ?  intval($_REQUEST['shipping_region_id']) : 0;
	$demo_list = !empty($_POST['checkboxes']) ? implode(',', $_POST['checkboxes']) : 0;
	if ($demo_list && $shipping_region_id)
	{
		$sql = "select * from ". $ecs->table('store_shipping_demo') . " where demo_id in ($demo_list) ";
		$res = $db->query($sql);
		while ($row=$db->fetchRow($res))
        {
			$sql="select fee_id from ". $ecs->table('store_shipping_fee') ." where shipping_region_id='$shipping_region_id' AND shipping_id='$row[shipping_id]'  ";
			$not_only=$db->getOne($sql);
			if (!$not_only)
			{
					$sql = "insert into ".$ecs->table('store_shipping_fee')." (shipping_region_id, shipping_id, configure )".
								" values('$shipping_region_id', '$row[shipping_id]', '$row[configure]')";
					$db->query($sql);
			}			
		}
	}

	/* 清除缓存 */
     //clear_cache_files();

     $link[0]['text'] = '返回列表页';
     $link[0]['href'] = 'store_manage.php?act=shipping_area_fee&id='.$shipping_region_id;

     sys_msg('批量添加成功', 0, $link);
}

/* 配送区域__运费_通过模板批量导入 */
if ($_REQUEST['act'] == 'shipping_area_fee_batch_new' )
{
	$shipping_region_id = $_REQUEST['shipping_region_id'] ?  trim($_REQUEST['shipping_region_id'],',') : 0;
	$demo_list = !empty($_POST['checkboxes']) ? implode(',', $_POST['checkboxes']) : 0;
	if ($demo_list && $shipping_region_id)
	{
		$shipping = explode(',',$shipping_region_id);
		if(count($shipping)<=0){
			sys_msg('请先选择运费模板的区域！', 1);
		}
		$sql = "select * from ". $ecs->table('store_shipping_demo') . " where demo_id in ($demo_list) ";
		$res = $db->query($sql);
		while ($row=$db->fetchRow($res))
        {
			foreach($shipping as $k=>$v){
				$sql="select fee_id from ". $ecs->table('store_shipping_fee') ." where shipping_region_id='$v' AND shipping_id='$row[shipping_id]'  ";
				$not_only=$db->getOne($sql);
				if (!$not_only)
				{
						$sql = "insert into ".$ecs->table('store_shipping_fee')." (shipping_region_id, shipping_id, configure )".
									" values('$v', '$row[shipping_id]', '$row[configure]')";
						$db->query($sql);
				}
			}			
		}
	}

	/* 清除缓存 */
     //clear_cache_files();


     sys_msg('批量设置成功', 0);
}

/* 配送区域__运费更新 */
if ($_REQUEST['act'] == 'shipping_area_fee_update' )
{	  
	  $fee_id = $_REQUEST['fee_id'] ?  intval($_REQUEST['fee_id']) : 0;
	  $shipping_region_id = $_REQUEST['shipping_region_id'] ?  intval($_REQUEST['shipping_region_id']) : 0;
	  $shipping_id = $_REQUEST['shipping_id'] ?  intval($_REQUEST['shipping_id']) : 0;
	  $free_money = $_REQUEST['free_money'] ? floatval($_REQUEST['free_money']) : 0;
	  $shipping_fee = $_REQUEST['shipping_fee'] ? floatval($_REQUEST['shipping_fee']) : 0;
	  $configure =  serialize(array('shipping_fee'=>$shipping_fee,'free_money'=>$free_money));

	  $sql="select fee_id from ". $ecs->table('store_shipping_fee') ." where shipping_region_id='$shipping_region_id' AND shipping_id='$shipping_id' AND fee_id !='$fee_id' ";
	  $not_only=$db->getOne($sql);
	  if ($not_only)
	  {
			sys_msg('对不起，您修改后的物流跟原来的发生重复！', 1);
	  }
	  $sql = "update ". $ecs->table('store_shipping_fee') ." set shipping_id='$shipping_id',  configure='$configure' ".
				" where fee_id='$fee_id' ";
	  $db->query($sql);

	 /* 清除缓存 */
     clear_cache_files();

     $link[0]['text'] = '返回列表页';
     $link[0]['href'] = 'store_manage.php?act=shipping_area_fee&id='.$shipping_region_id;

     sys_msg('更新成功', 0, $link);
}
/* 配送区域__运费删除 */
if ($_REQUEST['act'] == 'shipping_area_fee_remove' )
{  
	$shipping_region_id = $_REQUEST['sid'] ?  intval($_REQUEST['sid']) : 0;
	$fee_id = $_REQUEST['fee_id'] ?  intval($_REQUEST['fee_id']) : 0;
	$sql="delete from ". $ecs->table('store_shipping_fee') ." where fee_id='$fee_id' ";
	$db->query($sql);

	   /* 清除缓存 */
     clear_cache_files();

     $link[0]['text'] = '返回列表页';
     $link[0]['href'] = 'store_manage.php?act=shipping_area_fee&id='.$shipping_region_id;

     sys_msg('删除成功', 0, $link);
}

/* 删除主仓库 */
if ($_REQUEST['act'] == 'store_remove' )
{
	$store_id = $_REQUEST['id'] ?  intval($_REQUEST['id']) : 0;
	
	$num = $db->getOne("select count(store_id) from ". $ecs->table('store_main') ." where parent_id=".$store_id);
	if($num>0){
		sys_msg('请先删除下面的实体仓库!', 1);
	}
	$sql="delete from ". $ecs->table('store_main') ." where store_id='$store_id' ";
	$db->query($sql);
	//这里增加删除仓库的代码

	   /* 清除缓存 */
     clear_cache_files();

     $link[0]['text'] = '返回列表页';
     $link[0]['href'] = 'store_manage.php?act=list';

     sys_msg('删除成功', 0, $link);
}

/* 主仓库分配主管 */
if ($_REQUEST['act'] == 'store_set_adminer' )
{
	admin_priv('store_manage');

    $store_id = $_REQUEST['id'] ?  intval($_REQUEST['id']) : 0;
	$store_name= $db->getOne("select store_name from ". $ecs->table('store_main') ." where store_id='$store_id' ");
	$smarty->assign('ur_here',     $_LANG['store_set_adminer']." - [$store_name]");
	$smarty->assign('action_link', array('href' => 'store_manage.php?act=list', 'text' => $_LANG['store_main_list']));

	$sql="select * from ".$ecs->table('store_adminer')." where store_id='$store_id' ";
	$res_adminer = $db->query($sql);
	$list_adminer=array();
	while ($row_adminer = $db->fetchRow($res_adminer))
	{
		$row_adminer['checked']='checked';
		$list_adminer[$row_adminer['admin_id']]=$row_adminer;
	}
	$sql="select user_id, user_name from ".$ecs->table('admin_user')." order by  user_id asc";
	$res_admin = $db->query($sql);
	$admin_list =array();
	while ($row_admin = $db->fetchRow($res_admin))
	{
		$row_admin['checked'] = $list_adminer[$row_admin['user_id']]['checked'];
		$admin_list[$row_admin['user_id']] = $row_admin;
	}
	$smarty->assign('admin_list', $admin_list);
	$smarty->assign('list_adminer', $list_adminer);

    $smarty->assign('store_id', $store_id);
	$smarty->assign('form_action', 'store_set_adminer_save');

	assign_query_info();
	$smarty->display('store_set_adminer.htm');

}

/* 主仓库分配主管_保存 */
if ($_REQUEST['act'] == 'store_set_adminer_save' )
{
	admin_priv('store_manage');
    $store_id = $_REQUEST['store_id'] ? intval($_REQUEST['store_id']) : 0;
    $sql = "delete from ". $ecs->table('store_adminer') ." where store_id='$store_id' ";
	$db->query($sql);
	$admin_list = $_REQUEST['admin_id'];
	if (is_array($admin_list))
	{
		foreach ($admin_list AS $admin_item)
		{
			$mobile = $_REQUEST['mobile_'.$admin_item];
			$tel = $_REQUEST['tel_'.$admin_item];
			$admin_name =trim($_REQUEST['adminname_'.$admin_item]);
			$sql = "INSERT INTO " . $ecs->table('store_adminer') . " (store_id, admin_id, admin_name, mobile, tel) ".
           "VALUES ('$store_id', '$admin_item', '$admin_name', '$mobile', '$tel')";
			$db->query($sql);
		}
	}

	/* 清除缓存 */
    clear_cache_files();

    $link[0]['text'] = $_LANG['store_main_list'];
    $link[0]['href'] = 'store_manage.php?act=list';

    sys_msg($_LANG['store_set_adminer_succed'], 0, $link);
}
//设置区域信息
if ($_REQUEST['act'] == 'store_set_info')
{
	admin_priv('store_manage');

	$id = $_REQUEST['id'] ?  intval($_REQUEST['id']) : 0;
	$info= $db->getRow("select * from ". $ecs->table('store_main') ." where store_id='$id' ");

	$smarty->assign('ur_here',     $_LANG['set_store']." - [".$info['store_name']."]");
	$smarty->assign('action_link', array('href' => 'store_manage.php?act=list', 'text' => $_LANG['store_main_list']));
	$smarty->assign('store', $info);
	$smarty->assign('form_action', 'save_set_info');

	$smarty->assign('provinces', get_regions(1, '1'));
	if($info['province'])
	{
		$smarty->assign('cities', get_regions(2, $info['province']));
	}
	if($info['city'])
	{
		$smarty->assign('district', get_regions(3, $info['city']));
	}

	assign_query_info();
	$smarty->display('store_set_info.htm');
}
//保存区域信息
if ($_REQUEST['act'] == 'save_set_info' )
{
	admin_priv('store_manage');
	$id = $_REQUEST['store_id'] ? intval($_REQUEST['store_id']) : 0 ;
	$latlog= $_REQUEST['latlog'] ? trim($_REQUEST['latlog']) : '' ;
	$saveinfo = array(
		'province' => intval($_REQUEST['province']),
		'city' => intval($_REQUEST['city']),
		'district' => intval($_REQUEST['district']),
		'latlog'   => $latlog
		);

	$db->autoExecute($ecs->table('store_main'), $saveinfo, 'UPDATE', "store_id = '" . $id . "'");

	/* 清除缓存 */
    clear_cache_files();

    sys_msg($_LANG['save_store_succed']);
}

/* 设置仓库佣金 */
if ($_REQUEST['act'] == 'store_set_rebate' )
{
	admin_priv('store_manage');

    $store_id = $_REQUEST['id'] ?  intval($_REQUEST['id']) : 0;
	$store_name= $db->getOne("select store_name from ". $ecs->table('store_main') ." where store_id='$store_id' ");
	$smarty->assign('ur_here',     $_LANG['store_set_rebate']." - [$store_name]");
	$smarty->assign('action_link', array('href' => 'store_manage.php?act=list', 'text' => $_LANG['store_main_list']));

	$info = $db->getRow('select * from '.$ecs->table('store_main_rebate').' where store_id='.$store_id);
	if(empty($info)){
		$smarty->assign('doact', 'insert');
	}else{
		$province_list = get_regions(1, $info['country']);
		$city_list     = get_regions(2, $info['province']);
		$district_list = get_regions(3, $info['city']);
		
		$smarty->assign('province_list',    $province_list);
		$smarty->assign('city_list',        $city_list);
		$smarty->assign('district_list',    $district_list);
		$smarty->assign('info',$info);
		$smarty->assign('doact', 'update');
	}

	$paytime = array(1=>'周',2=>'月',3=>'季度',4=>'年');

	
	$smarty->assign('country_list',       get_regions());
	
	$smarty->assign('paytime', $paytime);
	$smarty->assign('store_id', $store_id);
	$smarty->assign('form_action', 'store_set_rebate_save');
	assign_query_info();
	$smarty->display('store_set_rebate.htm');

}

/* 查看仓库佣金 */
if ($_REQUEST['act'] == 'store_view_rebate' )
{
	admin_priv('store_manage');
	$store_id = $_REQUEST['id'] ?  intval($_REQUEST['id']) : 0;
	$store_name= $db->getOne("select store_name from ". $ecs->table('store_main') ." where store_id='$store_id' ");
	$smarty->assign('ur_here',     $_LANG['store_view_rebate']." - [$store_name]");

	$smarty->assign('full_page', 1);
	assign_query_info();
	$smarty->display('store_log_list.htm');
}

/* 主仓库分配主管_保存 */
if ($_REQUEST['act'] == 'store_set_rebate_save' )
{
	admin_priv('store_manage');
    $store_id = $_REQUEST['store_id'] ? intval($_REQUEST['store_id']) : 0;
	$store_rebate_id = intval($_POST['store_rebate_id']);
	$rebate = intval($_POST['rebate']);
	if(empty($rebate)){
		sys_msg($_LANG['store_set_rebate_error_rebate']);
	}
	$store_rebate_paytime = intval($_POST['store_rebate_paytime']);
	if(empty($store_rebate_paytime)){
		sys_msg($_LANG['store_set_rebate_error_store_rebate_paytime']);
	}
	$company = filter_var_value($_POST['company']);
	if(empty($company)){
		sys_msg($_LANG['store_set_rebate_error_company']);
	}
	$country = intval($_POST['country']);
	$province = intval($_POST['province']);
	$city = intval($_POST['city']);
	$district = intval($_POST['district']);
	if(empty($country) || empty($province) || empty($city) || empty($district)){
		sys_msg($_LANG['store_set_rebate_error_adress1']);
	}
	$address = filter_var_value($_POST['address']);
	if(empty($address)){
		sys_msg($_LANG['store_set_rebate_error_adress2']);
	}
	$mobile = filter_var_value($_POST['mobile']);
	if(empty($mobile)){
		sys_msg($_LANG['store_set_rebate_error_mobile']);
	}
	$bank_name = filter_var_value($_POST['bank_name']);
	$bank_number = filter_var_value($_POST['bank_number']);
	$alipay_number = filter_var_value($_POST['alipay_number']);

	$data = array(
		'store_id'	=>	$store_id,
		'rebate'	=>	$rebate,
		'store_rebate_paytime'	=>	$store_rebate_paytime,
		'company'	=>	$company,
		'country'	=>	$country,
		'province'	=>	$province,
		'city'		=>	$city,
		'district'	=>	$district,
		'address'	=>	$address,
		'mobile'	=>	$mobile,
		'bank_name'	=>	$bank_name,
		'bank_number'	=>	$bank_number,
		'alipay_number'	=>	$alipay_number
	);

	$info = $db->getRow('select * from '.$ecs->table('store_main_rebate').' where store_id='.$store_id);
	if(empty($info)){
		$db->autoExecute($ecs->table('store_main_rebate'), $data, 'INSERT');
	}else{
		if($info['store_rebate_id'] != $store_rebate_id){
			sys_msg($_LANG['all_error']);
		}
		$db->autoExecute($ecs->table('store_main_rebate'), $data, 'UPDATE', 'store_rebate_id='.$info['store_rebate_id']);
	}
    

	/* 清除缓存 */
    clear_cache_files();

    $link[0]['text'] = $_LANG['store_main_list'];
    $link[0]['href'] = 'store_manage.php?act=list';

    sys_msg($_LANG['store_set_rebate_succed'], 0, $link);
}



/*  仓储管理 */
if ($_REQUEST['act'] == 'list_sub')
{
	admin_priv('store_manage');
	$parent_id = $_REQUEST['pid'] ?  intval($_REQUEST['pid']) : 0;
	$parent_name= $db->getOne("select store_name from ". $ecs->table('store_main') ." where store_id='$parent_id' ");
	$smarty->assign('ur_here',     $_LANG['list_sub']." - [$parent_name]");
	$smarty->assign('full_page',    1);

	$sql="select * from ".$ecs->table('store_main')." where parent_id='$parent_id' order by store_id desc ";
    $store_res =$db->query($sql);
	$store_list =array();
	while ($store_row = $db->fetchRow($store_res))
	{
		$store_row['province_name'] = $db->getOne("select region_name from ". $ecs->table('region') ." where region_id= '$store_row[province]' ");
		$store_row['city_name'] = $db->getOne("select region_name from ". $ecs->table('region') ." where region_id= '$store_row[city]' ");
		$store_row['district_name'] = $db->getOne("select region_name from ". $ecs->table('region') ." where region_id= '$store_row[district]' ");
		$store_row['adminer'] = get_store_admin_info($store_row['store_id']);
		$store_list[]=$store_row;
	}
	$smarty->assign('store_list',    $store_list);

    $smarty->assign('action_link', array('href' => 'store_manage.php?act=add_sub&pid='.$parent_id, 'text' => $_LANG['add_sub']));
    $smarty->assign('action_link2',array('href' => 'store_manage.php?act=list', 'text' => $_LANG['back_parent']));

	 assign_query_info();
	$smarty->display('store_sub_list.htm');
}

/* 添加仓储 */
if ($_REQUEST['act'] == 'add_sub' )
{
	admin_priv('store_manage');
	$parent_id = $_REQUEST['pid'] ?  intval($_REQUEST['pid']) : 0;
	$parent_name= $db->getOne("select store_name from ". $ecs->table('store_main') ." where store_id='$parent_id' ");

	$smarty->assign('ur_here',     $_LANG['add_sub']." - [$parent_name]");
	$smarty->assign('action_link', array('href' => 'store_manage.php?act=list_sub&pid='.$parent_id, 'text' => $_LANG['list_sub']));
	
	$smarty->assign('provinces', get_regions(1, '1'));

	$sql="select user_id,user_name from ".$ecs->table('admin_user')." order by  user_id asc";
	$admin_list = $db->getAll($sql);
	$smarty->assign('admin_list', $admin_list);

	$smarty->assign('form_action', 'save_sub');
	$smarty->assign('store', array('parent_id'=>$parent_id));

	assign_query_info();
	$smarty->display('store_sub_info.htm');
}

/* 保存仓储 */
if ($_REQUEST['act'] == 'save_sub' )
{
	admin_priv('store_manage');
	$parent_id = $_REQUEST['parent_id'] ? intval($_REQUEST['parent_id']) : 0 ;
	$store_name= $_REQUEST['store_name'] ? trim($_REQUEST['store_name']) : '' ;

	$is_only = $exc->is_only('store_name', $store_name, 0, "parent_id = '$parent_id'");
    if (!$is_only)
    {
        sys_msg(sprintf($_LANG['subname_exist'], stripslashes($_REQUEST['store_name'])), 1);
    }

	$sql = "INSERT INTO " . $ecs->table('store_main') . " (parent_id, store_name, province, city, district, mianji) ".
           "VALUES ('$parent_id', '$store_name', '$_REQUEST[province]', '$_REQUEST[city]', '$_REQUEST[district]',  '$_REQUEST[mianji]')";
	$db->query($sql);
	$store_id = $db->insert_id();
	$admin_list = $_REQUEST['admin_id'];
	if (is_array($admin_list))
	{
		foreach ($admin_list AS $admin_item)
		{
			$mobile = $_REQUEST['mobile_'.$admin_item];
			$tel = $_REQUEST['tel_'.$admin_item];
			$admin_name =trim($_REQUEST['adminname_'.$admin_item]);
			$sql = "INSERT INTO " . $ecs->table('store_adminer') . " (store_id, admin_id, admin_name, mobile, tel) ".
           "VALUES ('$store_id', '$admin_item', '$admin_name', '$mobile', '$tel')";
			$db->query($sql);
		}
	}

	/* 清除缓存 */
    clear_cache_files();

    $link[0]['text'] = $_LANG['continue_add'];
    $link[0]['href'] = 'store_manage.php?act=add_sub&pid='.$parent_id;

    $link[1]['text'] = $_LANG['list_sub'];
    $link[1]['href'] = 'store_manage.php?act=list_sub&pid='.$parent_id;

    sys_msg($_LANG['add_sub_succed'], 0, $link);
}

/* 编辑仓储  */
if ($_REQUEST['act'] == 'edit_sub' )
{
	admin_priv('store_manage');

	$smarty->assign('ur_here',     $_LANG['edit_sub']);	

	$store_id = intval($_REQUEST['id']);
	$sql="select * from ". $ecs->table('store_main') ." where store_id='$store_id' ";
	$store = $db->getRow($sql);
	$smarty->assign('store', $store);
	
	$smarty->assign('provinces', get_regions(1, '1'));
	if($store['province'])
	{
		$smarty->assign('cities', get_regions(2, $store['province']));
	}
	if($store['city'])
	{
		$smarty->assign('district', get_regions(3, $store['city']));
	}
    
	$sql="select * from ".$ecs->table('store_adminer')." where store_id='$store_id' ";
	$res_adminer = $db->query($sql);
	$list_adminer=array();
	while ($row_adminer = $db->fetchRow($res_adminer))
	{
		$row_adminer['checked']='checked';
		$list_adminer[$row_adminer['admin_id']]=$row_adminer;
	}
	$sql="select user_id, user_name from ".$ecs->table('admin_user')." order by  user_id asc";
	$res_admin = $db->query($sql);
	$admin_list =array();
	while ($row_admin = $db->fetchRow($res_admin))
	{
		$row_admin['checked'] = $list_adminer[$row_admin['user_id']]['checked'];
		$admin_list[$row_admin['user_id']] = $row_admin;
	}
	$smarty->assign('admin_list', $admin_list);
	$smarty->assign('list_adminer', $list_adminer);

	$smarty->assign('form_action', 'update_sub');
	$smarty->assign('action_link', array('href' => 'store_manage.php?act=list_sub&pid='.$store['parent_id'], 'text' => $_LANG['list_sub']));
	

	assign_query_info();
	$smarty->display('store_sub_info.htm');
}

/* 更新仓储  */
if ($_REQUEST['act'] == 'update_sub' )
{
	admin_priv('store_manage');
	$store_id = $_REQUEST['store_id'] ? intval($_REQUEST['store_id']) : 0 ;
	$parent_id = $_REQUEST['parent_id'] ? intval($_REQUEST['parent_id']) : 0 ;
	$store_name= $_REQUEST['store_name'] ? trim($_REQUEST['store_name']) : '' ;

	$is_only = $exc->is_only('store_name', $store_name, $store_id, "parent_id = '$parent_id'");
    if (!$is_only)
    {
        sys_msg(sprintf($_LANG['subname_exist'], stripslashes($store_name)), 1);
    }

	$sql = "update " . $ecs->table('store_main') . " set store_name='$store_name', province='$_REQUEST[province]', city='$_REQUEST[city]', ".
				"district='$_REQUEST[district]', mianji='$_REQUEST[mianji]' ".
				" where store_id='$store_id'  ";
	$db->query($sql);

	$sql = "delete from ". $ecs->table('store_adminer') ." where store_id='$store_id' ";
	$db->query($sql);
	$admin_list = $_REQUEST['admin_id'];
	if (is_array($admin_list))
	{
		foreach ($admin_list AS $admin_item)
		{
			$mobile = $_REQUEST['mobile_'.$admin_item];
			$tel = $_REQUEST['tel_'.$admin_item];
			$admin_name =trim($_REQUEST['adminname_'.$admin_item]);
			$sql = "INSERT INTO " . $ecs->table('store_adminer') . " (store_id, admin_id, admin_name, mobile, tel) ".
           "VALUES ('$store_id', '$admin_item', '$admin_name', '$mobile', '$tel')";
			$db->query($sql);
		}
	}


	/* 清除缓存 */
    clear_cache_files();

    $link[0]['text'] = $_LANG['list_sub'];
    $link[0]['href'] = 'store_manage.php?act=list_sub&pid='.$parent_id;

    sys_msg($_LANG['edit_sub_succed'], 0, $link);

}

/*------------------------------------------------------ */
//-- 删除库房
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'sub_remove')
{
	$store_id = $_REQUEST['id'] ?  intval($_REQUEST['id']) : 0;
	$pid = $_REQUEST['pid'] ?  intval($_REQUEST['pid']) : 0;
	
	$num = $db->getOne("select sum(store_number) from ". $ecs->table('store_goods_stock') ." where store_id=".$store_id);
	if($num > 0){
		sys_msg('请先将仓库下的商品出库！!', 1);
	}
	
	$sql="delete from ". $ecs->table('store_main') ." where store_id='$store_id' ";
	$db->query($sql);

	   /* 清除缓存 */
     clear_cache_files();

     $link[0]['text'] = '返回列表页';
     $link[0]['href'] = 'store_manage.php?act=list_sub&pid='.$pid;

     sys_msg('删除成功', 0, $link);
}



/*------------------------------------------------------ */
//-- 添加新的仓库（库房）
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'add_store')
{
    check_authz_json('store_manage');

    $parent_id      = intval($_POST['parent_id']);
    $store_name    = json_str_iconv(trim($_POST['store_name']));

    if (empty($store_name))
    {
        make_json_error($_LANG['store_name_empty']);
    }

    /* 查看库房是否重复 */
    if (!$exc->is_only('store_name', $store_name, 0, "parent_id = '$parent_id'"))
    {
        make_json_error($_LANG['store_name_exist']);
    }

    $sql = "INSERT INTO " . $ecs->table('store_main') . " (parent_id, store_name) ".
           "VALUES ('$parent_id', '$store_name')";
    if ($GLOBALS['db']->query($sql, 'SILENT'))
    {
		//添加仓库的默认佣金比例
		$store_id = $GLOBALS['db']->insert_id();

		$sql_rebate = "insert into ".$ecs->table('store_main_rebate')." (store_id,rebate,store_rebate_paytime) ".
					"values ('$store_id','0','1')";
		$GLOBALS['db']->query($sql_rebate);

        /* 获取仓库列表 */
        $store_arr = store_list($keyword);
        $smarty->assign('store_arr',   $store_arr);

        make_json_result($smarty->fetch('store_list.htm'));
    }
    else
    {
        make_json_error($_LANG['add_area_error']);
    }
}

/*------------------------------------------------------ */
//-- 编辑仓库名称
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'edit_store_name')
{
    check_authz_json('store_manage');

    $id = intval($_POST['id']);
    $store_name = json_str_iconv(trim($_POST['val']));

    if (empty($store_name))
    {
        make_json_error($_LANG['store_name_empty']);
    }

    $msg = '';

    /* 查看区域是否重复 */
    $parent_id = $exc->get_name($id, 'parent_id');
    if (!$exc->is_only('store_name', $store_name, $id, "parent_id = '$parent_id'"))
    {
        make_json_error($_LANG['store_name_exist']);
    }

    if ($exc->edit("store_name = '$store_name'", $id))
    {
        make_json_result(stripslashes($store_name));
    }
    else
    {
        make_json_error($db->error());
    }
}

/*------------------------------------------------------ */
//-- 删除仓库
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'drop_store')
{
    check_authz_json('store_manage');

    $id = intval($_REQUEST['id']);

    $sql = "SELECT * FROM " . $ecs->table('store_main') . " WHERE store_id = '$id'";
    $region = $db->getRow($sql);

//    /* 如果底下有下级区域,不能删除 */
//    $sql = "SELECT COUNT(*) FROM " . $ecs->table('region') . " WHERE parent_id = '$id'";
//    if ($db->getOne($sql) > 0)
//    {
//        make_json_error($_LANG['parent_id_exist']);
//    }
    $region_type=$region['region_type'];
    $delete_region[]=$id;
    $new_region_id  =$id;
    if($region_type<6)
    {
        for($i=1;$i<6-$region_type;$i++)
        {
             $new_region_id=new_region_id($new_region_id);
             if(count($new_region_id))
             {
                  $delete_region=array_merge($delete_region,$new_region_id);
             }
             else
             {
                 continue;
             }
        }
    }
    $sql="DELETE FROM ". $ecs->table("region")."WHERE region_id".db_create_in($delete_region);
     $db->query($sql);
    if ($exc->drop($id))
    {
        admin_log(addslashes($region['region_name']), 'remove', 'area');

        /* 获取地区列表 */
        $region_arr = area_list($region['parent_id']);
        $smarty->assign('region_arr',   $region_arr);
        $smarty->assign('region_type', $region['region_type']);

        make_json_result($smarty->fetch('area_list.htm'));
    }
    else
    {
        make_json_error($db->error());
    }
}

/*获取子的城市id*/
function new_region_id($region_id)
{
    $regions_id=array();
    if(empty($region_id))
    {
        return $regions_id;
    }
    $sql="SELECT region_id FROM ". $GLOBALS['ecs']->table("region")."WHERE parent_id ".db_create_in($region_id);
    $result=$GLOBALS['db']->getAll($sql);
    foreach($result as $val)
    {
        $regions_id[]=$val['region_id'];
    }
    return $regions_id;
}

/* 仓库列表 */
function store_list($keyword)
{
    $area_arr = array();
	$where = " where store_type_id=0 and parent_id=0 ";
    if ($keyword)
	{
		$where .= " AND store_name like '%$keyword%' " ;
	}
    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('store_main').
           " $where ORDER BY store_id desc ";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $area_arr[] = $row;
    }

    return $area_arr;
}

/* 区域列表 */
function store_area_list($parent_id)
{
    $area_arr = array();

    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('store_shipping_region').
           " WHERE store_id = '$parent_id' ORDER BY rec_id desc ";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
		$row['area_name'] = get_region_name($row['province']).get_region_name($row['city']).get_region_name($row['district']).get_region_name($row['xiangcun']);
        $area_arr[] = $row;
    }

    return $area_arr;
}
/* 获得区域名称 */
function get_region_name($region_id)
{
	if (!$region_id)
	{
	    return '';
	}
	$sql="select region_name from ". $GLOBALS['ecs']->table('region') ." where region_id='$region_id' ";
	$region_name = $GLOBALS['db']->getOne($sql);
	return $region_name;
}
/*仓库管理员信息*/
function get_store_admin_info($store_id)
{
	$admin_info ='';
	 $sql="select * from ". $GLOBALS['ecs']->table('store_adminer') ." where store_id= $store_id";
	 $res = $GLOBALS['db']->query($sql);
	 while($row=$GLOBALS['db']->fetchRow($res))
	 {
		 $admin_info .= $admin_info ? "<br />" : "";
		 $admin_info .= trim($row['admin_name']);
		 $admin_info .=  $row['mobile'] ?  '&nbsp;'.$row['mobile'] : '';
		 $admin_info .=  $row['tel'] ?  '&nbsp;'.$row['tel'] : '';
	 }
	 return $admin_info;
}

?>