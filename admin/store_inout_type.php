<?php

/**
 * ECSHOP 管理中心   出库类型设置
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

$exc = new exchange($ecs->table("store_inout_type"), $db, 'type_id', 'type_name');

/*------------------------------------------------------ */
//-- 类型列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{  

	$in_out=!empty($_REQUEST['in_out']) ? intval($_REQUEST['in_out']) : 0;
    if($in_out == 1){
        admin_priv('store_in_type');
    }else{
        admin_priv('store_out_type');
    }
    $smarty->assign('ur_here',      $_LANG['store_inout_type'.$in_out]);
    $smarty->assign('action_link',  array('text' => $_LANG['type_out_add'.$in_out], 'href' => 'store_inout_type.php?act=add&in_out='.$in_out));
    $smarty->assign('full_page',    1);

    $type_list = get_typelist();

    $smarty->assign('type_list',   $type_list['typelist']);
    $smarty->assign('filter',       $type_list['filter']);
    $smarty->assign('record_count', $type_list['record_count']);
    $smarty->assign('page_count',   $type_list['page_count']);

    assign_query_info();
    $smarty->display('store_inout_type_list.htm');
}

/*------------------------------------------------------ */
//-- 添加类型
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    /* 权限判断 */

	$in_out=!empty($_REQUEST['in_out']) ? intval($_REQUEST['in_out']) : 0;
    if($in_out == 1){
        admin_priv('store_in_type');
    }else{
        admin_priv('store_out_type');
    }
    $smarty->assign('ur_here',     $_LANG['type_out_add'.$in_out]);
	
    $smarty->assign('action_link', array('text' => $_LANG['store_inout_type'.$in_out], 'href' => 'store_inout_type.php?act=list&in_out='.$in_out));
    $smarty->assign('form_action', 'insert');
	$smarty->assign('in_out', $in_out);

    assign_query_info();
    $smarty->assign('type', array('is_valid'=>1));
    $smarty->display('store_inout_type_info.htm');
}

elseif ($_REQUEST['act'] == 'insert')
{
    /*检查类型名是否重复*/
    $is_valid = isset($_REQUEST['is_valid']) ? intval($_REQUEST['is_valid']) : 0;
	$in_out = isset($_REQUEST['in_out']) ? intval($_REQUEST['in_out']) : 0;
    if($in_out == 1){
        admin_priv('store_in_type');
    }else{
        admin_priv('store_out_type');
    }
    $is_only = $exc->is_only('type_name', $_POST['type_name'],0,"in_out=".$in_out." and store_type_id=0");

    if (!$is_only)
    {
        sys_msg(sprintf($_LANG['typename_exist'], stripslashes($_POST['type_name'])), 1);
    }


    /*插入数据*/

    $sql = "INSERT INTO ".$ecs->table('store_inout_type')."(type_name,  is_valid, in_out) ".
				"VALUES ('$_POST[type_name]',  '$is_valid' , '$in_out')";
    $db->query($sql);

    /* 清除缓存 */
    clear_cache_files();

    $link[0]['text'] = $_LANG['continue_add'];
    $link[0]['href'] = 'store_inout_type.php?act=add&in_out='.$in_out;

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'store_inout_type.php?act=list&in_out='.$in_out;

    sys_msg($_LANG['typeadd_succed'], 0, $link);
}

/*------------------------------------------------------ */
//-- 编辑类型
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit')
{
    /* 权限判断 */
    admin_priv('store_in_type');
    admin_priv('store_out_type');
    $sql = "SELECT type_id, type_name, is_valid, in_out ".
            "FROM " .$ecs->table('store_inout_type'). " WHERE type_id='$_REQUEST[id]'";
    $type = $db->GetRow($sql);

    $smarty->assign('ur_here',     $_LANG['type_edit'.$type['in_out']]);
    $smarty->assign('action_link', array('text' => $_LANG['store_inout_type'.$type['in_out']], 'href' => 'store_inout_type.php?act=list&in_out='.$type['in_out'].'&' . list_link_postfix()));
    $smarty->assign('type',       $type);
	$smarty->assign('in_out',       $type['in_out']);
    $smarty->assign('form_action', 'updata');

    assign_query_info();
    $smarty->display('store_inout_type_info.htm');
}
elseif ($_REQUEST['act'] == 'updata')
{
    admin_priv('store_in_type');
    admin_priv('store_out_type');
    if ($_POST['type_name'] != $_POST['old_typename'])
    {
        /*检查品牌名是否相同*/
        $is_only = $exc->is_only('type_name', $_POST['type_name'], $_POST['id']);

        if (!$is_only)
        {
            sys_msg(sprintf($_LANG['typename_exist'], stripslashes($_POST['type_name'])), 1);
        }
    }

    $is_valid = isset($_REQUEST['is_valid']) ? intval($_REQUEST['is_valid']) : 0;
	$in_out = isset($_REQUEST['in_out']) ? intval($_REQUEST['in_out']) : 0;

    $param = "type_name = '$_POST[type_name]',   is_valid='$is_valid'  ";

    if ($exc->edit($param,  $_POST['id']))
    {
        /* 清除缓存 */
        clear_cache_files();

        $link[0]['text'] = $_LANG['back_list'];
        $link[0]['href'] = 'store_inout_type.php?act=list&in_out='.$in_out .'&'. list_link_postfix();
        $note = vsprintf($_LANG['typeedit_succed'], $_POST['type_name']);
        sys_msg($note, 0, $link);
    }
    else
    {
        die($db->error());
    }
}


/*------------------------------------------------------ */
//-- 删除类型
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('store_inout_type');

    $id = intval($_GET['id']);

    $exc->drop($id);

    $url = 'store_inout_type.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $type_list = get_typelist();
    $smarty->assign('type_list',   $type_list['typelist']);
    $smarty->assign('filter',       $type_list['filter']);
    $smarty->assign('record_count', $type_list['record_count']);
    $smarty->assign('page_count',   $type_list['page_count']);

    make_json_result($smarty->fetch('store_inout_type_list.htm'), '',
        array('filter' => $type_list['filter'], 'page_count' => $type_list['page_count']));
}

/**
 * 获取类型列表
 *
 * @access  public
 * @return  array
 */
function get_typelist()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 分页大小 */
        $filter = array();

        $filter['in_out'] = !empty($_REQUEST['in_out']) ? intval($_REQUEST['in_out']) : 1;
		$where =" where store_type_id=0 ";
		$where .= empty($filter['in_out']) ? '' : " AND in_out='$filter[in_out]' ";

        /* 记录总数以及页数 */
     
       $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('store_inout_type') . $where;        

        $filter['record_count'] = $GLOBALS['db']->getOne($sql);
        $filter = page_and_size($filter);

        /* 查询记录 */     
        $sql = "SELECT * FROM ".$GLOBALS['ecs']->table('store_inout_type')." $where  ORDER BY type_id DESC ";        

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
		$rows['is_valid_val'] = $rows['is_valid'] ? '是' : '否';
        $arr[] = $rows;
    }

    return array('typelist' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}

?>
