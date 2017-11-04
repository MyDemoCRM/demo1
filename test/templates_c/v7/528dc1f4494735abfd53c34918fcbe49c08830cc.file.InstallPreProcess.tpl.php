<?php /* Smarty version Smarty-3.1.7, created on 2017-11-01 07:23:19
         compiled from "D:\UwAmp\www\vtigercrm7\includes\runtime/../../layouts/v7\modules\Install\InstallPreProcess.tpl" */ ?>
<?php /*%%SmartyHeaderCode:1012359f97667519ea3-13241440%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '528dc1f4494735abfd53c34918fcbe49c08830cc' => 
    array (
      0 => 'D:\\UwAmp\\www\\vtigercrm7\\includes\\runtime/../../layouts/v7\\modules\\Install\\InstallPreProcess.tpl',
      1 => 1496723290,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1012359f97667519ea3-13241440',
  'function' => 
  array (
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.7',
  'unifunc' => 'content_59f9766752b36',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_59f9766752b36')) {function content_59f9766752b36($_smarty_tpl) {?>

<input type="hidden" id="module" value="Install" />
<input type="hidden" id="view" value="Index" />
<div class="container-fluid page-container">
	<div class="row">
		<div class="col-sm-6">
			<div class="logo">
				<img src="<?php echo vimage_path('logo.png');?>
"/>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="head pull-right">
				<h3><?php echo vtranslate('LBL_INSTALLATION_WIZARD','Install');?>
</h3>
			</div>
		</div>
	</div>
<?php }} ?>