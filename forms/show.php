<? require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
global $APPLICATION;

use Bitrix\Main\Context;

$request = Context::getCurrent()->getRequest();
$APPLICATION->ShowAjaxHead();
?>

<?
$APPLICATION->IncludeComponent(
    "custom:forms.grid",
    ".default",
    array
    (
        "FROM_ENTITY" => $request["PARAMS"]["params"]["FROM_ENTITY"],
        "ENTITY_TYPE" => $request["PARAMS"]["params"]["ENTITY_TYPE"],
        "FORM_ENTITY_ID" => $request["PARAMS"]["params"]["FORM_ENTITY_ID"],
    ),
    false,
    array('HIDE_ICONS' => 'Y', 'ACTIVE_COMPONENT' => 'Y')
);
?>

