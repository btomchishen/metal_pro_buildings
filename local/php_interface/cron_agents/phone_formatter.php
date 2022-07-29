<?
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("BX_CRONTAB", true);
define('BX_WITH_ON_AFTER_EPILOG', true);
define('BX_NO_ACCELERATOR_RESET', true);
set_time_limit(0);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

Bitrix\Main\Loader::includeModule('crm');

$dbResMultiFields = CCrmFieldMulti::GetList(
	array('ID' => 'asc'),
	array('TYPE_ID' => 'PHONE')
);

$chars = array('+', '-', ' ', '(', ')');

while($arMultiFields = $dbResMultiFields->Fetch())
{
	$number = $arMultiFields['VALUE'];
	$typeId = $arMultiFields['TYPE_ID'];
	$valueType = $arMultiFields['VALUE_TYPE'];
	$complexId = $arMultiFields['COMPLEX_ID'];
	$id = $arMultiFields['ID'];

	$finishNumber = str_replace($chars, '', $number);
	$finishNumber = '+'.$finishNumber;

	$cfm = new CCrmFieldMulti(false);
	$ar = array(
		'TYPE_ID'    => $typeId,
		'VALUE_TYPE'=> $valueType,
		'COMPLEX_ID'=> $complexId,
		'VALUE' => $finishNumber
	);

	$result = $cfm->Update($id, $ar);
}
$now = new DateTime();
fp($now, 'tomchyshen_phone_formatter_worked');
?>