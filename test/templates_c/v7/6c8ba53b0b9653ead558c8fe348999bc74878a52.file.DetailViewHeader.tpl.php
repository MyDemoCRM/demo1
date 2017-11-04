<?php /* Smarty version Smarty-3.1.7, created on 2017-11-01 08:50:26
         compiled from "D:\UwAmp\www\vtigercrm7\includes\runtime/../../layouts/v7\modules\Vtiger\DetailViewHeader.tpl" */ ?>
<?php /*%%SmartyHeaderCode:2516759f98ad26df607-07380254%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6c8ba53b0b9653ead558c8fe348999bc74878a52' => 
    array (
      0 => 'D:\\UwAmp\\www\\vtigercrm7\\includes\\runtime/../../layouts/v7\\modules\\Vtiger\\DetailViewHeader.tpl',
      1 => 1496723290,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '2516759f98ad26df607-07380254',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'MODULE' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_59f98ad26f2e8',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_59f98ad26f2e8')) {function content_59f98ad26f2e8($_smarty_tpl) {?>
<div class=" detailview-header-block"><div class="detailview-header"><div class="row"><?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("DetailViewHeaderTitle.tpl",$_smarty_tpl->tpl_vars['MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
<?php echo $_smarty_tpl->getSubTemplate (vtemplate_path("DetailViewActions.tpl",$_smarty_tpl->tpl_vars['MODULE']->value), $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, null, null, array(), 0);?>
</div></div><?php }} ?>