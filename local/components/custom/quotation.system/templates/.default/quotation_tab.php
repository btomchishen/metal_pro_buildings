<?require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
global $APPLICATION;
use Bitrix\Main\Context;
$request = Context::getCurrent()->getRequest();
$APPLICATION->ShowAjaxHead();
?>

<?
$APPLICATION->IncludeComponent(
	"custom:quotation.grid",
	".default",
    array
    (
        "FROM_ENTITY" => $request["PARAMS"]["params"]["FROM_ENTITY"],
        "ENTITY_TYPE" => $request["PARAMS"]["params"]["ENTITY_TYPE"],
        "QUATATION_ENTITY_ID" => $request["PARAMS"]["params"]["QUATATION_ENTITY_ID"],
        "QUATATION_CLIENT_NAME" => $request["PARAMS"]["params"]["QUATATION_CLIENT_NAME"],
        "QUATATION_COMPANY_TITLE"=> $request["PARAMS"]["params"]["QUATATION_COMPANY_TITLE"],
        "QUATATION_CLIENT_TEL"=> $request["PARAMS"]["params"]["QUATATION_CLIENT_TEL"],
        "QUATATION_CLIENT_WORK_TEL"=> $request["PARAMS"]["params"]["QUATATION_CLIENT_WORK_TEL"],
        "QUATATION_CLIENT_CELL"=> $request["PARAMS"]["params"]["QUATATION_CLIENT_CELL"],
        "QUATATION_CLIENT_EMAIL"=> $request["PARAMS"]["params"]["QUATATION_CLIENT_EMAIL"],
        "QUATATION_CLIENT_POSTAL_CODE"=> $request["PARAMS"]["params"]["QUATATION_CLIENT_POSTAL_CODE"],
        "QUATATION_CLIENT_CITY"=> $request["PARAMS"]["params"]["QUATATION_CLIENT_CITY"],
        "QUATATION_OWNER"=> $request["PARAMS"]["params"]["QUATATION_OWNER"],
    ),
    false,
    array('HIDE_ICONS' => 'Y', 'ACTIVE_COMPONENT' => 'Y')
);
?>

