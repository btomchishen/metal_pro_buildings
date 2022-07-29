<?require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
use Bitrix\Main\Context;
global $APPLICATION;
$request = Context::getCurrent()->getRequest();
if(isset($request["DELETE_DATA"]) && $request["DELETE_DATA"] == "Y")
{
    $res = CHighData::DeleteRecord(QUOTATION_SYSTEM_HIGHLOAD, $request["QUOTATION_ID"]);
}