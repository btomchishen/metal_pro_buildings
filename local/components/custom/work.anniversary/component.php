<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule('intranet')) {
    return;
}

if (trim($arParams["NAME_TEMPLATE"]) == '') {
    $arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
}

$arParams['SHOW_YEAR'] = $arParams['SHOW_YEAR'] == 'Y' ? 'Y' : ($arParams['SHOW_YEAR'] == 'M' ? 'M' : 'N');

if (!$arParams['DATE_FORMAT']) $arParams['DATE_FORMAT'] = CComponentUtil::GetDateFormatDefault();
if (!$arParams['DATE_FORMAT_NO_YEAR']) $arParams['DATE_FORMAT_NO_YEAR'] = CComponentUtil::GetDateFormatDefault(true);

$arParams['DETAIL_URL'] = trim($arParams['DETAIL_URL']);
if (!$arParams['DETAIL_URL'])
    $arParams['~DETAIL_URL'] = $arParams['DETAIL_URL'] = COption::GetOptionString('intranet', 'search_user_url', '/user/#ID#/');

$arResult['USERS'] = array();

$users = \AviviWorkAnniversary::getCurrentMonthAnniversaries();

foreach ($users as $arUser) {
    $arAnniversaryDate = ParseDateTime($arUser[\AviviWorkAnniversary::WORK_ANNIVERSARY], CSite::GetDateFormat('SHORT'));

    $arUser['IS_ANNIVERSARY'] = (intval($arAnniversaryDate['MM']) == date('n', time() + CTimeZone::GetOffset())) && (intval($arAnniversaryDate['DD']) == date('j', time() + CTimeZone::GetOffset()));

    $arUser['arAnniversaryDate'] = $arAnniversaryDate;

    if ($arParams['DETAIL_URL'])
        $arUser['DETAIL_URL'] = str_replace(array('#ID#', '#USER_ID#'), $arUser['ID'], $arParams['DETAIL_URL']);

    if (!$arUser['PERSONAL_PHOTO']) {
        switch ($arUser['PERSONAL_GENDER']) {
            case "M":
                $suffix = "male";
                break;
            case "F":
                $suffix = "female";
                break;
            default:
                $suffix = "unknown";
        }
        $arUser['PERSONAL_PHOTO'] = COption::GetOptionInt("socialnetwork", "default_user_picture_" . $suffix, false, SITE_ID);
    }

    $arResult['USERS'][$arUser['ID']] = $arUser;
}

unset($users);

$this->IncludeComponentTemplate();