<?php

/**
 * 管理中心 返佣管理
 * $Author: yangsong
 * 
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_rebate_store.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
//require(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/supplier.php');
$smarty->assign('lang', $_LANG);

$act = isset($_REQUEST['act1']) ? $_REQUEST['act1'] : $_REQUEST['act'];
/*------------------------------------------------------ */
//-- 返佣列表
/*------------------------------------------------------ */
if ($act == 'list')
{
     /* 检查权限 */
     admin_priv('store_rebate');

    /* 查询 */
    $result = rebate_list();

    /* 模板赋值 */
	$ur_here_lang = $_REQUEST['is_pay_ok'] =='1' ? '往期结算' : '本期待结';
    $smarty->assign('ur_here', $ur_here_lang); // 当前导航

	$statusinfo = rebateStatus();
	unset($statusinfo[4]);

    $smarty->assign('full_page',        1); // 翻页参数
	$smarty->assign('statusinfo',$statusinfo);
    $smarty->assign('supplier_list',    $result['result']);
    $smarty->assign('filter',       $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count',   $result['page_count']);
    $smarty->assign('sort_suppliers_id', '<img src="images/sort_desc.gif">');

	if(($storeids = havePower($_SESSION['admin_id'])) != false){
		$smarty->assign('ishavestore',1);
	}

    /* 显示模板 */
    assign_query_info();
    $smarty->display('store_rebate_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($act == 'query')
{
    check_authz_json('store_rebate');

    $result = rebate_list('list');

    $smarty->assign('supplier_list',    $result['result']);
    $smarty->assign('filter',       $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count',   $result['page_count']);

    /* 排序标记 */
    $sort_flag  = sort_flag($result['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('store_rebate_list.htm'), '',
        array('filter' => $result['filter'], 'page_count' => $result['page_count']));
}

//结算页面展示
elseif ($act == 'view')
{
	admin_priv('store_rebate');

	$id = intval($_REQUEST['rid']);
    if (($rebate = rebateHave($id)) === false)
    {
        sys_msg('该返佣记录不存在！');
    }
	else
	{
		$rebate['sign'] = createSign($rebate['rebate_id'],$rebate['store_id']);
		$rebate['rebate_paytime_start'] = local_date('Y.m.d', $rebate['rebate_paytime_start']);
		$paytime_end = $rebate['rebate_paytime_end'];
		$rebate['rebate_paytime_end'] = local_date('Y.m.d', $paytime_end);
		//结算信息
		$money = getRebateOrderMoney($id);
		$money_info = array();
		foreach($money['all'] as $key=>$val){
			$money_info[$key]['allmoney'] = $val;
			$money_info[$key]['allmoney'] = price_format($val);
			$money_info[$key]['supplier_rebate'] = $rebate['rebate'];
			$money_info[$key]['rebatemoney'] = price_format($money['rebate'][$key]*$rebate['rebate']/100);
		}
		$smarty->assign('money_info',   $money_info);

		//佣金统计
		$allmoney = array_sum($money['all']);
		$allrebate = array_sum($money['rebate']);
		$tongji['allmoney'] = price_format($allmoney);
		$tongji['allrebate'] = price_format($allrebate*$rebate['rebate']/100);
		$tongji['chamoney'] = price_format($allmoney - $allrebate*$rebate['rebate']/100);

		$tongji['rebate_all'] = ($rebate['rebate_all'] > 0) ? $rebate['rebate_all'] : $tongji['allmoney'];
		$tongji['rebate_money'] = ($rebate['rebate_money'] > 0) ? $rebate['rebate_money'] : $tongji['allrebate'];
		$tongji['payable_price'] = ($rebate['payable_price'] > 0) ? $rebate['payable_price'] : '';

		$rebate['caozuo'] = getRebateDo($rebate['status'],$rebate['rebate_id'],'rebate_view');
		$smarty->assign('allmoney',   $tongji);

		//商家店铺信息
		$sql = "select smr.*,sm.store_name from ".$ecs->table('store_main_rebate')." as smr left join ".$ecs->table('store_main')." as sm on smr.store_id=sm.store_id where smr.store_id='$rebate[store_id]'";
		$supplier =$db->getRow($sql);
		if (!empty($supplier))
		{
			$supplier['province'] = $db->getOne("select region_name from ". $ecs->table('region') ." where region_id='$supplier[province]' ");
			$supplier['city'] = $db->getOne("select region_name from ". $ecs->table('region') ." where region_id='$supplier[city]' ");
			$supplier['district'] = $db->getOne("select region_name from ". $ecs->table('region') ." where region_id='$supplier[district]' ");
			$supplier['userinfo'] = $db->getAll("select admin_name from ".$ecs->table('store_adminer')." where store_id='$rebate[store_id]'");
		}

		//佣金操作日志
		$sql = "select * from ".$ecs->table('store_rebate_log')." where rebateid=".$rebate['rebate_id']." and type=".REBATE_LOG_LIST." order by logid desc";
		$logs = array();
		$query = $db->query($sql);
		while($row = $db->fetchRow($query)){
			$row['addtime_dec'] = local_date('Y-m-d H:i', $row['addtime']);
			$logs[$row['logid']] = $row;
		}

		$smarty->assign('logs',   $logs);
	}

	$smarty->assign('rebate', $rebate);
	$smarty->assign('supplier', $supplier);

	$smarty->assign('isqueren',haveDoQueRen($rebate['store_id'],$_SESSION['admin_id']));

	 $smarty->assign('ur_here', '佣金详细信息');
	 $is_pay_ok = $rebate['is_pay_ok'];
	 $lang_rebate_list = $rebate['is_pay_ok'] ? $_LANG['03_rebate_pay'] : $_LANG['03_rebate_nopay'];
	 $href_rebate_list  =  "supplier_store_rebate.php?act=list&is_pay_ok=$is_pay_ok";
     $smarty->assign('action_link', array('href' => $href_rebate_list, 'text' =>$lang_rebate_list ));

	assign_query_info();
    $smarty->display('store_rebate_view.htm');
}



/*------------------------------------------------------ */
//-- 发起结算操作
/*------------------------------------------------------ */
elseif ($act=='update')
{
    /* 检查权限 */
    admin_priv('store_rebate'); 

	$rebate_all = (isset($_POST['rebate_all']) && floatval($_POST['rebate_all']) > 0) ? floatval($_POST['rebate_all']) : 0;
	$rebate_money = (isset($_POST['rebate_money']) && floatval($_POST['rebate_money']) > 0) ? floatval($_POST['rebate_money']) : 0;
	$remark = (isset($_POST['remark'])) ? addslashes($_POST['remark']) : '';

	if($rebate_all<=0){
		sys_msg('请调整授权调整货款！');
	}
	if($rebate_money<=0){
		sys_msg('请调整授权调整佣金！');
	}

   /* 提交值 */
   $rebate_id =  intval($_POST['id']);
   $payable_price = $rebate_all - $rebate_money;
   if (($rebate = rebateHave($rebate_id)) === false)
    {
          sys_msg('该返佣记录不存在！');
    }
   $rebate = array(
		'remark'   => $remark,
		'rebate_all'   => $rebate_all,
		'rebate_money'   => $rebate_money,
	    'payable_price' => $payable_price,
		'status'	=> 2
   );

	/* 保存返佣信息 */
	$db->autoExecute($ecs->table('store_rebate'), $rebate, 'UPDATE', "rebate_id = '" . $rebate_id . "'");

	//修改佣金信息状态记录
		$rebate_list = array(
				'rebateid' => $rebate_id,
				'username' => '平台方:'.$_SESSION['user_name'],
				'type' => REBATE_LOG_LIST,
				'typedec' => '发起分销商(仓库)结算',
				'contents' => '佣金状态由可结算变等待审核',
				'addtime' => gmtime()
		);
		$db->autoExecute($ecs->table('store_rebate_log'), $rebate_list, 'INSERT');
	 /* 清除缓存 */
	clear_cache_files();

	/* 提示信息 */
	$links[] = array('href' => 'supplier_store_rebate.php?act=list' , 'text' => '返回本期佣金列表');
	sys_msg('恭喜，处理成功！', 0, $links);    

}

/*------------------------------------------------------ */
//-- 取消结算操作
/*------------------------------------------------------ */
elseif ($act=='cancel')
{
	 /* 检查权限 */
    admin_priv('store_rebate');
	$rebate_id =  intval($_POST['id']);
   if (($rebate = rebateHave($rebate_id)) === false)
    {
          sys_msg('该返佣记录不存在！');
    }

	$rebate = array(
		'remark'   => '',
		'rebate_all'   => 0.00,
		'rebate_money'   => 0.00,
		'payable_price' => 0.00,
		'status'	=> 1
   );

	/* 保存返佣信息 */
	$db->autoExecute($ecs->table('store_rebate'), $rebate, 'UPDATE', "rebate_id = '" . $rebate_id . "'");

	//修改佣金信息状态记录
		$rebate_list = array(
				'rebateid' => $rebate_id,
				'username' => '平台方:'.$_SESSION['user_name'],
				'type' => REBATE_LOG_LIST,
				'typedec' => '取消发起分销商(仓库)结算',
				'contents' => '佣金状态由等待审核变可结算',
				'addtime' => gmtime()
		);
		$db->autoExecute($ecs->table('store_rebate_log'), $rebate_list, 'INSERT');
	 /* 清除缓存 */
	clear_cache_files();

	/* 提示信息 */
	$links[] = array('href' => 'supplier_store_rebate.php?act=list' , 'text' => '返回本期佣金列表');
	sys_msg('恭喜，处理成功！', 0, $links);    

}

//仓库方确认审核
elseif ($act=='queren')
{
    /* 检查权限 */
    admin_priv('store_rebate'); 

   /* 提交值 */
   $rebate_id =  intval($_POST['id']);
   if (($rebate = rebateHave($rebate_id)) === false)
    {
          sys_msg('该返佣记录不存在！');
    }
	if(haveDoQueRen($rebate['store_id'],$_SESSION['admin_id']) == false){
		sys_msg('你没有权限！');
	}
   $rebate = array(
		'status'	=> 3
   );

	/* 保存返佣信息 */
	$db->autoExecute($ecs->table('store_rebate'), $rebate, 'UPDATE', "rebate_id = '" . $rebate_id . "'");

	//修改佣金信息状态记录
		$rebate_list = array(
				'rebateid' => $rebate_id,
				'username' => '平台方:'.$_SESSION['user_name'],
				'type' => REBATE_LOG_LIST,
				'typedec' => '确认通过',
				'contents' => '佣金状态由等待审核变等待平台方付款',
				'addtime' => gmtime()
		);
		$db->autoExecute($ecs->table('store_rebate_log'), $rebate_list, 'INSERT');
	 /* 清除缓存 */
	clear_cache_files();

	/* 提示信息 */
	$links[] = array('href' => 'supplier_store_rebate.php?act=list' , 'text' => '返回本期佣金列表');
	sys_msg('恭喜，处理成功！', 0, $links);    

}


/*------------------------------------------------------ */
//-- 平台方付款操作
/*------------------------------------------------------ */
elseif ($act=='finish')
{
    /* 检查权限 */
    admin_priv('store_rebate'); 

   /* 提交值 */
   $rebate_id =  intval($_POST['id']);
   $remark = (isset($_POST['remark'])) ? addslashes($_POST['remark']) : '';
   if (($rebates = rebateHave($rebate_id)) === false)
    {
          sys_msg('该返佣记录不存在！');
    }

	include_once(ROOT_PATH . '/includes/cls_image.php');
	$image = new cls_image($_CFG['bgcolor']);

	if($_FILES['rebate_img']['size']<=0){
		 sys_msg('汇票凭证必须上传！');
	}
	if ($_FILES['rebate_img']['error'] == 0)
	{
		if (!$image->check_img_type($_FILES['rebate_img']['type']))
		{
			sys_msg($_LANG['invalid_goods_thumb'], 1, array(), false);
		}
	}
	$dir = 'store_rebate/'.local_date("Ymd",gmtime()).'/'.$rebates['store_id'];
	$rebate_img   = $image->upload_image($_FILES['rebate_img'],$dir); 

   $rebate = array(
	    'is_pay_ok' => 1,
	    'pay_time'  => gmtime(),
	    'rebate_img' => $rebate_img,
		'status'	=> 4
   );

	/* 保存返佣信息 */
	$db->autoExecute($ecs->table('store_rebate'), $rebate, 'UPDATE', "rebate_id = '" . $rebate_id . "'");
	
	$loginfo = array(
		'rebateid'=>$rebate_id,
		'addtime'=>$addtime,
		'reason'=>'平台方佣金'.createSign($rebates['rebate_id'],$rebates['store_id']).'转帐：'.$rebates['payable_price'],
		'supplier_money'=>$rebates['payable_price'],
		'doman'=>'平台方:'.$_SESSION['user_name'],
		'supplier_id'=>0,
		'store_id'=>$rebates['store_id']
	);
	$db->autoExecute($ecs->table('store_money_log'), $loginfo, 'INSERT');
	$db->query('update '.$ecs->table('store_main_rebate')." set store_money = store_money + ".$rebates['payable_price']." where store_id=".$rebates['store_id']);

	//修改佣金信息状态记录
		$rebate_list = array(
				'rebateid' => $rebate_id,
				'username' => '平台方:'.$_SESSION['user_name'],
				'type' => REBATE_LOG_LIST,
				'typedec' => '平台方付款分销商(仓库)',
				'contents' => '佣金状态由等待付款变结算完成',
				'addtime' => gmtime()
		);
		$db->autoExecute($ecs->table('store_rebate_log'), $rebate_list, 'INSERT');
	 /* 清除缓存 */
	clear_cache_files();

	/* 提示信息 */
	$links[] = array('href' => 'supplier_store_rebate.php?act=list' , 'text' => '返回本期佣金列表');
	sys_msg('恭喜，处理成功！', 0, $links);    
}

/*------------------------------------------------------ */
//-- 已经完成的佣金做备注
/*------------------------------------------------------ */
elseif ($act=='beizhu')
{
	/* 检查权限 */
    admin_priv('store_rebate');
	$rebate_id =  intval($_POST['id']);
    $remark = (isset($_POST['remark'])) ? addslashes($_POST['remark']) : '';
	$rebate = array(
	    'remark' => $remark
   );

	/* 保存返佣信息 */
	$db->autoExecute($ecs->table('store_rebate'), $rebate, 'UPDATE', "rebate_id = '" . $rebate_id . "'");
	/* 清除缓存 */
	clear_cache_files();

	/* 提示信息 */
	$links[] = array('href' => 'supplier_store_rebate.php?act=list&is_pay_ok=1' , 'text' => '返回往期佣金列表');
	sys_msg('恭喜，处理成功！', 0, $links);  
}

/**
 *  获取供应商列表信息
 *
 * @access  public
 * @param
 *
 * @return void
 */
function rebate_list($act='')
{
    $result = get_filter();
    if ($result === false)
    {
        //$aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;

		$where_store = '';
		if(isAdminUser()){
			$where_store = '';
		}else{
			$store_ids = loginUserStore($_SESSION['admin_id']);
			if(count($store_ids)>0){
				$store_ids = array_unique($store_ids);
				$where_store = ' and sr.store_id in('.implode(',',$store_ids).') ';
			}
		}



        /* 过滤信息 */
        $filter['rebate_paytime_start'] = !empty($_REQUEST['rebate_paytime_start']) ? local_strtotime($_REQUEST['rebate_paytime_start']) : 0;
		$filter['rebate_paytime_end'] = !empty($_REQUEST['rebate_paytime_end']) ? local_strtotime($_REQUEST['rebate_paytime_end']." 23:59:59") : 0;
		$filter['status'] = (isset($_REQUEST['status'])) ? intval($_REQUEST['status']) : -1;
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? ' sr.supplier_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? ' ASC' : trim($_REQUEST['sort_order']);
		$filter['is_pay_ok'] = empty($_REQUEST['is_pay_ok']) ? '0' : intval($_REQUEST['is_pay_ok']);
		$filter['actname'] = empty($act) ? trim($_REQUEST['act']) : $act;
       
        //$where = 'WHERE sr.supplier_id=0 '.$where_store;
		$where = 'where 1 '.$where_store;
        $where .= $filter['rebate_paytime_start'] ? " AND sr.rebate_paytime_start >= '". $filter['rebate_paytime_start']. "' " :  " ";
		$where .= $filter['rebate_paytime_end'] ? " AND sr.rebate_paytime_end <= '". $filter['rebate_paytime_end']. "' " :  " ";
		$where .= $filter['is_pay_ok'] ? " AND sr.is_pay_ok = '". $filter['is_pay_ok']. "' " :  " AND sr.is_pay_ok = '0' ";
		$where .= ($filter['status'] > -1) ? " AND sr.status = '". $filter['status']. "' " :  " ";

        /* 分页大小 */
        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
        {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        }
        elseif (isset($_COOKIE['ECSCP']['page_size']) && intval($_COOKIE['ECSCP']['page_size']) > 0)
        {
            $filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
        }
        else
        {
            $filter['page_size'] = 15;
        }

        /* 记录总数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('store_rebate') ." AS sr  " . $where;
        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT sr.*,s.store_name, s.store_id,s.supplier_id, smr.rebate, ifnull(ss.supplier_name,'平台方') as sname ".
                "FROM " . $GLOBALS['ecs']->table("store_rebate") . " AS  sr left join " .$GLOBALS['ecs']->table("store_main") .  " AS s on sr.store_id=s.store_id left join ".$GLOBALS['ecs']->table("store_main_rebate")." as smr on s.store_id = smr.store_id left join ".$GLOBALS['ecs']->table("supplier")." as ss on sr.supplier_id=ss.supplier_id  
                $where
                ORDER BY " . $filter['sort_by'] . " " . $filter['sort_order']. "
                LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ", " . $filter['page_size'] . " ";
        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

	$list=array();
	$res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
	{
		$row['sign'] = createSign($row['rebate_id'],$row['store_id']);
		$row['rebate_paytime_start'] = local_date('Y.m.d', $row['rebate_paytime_start']);
		$endtime = $row['rebate_paytime_end'];//+$GLOBALS['_CFG']['tuihuan_days_qianshou']*3600*24;
		$row['rebate_paytime_end'] = local_date('Y.m.d', $endtime);
		//$row['all_money'] = $GLOBALS['db']->getOne("select sum(money_paid + surplus) from ". $GLOBALS['ecs']->table('order_info') ." where rebate_id=". $row['rebate_id'] ." and rebate_ispay=2");
		$row['all_money'] = $GLOBALS['db']->getOne("select sum(" . order_amount_field() . ") from ". $GLOBALS['ecs']->table('order_info') ." where store_rebate_id=". $row['rebate_id'] ." and store_rebate_ispay=2");
		$row['all_money_formated'] = price_format($row['all_money']);
		$row['rebate_money'] = ($row['all_money']>0) ? getGoodsRbatePrice($row['rebate_id']) : 0;//
		$row['rebate_money'] = round(($row['rebate_money'] * $row['rebate'])/100, 2);
		$row['rebate_money_formated'] =  price_format($row['rebate_money']);
		$row['pay_money'] = $row['all_money'] - $row['rebate_money'];
		$row['pay_money_formated'] = price_format($row['pay_money']);
		$row['pay_status'] = $row['is_pay_ok'] ? "已处理，已返佣" : "未处理";
		$row['pay_time'] = local_date('Y.m.d', $row['pay_time']);
		$row['user'] = $_SESSION['user_name'];
		$row['payable_price'] = price_format($row['payable_price']);
		$row['status_name'] = rebateStatus($row['status']);
		$row['caozuo'] = getRebateDo($row['status'],$row['rebate_id'],$filter['actname']);
		$list[]=$row;
	}
    $arr = array('result' => $list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}
//获取当前登陆人有几个仓库的管理权限
function loginUserStore($admin_id){
	global $db,$ecs;

	$sql = "select store_id from ".$ecs->table('store_adminer')." where admin_id=".$admin_id." and supplier_id=0";

	$query = $db->query($sql); 

	$ret = array();
	while($row = $db->fetchRow($query)){
		$ret[] = $row['store_id'];
	}
	return $ret;
}
?>