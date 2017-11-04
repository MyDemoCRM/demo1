<?php /* Smarty version Smarty-3.1.7, created on 2017-11-01 08:49:11
         compiled from "D:\UwAmp\www\vtigercrm7\includes\runtime/../../layouts/v7\modules\Settings\Vtiger\SidebarHeader.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1546259f98a87360d80-65085590%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '8c8e06972c4da3cc3eeae5f4fdfec453407d59a7' => 
    array (
      0 => 'D:\\UwAmp\\www\\vtigercrm7\\includes\\runtime/../../layouts/v7\\modules\\Settings\\Vtiger\\SidebarHeader.tpl',
      1 => 1496723290,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1546259f98a87360d80-65085590',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'SELECTED_MENU_CATEGORY' => 0,
    'MODULE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_59f98a8737848',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_59f98a8737848')) {function content_59f98a8737848($_smarty_tpl) {?>
<?php $_smarty_tpl->tpl_vars["APP_IMAGE_MAP"] = new Smarty_variable(array('MARKETING'=>'fa-users','SALES'=>'fa-dot-circle-o','SUPPORT'=>'fa-life-ring','INVENTORY'=>'vicon-inventory','PROJECT'=>'fa-briefcase'), null, 0);?>
<div class="col-sm-12 col-xs-12 app-indicator-icon-container app-<?php echo $_smarty_tpl->tpl_vars['SELECTED_MENU_CATEGORY']->value;?>
">
    <div class="row" title="<?php echo vtranslate("LBL_SETTINGS",$_smarty_tpl->tpl_vars['MODULE']->value);?>
">
        <span class="app-indicator-icon fa fa-cog"></span>
    </div>
</div>
    
<?php echo $_smarty_tpl->getSubTemplate ("modules/Vtiger/partials/SidebarAppMenu.tpl", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php }} ?>