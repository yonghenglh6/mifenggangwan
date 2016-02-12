<?php

/**
 * ECSHOP 入库管理 程序文件
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

require_once(ROOT_PATH . 'includes/cls_image.php');

/*初始化数据交换对象 */
$exc   = new exchange($ecs->table("store_inout_list"), $db, 'rec_id', 'inout_sn');

/*  AJAX获取库房管理员 */
if ($_REQUEST['act'] == 'get_store_admin')
{
	require(ROOT_PATH . 'includes/cls_json.php');
	$json = new JSON;
	$opt['error']=0;
	$store_id =  empty($_GET['store_id']) ? '0' : trim($_GET['store_id']);	
	$sql = "select admin_name, admin_id  from ". $ecs->table('store_adminer') ." where store_id = '$store_id' ";
	$admin_row = $db->getRow($sql);
	if (!$admin_row)
	{
		$opt['error']=1;
		$opt['admin_name']='';
		$opt['admin_id']='';
	}
	else
	{
		$opt['admin_name']=$admin_row['admin_name'];
		$opt['admin_id']=$admin_row['admin_id'];
	}
	$sql = "select rec_id from ". $ecs->table('store_adminer') ." where admin_id='$_SESSION[admin_id]' and store_id = '$store_id' ";
	$rec_id =$db->getOne($sql);
	$opt['admin_isme'] = $rec_id ? '1' : '0';

	echo $json->encode($opt);
}

/*  ajax获取商品列表__根据订单号（退货单）  */
if ($_REQUEST['act'] == 'get_goodslist_byOrdersn')
{
	require(ROOT_PATH . 'includes/cls_json.php');
	$json = new JSON;
	$opt['error']=0;
	$order_sn =  empty($_GET['order_sn']) ? '0' : trim($_GET['order_sn']);	
	$sql = "select back_id from ". $ecs->table('back_order') ." where order_sn = '$order_sn' ";
	$back_id = $db->getOne($sql);
	if (!$back_id)
	{
		$opt['error']=1;
	}
	else
	{
		$sql = "select  b.goods_id,  b.goods_sn, b.goods_attr, b.back_goods_number,  g.goods_name, g.goods_thumb ".
				" from ". $ecs->table('back_goods') ." AS b left  join ". $ecs->table('goods') ." AS g on b.goods_id=g.goods_id ".
				" where b.back_id='$back_id' ";
		$res_ecshop120 = $db->query($sql);
		while ($row_ecshop120 = $db->fetchRow($res_ecshop120))
		{
			$row_ecshop120['goods_thumb'] =  get_image_path($row_ecshop120['goods_id'], $row_ecshop120['goods_thumb'], true);
			$row_ecshop120['goods_attr_name'] = nl2br($row_ecshop120['goods_attr']);
			$row_ecshop120['goods_attr'] = get_goods_attr_id($row_ecshop120['goods_id'], $row_ecshop120['goods_attr']);
			$opt['goods_list'][]=$row_ecshop120;
		}
	}
	echo $json->encode($opt);
}

/*  ajax获取商品信息__根据商品货号  */
if ($_REQUEST['act'] == 'get_goodsInfo_bysn')
{
	require(ROOT_PATH . 'includes/cls_json.php');
	$opt['error']=0;
	$goods_sn   = empty($_GET['goods_sn']) ? 0 : trim($_GET['goods_sn']);
    $sql = "select goods_id, goods_name, goods_thumb, supplier_id from ". $ecs->table('goods') ." where goods_sn= '$goods_sn' ";
    $goodsinfo = $db->getRow($sql);
    if ($goodsinfo['goods_id']>0 && $goodsinfo['supplier_id'] == 0)
	{
		$opt['goods_thumb'] = get_image_path($goodsinfo['goods_id'], $goodsinfo['goods_thumb'], true);
		$opt['goods_name']=$goodsinfo['goods_name'];
		$opt['goods_id']=$goodsinfo['goods_id'];
		//获取属性
		$sql = "SELECT g.goods_attr_id, g.attr_value, g.attr_id, a.attr_name
            FROM " . $GLOBALS['ecs']->table('goods_attr') . " AS g
                LEFT JOIN " . $GLOBALS['ecs']->table('attribute') . " AS a
                    ON a.attr_id = g.attr_id
            WHERE goods_id = '$goodsinfo[goods_id]'
            AND a.attr_type = 1
            ORDER BY g.attr_id ASC";
		$attribute=array();
		$results = $db->query($sql);
		while ($rows=$db->fetchRow($results))
		{
				$attribute[$rows['attr_id']]['attr_values'][$rows['goods_attr_id']] = $rows['attr_value'];
				$attribute[$rows['attr_id']]['attr_id']  = $rows['attr_id'];
				$attribute[$rows['attr_id']]['attr_name']  = $rows['attr_name'];
		}
		$optionss="";
		foreach($attribute as $akey=>$aval)
		{
			$optionss .= '<input type="hidden" name="attr_name" value="'. $aval['attr_name'] .'">';
			$optionss .= $aval['attr_name'].'<select name="attr_val">';
			foreach ($aval['attr_values'] AS $attr_key =>$attr_val)
			{
				$optionss .= '<option value="'. $attr_key .'">'. $attr_val .'</option>';
			}
			$optionss .= '</select>';
		}
		$opt['optionss']=$optionss;
	}
	else
	{
		$opt['error']=1;
		$opt['message'] = '商品不存在!';
	}
    
	

    $json = new JSON;
	echo $json->encode($opt);
}

/*------------------------------------------------------ */
//-- 入库单列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 取得过滤条件 */
    $filter = array();
    $smarty->assign('ur_here',      $_LANG['03_store_inout_in']);

	$sql="SELECT a.rec_id, a.admin_id FROM ".$ecs->table('store_adminer')." AS a ".
				"left join ". $ecs->table('store_main') ." as m on a.store_id=m.store_id  WHERE a.admin_id='$_SESSION[admin_id]' and m.parent_id>0 and a.store_type_id=0";
	$is_add = $db->getRow($sql);
	if($is_add)
	{
		$smarty->assign('action_link',  array('text' => $_LANG['store_inout_add_in'], 'href' => 'store_inout_in.php?act=add'));
	}
    $smarty->assign('full_page',    1);
    $smarty->assign('filter',       $filter);
	$smarty->assign('showck',(isset($_REQUEST['sid']) ? 1 : 0));

	/* 入库类型 */
	$sql = "select type_id, type_name from ". $ecs->table('store_inout_type') ." where in_out=2 and is_valid=1 and store_type_id=0 order by type_id asc";
	$inout_type_list = $db->getAll($sql);
	$smarty->assign('inout_type_list', $inout_type_list);
    
	/* 仓库 */
	$sql="select store_id, store_name from ". $ecs->table('store_main')." where parent_id='0' and store_type_id=0 ";
	$store_list=$db->getAll($sql);
	$smarty->assign('store_list', $store_list);
	/* 库房 */
	$sid=$_REQUEST['sid'] ? intval($_REQUEST['sid']) : 0;
	$sql = " select store_id,store_name  from ". $ecs->table('store_main')." where store_type_id=0 and ";
	$sql .=  $sid ? "parent_id='$sid' " : "parent_id>0 ";
	$sub_list=$db->getAll($sql);
	$smarty->assign('sub_list', $sub_list);


    $inout_list = get_inoutlist();

    $smarty->assign('inout_list',    $inout_list['arr']);
    $smarty->assign('filter',          $inout_list['filter']);
    $smarty->assign('record_count',    $inout_list['record_count']);
    $smarty->assign('page_count',      $inout_list['page_count']);

    $sort_flag  = sort_flag($inout_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('store_inout_in_list.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    check_authz_json('inout_in_manage');

    $inout_list = get_inoutlist();

    $smarty->assign('inout_list',    $inout_list['arr']);
    $smarty->assign('filter',          $inout_list['filter']);
    $smarty->assign('record_count',    $inout_list['record_count']);
    $smarty->assign('page_count',      $inout_list['page_count']);

    $sort_flag  = sort_flag($inout_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('store_inout_in_list.htm'), '',
        array('filter' => $inout_list['filter'], 'page_count' => $inout_list['page_count']));
}

/*------------------------------------------------------ */
//-- 添加入库单
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'add')
{
    /* 权限判断 */
    admin_priv('store_inout_in');

    /*初始化*/
	$inout = array();

	$inout['store_id'] = $_REQUEST['store_id'] ? intval($_REQUEST['store_id']) : 0;
	if ($inout['store_id'])
	{
		$inout['sub_id_list'] = $db->getAll("select store_id, store_name from ". $ecs->table('store_main') ." where parent_id='$inout[store_id]' and store_type_id=0 ");
	}
	$inout['sub_id'] = $_REQUEST['sub_id'] ? intval($_REQUEST['sub_id']) : 0;
	$inout['inout_type'] = $_REQUEST['inout_type'] ? intval($_REQUEST['inout_type']) : 0;
	$inout['takegoods_man'] = $_REQUEST['takegoods_man'] ? trim($_REQUEST['takegoods_man']) : '';

	/* 如果有CSV文件传入的话 */
	$file = @fopen($_FILES['csv_file']['tmp_name'],'r');
	if ($file)
	{
		$kkk=0;
		while ($data = fgetcsv($file, '1000', ',')) 
		{ 	
			if ($kkk==0)
			{
				$kkk++;
				continue;			
			}
			$goods_list[$kkk]['goods_thumb'] = '../'.$data[0];
			$goods_list[$kkk]['goods_id'] = $data[1];
			$goods_list[$kkk]['goods_sn'] = $data[2];
			$goods_list[$kkk]['goods_name'] = ecs_iconv('gb2312', 'UTF8', $data[3]);
			$goods_list[$kkk]['goods_attr1'] = ecs_iconv('gb2312', 'UTF8', $data[4]);
			$goods_list[$kkk]['goods_attr2'] = ecs_iconv('gb2312', 'UTF8', $data[5]);
			$data[6]=trim($data[6]);
			$goods_list[$kkk]['number_yingshou'] = !empty($data[6]) ? intval($data[6]) : '1';
			$goods_list[$kkk]['number_shishou'] = $data[7];
			$kkk++;
		}
		//echo '<pre>';
		//print_r($goods_list);
		//echo '</pre>';
		$smarty->assign('goods_list', $goods_list);
	}

	$inout['add_time_date'] = local_date('Y-m-d');
    $inout['add_date'] = local_date('Ymd');

	$sql="select max(today_sn) from ". $ecs->table('store_inout_list') ." where add_date='$inout[add_date]' ";
	$inout_count = $db->getOne($sql);
	$inout_sn = $inout_count ? intval($inout_count + 1) : 1;
	$inout_sn = str_pad($inout_sn, 4, "0", STR_PAD_LEFT);
	$inout_sn =  'rk'.$inout['add_date'] . $inout_sn;
	$inout['inout_sn'] = $inout_sn;

    /* 入库类型 */
	$sql = "select type_id, type_name from ". $ecs->table('store_inout_type') ." where in_out=2 and is_valid=1 and store_type_id=0 order by type_id asc";
	$inout_type_list = $db->getAll($sql);
	$smarty->assign('inout_type_list', $inout_type_list);

    /* 取得仓库 */
	$sql="select store_id, store_name from ". $ecs->table('store_main') ." where parent_id=0 and store_type_id=0 ";
	$store_list=array();
	$ress=$db->query($sql);
	while($rows=$db->fetchRow($ress))
	{
		$store_list[] = $rows;
	}
    $smarty->assign('store_list', $store_list);

    $smarty->assign('inout',     $inout);
    $smarty->assign('ur_here',     $_LANG['store_inout_add_in']);
    $smarty->assign('action_link', array('text' => $_LANG['store_inout_list_in'], 'href' => 'store_inout_in.php?act=list&' . list_link_postfix()));
    $smarty->assign('form_action', 'insert');

    assign_query_info();
    $smarty->display('store_inout_in_info.htm');
}

/**
*  批量导入入库单商品
*/
if ($_REQUEST['act'] == 'batch_import')
{
	$smarty->assign('ur_here',     '批量导入入库商品');
    $smarty->assign('action_link', array('text' => $_LANG['store_inout_list_in'], 'href' => 'store_inout_in.php?act=list&' . list_link_postfix()));
    $smarty->assign('form_action', 'add');

	$store_id =  $_REQUEST['store_id'] ? intval($_REQUEST['store_id']) : 0;
	$sub_id =  $_REQUEST['sub_id'] ? intval($_REQUEST['sub_id']) : 0;
	$inout_type =  $_REQUEST['inout_type'] ? intval($_REQUEST['inout_type']) : 0;
	$takegoods_man =  $_REQUEST['takegoods_man'] ? trim($_REQUEST['takegoods_man']) : 0;
	$smarty->assign("store_id", $store_id);
	$smarty->assign("sub_id", $sub_id);
	$smarty->assign("inout_type", $inout_type);
	$smarty->assign("takegoods_man", $takegoods_man);

    assign_query_info();
    $smarty->display('store_inout_in_import.htm');
}

/*------------------------------------------------------ */
//-- 插入入库单
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'insert')
{
    /* 权限判断 */
    admin_priv('store_inout_in');

	//判断是否入库的商品都是运营商的
	$goodIds = array_unique($_POST['goods_id']);
	$sql = "select count(goods_id) as num from ". $ecs->table('goods') ." where goods_id in (".implode(",",$goodIds).") and supplier_id=0";
	$num = $db->getOne($sql);
	if($num != count($goodIds)){
		sys_msg('你入库的商品中有不属于你的商品，请重新操作！');
	}

    /*插入数据*/
    $add_date = local_date('Ymd');
	$store_id = $_POST['sub_id'] ? intval($_POST['sub_id']) : 0;
	$sql="select max(today_sn) from ". $ecs->table('store_inout_list') ." where add_date='$add_date' ";
	$inout_count = $db->getOne($sql);
	$today_sn = $inout_count ? intval($inout_count + 1) : 1;
	$inout_sn = str_pad($today_sn, 4, "0", STR_PAD_LEFT);
	$inout_sn =  'rk'.$add_date . $inout_sn;
    
    $add_time = gmtime();
    $sql = "INSERT INTO ".$ecs->table('store_inout_list')."(inout_sn, store_id, adminer_id,  inout_type, inout_mode, order_sn,  takegoods_man, ".
                " inout_status, today_sn, add_date, add_time) ".
            "VALUES ('$inout_sn', '$store_id', '$_SESSION[admin_id]', '$_POST[inout_type]', '2', '$_POST[order_sn]', '$_POST[takegoods_man]', ".
                " '1', '$today_sn', '$add_date', '$add_time')";
    $db->query($sql);

    /* 处理关联商品 */
    $inout_rec_id = $db->insert_id();  //出入库记录ID
	foreach ($_POST['goods_id']  AS $gkey=>$gval)
	{
			$goods_id = $gval;
			$goods_sn = $_POST['goods_sn'][$gkey];
			$attr_value = $_POST['goods_attr'][$gkey];
			$number_yingshou = $_POST['number_yingshou'][$gkey];
			$number_shishou = $_POST['number_shishou'][$gkey];
			$sql = "insert into ". $ecs->table('store_inout_goods') ." (inout_rec_id, goods_id, goods_sn, inout_mode, attr_value, number_yingshou, number_shishou ) ".
						"values('$inout_rec_id',  '$goods_id', '$goods_sn', '2', '$attr_value', '$number_yingshou', '$number_shishou')";
			$db->query($sql);
	}    

    $link[0]['text'] = $_LANG['continue_add'];
    $link[0]['href'] = 'store_inout_in.php?act=add';

    $link[1]['text'] = $_LANG['back_list_in'];
    $link[1]['href'] = 'store_inout_in.php?act=list';

    clear_cache_files(); // 清除相关的缓存文件

    sys_msg($_LANG['inoutadd_succeed_in'],0, $link);
}

/*------------------------------------------------------ */
//-- 审核入库单
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'check' or $_REQUEST['act'] == 'view')
{
	/* 权限判断 */
    admin_priv('store_inout_in');

	$smarty->assign('ur_here',     $_LANG['store_inout_check_in']);
    $smarty->assign('action_link', array('text' => $_LANG['store_inout_list_in'], 'href' => 'store_inout_in.php?act=list&' . list_link_postfix()));
	$rec_id = empty($_REQUEST['id']) ? '0' : intval($_REQUEST['id']) ;
	$sql="select * from ". $ecs->table('store_inout_list') ." where rec_id= '$rec_id' ";
	$inout = $db->getRow($sql);
	if ($inout)
	{
		//判断有没有审核权限
		if ($_REQUEST['act'] == 'check')
		{
			$sql="select a.rec_id from ". $ecs->table('store_main') ." AS m left join ". $ecs->table('store_adminer') ." AS a on m.parent_id=a.store_id where m.store_id='$inout[store_id]' and a.admin_id='$_SESSION[admin_id]' ";
			$rec_id = $db->getOne($sql);
			if (!$rec_id)
			{
				sys_msg('对不起，您没有审核权限！');
			}
		}

		$inout['store_name'] = get_store_fullname($inout['store_id']);
		$inout['inout_type_name'] = get_inout_type_name($inout['inout_type']);
		$inout['add_time_date'] = local_date($GLOBALS['_CFG']['time_format'], $inout['add_time']);

		$sql="select admin_name from ". $ecs->table('store_adminer') ." where store_id='$inout[store_id]' ";
		$inout['admin_name'] =$db->getOne($sql);

		/* 商品明细 */
		$sql="select ig.*, g.goods_thumb, g.goods_name from ". $ecs->table('store_inout_goods') ." AS ig ".
				" left join ". $ecs->table('goods') ." AS g on ig.goods_id=g.goods_id ".
			    " where inout_rec_id= '$inout[rec_id]' order by book_id ";
		$res=$db->query($sql);
		while ($row= $db->fetchRow($res))
		{
			$row['goods_thumb'] =  get_image_path($row['goods_id'], $row['goods_thumb'], true);
			$row['attr_value'] =  get_attr_name($row['attr_value']);
			$inout['goods_list'][]=$row;
		}

		/* 备注 */
		$sql = "select * from ". $ecs->table('store_inout_note') ." where inout_rec_id= '$inout[rec_id]' ";
		$res = $db->query($sql);
		while ($row= $db->fetchRow($res))
		{
			$row['inout_status'] = $_LANG['inout_status'][$row['inout_status']];
			$row['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $row['add_time']);
			if($row['supplier_id']>0){//入驻商租自营商仓库
				$row['adminer_name'] = '入驻方:'.$db->getOne("select user_name from ". $ecs->table('supplier_admin_user') ." where user_id='$row[adminer_id]' ");
			}else{
				$row['adminer_name'] = '自营方:'.$db->getOne("select user_name from ". $ecs->table('admin_user') ." where user_id='$row[adminer_id]' ");
			}
			$inout['note_list'][]=$row;
		}
	}
	else
	{
		sys_msg('对不起，没有这个入库单！');
	}
	$smarty->assign('inout', $inout);

    assign_query_info();
    $smarty->display('store_inout_in_view.htm');
}

/*------------------------------------------------------ */
//-- 审核入库单_insert
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'check_insert')
{
		admin_priv('store_inout_in');
		
		$rec_id= empty($_REQUEST['rec_id']) ? 0 : intval($_REQUEST['rec_id']);
		/* 获取入库单信息 */
		$sql = "select rec_id, store_id, supplier_id  from ". $ecs->table('store_inout_list') ." where rec_id='$rec_id' ";
		$inout_row = $db->getRow($sql);
		if (!$inout_row)
		{
			sys_msg('对不起，不存在这个入库单！');
		}

		//判断有没有审核权限
		if ($_REQUEST['inout_status'] != '2') //2 是提交审核
		{
			$sql_sh="select a.rec_id from ". $ecs->table('store_main') ." AS m left join ". $ecs->table('store_adminer') ." AS a on m.parent_id=a.store_id where m.store_id='$inout_row[store_id]' and a.admin_id='$_SESSION[admin_id]' ";
			$rec_id_sh = $db->getOne($sql_sh);
			if (!$rec_id_sh)
			{
				sys_msg('对不起，您没有审核权限！');
			}
		}

		$add_time = gmtime();		
		$inout_note = empty($_REQUEST['note']) ? '' : trim($_REQUEST['note']);
		$inout_status = empty($_REQUEST['inout_status']) ? '' : intval($_REQUEST['inout_status']);
		$action_val = empty($_REQUEST['action_val']) ? '' : trim($_REQUEST['action_val']);
		$sql="insert into  ". $ecs->table('store_inout_note').
				" (inout_rec_id, adminer_id, action_val, inout_status, inout_note, add_time)".
				" values('$rec_id', '$_SESSION[admin_id]', '$action_val', '$inout_status', '$inout_note', '$add_time')";
		$db->query($sql);

		/* 更新入库单状态 */
		$sql="update  ". $ecs->table('store_inout_list').
				" set inout_status='$inout_status' where rec_id='$rec_id' ";
		$db->query($sql);

		if ($inout_status == '3') //审核通过
		{
			update_stock_in($rec_id, $inout_row['store_id']);  //更新库存表
		}

		$link[0]['text'] = $_LANG['back_list_in'];
		$link[0]['href'] = 'store_inout_in.php?act=list';

		clear_cache_files(); // 清除相关的缓存文件

		sys_msg('恭喜，操作成功',0, $link);
}

/*------------------------------------------------------ */
//-- 编辑入库单
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'edit')
{
    admin_priv('store_inout_in');

	$id = intval($_REQUEST['id']);

    /* 取库单数据 */
    $sql = "SELECT * FROM " .$ecs->table('store_inout_list'). " WHERE rec_id='$id' ";
    $inout = $db->GetRow($sql);	
	if($inout['adminer_id']!=$_SESSION['admin_id'])
	{
		sys_msg('您无权编辑这个出库单！');
	}
	$inout['add_time_date'] = local_date($GLOBALS['_CFG']['time_format'], $inout['add_time']);
	$inout['sub_id'] = $inout['store_id'];
	$inout['store_id']=$db->getOne("select parent_id from ". $ecs->table('store_main') ." where store_id='$inout[store_id]' ");
	if ($inout['store_id'])
	{
		$inout['sub_id_list'] = $db->getAll("select store_id, store_name from ". $ecs->table('store_main') ." where parent_id='$inout[store_id]' ");
	}

	/* 出入库类型 */
	$sql = "select type_id, type_name from ". $ecs->table('store_inout_type') ." where in_out=2 order by type_id asc";
	$inout_type_list = $db->getAll($sql);
	$smarty->assign('inout_type_list', $inout_type_list);

    /* 取得仓库 */
	$sql="select store_id, store_name from ". $ecs->table('store_main') ." where parent_id=0 ";
	$store_list=array();
	$ress=$db->query($sql);
	while($rows=$db->fetchRow($ress))
	{
		$store_list[] = $rows;
	}
    $smarty->assign('store_list', $store_list);

	//取得出入单商品
	$goods_list=array();
	$sql = "select i.*, g.goods_name, g.goods_thumb from ". $ecs->table('store_inout_goods') ." AS i left join ". $ecs->table('goods') ." AS g on i.goods_id=g.goods_id where i.inout_rec_id='$id' ";
	$goods_res = $db->query($sql);
	while ($goods_row=$db->fetchRow($goods_res))
	{
		$goods_row['goods_thumb'] = "../".get_image_path($goods_row['goods_id'], $goods_row['goods_thumb']);
		$goods_row['goods_attr1'] = get_attr_name($goods_row['attr_value']);
		$goods_row['goods_attr2'] = $goods_row['attr_value'];
 		$goods_list[]=$goods_row;
	}
	$smarty->assign('goods_list', $goods_list);

    $smarty->assign('inout',     $inout);
    $smarty->assign('ur_here',     $_LANG['store_inout_edit']);
    $smarty->assign('action_link', array('text' => $_LANG['store_inout_list_in'], 'href' => 'store_inout_in.php?act=list&' . list_link_postfix()));
    $smarty->assign('form_action', 'update');

    assign_query_info();
    $smarty->display('store_inout_in_info.htm');
}

// 更新某条入库单
if ($_REQUEST['act'] =='update')
{
    /* 权限判断 */
    admin_priv('store_inout_in');

	$rec_id= $_REQUEST['rec_id'] ? intval($_REQUEST['rec_id']) : 0;
	$sql = "select rec_id from ". $ecs->table('store_inout_list') ." where rec_id='$rec_id' ";
	$rec_id2=$db->getOne($sql);
	if(!$rec_id2)
	{
		sys_msg('对不起，不存在这个入库单！', 0, $link);
	}

	$sql ="update ". $ecs->table('store_inout_list') ." set store_id='$_POST[sub_id]', inout_type='$_POST[inout_type]', order_sn='$_POST[order_sn]', ".
				" takegoods_man='$_POST[takegoods_man]'  where rec_id='$rec_id' ";
	$db->query($sql);

	//更新出库单商品明细
	$sql="delete from ". $ecs->table('store_inout_goods') ." where inout_rec_id = '$rec_id' ";
	$db->query($sql);
	foreach ($_POST['goods_id']  AS $gkey=>$gval)
	{
			$goods_id = $gval;
			$goods_sn = $_POST['goods_sn'][$gkey];
			$attr_value = $_POST['goods_attr'][$gkey];
			$number_yingshou = $_POST['number_yingshou'][$gkey];
			$number_shishou = $_POST['number_shishou'][$gkey];
			$sql = "insert into ". $ecs->table('store_inout_goods') ." (inout_rec_id, goods_id, goods_sn, inout_mode, attr_value, number_yingshou, number_shishou ) ".
						"values('$rec_id',  '$goods_id', '$goods_sn', '2', '$attr_value', '$number_yingshou', '$number_shishou')";
			$db->query($sql);
	} 

	$link[0]['text'] = $_LANG['back_list_in'];
	$link[0]['href'] = 'store_inout_in.php?act=list&' . list_link_postfix();

	clear_cache_files();

    sys_msg('恭喜，更新成功！', 0, $link);    
}

/*------------------------------------------------------ */
//-- 编辑文章主题
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_title')
{
    check_authz_json('article_manage');

    $id    = intval($_POST['id']);
    $title = json_str_iconv(trim($_POST['val']));

    /* 检查文章标题是否重复 */
    if ($exc->num("title", $title, $id) != 0)
    {
        make_json_error(sprintf($_LANG['title_exist'], $title));
    }
    else
    {
        if ($exc->edit("title = '$title'", $id))
        {
            clear_cache_files();
            admin_log($title, 'edit', 'article');
            make_json_result(stripslashes($title));
        }
        else
        {
            make_json_error($db->error());
        }
    }
}

/*------------------------------------------------------ */
//-- 切换是否显示
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'toggle_show')
{
    check_authz_json('article_manage');

    $id     = intval($_POST['id']);
    $val    = intval($_POST['val']);

    $exc->edit("is_open = '$val'", $id);
    clear_cache_files();

    make_json_result($val);
}

/*------------------------------------------------------ */
//-- 切换文章重要性
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'toggle_type')
{
    check_authz_json('article_manage');

    $id     = intval($_POST['id']);
    $val    = intval($_POST['val']);

    $exc->edit("article_type = '$val'", $id);
    clear_cache_files();

    make_json_result($val);
}



/*------------------------------------------------------ */
//-- 删除入库单
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{    
    /* 权限判断 */	
    admin_priv('inout_out_manage');
	$id=intval($_REQUEST['id']);

	$sql="select adminer_id, inout_status  from ". $ecs->table('store_inout_list') ." where rec_id= '$id' ";
	$row_inout = $db->getRow($sql);
	if (!$row_inout)
	{
		 sys_msg('对不起，不存在该入库单');
	}
	else
	{
		if ($row_inout['adminer_id']!=$_SESSION['admin_id'])
		{
			sys_msg('对不起，您没有删除权限');
		}
		elseif ($row_inout['inout_status']!='1')
		{
			sys_msg('对不起，该订单已经进入审核流程，不能删除！');
		}
	}

	$sql = "delete from ". $ecs->table('store_inout_list') ." where rec_id='$id' ";
	$db->query($sql);
	$sql = "delete from ". $ecs->table('store_inout_goods') ." where inout_rec_id='$id' ";
	$db->query($sql);
	$sql = "delete from ". $ecs->table('store_inout_note') ." where inout_rec_id='$id' ";
	$db->query($sql);

	$link[0]['text'] = $_LANG['back_list_in'];
    $link[0]['href'] = 'store_inout_in.php?act=list&' . list_link_postfix();

	clear_cache_files();

    sys_msg('成功删除', 0, $link);
}

/*------------------------------------------------------ */
//-- 将商品加入关联
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add_link_goods')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    check_authz_json('article_manage');

    $add_ids = $json->decode($_GET['add_ids']);
    $args = $json->decode($_GET['JSON']);
    $article_id = $args[0];

    if ($article_id == 0)
    {
        $article_id = $db->getOne('SELECT MAX(article_id)+1 AS article_id FROM ' .$ecs->table('article'));
    }

    foreach ($add_ids AS $key => $val)
    {
        $sql = 'INSERT INTO ' . $ecs->table('goods_article') . ' (goods_id, article_id) '.
               "VALUES ('$val', '$article_id')";
        $db->query($sql, 'SILENT') or make_json_error($db->error());
    }

    /* 重新载入 */
    $arr = get_article_goods($article_id);
    $opt = array();

    foreach ($arr AS $key => $val)
    {
        $opt[] = array('value'  => $val['goods_id'],
                        'text'  => $val['goods_name'],
                        'data'  => '');
    }

    make_json_result($opt);
}

/*------------------------------------------------------ */
//-- 将商品删除关联
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'drop_link_goods')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    check_authz_json('article_manage');

    $drop_goods     = $json->decode($_GET['drop_ids']);
    $arguments      = $json->decode($_GET['JSON']);
    $article_id     = $arguments[0];

    if ($article_id == 0)
    {
        $article_id = $db->getOne('SELECT MAX(article_id)+1 AS article_id FROM ' .$ecs->table('article'));
    }

    $sql = "DELETE FROM " . $ecs->table('goods_article').
            " WHERE article_id = '$article_id' AND goods_id " .db_create_in($drop_goods);
    $db->query($sql, 'SILENT') or make_json_error($db->error());

    /* 重新载入 */
    $arr = get_article_goods($article_id);
    $opt = array();

    foreach ($arr AS $key => $val)
    {
        $opt[] = array('value'  => $val['goods_id'],
                        'text'  => $val['goods_name'],
                        'data'  => '');
    }

    make_json_result($opt);
}

/*------------------------------------------------------ */
//-- 搜索商品
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'get_goods_list')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    $filters = $json->decode($_GET['JSON']);

    $arr = get_goods_list($filters);
    $opt = array();

    foreach ($arr AS $key => $val)
    {
        $opt[] = array('value' => $val['goods_id'],
                        'text' => $val['goods_name'],
                        'data' => $val['shop_price']);
    }

    make_json_result($opt);
}
/*------------------------------------------------------ */
//-- 批量操作
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'batch')
{
    /* 批量删除 */
    if (isset($_POST['type']))
    {
        if ($_POST['type'] == 'button_remove')
        {
            admin_priv('article_manage');

            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
            {
                sys_msg($_LANG['no_select_article'], 1);
            }

            /* 删除原来的文件 */
            $sql = "SELECT file_url FROM " . $ecs->table('article') .
                    " WHERE article_id " . db_create_in(join(',', $_POST['checkboxes'])) .
                    " AND file_url <> ''";

            $res = $db->query($sql);
            while ($row = $db->fetchRow($res))
            {
                $old_url = $row['file_url'];
                if (strpos($old_url, 'http://') === false && strpos($old_url, 'https://') === false)
                {
                    @unlink(ROOT_PATH . $old_url);
                }
            }

            foreach ($_POST['checkboxes'] AS $key => $id)
            {
                if ($exc->drop($id))
                {
                    $name = $exc->get_name($id);
                    admin_log(addslashes($name),'remove','article');
                }
            }

        }

        /* 批量隐藏 */
        if ($_POST['type'] == 'button_hide')
        {
            check_authz_json('article_manage');
            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
            {
                sys_msg($_LANG['no_select_article'], 1);
            }

            foreach ($_POST['checkboxes'] AS $key => $id)
            {
              $exc->edit("is_open = '0'", $id);
            }
        }

        /* 批量显示 */
        if ($_POST['type'] == 'button_show')
        {
            check_authz_json('article_manage');
            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']))
            {
                sys_msg($_LANG['no_select_article'], 1);
            }

            foreach ($_POST['checkboxes'] AS $key => $id)
            {
              $exc->edit("is_open = '1'", $id);
            }
        }

        /* 批量移动分类 */
        if ($_POST['type'] == 'move_to')
        {
            check_authz_json('article_manage');
            if (!isset($_POST['checkboxes']) || !is_array($_POST['checkboxes']) )
            {
                sys_msg($_LANG['no_select_article'], 1);
            }

            if(!$_POST['target_cat'])
            {
                sys_msg($_LANG['no_select_act'], 1);
            }
            
            foreach ($_POST['checkboxes'] AS $key => $id)
            {
              $exc->edit("cat_id = '".$_POST['target_cat']."'", $id);
            }
        }
    }

    /* 清除缓存 */
    clear_cache_files();
    $lnk[] = array('text' => $_LANG['back_list'], 'href' => 'article.php?act=list');
    sys_msg($_LANG['batch_handle_ok'], 0, $lnk);
}

/* 把商品删除关联 */
function drop_link_goods($goods_id, $article_id)
{
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('goods_article') .
            " WHERE goods_id = '$goods_id' AND article_id = '$article_id' LIMIT 1";
    $GLOBALS['db']->query($sql);
    create_result(true, '', $goods_id);
}

/* 取得文章关联商品 */
function get_article_goods($article_id)
{
    $list = array();
    $sql  = 'SELECT g.goods_id, g.goods_name'.
            ' FROM ' . $GLOBALS['ecs']->table('goods_article') . ' AS ga'.
            ' LEFT JOIN ' . $GLOBALS['ecs']->table('goods') . ' AS g ON g.goods_id = ga.goods_id'.
            " WHERE ga.article_id = '$article_id'";
    $list = $GLOBALS['db']->getAll($sql);

    return $list;
}

/* 获得出入库列表 */
function get_inoutlist()
{
    $result = get_filter();
    if ($result === false)
    {
        $filter = array();
        $filter['sid']    = empty($_REQUEST['sid']) ? '0' : intval($_REQUEST['sid']);
		$filter['ssid']    = empty($_REQUEST['ssid']) ? '0' : intval($_REQUEST['ssid']);
		$filter['inout_status']    = empty($_REQUEST['inout_status']) ? '0' : intval($_REQUEST['inout_status']);
		$filter['inout_type']    = empty($_REQUEST['inout_type']) ? '0' : intval($_REQUEST['inout_type']);
		$filter['add_time1']    = empty($_REQUEST['add_time1']) ? '' : (strpos($_REQUEST['add_time1'], '-') > 0 ?  local_strtotime($_REQUEST['add_time1']) : $_REQUEST['add_time1']);
		$filter['add_time2']    = empty($_REQUEST['add_time2']) ? '' : (strpos($_REQUEST['add_time2'], '-') > 0 ?  local_strtotime($_REQUEST['add_time2']) : $_REQUEST['add_time2']);
		$filter['inout_sn']    = empty($_REQUEST['inout_sn']) ? '' : trim($_REQUEST['inout_sn']);
		$filter['takegoods_man']    = empty($_REQUEST['takegoods_man']) ? '' : trim($_REQUEST['takegoods_man']);

        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'rec_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = 'AND store_type_id=0 ';
        if ($filter['ssid'])
        {
            $where .= " AND store_id = '" . $filter['ssid']."' ";
        }
		else
		{
			if ($filter['sid'])
			{
				$where .= " AND store_id in " . get_ssid_list($filter['sid']);
			}
		}	
		if ($filter['inout_status'])
		{
			$where .= " AND inout_status = '" . $filter['inout_status']."' ";
		}
		if ($filter['inout_type'])
		{
			$where .= " AND inout_type = '" . $filter['inout_type']."' ";
		}
		if ($filter['add_time1'])
		{
			$where .= " AND add_time>=  '" . $filter['add_time1']."' ";
		}
		if ($filter['add_time2'])
		{
			$where .= " AND add_time<=  '" . $filter['add_time2']."' ";
		}
		if ($filter['inout_sn'])
		{
			$where .= " AND inout_sn = '" . $filter['inout_sn']."' ";
		}
		if ($filter['takegoods_man'])
		{
			$where .= " AND takegoods_man like '%" . $filter['takegoods_man']."%' ";
		}

		//获取当前管理员负责的store_id列表
		$storeid_list="";
		$sql1 = "select store_id from " . $GLOBALS['ecs']->table('store_adminer') ." where admin_id = '$_SESSION[admin_id]' ";
		$res1=$GLOBALS['db']->query($sql1);
		while($row1=$GLOBALS['db']->fetchRow($res1))
		{
			$storeid_list .= $storeid_list ? "," : "";
			$storeid_list .= $row1['store_id'];
			$sql2="select store_id from " . $GLOBALS['ecs']->table('store_main') ." where parent_id = '$row1[store_id]' ";
			$res2=$GLOBALS['db']->query($sql2);
			while($row2=$GLOBALS['db']->fetchRow($res2))
			{
				$storeid_list .= $storeid_list ? "," : "";
				$storeid_list .= $row2['store_id'];
			}
		}
		if ($storeid_list)
		{
			$where .=" AND store_id in ($storeid_list) ";
		}

        /* 记录总数 */
        $sql = 'SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('store_inout_list'). 
               'WHERE inout_mode=2 ' .$where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 获取入库单数据 */
        $sql = 'SELECT *  '.
               'FROM ' .$GLOBALS['ecs']->table('store_inout_list'). 
               'WHERE inout_mode=2 ' .$where. ' ORDER by '.$filter['sort_by'].' '.$filter['sort_order'];

        $filter['keyword'] = stripslashes($filter['keyword']);
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $arr = array();
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
		$rows['store_name'] = get_store_fullname($rows['store_id']);
        $rows['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $rows['add_time']);
		$rows['inout_status_name'] = $GLOBALS['_LANG']['inout_status'][$rows['inout_status']];
		$rows['inout_type_name'] = get_inout_type_name($rows['inout_type']);
		$rows['admin_name'] = $GLOBALS['db']->getOne("select admin_name from ". $GLOBALS['ecs']->table('store_adminer') ." where store_id='$rows[store_id]' ");
    	if($rows['inout_status'] == 1){//是否有提交申请的权利
			$sql_sh="select rec_id from ". $GLOBALS['ecs']->table('store_adminer') ." where store_id='$rows[store_id]' and admin_id='$_SESSION[admin_id]' ";
			$rec_id_sh = $GLOBALS['db']->getOne($sql_sh);
			if (!$rec_id_sh)
			{
				$rows['tjsq'] = 0;
			}else{
				$rows['tjsq'] = 1;
			}
		}
		$arr[] = $rows;
    }
    return array('arr' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}


?>