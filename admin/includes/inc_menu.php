<?php

/**
 * ECSHOP 管理中心菜单数组
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: inc_menu.php 17217 2011-01-19 06:29:08Z liubo $
*/

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

$modules['02_cat_and_goods']['01_goods_list']       = 'goods.php?act=list';         // 商品列表
$modules['02_cat_and_goods']['02_supplier_goods_list']       = 'goods.php?act=list&supp=1';         // 供货商商品列表
$modules['02_cat_and_goods']['03_goods_add']        = 'goods.php?act=add';          // 添加商品
$modules['02_cat_and_goods']['04_category_list']    = 'category.php?act=list';
$modules['02_cat_and_goods']['05_comment_manage']   = 'comment_manage.php?act=list';
/* 晒单插件 增加 by www.68ecshop.com */
$modules['02_cat_and_goods']['05_shaidan_manage']   = 'shaidan.php?act=list';
$modules['02_cat_and_goods']['05_goods_tags']       = 'goods_tags.php?act=list';
/* 晒单插件 增加 by www.68ecshop.com */
//$modules['02_cat_and_goods']['05_question_manage']   = 'question_manage.php?act=list';
$modules['02_cat_and_goods']['06_goods_brand_list'] = 'brand.php?act=list';
$modules['02_cat_and_goods']['08_goods_type']       = 'goods_type.php?act=manage';
$modules['02_cat_and_goods']['11_goods_trash']      = 'goods.php?act=trash';        // 商品回收站
$modules['02_cat_and_goods']['12_batch_pic']        = 'picture_batch.php';
$modules['02_cat_and_goods']['13_batch_add']        = 'goods_batch.php?act=add';    // 商品批量上传
$modules['02_cat_and_goods']['14_goods_export']     = 'goods_export.php?act=goods_export';
$modules['02_cat_and_goods']['15_batch_edit']       = 'goods_batch.php?act=select'; // 商品批量修改
$modules['02_cat_and_goods']['16_goods_script']     = 'gen_goods_script.php?act=setup';
$modules['02_cat_and_goods']['17_tag_manage']       = 'tag_manage.php?act=list';
/*$modules['02_cat_and_goods']['50_virtual_card_list']   = 'goods.php?act=list&extension_code=virtual_card';
$modules['02_cat_and_goods']['51_virtual_card_add']    = 'goods.php?act=add&extension_code=virtual_card';
$modules['02_cat_and_goods']['52_virtual_card_change'] = 'virtual_card.php?act=change';*/
$modules['02_cat_and_goods']['goods_auto']             = 'goods_auto.php?act=list';

/* 代码增加_start  By   morestock_morecity */
$modules['02_store_and_goods']['01_store_manage']       = 'store_manage.php?act=list';         // 仓库设置
$modules['02_store_and_goods']['02_store_shipping_demo']       = 'store_shipping_demo.php?act=list';         // 运费模板
$modules['02_store_and_goods']['03_store_inout_goods']       = 'store_inout_goods.php?act=list';         //出入库序时簿
$modules['02_store_and_goods']['03_store_inout_out']       = 'store_inout_out.php?act=list';         //出库管理
$modules['02_store_and_goods']['03_store_inout_in']       = 'store_inout_in.php?act=list';			 //入库管理
$modules['02_store_and_goods']['04_store_inout_stock']       = 'store_inout_stock.php?act=list';			 //即时库存查询
$modules['02_store_and_goods']['15_store_out_type']       = 'store_inout_type.php?act=list&in_out=1';         // 出库类型设置
$modules['02_store_and_goods']['15_store_in_type']       = 'store_inout_type.php?act=list&in_out=2';             // 入库类型设置
$modules['02_store_and_goods']['16_store_move']       = 'store_move.php?act=list';             // 商品移库
$modules['02_store_and_goods']['17_store_rebate_finish']       = 'supplier_store_rebate.php?act=list&is_pay_ok=1';             // 往期结算
$modules['02_store_and_goods']['18_store_rebate']       = 'supplier_store_rebate.php?act=list&is_pay_ok=0';             // 本期待结
/* 代码增加_end  By   morestock_morecity */

/* 代码增加_start  By   DSS */
$modules['02_dss_chart']['02_dss_kcxz']       = '/dss/index.php?c=originData&a=upload&user_id=' . $_SESSION['admin_id'];
$modules['02_dss_chart']['02_dss_chart']       = '/dss/index.php?c=originData&a=chart&user_id='  . $_SESSION['admin_id'];
/* 代码增加_end  By   DSS */

/* 代码增加_start  By   carpart */
$_LANG['03_carpart'] = '配件管理';

$_LANG['01_carpart'] = '配件列表';
$_LANG['02_cartype'] = '车型列表';
$_LANG['03_carpart_attr'] = '配件属性';
$_LANG['04_carpart_cartype_relation'] = '配件车型信息';
$_LANG['05_carpart_homo_relation'] = '配件替代关系';

$modules['03_carpart']['01_carpart']       = '/carpart/carpart.php';
$modules['03_carpart']['02_cartype']       = '/carpart/cartype.php';
$modules['03_carpart']['03_carpart_attr']       = '/carpart/carpart_attr.php';
$modules['03_carpart']['04_carpart_cartype_relation']       = '/carpart/carpart_cartype_relation.php';
$modules['03_carpart']['05_carpart_homo_relation']       = '/carpart/carpart_homo_relation.php';

/* 代码增加_end  By   carpart */

//$modules['03_promotion']['02_snatch_list']          = 'snatch.php?act=list';
$modules['03_promotion']['04_bonustype_list']       = 'bonus.php?act=list';
//$modules['03_promotion']['06_pack_list']            = 'pack.php?act=list';
//$modules['03_promotion']['07_card_list']            = 'card.php?act=list';
//$modules['03_promotion']['08_group_buy']            = 'group_buy.php?act=list';
$modules['03_promotion']['09_topic']                = 'topic.php?act=list';
//$modules['03_promotion']['10_auction']              = 'auction.php?act=list';
$modules['03_promotion']['12_favourable']           = 'favourable.php?act=list';
//$modules['03_promotion']['13_wholesale']            = 'wholesale.php?act=list';
//$modules['03_promotion']['14_package_list']         = 'package.php?act=list';
//$modules['03_promotion']['ebao_commend']            = 'ebao_commend.php?act=list';
$modules['03_promotion']['15_exchange_goods']       = 'exchange_goods.php?act=list';


$modules['04_order']['01_order_list']               = 'order.php?act=list';
$modules['04_order']['02_supplier_order']           = 'order.php?act=list&supp=1';
$modules['04_order']['03_order_query']              = 'order.php?act=order_query';
$modules['04_order']['04_merge_order']              = 'order.php?act=merge';
$modules['04_order']['05_edit_order_print']         = 'order.php?act=templates';
$modules['04_order']['06_undispose_booking']        = 'goods_booking.php?act=list_all';
//$modules['04_order']['07_repay_application']        = 'repay.php?act=list_all';
$modules['04_order']['08_add_order']                = 'order.php?act=add';
$modules['04_order']['09_delivery_order']           = 'order.php?act=delivery_list';
//$modules['04_order']['10_back_order']               = 'order.php?act=back_list';
$modules['04_order']['10_back_order']               = 'back.php?act=back_list';  //代码修改 By www.68ecshop.com
$modules['04_order']['11_supplier_back_order']               = 'back.php?act=back_list&supp=1';  //代码修改 By www.68ecshop.com
//ecshop add start 
$modules['04_order']['12_order_excel']              = 'excel.php?act=order_excel';
//ecshop add end 
$modules['04_order']['11_kuaidi_order']               = 'kuaidi_order.php?act=list';  //代码增加  By  www.68ecshop.com
$modules['04_order']['12_kuaidi_order2']             = 'kuaidi_order.php?act=list&order_status=4&is_finish=1';  //代码增加  By  www.68ecshop.com
$modules['05_banner']['ad_position']                = 'ad_position.php?act=list';
$modules['05_banner']['ad_list']                    = 'ads.php?act=list';

$modules['06_stats']['flow_stats']                  = 'flow_stats.php?act=view';
/* 代码添加_START   By www.68ecshop.com */
$modules['06_stats']['keyword']                     = 'keyword.php?act=list';
/* 代码添加_SEND  By www.68ecshop.com */
$modules['06_stats']['searchengine_stats']          = 'searchengine_stats.php?act=view';
$modules['06_stats']['z_clicks_stats']              = 'adsense.php?act=list';
$modules['06_stats']['report_guest']                = 'guest_stats.php?act=list';
$modules['06_stats']['report_order']                = 'order_stats.php?act=list';
$modules['06_stats']['report_sell']                 = 'sale_general.php?act=list';
$modules['06_stats']['sale_list']                   = 'sale_list.php?act=list';
$modules['06_stats']['sell_stats']                  = 'sale_order.php?act=goods_num';
$modules['06_stats']['report_users']                = 'users_order.php?act=order_num';
$modules['06_stats']['visit_buy_per']               = 'visit_sold.php?act=list';

$modules['07_content']['03_article_list']           = 'article.php?act=list';
$modules['07_content']['02_articlecat_list']        = 'articlecat.php?act=list';
$modules['07_content']['vote_list']                 = 'vote.php?act=list';
$modules['07_content']['article_auto']              = 'article_auto.php?act=list';
//$modules['07_content']['shop_help']                 = 'shophelp.php?act=list_cat';
//$modules['07_content']['shop_info']                 = 'shopinfo.php?act=list';


$modules['08_members']['03_users_list']             = 'users.php?act=list';

$modules['08_members']['04_users_export']             = 'users_export.php';     //代码增加 By www.68ecshop.com

$modules['08_members']['04_users_add']              = 'users.php?act=add';
$modules['08_members']['05_user_rank_list']         = 'user_rank.php?act=list';
$modules['08_members']['06_list_integrate']         = 'integrate.php?act=list';
$modules['08_members']['08_unreply_msg']            = 'user_msg.php?act=list_all';
$modules['08_members']['09_user_account']           = 'user_account.php?act=list';
$modules['08_members']['10_user_account_manage']    = 'user_account_manage.php?act=list';
$modules['08_members']['11_postman_list']             = 'postman.php?act=list';   // 代码增加   By   www.68ecshop.com

$modules['10_priv_admin']['admin_logs']             = 'admin_logs.php?act=list';
$modules['10_priv_admin']['admin_list']             = 'privilege.php?act=list';
$modules['10_priv_admin']['admin_role']             = 'role.php?act=list';
$modules['10_priv_admin']['agency_list']            = 'agency.php?act=list';
$modules['10_priv_admin']['suppliers_list']         = 'suppliers.php?act=list'; // 供货商

$modules['11_system']['01_shop_config']             = 'shop_config.php?act=list_edit';
$modules['11_system']['02_payment_list']            = 'payment.php?act=list';
$modules['11_system']['03_shipping_list']           = 'shipping.php?act=list';
$modules['11_system']['04_mail_settings']           = 'shop_config.php?act=mail_settings';
$modules['11_system']['05_area_list']               = 'area_manage.php?act=list';
//$modules['11_system']['06_plugins']                 = 'plugins.php?act=list';
$modules['11_system']['07_cron_schcron']            = 'cron.php?act=list';
$modules['11_system']['08_friendlink_list']         = 'friend_link.php?act=list';
$modules['11_system']['sitemap']                    = 'sitemap.php';
$modules['11_system']['check_file_priv']            = 'check_file_priv.php?act=check';
$modules['11_system']['captcha_manage']             = 'captcha_manage.php?act=main';
$modules['11_system']['ucenter_setup']              = 'integrate.php?act=setup&code=ucenter';
$modules['11_system']['flashplay']                  = 'flashplay.php?act=list';
$modules['11_system']['navigator']                  = 'navigator.php?act=list';
$modules['11_system']['file_check']                 = 'filecheck.php';
//$modules['11_system']['fckfile_manage']             = 'fckfile_manage.php?act=list';
$modules['11_system']['021_reg_fields']             = 'reg_fields.php?act=list';


//$modules['12_template']['02_template_select']       = 'template.php?act=list';
$modules['12_template']['03_template_setup']        = 'template.php?act=setup';
$modules['12_template']['04_template_library']      = 'template.php?act=library';
$modules['12_template']['05_edit_languages']        = 'edit_languages.php?act=list';
$modules['12_template']['06_template_backup']       = 'template.php?act=backup_setting';
$modules['12_template']['mail_template_manage']     = 'mail_template.php?act=list';


$modules['13_backup']['02_db_manage']               = 'database.php?act=backup';
$modules['13_backup']['03_db_optimize']             = 'database.php?act=optimize';
$modules['13_backup']['04_sql_query']               = 'sql.php?act=main';
//$modules['13_backup']['05_synchronous']             = 'integrate.php?act=sync';


//$modules['14_sms']['02_sms_my_info']                = 'sms.php?act=display_my_info';
$modules['14_sms']['03_sms_send']                   = 'sms.php?act=display_send_ui';
//$modules['14_sms']['04_sms_charge']                 = 'sms.php?act=display_charge_ui';
//$modules['14_sms']['05_sms_send_history']           = 'sms.php?act=display_send_history_ui';
//$modules['14_sms']['06_sms_charge_history']         = 'sms.php?act=display_charge_history_ui';

$modules['15_rec']['affiliate']                     = 'affiliate.php?act=list';
$modules['15_rec']['affiliate_ck']                  = 'affiliate_ck.php?act=list';

$modules['16_email_manage']['email_list']           = 'email_list.php?act=list';
$modules['16_email_manage']['magazine_list']        = 'magazine_list.php?act=list';
$modules['16_email_manage']['attention_list']       = 'attention_list.php?act=list';
$modules['16_email_manage']['view_sendlist']        = 'view_sendlist.php?act=list';

/* 代码增加_start  By  www.68ecshop.com */
$modules['02_supplier']['05_supplier_rank']       = 'supplier_rank.php?act=list'; 
$modules['02_supplier']['01_supplier_reg']       = 'supplier.php?act=list'; 
$modules['02_supplier']['02_supplier_list']       = 'supplier.php?act=list&status=1'; 
$modules['02_supplier']['03_rebate_nopay']       = 'supplier_rebate.php?act=list&is_pay_ok=0'; 
$modules['02_supplier']['03_rebate_pay']       = 'supplier_rebate.php?act=list&is_pay_ok=1'; 
$modules['02_supplier']['04_shop_category']       = 'supplier_street_category.php?act=list';
$modules['02_supplier']['05_shop_street']       = 'supplier_street.php?act=list';
/* 代码增加_end  By  www.68ecshop.com */
$modules['11_system']['website']  = 'website.php?act=list';
/* 代码增加_start  By  www.68ecshop.com */
$modules['03_promotion']['16_takegoods_list']       = 'takegoods.php?act=list';
$modules['03_promotion']['16_takegoods_order']       = 'takegoods.php?act=order_list';
/* 代码增加_end  By  www.68ecshop.com */
$modules['16_email_manage']['sendmail']           =    'sendmail.php?act=sendmail';
/* 代码增加_start   By  www.68ecshop.com */ 
$modules['02_cat_and_goods']['pricecut']             =   'pricecut.php?act=list&status=-1';
$purview['pricecut']           =   'pricecut';
$_LANG['pricecut']   = '降价通知列表';
/* 代码增加_end   By  www.68ecshop.com */ 
/* 代码增加_start   By www.ecshop68.com */
$modules['17_pickup_point_manage']['pickup_point_list']       = 'pickup_point.php?act=list';
$modules['17_pickup_point_manage']['pickup_point_add']        = 'pickup_point.php?act=add';
$modules['17_pickup_point_manage']['pickup_point_batch_add']  = 'pickup_point.php?act=batch_add';
/* 代码增加_end   By www.ecshop68.com */

/* 代码增加_start   By  www.68ecshop.com */
$modules['03_promotion']['19_valuecard_list']          = 'valuecard.php?act=list';
$_LANG['19_valuecard_list'] ='储值卡管理';
$purview['19_valuecard_list']      = 'bonus_manage';
/* 代码增加_end   By  www.68ecshop.com */
?>
