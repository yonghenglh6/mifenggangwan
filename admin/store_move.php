<?php

/**
 * ECSHOP 转拨程序(yangsong)
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

$act = (isset($_REQUEST['act']) && !empty($_REQUEST['act'])) ? trim($_REQUEST['act']) : 'list';


if($act == 'list'){

	$store = getUserStock($_SESSION['admin_id']);
	$store_info = getStoreId($_SESSION['admin_id']);
	if(count($store_info) > 0){
		$smarty->assign('action_link',  array('text' => $_LANG['store_move_add'], 'href' => 'store_move.php?act=add'));
	}
	$store_str = implode(',',$store_info);
	if(empty($store_str)){
		sys_msg('您当前登陆的用户没有任何仓库管理权限,请先给他一个仓库管理员的身份!');
	}

	$inorout = (isset($_REQUEST['io']) && !empty($_REQUEST['io'])) ? $_REQUEST['io'] : 'out';

	$smarty->assign('ur_here',      $_LANG['move_here_'.$inorout]);
	
	$info = getMoveList($store_str);

	$smarty->assign('store_list',$store);
	$smarty->assign('status',getMoveStatus());
	$smarty->assign('full_page',    1);
	$smarty->assign('info',$info['arr']);
	$smarty->assign('filter',          $info['filter']);
    $smarty->assign('record_count',    $info['record_count']);
    $smarty->assign('page_count',      $info['page_count']);
	assign_query_info();
	$smarty->display('store_move.htm');
}
elseif($act == 'add' || $act == 'edit'){

	$move_id = (isset($_REQUEST['move_id']) && intval($_REQUEST['move_id'])>0) ? intval($_REQUEST['move_id']) : 0;
	$smarty->assign('action_link',  array('text' => $_LANG['store_move_list'], 'href' => 'store_move.php'));
	$smarty->assign('ur_here',      $_LANG['move_here_add_edit']);
	if($move_id > 0){
		$inout = $db->getRow('select sm.*,sil.inout_sn,sil.rec_id from '.$ecs->table('store_move').' as sm left join '.$ecs->table("store_inout_list").' as sil on sm.move_id=sil.move_id where sm.move_id='.$move_id);
		$inout['add_time_date'] = local_date('Y-m-d',$inout['add_time']);
		$inout['move_status_info'] = getMoveStatus($inout['status']);
		$inout['move_id'] = $move_id;

		//商品信息
		$goods_list = getMoveGoods($inout['rec_id']);

		//仓库信息
		$smarty->assign('instoreinfo',getStoreInfo($inout['store_id_in']));
		$smarty->assign('outstoreinfo',getStoreInfo($inout['store_id_out']));
		$smarty->assign('form_action', 'update');
	}else{
		$inout = createSn('zb');
		$inout['move_status_info'] = getMoveStatus(0);
		if(isset($_REQUEST['sub_id_out']) && intval($_REQUEST['sub_id_out'])>0){
			$smarty->assign('outstoreinfo',getStoreInfo(intval($_REQUEST['sub_id_out'])));
		}
		if(isset($_REQUEST['sub_id_in']) && intval($_REQUEST['sub_id_in'])>0){
			$smarty->assign('instoreinfo',getStoreInfo(intval($_REQUEST['sub_id_in'])));
		}
		$goods_list = '';
		$smarty->assign('form_action', 'insert');
	}

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
	}

	$smarty->assign('goodsinfo', $goods_list);

	getAllStock();
	$smarty->assign('inout', $inout);
	$smarty->display('store_move_info.htm');
}
elseif($act == 'view' || $act == 'check'){
	$move_id = (isset($_REQUEST['move_id']) && intval($_REQUEST['move_id'])>0) ? intval($_REQUEST['move_id']) : 0;
	$smarty->assign('action_link',  array('text' => $_LANG['store_move_list'], 'href' => 'store_move.php'));
	$smarty->assign('ur_here',      $_LANG['move_here_info']);
	if($move_id > 0){
		$inout = $db->getRow('select sm.*,sil.inout_sn,sil.rec_id from '.$ecs->table('store_move').' as sm left join '.$ecs->table("store_inout_list").' as sil on sm.move_id=sil.move_id where sm.move_id='.$move_id);
		$inout['add_time_date'] = local_date('Y-m-d',$inout['add_time']);
		$inout['move_status_info'] = getMoveStatus($inout['status']);
		$inout['move_id'] = $move_id;
		$inout['outtime'] = ($inout['out_time']>0) ? local_date('Y-m-d H:i:s',$inout['out_time']) : '';
		$inout['out_store_name'] = getStoreName($inout['store_id_out']);
		$inout['in_store_name'] = getStoreName($inout['store_id_in']);
		$inout['out_store_user'] = getUserName($inout['store_user_out']);
		$inout['in_store_user'] = ($inout['store_user_in']>0) ? getUserName($inout['store_user_in']) : '';
		$inout['status_info'] = getMoveStatus($inout['status']);

		//商品信息
		$smarty->assign('goodsinfo',getMoveGoods($inout['rec_id']));
		//转拨单操作日志
		$smarty->assign('notlist',getMoveAction($move_id));
		$smarty->assign('inout', $inout);
		$smarty->assign('three',MOVE_THREE);
		$smarty->assign('four',MOVE_FOUR);
		$smarty->display('store_move_view.htm');
	}

}
elseif($act == 'insert'){
	$store_id_out = (isset($_REQUEST['store_id_out'])) ? intval($_REQUEST['store_id_out']) : 0;
	$store_id_in = (isset($_REQUEST['store_id_in'])) ? intval($_REQUEST['store_id_in']) : 0;
	if($store_id_out <= 0 || $store_id_in <= 0){
		sys_msg('请选择出入仓库！');
	}
	if($store_id_out == $store_id_in){
		sys_msg('出入仓库不能一样！');
	}
	if(!isset($_POST['goods_id']) || !is_array($_POST['goods_id'])){
		sys_msg('请添加出库商品！');
	}

	$sub_id_out = (isset($_REQUEST['sub_id_out'])) ? intval($_REQUEST['sub_id_out']) : 0;
	$sub_id_in = (isset($_REQUEST['sub_id_in'])) ? intval($_REQUEST['sub_id_in']) : 0;
	$store_user_out = $_SESSION['admin_id'];
	$add_time = gmtime();

	if(($goodsinfo = checkGoodStock($_POST['goods_id'],$_POST['number_shishou'],$_POST['goods_attr'],$sub_id_out)) !== true){
		//验证出库商品的数量是否小于等于库存
		$message = '';
		$message = implode('<br>',$goodsinfo);
		sys_msg($message);
	}
	//插入转拨表
	$move_info = array(
		'store_id_out' => $sub_id_out,
		'store_id_in' => $sub_id_in,
		'store_user_out' => $store_user_out,
		'status' => MOVE_ONE,
		'add_time' => $add_time,
		'supplier_id' => 0,
		'store_type_id' => 0
	);
	$db->autoExecute($ecs->table('store_move'), $move_info, 'INSERT');
	$move_id = $db->insert_id();
	//创建出库单
	$inout_sn = createSn('zb');
	$inout_info = array(
		'inout_sn' => $inout_sn['inout_sn'],
		'inout_status' => 2,//等待审核
		'store_id'   => $sub_id_out,//出库id
		'adminer_id'   => $store_user_out,
		'inout_type' => INOUT_MOVE_OUT,
		'inout_mode' => 1,
		'takegoods_man' => $_SESSION['user_name'],
		'today_sn'  => $inout_sn['today_sn'],
		'add_date' => $inout_sn['add_date'],
		'add_time' => $add_time,
		'move_id' => $move_id,
		'supplier_id' => 0,
		'store_type_id' => 0
	);
	$db->autoExecute($ecs->table('store_inout_list'), $inout_info, 'INSERT');
	$inout_rec_id = $db->insert_id();

	/* 处理关联商品 */
	foreach ($_POST['goods_id']  AS $gkey=>$gval)
	{
			$goods_id = $gval;
			$goods_sn = $_POST['goods_sn'][$gkey];
			//查看商品是平台方还是入驻商
			$supplier_id = getGoodsUser($goods_id);
			$attr_value = $_POST['goods_attr'][$gkey];
			$number_yingshou = $_POST['number_yingshou'][$gkey];
			$number_shishou = $_POST['number_shishou'][$gkey];
			$sql = "insert into ". $ecs->table('store_inout_goods') ." (inout_rec_id, goods_id, goods_sn, inout_mode, attr_value, number_yingshou, number_shishou, supplier_id ) ".
						"values('$inout_rec_id',  '$goods_id', '$goods_sn', '1', '$attr_value', '$number_shishou', '$number_shishou', '$supplier_id')";
			$db->query($sql);
	}
	
	$link[0]['text'] = $_LANG['continue_add'];
    $link[0]['href'] = 'store_move.php?act=add';

    $link[1]['text'] = $_LANG['back_list_move'];
    $link[1]['href'] = 'store_move.php?act=list';

    clear_cache_files(); // 清除相关的缓存文件

    sys_msg($_LANG['moveadd_out_succeed_in'],0, $link);
}
elseif($act == 'update'){
	$move_id = (isset($_REQUEST['move_id'])) ? intval($_REQUEST['move_id']) : 0;
	$moveinfo = $db->getRow('select * from '.$ecs->table('store_move').' where move_id='.$move_id);
	if(!$moveinfo){
		sys_msg('没有此转拨单！');
	}
	$inoutinfo = $db->getRow('select * from '.$ecs->table('store_inout_list').' where move_id='.$move_id.' and inout_mode=1');
	if(!$inoutinfo){
		sys_msg('没有此转拨单对应的出库单！');
	}
	$store_id_out = (isset($_REQUEST['store_id_out'])) ? intval($_REQUEST['store_id_out']) : 0;
	$store_id_in = (isset($_REQUEST['store_id_in'])) ? intval($_REQUEST['store_id_in']) : 0;
	if($store_id_out <= 0 || $store_id_in <= 0){
		sys_msg('请选择出入仓库！');
	}
	if($store_id_out == $store_id_in){
		sys_msg('出入仓库不能一样！');
	}
	if(!isset($_POST['goods_id']) || !is_array($_POST['goods_id'])){
		sys_msg('请添加出库商品！');
	}

	$sub_id_out = (isset($_REQUEST['sub_id_out'])) ? intval($_REQUEST['sub_id_out']) : 0;
	$sub_id_in = (isset($_REQUEST['sub_id_in'])) ? intval($_REQUEST['sub_id_in']) : 0;
	$store_user_out = $_SESSION['admin_id'];
	$add_time = gmtime();
	if(($goodsinfo = checkGoodStock($_POST['goods_id'],$_POST['number_shishou'],$_POST['goods_attr'],$sub_id_out)) !== true){
		//验证出库商品的数量是否小于等于库存
		$message = '';
		$message = implode('<br>',$goodsinfo);
		sys_msg($message);
	}
	//修改转拨表
	$move_info = array(
		'store_id_out' => $sub_id_out,
		'store_id_in' => $sub_id_in,
		'store_user_out' => $store_user_out,
		'status' => MOVE_ONE,
		'supplier_id' => 0,
		'store_type_id' => 0
	);
	$db->autoExecute($ecs->table('store_move'), $move_info, 'UPDATE', 'move_id='.$move_id);
	//修改转拨出库单
	$inout_info = array(
		'store_id'   => $sub_id_out,//出库id
		'adminer_id'   => $store_user_out,
		'takegoods_man' => $_SESSION['user_name'],
		'supplier_id' => 0,
		'store_type_id' => 0
	);
	$db->autoExecute($ecs->table('store_inout_list'), $inout_info, 'UPDATE', 'rec_id='.$inoutinfo['rec_id']);
	$inout_rec_id = $inoutinfo['rec_id'];
	/* 处理关联商品 */
	//删除之前的
	$db->query("delete from ". $ecs->table('store_inout_goods') ." where inout_rec_id = '$inout_rec_id' ");
	//添加修改后的
	foreach ($_POST['goods_id']  AS $gkey=>$gval)
	{
			$goods_id = $gval;
			$goods_sn = $_POST['goods_sn'][$gkey];
			$attr_value = $_POST['goods_attr'][$gkey];
			//查看商品是平台方还是入驻商
			$supplier_id = getGoodsUser($goods_id);
			$number_yingshou = $_POST['number_yingshou'][$gkey];
			$number_shishou = $_POST['number_shishou'][$gkey];
			$sql = "insert into ". $ecs->table('store_inout_goods') ." (inout_rec_id, goods_id, goods_sn, inout_mode, attr_value, number_yingshou, number_shishou, supplier_id ) ".
						"values('$inout_rec_id',  '$goods_id', '$goods_sn', '1', '$attr_value', '$number_shishou', '$number_shishou', '$supplier_id')";
			$db->query($sql);
	}

    $link[0]['text'] = $_LANG['back_list_move'];
    $link[0]['href'] = 'store_move.php?act=list';

    clear_cache_files(); // 清除相关的缓存文件

    sys_msg($_LANG['moveedit_out_succeed_in'],0, $link);
}
elseif($act == 'check_move_out'){
	$move_id = (isset($_REQUEST['move_id']) && intval($_REQUEST['move_id'])>0) ? intval($_REQUEST['move_id']) : 0;
	$move_status = (isset($_REQUEST['move_status']) && intval($_REQUEST['move_status'])>0) ? intval($_REQUEST['move_status']) : 0;
	$move_action = (isset($_REQUEST['action_val']) && !empty($_REQUEST['action_val'])) ? trim($_REQUEST['action_val']) : '';
	$note = (isset($_REQUEST['note']) && !empty($_REQUEST['note'])) ? trim($_REQUEST['note']) : '';
	if($move_id <=0){
		sys_msg('非法操作！');
	}
	if($move_status != MOVE_TWO){
		sys_msg('非法操作！状态修改不正确！');
	}

	$store_user_out = $_SESSION['admin_id'];
	$out_time = gmtime();

	//获取转拨单中的出库单单号和仓库
	$sql = "select rec_id,store_id from ".$ecs->table('store_inout_list').' where move_id='.$move_id.' and inout_mode=1 and inout_status=2';
	$out_row = $db->getRow($sql);
	if (!$out_row)
	{
		sys_msg('对不起，不存在这个出库单！');
	}
	//将商品出库，库存做减操作
	$upre = update_stock_out($out_row['rec_id'], $out_row['store_id']);  //更新库存表
	if($upre['error'])
	{
		sys_msg($upre['error_item']);
	}

	//修改转拨单状态
	$update_move = array(
		'store_user_out' => $store_user_out,
		'status' => MOVE_TWO,//等收货人审核
		'out_time' => $out_time
	);
	$db->autoExecute($ecs->table('store_move'), $update_move, 'UPDATE', 'move_id='.$move_id);
	//修改转拨单出库申请单状态
	$update_inout_list = array(
		'inout_status'=>3,//出库审核通过
		'adminer_id'=>$store_user_out
	);
	$db->autoExecute($ecs->table('store_inout_list'), $update_inout_list, 'UPDATE', 'rec_id='.$out_row['rec_id']);
	//插入出库单通过审核的日志
	$insert_inout_note = array(
		'inout_rec_id' => $out_row['rec_id'],
		'adminer_id' => $store_user_out,
		'action_val' => $move_action,
		'inout_status' => 3,
		'inout_note' => $note,
		'add_time' => $out_time
	);
	$db->autoExecute($ecs->table('store_inout_note'), $insert_inout_note, 'INSERT');

	$link[0]['text'] = $_LANG['back_list_move'];
    $link[0]['href'] = 'store_move.php?act=list&io=out';

    clear_cache_files(); // 清除相关的缓存文件

    sys_msg($_LANG['move_out_succeed'],0, $link);

}
elseif($act == 'check_move_in'){
	$move_id = (isset($_REQUEST['move_id']) && intval($_REQUEST['move_id'])>0) ? intval($_REQUEST['move_id']) : 0;
	$move_status = (isset($_REQUEST['move_status']) && intval($_REQUEST['move_status'])>0) ? intval($_REQUEST['move_status']) : 0;
	$move_action = (isset($_REQUEST['action_val']) && !empty($_REQUEST['action_val'])) ? trim($_REQUEST['action_val']) : '';
	$note = (isset($_REQUEST['note']) && !empty($_REQUEST['note'])) ? trim($_REQUEST['note']) : '';
	if($move_id <=0){
		sys_msg('非法操作！');
	}
	if($move_status != MOVE_THREE && $move_status != MOVE_FIVE){
		sys_msg('非法操作！状态修改不正确！');
	}
	$store_user_in = $_SESSION['admin_id'];
	$in_time = gmtime();

	//获取转拨单信息
	$sql = "select * from ".$ecs->table('store_move').' where move_id='.$move_id;
	$move_row = $db->getRow($sql);
	if (!$move_row)
	{
		sys_msg('对不起，不存在这个转拨单！');
	}

	//获取转拨单中的出库单信息
	$sql = "select * from ".$ecs->table('store_inout_list').' where move_id='.$move_id;
	$out_row = $db->getRow($sql);
	if (!$out_row)
	{
		sys_msg('对不起，不存在这个出库单！');
	}

	
	//通过审核录入转拨单中的商品进库存或拒绝后出库转拨单中的商品回库
	//修改转拨单状态
	$update_move = array(
		'store_user_in'=>$store_user_in,
		'status'=>$move_status,
		'in_time'=>$in_time
	);
	$db->autoExecute($ecs->table('store_move'), $update_move, 'UPDATE', 'move_id='.$move_id);

	//创建转拨单的入库单

	$store_id = $move_row['store_id_in'];//初始入库仓库id
	$inout_type = INOUT_MOVE_IN;
	$action_txt = $_LANG['move_in_succeed'];
	if($move_status == MOVE_FIVE){
		$store_id = $move_row['store_id_out'];
		$inout_type = INOUT_MOVE_OUT_IN;
		$action_txt = $_LANG['move_out_in_succeed'];
	}
	$in_list_info = createSn('zb');
	$insert_inout_list = array(
		'inout_sn'=>$in_list_info['inout_sn'],
		'inout_status'=>$out_row['inout_status'],
		'store_id'=>$store_id,
		'adminer_id'=>$store_user_in,
		'inout_type'=>$inout_type,
		'inout_mode'=>2,
		'takegoods_man'=>$_SESSION['user_name'],
		'today_sn'=>$in_list_info['today_sn'],
		'add_date'=>$in_list_info['add_date'],
		'add_time'=>$in_time,
		'move_id'=>$out_row['move_id'],
		'supplier_id'=>0,
		'store_type_id'=>0
	);
	$db->autoExecute($ecs->table('store_inout_list'), $insert_inout_list, 'INSERT');
	$inout_rec_id = $db->insert_id();

	//获取出库的商品并添加成为入库
	$sql = "select * from ".$ecs->table('store_inout_goods').' where inout_rec_id='.$out_row['rec_id'];
	$ret = $db->query($sql);
	while($row = $db->fetchRow($ret)){
		$in_goods = array(
			'inout_rec_id'=>$inout_rec_id,
			'goods_sn'=>$row['goods_sn'],
			'goods_id'=>$row['goods_id'],
			'attr_value'=>$row['attr_value'],
			'inout_mode'=>2,
			'number_yingshou'=>$row['number_yingshou'],
			'number_shishou'=>$row['number_shishou'],
			'supplier_id'=>$row['supplier_id'],
			'store_type_id'=>$row['store_type_id']
		);
		$db->autoExecute($ecs->table('store_inout_goods'), $in_goods, 'INSERT');
		unset($in_goods);
	}
	//入库商品库存变更
	update_stock_in($inout_rec_id, $store_id);

	//插入入库单通过审核的日志或撤销后日志
	$insert_inout_note = array(
		'inout_rec_id' => $inout_rec_id,
		'adminer_id' => $store_user_in,
		'action_val' => $move_action,
		'inout_status' => 3,
		'inout_note' => $note,
		'add_time' => $in_time
	);
	$db->autoExecute($ecs->table('store_inout_note'), $insert_inout_note, 'INSERT');
	

	$link[0]['text'] = $_LANG['back_list_move'];
    $link[0]['href'] = 'store_move.php?act=list&io=in';

    clear_cache_files(); // 清除相关的缓存文件

    sys_msg($action_txt,0, $link);
}
elseif($act == 'check_move_no'){
	$move_id = (isset($_REQUEST['move_id']) && intval($_REQUEST['move_id'])>0) ? intval($_REQUEST['move_id']) : 0;
	$move_status = (isset($_REQUEST['move_status']) && intval($_REQUEST['move_status'])>0) ? intval($_REQUEST['move_status']) : 0;
	$move_action = (isset($_REQUEST['action_val']) && !empty($_REQUEST['action_val'])) ? trim($_REQUEST['action_val']) : '';
	$note = (isset($_REQUEST['note']) && !empty($_REQUEST['note'])) ? trim($_REQUEST['note']) : '';
	if($move_id <=0){
		sys_msg('非法操作！');
	}
	if($move_status != MOVE_FOUR){
		sys_msg('非法操作！状态修改不正确！');
	}
	$store_user_in = $_SESSION['admin_id'];
	$in_time = gmtime();

	//获取转拨单信息
	$sql = "select * from ".$ecs->table('store_move').' where move_id='.$move_id;
	$move_row = $db->getRow($sql);
	if (!$move_row)
	{
		sys_msg('对不起，不存在这个转拨单！');
	}

	//获取转拨单中的出库单信息
	$sql = "select * from ".$ecs->table('store_inout_list').' where move_id='.$move_id;
	$out_row = $db->getRow($sql);
	if (!$out_row)
	{
		sys_msg('对不起，不存在这个出库单！');
	}

	
	//通过审核录入转拨单中的商品进库存或拒绝后出库转拨单中的商品回库
	//修改转拨单状态
	$update_move = array(
		'store_user_in'=>$store_user_in,
		'status'=>$move_status,
		'in_time'=>$in_time
	);
	$db->autoExecute($ecs->table('store_move'), $update_move, 'UPDATE', 'move_id='.$move_id);

	//插入入库单通过审核的日志或撤销后日志
	$insert_inout_note = array(
		'inout_rec_id' => $out_row['rec_id'],
		'adminer_id' => $store_user_in,
		'action_val' => $move_action,
		'inout_status' => 3,
		'inout_note' => $note,
		'add_time' => $in_time
	);
	$db->autoExecute($ecs->table('store_inout_note'), $insert_inout_note, 'INSERT');
	

	$link[0]['text'] = $_LANG['back_list_move'];
    $link[0]['href'] = 'store_move.php?act=list&io=in';

    clear_cache_files(); // 清除相关的缓存文件

    sys_msg($_LANG['move_no_succeed'],0, $link);
}
elseif ($act == 'batch_import'){
	$smarty->assign('ur_here',     '批量导入入库商品');
    $smarty->assign('action_link', array('text' => $_LANG['back_list_move'], 'href' => 'store_move.php?act=list'));
    $smarty->assign('form_action', 'add');
	$sub_id_out =  $_REQUEST['sub_id_out'] ? intval($_REQUEST['sub_id_out']) : 0;
	$sub_id_in =  $_REQUEST['sub_id_in'] ? intval($_REQUEST['sub_id_in']) : 0;
	$move_id =  $_REQUEST['move_id'] ? intval($_REQUEST['move_id']) : 0;

	$smarty->assign("sub_id_out", $sub_id_out);
	$smarty->assign("sub_id_in", $sub_id_in);
	$smarty->assign("move_id", $move_id);


    assign_query_info();
    $smarty->display('store_move_import.htm');
}
/*ajax操作*/
elseif ($act == 'get_goodsInfo_bysn'){
	require(ROOT_PATH . 'includes/cls_json.php');
	$opt['cuowu']=0;
	$goods_sn   = empty($_GET['goods_sn']) ? 0 : trim($_GET['goods_sn']);
	$store_id   = empty($_GET['store_id']) ? 0 : intval($_GET['store_id']);
    $sql = "select g.goods_id, g.goods_name, g.goods_thumb,sgs.store_number from ". $ecs->table('goods') ." as g left join ". $ecs->table('store_goods_stock') ." as sgs on g.goods_id = sgs.goods_id where g.goods_sn= '$goods_sn' and sgs.store_id='$store_id' ";
    $goodsinfo = $db->getRow($sql);
    if ($goodsinfo['goods_id']>0 && intval($goodsinfo['store_number'])>0)
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
		$opt['cuowu']=1;
		$opt['message'] = '对不起，此商品在对应的发货仓库中不存在!';
	}
    
	

    $json = new JSON;
	echo $json->encode($opt);
}
elseif($act == 'is_have_kucun'){
	require(ROOT_PATH . 'includes/cls_json.php');
	$res = array('error'=>0,'message'=>'');
	$store_id = intval($_REQUEST['store_id']);
	$goods_id = intval($_REQUEST['goods_id']);
	$attr = trim($_REQUEST['attr']);
	$attr = trim($_REQUEST['attr']);
	$where_attr = "";
	if(!empty($attr)){
		$where_attr = " and goods_attr='".$attr."'";
	}
	$goods_number = intval($_REQUEST['goods_number']);
	$sql = "select store_number from ".$ecs->table('store_goods_stock')." where goods_id=".$goods_id.$where_attr." and store_id=".$store_id;
	$num = $db->getOne($sql);
	if($goods_number <= $num){
		$res['attr'] = $attr;
	}else{
		$res['error'] = 1;
		$res['stock'] = $num;
	}
	$json = new JSON;
	echo $json->encode($res);
}
elseif($act == 'del'){
	require(ROOT_PATH . 'includes/cls_json.php');
	$res = array('error'=>0,'message'=>'');
	$move_id = (isset($_REQUEST['move_id']) && intval($_REQUEST['move_id'])>0) ? intval($_REQUEST['move_id']) : 0;
	$json = new JSON;
	if($move_id <= 0){
		$res['message'] = '非法操作';
		die($json->encode($res));
	}
	$rec_id = $db->getOne('select rec_id from '.$ecs->table('store_inout_list')." where move_id=".$move_id);
	$db->query("delete from ". $ecs->table('store_inout_goods') ." where inout_rec_id = ".$rec_id);
	$db->query("delete from ". $ecs->table('store_inout_list') ." where rec_id = ".$rec_id);
	$db->query("delete from ". $ecs->table('store_move') ." where move_id = ".$move_id);
	$res['error'] = $move_id;
	die($json->encode($res));
}
elseif ($_REQUEST['act'] == 'query'){
	$store_info = getStoreId($_SESSION['admin_id']);
	$store_str = implode(',',$store_info);

	$info = getMoveList($store_str);
	$smarty->assign('info',$info['arr']);
	$smarty->assign('filter',          $info['filter']);
    $smarty->assign('record_count',    $info['record_count']);
    $smarty->assign('page_count',      $info['page_count']);
    $sort_flag  = sort_flag($info['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('store_move.htm'), '',
        array('filter' => $info['filter'], 'page_count' => $info['page_count']));
}
//////////////////////////////////////////////////////////////////////////方法////////////////////////////////////////////
/*
获取所有的仓库
*/
function getAllStock(){
	global $db,$ecs,$smarty;
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
}
/*
获取当前管理人员管理的仓库信息
*/
function getUserStock($userid){
	global $db,$ecs;

	$sql_in = "select sm.store_id from ".$ecs->table('store_adminer')." as sa,".$ecs->table('store_main')." as sm where sa.admin_id=".$userid." and sm.store_id = sa.store_id and sm.parent_id>0 and sa.supplier_id=0 and sa.store_type_id=0";

	$sql = 'select a.store_id as ssid,a.store_name as ssname,b.store_id as psid,b.store_name as psname from '.$ecs->table('store_main').' as a left join '.$ecs->table('store_main').' as b on a.parent_id=b.store_id where a.store_id in('.$sql_in.')';
	$ret = $db->query($sql);
	$array = array();
	while($row = $db->fetchRow($ret)){
		$array[$row['ssid']] = $row['psname']."(".$row['ssname'].")";
	}
	return $array;
}
/*
获取转拨单的操作日志
*/
function getMoveAction($moveid){
	global $db,$ecs;
	$sql = "select * from ".$ecs->table('store_inout_note')." where inout_rec_id in(select rec_id from ".$ecs->table('store_inout_list')." where move_id=".$moveid.")";
	$ret = $db->query($sql);
	$array = array();
	while($row = $db->fetchRow($ret)){
		$row['adduser'] = getUserName($row['adminer_id']);
		$row['addtime'] = local_date('Y-m-d H:i:s',$row['add_time']);
		$array[$row['note_id']] = $row;
	}
	return $array;
}
/*
获取转拨单中相关商品信息
*/
function getMoveGoods($recid){
	global $db,$ecs;
	$sql = "select i.*,g.goods_name, g.goods_thumb from ".$ecs->table('store_inout_goods')." as i left join ".$ecs->table('goods')." as g on i.goods_id=g.goods_id where inout_rec_id = ".$recid;
	$ret = $db->query($sql);
	$goodsinfo = array();
	while($row = $db->fetchRow($ret)){
		$row['goods_thumb'] = "../".get_image_path($row['goods_id'], $row['goods_thumb']);
		$row['goods_attr1'] = get_attr_name($row['attr_value']);
		$row['goods_attr2'] = $row['attr_value'];
		$goodsinfo[$row['book_id']] = $row;
	}
	return $goodsinfo;
}
/*
获取仓库信息
*/
function getStoreInfo($subid)
{
	global $db,$ecs;
	$store_info = $db->getRow('select a.store_id as ssid,a.store_name as ssname,b.store_id as psid,b.store_name as psname from '.$ecs->table('store_main').' as a left join '.$ecs->table('store_main').' as b on a.parent_id=b.store_id where a.store_id='.$subid);
	$sql = "select * from ".$ecs->table('store_main')." where parent_id=".$store_info['psid'];
	$ret = $db->query($sql);
	$sub_store = array();
	while($row = $db->fetchRow($ret)){
		$sub_store[$row['store_id']] = $row['store_name'];
	}
	$store_info['info'] = $sub_store;
	return $store_info;
}
/*
生成订单号
*/
function createSn($sn='rk'){
	global $db,$ecs;
	$inout['add_time_date'] = local_date('Y-m-d');
    $inout['add_date'] = local_date('Ymd');

	$sql="select max(today_sn) from ". $ecs->table('store_inout_list') ." where add_date='$inout[add_date]' ";
	$inout_count = $db->getOne($sql);
	$inout_sn = $inout_count ? intval($inout_count + 1) : 1;
	$inout['today_sn'] = $inout_sn;
	$inout_sn = str_pad($inout_sn, 4, "0", STR_PAD_LEFT);
	$inout_sn =  $sn.$inout['add_date'] . $inout_sn;
	$inout['inout_sn'] = $inout_sn;
	return $inout;
}
/*
获取登陆人所负责的仓库
*/
function getStoreId($userid){
	global $db,$ecs;

	$sql = "select store_id from ".$ecs->table('store_adminer')." where admin_id=".$userid." and supplier_id=0 and store_type_id=0";
	$ret = $db->query($sql);
	$array = array();
	while($row = $db->fetchRow($ret)){
		$array[] = $row['store_id'];
	}
	return $array;
}
/*
获取仓库
*/
function getStoreName($store_id){
	global $db,$ecs;

	$sql = "select a.store_name as child_name,b.store_name as parent_name from ".$ecs->table('store_main')." as a left join ".$ecs->table('store_main')." as b on a.parent_id=b.store_id where a.store_id=".$store_id." limit 1";
	$ret = $db->query($sql);
	$name = '';
	while($row = $db->fetchRow($ret)){
		$name = $row['parent_name']."(".$row['child_name'].")";
	}
	return $name;
}
/*
根据用户id获取用户名称
*/
function getUserName($userid){
	global $db,$ecs;
	$sql = "select user_name from ".$ecs->table('admin_user')." where user_id=".$userid;
	return $db->getOne($sql);
}
/*
获取此次转拨单中的商品总数量
*/
function getMoveGoodsNum($move_id,$inouttype){
	global $db,$ecs;
	$sql = "SELECT sum( number_shishou ) AS num
			FROM ".$ecs->table('store_inout_goods')." AS sig, ".$ecs->table('store_inout_list')." AS sil
			WHERE sil.move_id =".$move_id."
			AND sil.inout_mode =".$inouttype."
			AND sig.inout_rec_id = sil.rec_id";
	return $db->getOne($sql);
}
/*
获取转拨单状态
*/
function getMoveStatus($status=-1){
	$status_info = array(0=>'转拨单录入中',1=>'待确认收货',2=>'转拨完成',3=>'转拨拒绝',4=>'撤销完成');
	if($status == -1){
		return $status_info;
	}
	return $status_info[$status];
}
/*
根据转拨单状态获取相应操作
*/
function getStatusDo($move_id,$status,$inout){
	$doinfo = array(
		'out'=>array(
			0=>array(
				array('name'=>'查看','url'=>'store_move.php?act=view&move_id='.$move_id),
				array('name'=>'编辑','url'=>'store_move.php?act=edit&move_id='.$move_id),
				array('name'=>'删除','url'=>"javascript:del(".$move_id.")"),
				array('name'=>'提交','url'=>"javascript:showDiv(".$move_id.", ".MOVE_TWO.", '转拨出库单提交备注', 'check_move_out')")
			),
			1=>array(
				array('name'=>'查看','url'=>'store_move.php?act=view&move_id='.$move_id)
			),
			2=>array(
				array('name'=>'查看','url'=>'store_move.php?act=view&move_id='.$move_id)
			),
			3=>array(
				array('name'=>'查看','url'=>'store_move.php?act=view&move_id='.$move_id),
				array('name'=>'撤销','url'=>"javascript:showDiv(".$move_id.", ".MOVE_FIVE.", '转拨出库单提交备注', 'check_move_in')")
			),
			4=>array(
				array('name'=>'查看','url'=>'store_move.php?act=view&move_id='.$move_id)
			)
		),
		'in'=>array(
			0=>array(
				array('name'=>'查看','url'=>'store_move.php?act=view&move_id='.$move_id),
			),
			1=>array(
				array('name'=>'查看','url'=>'store_move.php?act=view&move_id='.$move_id),
				array('name'=>'审核','url'=>'store_move.php?act=check&move_id='.$move_id),
				array('name'=>'快速审核','url'=>"javascript:showDiv(".$move_id.", ".MOVE_THREE.", '转拨入库单通过备注','check_move_in')")
			),
			2=>array(
				array('name'=>'查看','url'=>'store_move.php?act=view&move_id='.$move_id)
			),
			3=>array(
				array('name'=>'查看','url'=>'store_move.php?act=view&move_id='.$move_id)
			),
			4=>array(
				array('name'=>'查看','url'=>'store_move.php?act=view&move_id='.$move_id)
			)
		)
	);
	return $doinfo[$inout][$status];
}
/*
获取转拨单列表
*/
function getMoveList($store_str){
	global $db,$ecs,$smarty;

	$result = get_filter();
    if ($result === false)
    {
		$inorout = (isset($_REQUEST['io']) && !empty($_REQUEST['io'])) ? $_REQUEST['io'] : 'out';

		$where_and = '1';
		if(!empty($store_str)){
			$where_and = "store_id_".$inorout." in(".$store_str.")";
		}

		if($inorout == 'in'){
			//录入中状态的转拨单，被入仓库的相关人不可以查看到
			$where_and .= ' and status > 0 ';
		}

		$filter['store_id_out']    = empty($_REQUEST['store_id_out']) ? 0 : intval($_REQUEST['store_id_out']);
		$filter['store_id_in']    = empty($_REQUEST['store_id_in']) ? 0 : intval($_REQUEST['store_id_in']);
		$filter['status']    = (isset($_REQUEST['status'])) ? intval($_REQUEST['status']) : -1;
		$filter['add_time1']    = empty($_REQUEST['add_time1']) ? '' : (strpos($_REQUEST['add_time1'], '-') > 0 ?  local_strtotime($_REQUEST['add_time1']) : $_REQUEST['add_time1']);
		$filter['add_time2']    = empty($_REQUEST['add_time2']) ? '' : (strpos($_REQUEST['add_time2'], '-') > 0 ?  local_strtotime($_REQUEST['add_time2']) : $_REQUEST['add_time2']);

		
		if($filter['store_id_out']){
			$where_and .= " and store_id_out=".$filter['store_id_out']." ";
		}
		if($filter['store_id_in']){
			$where_and .= " and store_id_in=".$filter['store_id_in']." ";
		}
		if($filter['status'] > -1){
			$where_and .= " and status=".$filter['status']." ";
		}
		if ($filter['add_time1'])
		{
			$where_and .= " AND out_time>=  '" . $filter['add_time1']."' ";
		}
		if ($filter['add_time2'])
		{
			$where_and .= " AND out_time<=  '" . $filter['add_time2']."' ";
		}
		

		/* 记录总数 */
		$sql = "select count(*) from ".$ecs->table('store_move')." where ".$where_and." and supplier_id=0 and store_type_id=0";
		$filter['record_count'] = $db->getOne($sql);
		$filter = page_and_size($filter);

		$sql = "select * from ".$ecs->table('store_move')." where ".$where_and." and supplier_id=0 and store_type_id=0 order by move_id desc";

		set_filter($filter, $sql);
	}
	else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }
	$ret = $db->selectLimit($sql, $filter['page_size'], $filter['start']);
	//$ret = $db->query($sql);
	$info = array();
	while($row = $db->fetchRow($ret)){
		$info[$row['move_id']]['move_id'] = $row['move_id'];
		$info[$row['move_id']]['out_store_name'] = getStoreName($row['store_id_out']);
		$info[$row['move_id']]['in_store_name'] = getStoreName($row['store_id_in']);
		$info[$row['move_id']]['out_store_user'] = getUserName($row['store_user_out']);
		$info[$row['move_id']]['in_store_user'] = ($row['store_user_in']>0) ? getUserName($row['store_user_in']) : '';
		$info[$row['move_id']]['number'] = getMoveGoodsNum($row['move_id'],(($inorout=='out') ? 1 : 2));
		$info[$row['move_id']]['addtime'] = local_date('Y-m-d H:i:s',$row['add_time']);
		$info[$row['move_id']]['outtime'] = ($row['out_time']>0) ? local_date('Y-m-d H:i:s',$row['out_time']) : '';
		$info[$row['move_id']]['intime'] = ($row['in_time']>0) ? local_date('Y-m-d H:i:s',$row['in_time']) : '';
		$info[$row['move_id']]['status_info'] = getMoveStatus($row['status']);
		$info[$row['move_id']]['status'] = $row['status'];
		$info[$row['move_id']]['doing'] = getStatusDo($row['move_id'],$row['status'],$inorout);
	}
	$smarty->assign('inorout',$inorout);

	return array('arr' => $info, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	//return $info;
}
/*
商品出库时，库存做验证
*/
function checkGoodStock($goods_id,$goods_num,$goods_attr,$stock){

	global $ecs,$db;
	$goodsinfo = array();
	$result = array();
	foreach($goods_id as $key=>$val){
		$k = $val."_".$goods_attr[$key];
		$goodsinfo[$k] = isset($goodsinfo[$k]) ? ($goodsinfo[$k]+$goods_num[$key]) : $goods_num[$key];
	}
	$goodsids = array_unique($goods_id);

	$sql = "select sgs.*,g.goods_name from ".$ecs->table('store_goods_stock')." as sgs,".$ecs->table('goods')." as g where sgs.goods_id in(".implode(',',$goodsids).") and sgs.store_id=".$stock." and sgs.goods_id=g.goods_id";
	$ret = $db->query($sql);
	while($row = $db->fetchRow($ret)){
		$keys = $row['goods_id']."_".$row['goods_attr'];
		if($row['store_number'] <= 0){
			$result[$keys] = "商品[".$row['goods_name']."]最大库存:[".$row['store_number']."]";
		}elseif(isset($goodsinfo[$keys]) && ($goodsinfo[$keys] > $row['store_number'])){
			$result[$keys] = "商品[".$row['goods_name']."]最大库存:[".$row['store_number']."]";
		}
	}
	return (empty($result)) ? true : $result;
}
/*
获取商品所属
*/
function getGoodsUser($goods_id){
	global $ecs,$db;
	return $db->getOne("select supplier_id from ".$ecs->table('goods')." where goods_id=".$goods_id);
}
?>