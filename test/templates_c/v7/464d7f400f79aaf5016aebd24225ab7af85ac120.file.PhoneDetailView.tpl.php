<?php /* Smarty version Smarty-3.1.7, created on 2017-11-04 15:21:53
         compiled from "G:\UwAmp\www\github\demo1\includes\runtime/../../layouts/v7\modules\Vtiger\uitypes\PhoneDetailView.tpl" */ ?>
<?php /*%%SmartyHeaderCode:931859fddb113aaac3-08270607%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '464d7f400f79aaf5016aebd24225ab7af85ac120' => 
    array (
      0 => 'G:\\UwAmp\\www\\github\\demo1\\includes\\runtime/../../layouts/v7\\modules\\Vtiger\\uitypes\\PhoneDetailView.tpl',
      1 => 1496723290,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '931859fddb113aaac3-08270607',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'FIELD_MODEL' => 0,
    'RECORD' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_59fddb1141fdc',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_59fddb1141fdc')) {function content_59fddb1141fdc($_smarty_tpl) {?>

<?php $_smarty_tpl->tpl_vars['FIELD_VALUE'] = new Smarty_variable($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue'), null, 0);?>
<?php echo $_smarty_tpl->tpl_vars['FIELD_MODEL']->value->getDisplayValue($_smarty_tpl->tpl_vars['FIELD_MODEL']->value->get('fieldvalue'),$_smarty_tpl->tpl_vars['RECORD']->value->getId(),$_smarty_tpl->tpl_vars['RECORD']->value);?>

<?php }} ?>