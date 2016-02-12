<?php

/**
 * ECSHOP 管理中心运费模板管理
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

$exc = new exchange($ecs->table("store_shipping_demo"), $db, 'demo_id', 'shipping_id');

/*------------------------------------------------------ */
//-- 品牌列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    $smarty->assign('ur_here',      $_LANG['02_store_shipping_demo']);
    $smarty->assign('action_link',  array('text' => $_LANG['demo_add'], 'href' => 'store_shipping_demo.php?act=add'));
    $smarty->assign('full_page',    1);

    $demo_list = get_demolist();

    $smarty->assign('demo_list',   $demo_list['list']);
    $smarty->assign('filter',       $demo_list['filter']);
    $smarty->assign('record_count', $demo_list['record_count']);
    $smarty->assign('page_count',   $demo_list['page_count']);

    assign_query_info();
    $smarty->display('store_shipping_demo_list.htm');
}

/*------------------------------------------------------ */
//-- 添加模板
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    /* 权限判断 */
    admin_priv('store_shipping_demo');

    $smarty->assign('ur_here',     $_LANG['demo_add']);
    $smarty->assign('action_link', array('text' => $_LANG['02_store_shipping_demo'], 'href' => 'store_shipping_demo.php?act=list'));
    $smarty->assign('form_action', 'insert');

	$sql="select shipping_id, shipping_code, shipping_name from ". $ecs->table('shipping') ." where enabled=1 ";
	$shipping_list=$db->getAll($sql);
	$smarty->assign('shipping_list', $shipping_list);

    assign_query_info();
    $smarty->assign('demo', array('sort_order'=>50, 'is_show'=>1));
    $smarty->display('store_shipping_demo_info.htm');
}

/* 保存模板 */
elseif ($_REQUEST['act'] == 'insert')
{
    admin_priv('store_shipping_demo');

    /*插入数据*/
    $shipping_id = $_REQUEST['shipping_id'] ? intval($_REQUEST['shipping_id']) : 0;
	$shipping_name =$db->getOne("select shipping_name from ". $ecs->table('shipping') ." where shipping_id='$shipping_id' ");
	//$fee_compute_mode = $_REQUEST['fee_compute_mode'] ? trim($_REQUEST['fee_compute_mode']) : 'by_weight';
	//$base_fee= $_REQUEST['base_fee'] ? intval($_REQUEST['base_fee']) : 0;
	//$step_fee = $_REQUEST['step_fee'] ? intval($_REQUEST['step_fee']) : 0;
	$free_money = $_REQUEST['free_money'] ? floatval($_REQUEST['free_money']) : 0;
	//$item_fee = $_REQUEST['item_fee'] ? intval($_REQUEST['item_fee']) : 0;
	$shipping_fee = $_REQUEST['shipping_fee'] ? floatval($_REQUEST['shipping_fee']) : 0;
	$configure =  serialize(array('shipping_fee'=>$shipping_fee,'free_money'=>$free_money));

    $sql = "INSERT INTO ".$ecs->table('store_shipping_demo')."(shipping_id, shipping_name, configure) ".
           "VALUES ('$shipping_id', '$shipping_name', '$configure')";
    $db->query($sql);

    /* 清除缓存 */
    clear_cache_files();

    $link[0]['text'] = $_LANG['continue_add'];
    $link[0]['href'] = 'store_shipping_demo.php?act=add';

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'store_shipping_demo.php?act=list';

    sys_msg($_LANG['brandadd_succed'], 0, $link);
}

/*------------------------------------------------------ */
//-- 编辑模板
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit')
{
    /* 权限判断 */
    admin_priv('store_shipping_demo');
    $sql = "SELECT * ".
            "FROM " .$ecs->table('store_shipping_demo'). " WHERE demo_id='$_REQUEST[id]'";
    $demo = $db->GetRow($sql);

	$demo['configure'] = unserialize($demo['configure']);

	$sql="select shipping_id, shipping_code, shipping_name from ". $ecs->table('shipping') ." where enabled=1 ";
	$shipping_list=$db->getAll($sql);
	$smarty->assign('shipping_list', $shipping_list);

    $smarty->assign('ur_here',     $_LANG['demo_edit']);
    $smarty->assign('action_link', array('text' => $_LANG['back_list'], 'href' => 'store_shipping_demo.php?act=list&' . list_link_postfix()));
    $smarty->assign('demo',       $demo);
    $smarty->assign('form_action', 'updata');	

    assign_query_info();
    $smarty->display('store_shipping_demo_info.htm');
}

/* 更新模板 */
elseif ($_REQUEST['act'] == 'updata')
{
    admin_priv('store_shipping_demo');
   
	/*检查模板是否相同*/

    $shipping_id = $_REQUEST['shipping_id'] ? intval($_REQUEST['shipping_id']) : 0;
	$shipping_name =$db->getOne("select shipping_name from ". $ecs->table('shipping') ." where shipping_id='$shipping_id' ");
	//$fee_compute_mode = $_REQUEST['fee_compute_mode'] ? trim($_REQUEST['fee_compute_mode']) : 'by_weight';
	//$base_fee= $_REQUEST['base_fee'] ? intval($_REQUEST['base_fee']) : 0;
	//$step_fee = $_REQUEST['step_fee'] ? intval($_REQUEST['step_fee']) : 0;
	$free_money = $_REQUEST['free_money'] ? floatval($_REQUEST['free_money']) : 0;
	//$item_fee = $_REQUEST['item_fee'] ? intval($_REQUEST['item_fee']) : 0;
	$shipping_fee = $_REQUEST['shipping_fee'] ? floatval($_REQUEST['shipping_fee']) : 0;
	$configure =  serialize(array('shipping_fee'=>$shipping_fee,'free_money'=>$free_money));

    $param = "shipping_id='$shipping_id', shipping_name = '$shipping_name', configure='$configure' ";
    if ($exc->edit($param,  $_POST['id']))
    {
        /* 清除缓存 */
        clear_cache_files();

        $link[0]['text'] = $_LANG['back_list'];
        $link[0]['href'] = 'store_shipping_demo.php?act=list&' . list_link_postfix();
        sys_msg($_LANG['demoedit_succed'], 0, $link);
    }
    else
    {
        die($db->error());
    }
}


/*------------------------------------------------------ */
//-- 删除模板
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('store_shipping_demo');

    $id = intval($_GET['id']);

    $exc->drop($id);

    $url = 'store_shipping_demo.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}



/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $demo_list = get_demolist();
    $smarty->assign('demo_list',   $demo_list['list']);
    $smarty->assign('filter',       $demo_list['filter']);
    $smarty->assign('record_count', $demo_list['record_count']);
    $smarty->assign('page_count',   $demo_list['page_count']);

    make_json_result($smarty->fetch('store_shipping_demo_list.htm'), '',
        array('filter' => $demo_list['filter'], 'page_count' => $demo_list['page_count']));
}

/**
 * 获取模板列表
 *
 * @access  public
 * @return  array
 */
function get_demolist()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 分页大小 */
        $filter = array();

        /* 记录总数以及页数 */        
        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('store_shipping_demo')." WHERE store_type_id=0";

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter = page_and_size($filter);

        /* 查询记录 */
       $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('store_shipping_demo')." WHERE store_type_id=0 ORDER BY demo_id DESC";       

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
    $res = $GLOBALS['db']->selectLimit($sql, $filter['page_size'], $filter['start']);

    $arr = array();
    while ($rows = $GLOBALS['db']->fetchRow($res))
    {
		$rows['configure'] = unserialize($rows['configure']);
		//$rows['demo_name'] = $rows['shipping_name'].($rows['fee_compute_mode']=='by_number' ? '按商品件数' : '按重量'). $rows['base_fee'].'元';
		//$rows['compute_mode_name'] =  $rows['fee_compute_mode']=='by_number' ? '按商品件数' : '按重量';
		//$rows['fee_desc'] = $rows['fee_compute_mode']=='by_number' ? ('单件商品费用：'.$rows['item_fee'].'元') : ('1000克以内费用：'.$rows['base_fee'].'元，续重每1000克或其零数的费用：'.$rows['step_fee'].'元');
		$rows['fee_desc'] ='不足免费额度运费需支付'.$rows['configure']['shipping_fee'].'元';
		$rows['fee_desc'] .='，免费额度：'.$rows['configure']['free_money'].'元';
        $arr[] = $rows;
    }

    return array('list' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>
