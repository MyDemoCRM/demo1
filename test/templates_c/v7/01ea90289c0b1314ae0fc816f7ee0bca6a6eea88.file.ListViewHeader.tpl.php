<?php /* Smarty version Smarty-3.1.7, created on 2017-11-01 08:49:14
         compiled from "D:\UwAmp\www\vtigercrm7\includes\runtime/../../layouts/v7\modules\Users\ListViewHeader.tpl" */ ?>
<?php /*%%SmartyHeaderCode:491359f98a8a2f7605-94650932%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '01ea90289c0b1314ae0fc816f7ee0bca6a6eea88' => 
    array (
      0 => 'D:\\UwAmp\\www\\vtigercrm7\\includes\\runtime/../../layouts/v7\\modules\\Users\\ListViewHeader.tpl',
      1 => 1496723290,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '491359f98a8a2f7605-94650932',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE' => 0,
    'LISTVIEW_ENTIRES_COUNT' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_59f98a8a33db0',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_59f98a8a33db0')) {function content_59f98a8a33db0($_smarty_tpl) {?>

<div class="listViewPageDiv" id="listViewContent"><div class="col-sm-12 col-xs-12 full-height"><div id="listview-actions" class="listview-actions-container"><div class = "row"><div class="btn-group col-md-3"></div><div class='col-md-6' style="padding-top: 5px"><div class="btn-group userFilter" style="margin-left: 25%"><button class="btn btn-default btn-primary" id="activeUsers" data-searchvalue="Active"><?php echo vtranslate('LBL_ACTIVE_USERS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</button><button class="btn btn-default" id="inactiveUsers" data-searchvalue="Inactive"><?php echo vtranslate('LBL_INACTIVE_USERS',$_smarty_tpl->tpl_vars['MODULE']->value);?>
</button></div></div><div class="col-md-3"><?php $_smarty_tpl->tpl_vars['RECORD_COUNT'] = new Smarty_variable($_smarty_tpl->tpl_vars['LISTVIEW_ENTIRES_COUNT']->value, null, 0);?><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("Pagination.tpl",$_smarty_tpl->tpl_vars['MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array('SHOWPAGEJUMP'=>true), 0);?>
</div></div><div class="list-content">
<?php }} ?>