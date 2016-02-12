<?php

/**
 * ECSHOP 出入库序时薄 程序文件
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

require_once(ROOT_PATH . 'includes/cls_image.php');

/*初始化数据交换对象 */
$exc   = new exchange($ecs->table("store_inout_list"), $db, 'rec_id', 'inout_sn');

/*  ajax获取出入库类型  */
if ($_REQUEST['act'] == 'get_inout_type')
{
	require(ROOT_PATH . 'includes/cls_json.php');
	$opt['cuowu']=0;
	$inout_mode   = empty($_GET['inout_mode']) ? 0 : intval($_GET['inout_mode']);
	$target   = empty($_GET['target']) ? '' : trim($_GET['target']);
	if($inout_mode=='1')
	{
		$opt['inout_mode'] ='出库类型';
	}
	elseif($inout_mode=='2')
	{
		$opt['inout_mode'] ='入库类型';
	}
	else
	{
		$opt['inout_mode'] ='出入库类型';
	}
	$opt['target']=$target;
    $sql = "select type_id, type_name from ". $ecs->table('store_inout_type') ." where is_valid ='1' AND in_out= '$inout_mode' order by type_id asc ";
    $restype = $db->getAll($sql);
	$opt['type_list']=$restype;
    $json = new JSON;
	echo $json->encode($opt);
}

/*------------------------------------------------------ */
//-- 序时簿列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
	admin_priv('store_inout_goods');
    /* 取得过滤条件 */
    $filter = array();
    $smarty->assign('ur_here',      $_LANG['03_store_inout_goods']);
    $smarty->assign('full_page',    1);
    $smarty->assign('filter',       $filter);
	$smarty->assign('showck',(isset($_REQUEST['sid']) ? 1 : 0));

	/* 入库类型 */
	$sql = "select type_id, type_name from ". $ecs->table('store_inout_type') ." where in_out=2 and store_type_id=0 order by type_id asc";
	$inout_type_list = $db->getAll($sql);
	$smarty->assign('inout_type_list', $inout_type_list);

	/* 商品品牌 */
	$sql = "select brand_id	, brand_name from ". $ecs->table('brand') ." order by  brand_id ";
	$brand_list = $db->getAll($sql);
	$smarty->assign('brand_list', $brand_list);
    
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


    $inout_goods_list = get_inoutgoods_list();

    $smarty->assign('inout_goods_list',    $inout_goods_list['arr']);
    $smarty->assign('filter',          $inout_goods_list['filter']);
    $smarty->assign('record_count',    $inout_goods_list['record_count']);
    $smarty->assign('page_count',      $inout_goods_list['page_count']);

    $sort_flag  = sort_flag($inout_goods_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('store_inout_goods.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    check_authz_json('store_inout_goods');

    $inout_goods_list = get_inoutgoods_list();

    $smarty->assign('inout_goods_list',    $inout_goods_list['arr']);
    $smarty->assign('filter',          $inout_goods_list['filter']);
    $smarty->assign('record_count',    $inout_goods_list['record_count']);
    $smarty->assign('page_count',      $inout_goods_list['page_count']);

    $sort_flag  = sort_flag($inout_goods_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('store_inout_goods.htm'), '',
        array('filter' => $inout_goods_list['filter'], 'page_count' => $inout_goods_list['page_count']));
}

/*------------------------------------------------------ */
//-- 添加入库单
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'add')
{
    /* 权限判断 */
    admin_priv('goods_manage');

    /*初始化*/
	$inout = array();
	$inout['add_time_date'] = local_date('Y-m-d');
    $inout['add_date'] = local_date('Ymd');

	$sql="select max(today_sn) from ". $ecs->table('store_inout_list') ." where add_date='$inout[add_date]' ";
	$inout_count = $db->getOne($sql);
	$inout_sn = $inout_count ? intval($inout_count + 1) : 1;
	$inout_sn = str_pad($inout_sn, 4, "0", STR_PAD_LEFT);
	$inout_sn =  'rk'.$inout['add_date'] . $inout_sn;
	$inout['inout_sn'] = $inout_sn;

    /* 入库类型 */
	$sql = "select type_id, type_name from ". $ecs->table('store_inout_type') ." where in_out=2 and supplier_id in (-1,0) order by type_id asc";
	$inout_type_list = $db->getAll($sql);
	$smarty->assign('inout_type_list', $inout_type_list);

    /* 取得仓库 */
	$sql="select store_id, store_name from ". $ecs->table('store_main') ." where supplier_id = 0 and parent_id=0 ";
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

/*------------------------------------------------------ */
//-- 插入入库单
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'insert')
{
    /* 权限判断 */
    admin_priv('goods_manage');

    /*插入数据*/
    $add_date = local_date('Ymd');
	$sql="select max(today_sn) from ". $ecs->table('store_inout_list') ." where add_date='$add_date' ";
	$inout_count = $db->getOne($sql);
	$today_sn = $inout_count ? intval($inout_count + 1) : 1;
	$inout_sn = str_pad($today_sn, 4, "0", STR_PAD_LEFT);
	$inout_sn =  'rk'.$add_date . $inout_sn;
    
    $add_time = gmtime();
    $sql = "INSERT INTO ".$ecs->table('store_inout_list')."(inout_sn, store_id, inout_type, inout_mode, order_sn,  takegoods_man, ".
                " today_sn, add_date, add_time) ".
            "VALUES ('$inout_sn', '$_POST[sub_id]', '$_POST[inout_type]', '2', '$_POST[order_sn]', '$_POST[takegoods_man]', ".
                " '$today_sn', '$add_date', '$add_time')";
    $db->query($sql);

    /* 处理关联商品 */
    $inout_rec_id = $db->insert_id();  //出入库记录ID
    $sql = "";
    //$db->query($sql);

    $link[0]['text'] = $_LANG['continue_add'];
    $link[0]['href'] = 'store_inout_in.php?act=add';

    $link[1]['text'] = $_LANG['back_list_in'];
    $link[1]['href'] = 'store_inout_in.php?act=list';

    clear_cache_files(); // 清除相关的缓存文件

    sys_msg($_LANG['inoutadd_succeed_in'],0, $link);
}

/*------------------------------------------------------ */
//-- 编辑
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'edit')
{
    /* 权限判断 */
    admin_priv('article_manage');

    /* 取文章数据 */
    $sql = "SELECT * FROM " .$ecs->table('article'). " WHERE article_id='$_REQUEST[id]'";
    $article = $db->GetRow($sql);

    /* 创建 html editor */
   create_html_editor('FCKeditor1',htmlspecialchars($article['content'])); /* 修改 by www.68ecshop.com 百度编辑器 */


    /* 取得分类、品牌 */
    $smarty->assign('goods_cat_list', cat_list());
    $smarty->assign('brand_list', get_brand_list());

    /* 取得关联商品 */
    $goods_list = get_article_goods($_REQUEST['id']);
    $smarty->assign('goods_list', $goods_list);

    $smarty->assign('article',     $article);
    $smarty->assign('cat_select',  article_cat_list(0, $article['cat_id']));
    $smarty->assign('ur_here',     $_LANG['article_edit']);
    $smarty->assign('action_link', array('text' => $_LANG['03_article_list'], 'href' => 'article.php?act=list&' . list_link_postfix()));
    $smarty->assign('form_action', 'update');

    assign_query_info();
    $smarty->display('article_info.htm');
}

if ($_REQUEST['act'] =='update')
{
    /* 权限判断 */
    admin_priv('article_manage');

    /*检查文章名是否相同*/
    $is_only = $exc->is_only('title', $_POST['title'], $_POST['id'], "cat_id = '$_POST[article_cat]'");

    if (!$is_only)
    {
        sys_msg(sprintf($_LANG['title_exist'], stripslashes($_POST['title'])), 1);
    }


    if (empty($_POST['cat_id']))
    {
        $_POST['cat_id'] = 0;
    }

    /* 取得文件地址 */
    $file_url = '';
    if (empty($_FILES['file']['error']) || (!isset($_FILES['file']['error']) && isset($_FILES['file']['tmp_name']) && $_FILES['file']['tmp_name'] != 'none'))
    {
        // 检查文件格式
        if (!check_file_type($_FILES['file']['tmp_name'], $_FILES['file']['name'], $allow_file_types))
        {
            sys_msg($_LANG['invalid_file']);
        }

        // 复制文件
        $res = upload_article_file($_FILES['file']);
        if ($res != false)
        {
            $file_url = $res;
        }
    }

    if ($file_url == '')
    {
        $file_url = $_POST['file_url'];
    }

    /* 计算文章打开方式 */
    if ($file_url == '')
    {
        $open_type = 0;
    }
    else
    {
        $open_type = $_POST['FCKeditor1'] == '' ? 1 : 2;
    }

    /* 如果 file_url 跟以前不一样，且原来的文件是本地文件，删除原来的文件 */
    $sql = "SELECT file_url FROM " . $ecs->table('article') . " WHERE article_id = '$_POST[id]'";
    $old_url = $db->getOne($sql);
    if ($old_url != '' && $old_url != $file_url && strpos($old_url, 'http://') === false && strpos($old_url, 'https://') === false)
    {
        @unlink(ROOT_PATH . $old_url);
    }

    if ($exc->edit("title='$_POST[title]', cat_id='$_POST[article_cat]', article_type='$_POST[article_type]', is_open='$_POST[is_open]', author='$_POST[author]', author_email='$_POST[author_email]', keywords ='$_POST[keywords]', file_url ='$file_url', open_type='$open_type', content='$_POST[FCKeditor1]', link='$_POST[link_url]', description = '$_POST[description]'", $_POST['id']))
    {
        $link[0]['text'] = $_LANG['back_list'];
        $link[0]['href'] = 'article.php?act=list&' . list_link_postfix();

        $note = sprintf($_LANG['articleedit_succeed'], stripslashes($_POST['title']));
        admin_log($_POST['title'], 'edit', 'article');

        clear_cache_files();

        sys_msg($note, 0, $link);
    }
    else
    {
        die($db->error());
    }
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
//-- 删除文章主题
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    check_authz_json('article_manage');

    $id = intval($_GET['id']);

    /* 删除原来的文件 */
    $sql = "SELECT file_url FROM " . $ecs->table('article') . " WHERE article_id = '$id'";
    $old_url = $db->getOne($sql);
    if ($old_url != '' && strpos($old_url, 'http://') === false && strpos($old_url, 'https://') === false)
    {
        @unlink(ROOT_PATH . $old_url);
    }

    $name = $exc->get_name($id);
    if ($exc->drop($id))
    {
        $db->query("DELETE FROM " . $ecs->table('comment') . " WHERE " . "comment_type = 1 AND id_value = $id");
        
        admin_log(addslashes($name),'remove','article');
        clear_cache_files();
    }

    $url = 'article.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
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

/* 获得出入库商品明细 */
function get_inoutgoods_list()
{
    $result = get_filter();
    if ($result === false)
    {
        $filter = array();
        $filter['sid']    = empty($_REQUEST['sid']) ? '0' : intval($_REQUEST['sid']);
		$filter['ssid']    = empty($_REQUEST['ssid']) ? '0' : intval($_REQUEST['ssid']);
		$filter['inout_mode']    = empty($_REQUEST['inout_mode']) ? '0' : intval($_REQUEST['inout_mode']);
		$filter['inout_type']    = empty($_REQUEST['inout_type']) ? '0' : intval($_REQUEST['inout_type']);
		$filter['add_time1']    = empty($_REQUEST['add_time1']) ? '' : (strpos($_REQUEST['add_time1'], '-') > 0 ?  local_strtotime($_REQUEST['add_time1']) : $_REQUEST['add_time1']);
		$filter['add_time2']    = empty($_REQUEST['add_time2']) ? '' : (strpos($_REQUEST['add_time2'], '-') > 0 ?  local_strtotime($_REQUEST['add_time2']) : $_REQUEST['add_time2']);
		$filter['brand']    = empty($_REQUEST['brand']) ? '0' : intval($_REQUEST['brand']);
		$filter['goods_sn']    = empty($_REQUEST['goods_sn']) ? '' : trim($_REQUEST['goods_sn']);
		$filter['goods_name']    = empty($_REQUEST['goods_name']) ? '' : trim($_REQUEST['goods_name']);
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 'b.book_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = ' AND b.store_type_id=0 ';
        if ($filter['ssid'])
        {
            $where .= " AND l.store_id = '" . $filter['ssid']."' ";
        }
		else
		{
			if ($filter['sid'])
			{
				$where .= " AND l.store_id in " . get_ssid_list($filter['sid']);
			}
		}	
		if ($filter['inout_type'])
		{
			$where .= " AND l.inout_type = '" . $filter['inout_type']."' ";
		}
		if ($filter['add_time1'])
		{
			$where .= " AND l.add_time>=  '" . $filter['add_time1']."' ";
		}
		if ($filter['add_time2'])
		{
			$where .= " AND l.add_time<=  '" . $filter['add_time2']."' ";
		}
		if ($filter['inout_mode'])
		{
			$where .= " AND b.inout_mode = '" . $filter['inout_mode']."' ";
		}
		if ($filter['goods_sn'])
		{
			$where .= " AND g.goods_sn = '" . $filter['goods_sn']."' ";
		}
		if ($filter['goods_name'])
		{
			$where .= " AND g.goods_name like '%" . $filter['goods_name']."%' ";
		}
		if ($filter['brand'])
		{
			$where .= " AND g.brand_id = '" . $filter['brand']."' ";
		}
		

        /* 总数 */
        $sql = 'SELECT COUNT(*) FROM ' .$GLOBALS['ecs']->table('store_inout_xushibu'). ' AS b '.
			   ' left join '.  $GLOBALS['ecs']->table('store_inout_list') .' AS l on b.inout_rec_id=l.rec_id  '.
				 ' left join '. $GLOBALS['ecs']->table('goods') .' AS g on b.goods_id = g.goods_id '.
               ' WHERE 1 ' .$where;
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 获取入库单数据 */
        $sql = 'SELECT b.book_id, b.attr_value, b.inout_mode, b.number_shishou, b.number_stock, g.goods_id, '.
				'g.goods_name, g.goods_sn, g.goods_thumb, l.store_id, l.inout_sn, l.order_sn, l.inout_type, l.takegoods_man, l.add_time  '.
               'FROM ' .$GLOBALS['ecs']->table('store_inout_xushibu'). ' AS b '.
				' left join '.  $GLOBALS['ecs']->table('store_inout_list') .' AS l on b.inout_rec_id=l.rec_id  '.
			   ' left join '. $GLOBALS['ecs']->table('goods') .' AS g on b.goods_id=g.goods_id '.
               ' WHERE 1 ' .$where. ' ORDER by '.$filter['sort_by'].' '.$filter['sort_order'];

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
		$rows['goods_attr_name'] = get_attr_name($rows['attr_value']);
        $rows['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $rows['add_time']);
		$rows['goods_thumb'] = get_image_path($rows['goods_id'], $rows['goods_thumb'], true);
		$rows['inout_type_name'] =get_inout_type_name($rows['inout_type']);
        $arr[] = $rows;
    }
    return array('arr' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}



?>