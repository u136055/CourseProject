<?
define("MODULE_ID", "lists");
define("ENTITY", "BizprocDocument");

$fp = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizprocdesigner/admin/bizproc_wf_settings.php";
if (file_exists($fp))
	require($fp);