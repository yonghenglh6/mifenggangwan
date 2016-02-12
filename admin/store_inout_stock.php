<?php

/**
 * ECSHOP 即时库存查询 程序文件
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

require_once(ROOT_PATH . 'includes/cls_image.php');

/*初始化数据交换对象 */
$exc   = new exchange($ecs->table("store_inout_list"), $db, 'rec_id', 'inout_sn');

/*  ajax获取商品信息  */
if ($_REQUEST['act'] == 'get_goodsInfo_bysn')
{
	require(ROOT_PATH . 'includes/cls_json.php');
	$opt['cuowu']=0;
	$goods_sn   = empty($_GET['goods_sn']) ? 0 : trim($_GET['goods_sn']);
    $sql = "select goods_id, goods_name from ". $ecs->table('goods') ." where goods_sn= '$goods_sn' ";
    $goodsinfo = $db->getRow($sql);
    if ($goodsinfo['goods_id']>0)
	{
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
			$optionss .= $aval['attr_name'].'<select name="attr['. $akey .']">';
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
		$opt['cuowu']=1;		
	}
    
	

    $json = new JSON;
	echo $json->encode($opt);
}

/*------------------------------------------------------ */
//-- 序时簿列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
	admin_priv('store_inout_stock');
    /* 取得过滤条件 */
    $filter = array();
    $smarty->assign('ur_here',      $_LANG['04_store_inout_stock']);
    $smarty->assign('full_page',    1);
    $smarty->assign('filter',       $filter);
	$smarty->assign('showck',(isset($_REQUEST['sid']) ? 1 : 0));

	/* 入库类型 */
	$sql = "select type_id, type_name from ". $ecs->table('store_inout_type') ." where in_out=2 and store_type_id=0 order by type_id asc";
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


    $stock_list = get_stocklist();

    $smarty->assign('stock_list',    $stock_list['arr']);
    $smarty->assign('filter',          $stock_list['filter']);
    $smarty->assign('record_count',    $stock_list['record_count']);
    $smarty->assign('page_count',      $stock_list['page_count']);

    $sort_flag  = sort_flag($inout_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    assign_query_info();
    $smarty->display('store_inout_stock.htm');
}

/*------------------------------------------------------ */
//-- 翻页，排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    check_authz_json('store_inout_stock');

    $stock_list = get_stocklist();

    $smarty->assign('stock_list',    $stock_list['arr']);
    $smarty->assign('filter',          $stock_list['filter']);
    $smarty->assign('record_count',    $stock_list['record_count']);
    $smarty->assign('page_count',      $stock_list['page_count']);

    $sort_flag  = sort_flag($stock_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('store_inout_stock.htm'), '',
        array('filter' => $stock_list['filter'], 'page_count' => $stock_list['page_count']));
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
	$sql = "select type_id, type_name from ". $ecs->table('store_inout_type') ." where in_out=2 and store_type_id=0 order by type_id asc";
	$inout_type_list = $db->getAll($sql);
	$smarty->assign('inout_type_list', $inout_type_list);

    /* 取得仓库 */
	$sql="select store_id, store_name from ". $ecs->table('store_main') ." where store_type_id=0 and parent_id=0 ";
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
//-- 导出仓库商品信息
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'export_goods')
{
	include_once('includes/cls_phpzip.php');
    $zip = new PHPZip;
	$csvtitle_store_goods = array('商品图片', '商品ID', '商品货号', '商品名称', '商品属性', '商品属性ID', '应收数量', '实收数量');
	$content = '"' . implode('","', $csvtitle_store_goods) . "\"\n";

	/* 取得要操作的商品编号 */
    $export_info = !empty($_POST['checkboxes']) ? array_unique(array_filter($_POST['checkboxes'])) : 0;
	if($export_info){
		foreach($export_info as $key => $val){
			$valInfo = explode('|',$val);
			$goodId = intval($valInfo[0]);
			$storeId = intval($valInfo[1]);
			$goods_value = array();
			$sql = "select sgs.goods_id, s.goods_sn, s.goods_name, s.goods_thumb,sgs.goods_attr,sgs.store_number from ". $ecs->table('store_goods_stock') ." as sgs left join ".
				$ecs->table('goods')." as s on sgs.goods_id=s.goods_id where sgs.goods_id = ".$goodId." and sgs.store_id =".$storeId;
			$res = $db->query($sql);
			while ($row=$db->fetchRow($res))
			{
				$goods_value['goods_img'] = '"'.$row['goods_thumb'].'"';
				$goods_value['goods_id'] = '"'. $row['goods_id'] . '"';
				$goods_value['goods_sn'] = '"' . $row['goods_sn'] . '"';
				$goods_value['goods_name'] = '"' . $row['goods_name'] . '"';

				$goods_attr = trim($row['goods_attr']);
				if(empty($goods_attr)){
					$goods_value['goods_attr_item'] = '"  "';
				}else{
					$attr_item = str_replace("|",',',$goods_attr);
					$ssql = "SELECT a.attr_name, ga.attr_value
							FROM ".$ecs->table('goods_attr')." AS ga, ".$ecs->table('attribute')." AS a
							WHERE ga.attr_id = a.attr_id
							AND ga.goods_id =".$row['goods_id']."
							AND ga.goods_attr_id
							IN (".$goods_attr.")";
					$rett = $db->query($ssql);
					$value = array();
					while($r = $db->fetchRow($rett)){
						$value[] = $r['attr_name'].":".$r['attr_value'];
					}
					$goods_value['goods_attr_item'] = '"' .implode('\n',$value). '"';
				}
				
				$goods_value['goods_attr_value'] = '"' . $goods_attr . '"';
				$goods_value['num_shishou'] = '"' . $row['store_number'] . '"';
				$goods_value['num_yingshou'] = '"' . $row['store_number'] . '"';
				$content .= implode(",", $goods_value) . "\n";

			}
		}
	}
	$zip->add_file(ecs_iconv(EC_CHARSET, 'GBK', $content), 'goods_getout.csv');
	header("Content-Disposition: attachment; filename=goods_getout.zip");
	header("Content-Type: application/unknown");
	die($zip->file());
	
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

/* 获得库存列表 */
function get_stocklist()
{
    $result = get_filter();
    if ($result === false)
    {
        $filter = array();
		$filter['sid']    = empty($_REQUEST['sid']) ? '0' : intval($_REQUEST['sid']);
		$filter['ssid']    = empty($_REQUEST['ssid']) ? '0' : intval($_REQUEST['ssid']);
        $filter['goods_sn']    = empty($_REQUEST['goods_sn']) ? '' : trim($_REQUEST['goods_sn']);
        $filter['goods_name'] = empty($_REQUEST['goods_name']) ? '' : trim($_REQUEST['goods_name']);
        $filter['sort_by']    = empty($_REQUEST['sort_by']) ? 's.goods_id' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = ' AND store_type_id=0 ';
      
        if (!empty($filter['goods_sn']))
        {  
			$sql1="select goods_id from ". $GLOBALS['ecs']->table('goods') ." where goods_sn like '%$filter[goods_sn]%' ";
			$goods_id1= $GLOBALS['db']->getCol($sql1);
			if(count($goods_id1)>0){
				$where .= " AND goods_id in (".implode(',',$goods_id1).")";
			}else{
				$where .= " AND 1!=1 ";
			}
			
        }
		elseif (!empty($filter['goods_name']))
        {
			$sql1="select goods_id from ". $GLOBALS['ecs']->table('goods') ." where goods_name LIKE '%" . mysql_like_quote($filter['goods_name']) . "%' ";
			$res_goods= $GLOBALS['db']->query($sql1);
			$goods_id="0";
			while ($row_goods=$GLOBALS['db']->fetchRow($res_goods))
			{
					$goods_id .= ",".$row_goods['goods_id'];
			}
			$where .= " AND goods_id in ($goods_id)";
        }

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

        /* 库存总数 */
        $sql = 'select count(*) from (select count(*) from ' .$GLOBALS['ecs']->table('store_goods_stock'). 			   
               ' WHERE 1 ' .$where. " group by goods_id,store_id) AS countTemp  ";
        $filter['record_count'] = $GLOBALS['db']->getOne($sql);

        $filter = page_and_size($filter);

        /* 获取库存数据 */
        $sql = 'SELECT stock_id, goods_id, store_id  '.
               'FROM ' .$GLOBALS['ecs']->table('store_goods_stock').
               ' WHERE 1 ' .$where. ' group by goods_id,store_id  ORDER BY  NULL ';

        $filter['goods_name'] = stripslashes($filter['goods_name']);
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
		$sql="select  goods_name, goods_sn, goods_thumb from ". $GLOBALS['ecs']->table('goods') ."  where goods_id='$rows[goods_id]' ";
		$goods_row = $GLOBALS['db']->getRow($sql);
		$rows['goods_thumb'] = get_image_path($rows['goods_id'], $goods_row['goods_thumb'], true);
		$rows['goods_name'] = $goods_row['goods_name'];
		$rows['goods_sn'] = $goods_row['goods_sn'];
		$rows['store_name'] = get_store_fullname($rows['store_id']);
		$rows['attr_stock'] =  get_attr_stock($rows['goods_id'], $rows['store_id']);
        $arr[] = $rows;
    }
    return array('arr' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
}



function get_attr_stock($goods_id, $store_id)
{
	$sql = "select goods_attr, store_number from ". $GLOBALS['ecs']->table('store_goods_stock') ."  where goods_id= '$goods_id' and store_id= '$store_id' ";
	$res=$GLOBALS['db']->query($sql);
	$arr=array();
	while ($row=$GLOBALS['db']->fetchRow($res))
	{
		$row['goods_attr_name'] = get_attr_name($row['goods_attr']);
		$row['store_number'] =   $row['store_number'] ? $row['store_number'] : '0';
		$arr[]=$row; 
	}
	return $arr;
}






?>