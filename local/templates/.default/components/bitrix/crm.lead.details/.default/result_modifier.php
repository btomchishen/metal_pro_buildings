<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

//Avivi: delete tab "Quotes"
$arDeleteTabs = array('tab_quote', 'tab_products', 'tab_automation', 'tab_bizproc');
foreach($arResult['TABS'] as $key => $tab)
{
    if(in_array($tab['id'], $arDeleteTabs))
        unset($arResult['TABS'][$key]);
}

//Avivi: creating a tab and transferring data from an entity to it
if(isset($arResult["ENTITY_DATA"]["PHONE"]) && !empty($arResult["ENTITY_DATA"]["PHONE"]))
{
    foreach ($arResult["ENTITY_DATA"]["PHONE"] as $phone)
    {
        if($phone["VALUE_TYPE"] == "WORK")
            $workPhone = $phone["VALUE"];
        if($phone["VALUE_TYPE"] == "MOBILE")
            $cellPhone = $phone["VALUE"];
        if($phone["VALUE_TYPE"] == "HOME")
            $homePhone = $phone["VALUE"];
    }
}

if(isset($arResult["ENTITY_DATA"]["EMAIL"]) && !empty($arResult["ENTITY_DATA"]["EMAIL"]))
{
    $emailData = $arResult["ENTITY_DATA"]["EMAIL"];
    $emailData = array_shift($emailData);
}

$arResult['TABS'][] = array(
    'id' => 'tab_quotation',
    'name' => "Quotes",
    'loader' => array(
        'serviceUrl' => '/local/components/custom/quotation.system/templates/.default/quotation_tab.php?&site='.SITE_ID.'&'.bitrix_sessid_get(),
        'componentData' => array(
            'template' => '.default',
            'params' => array(
                "FROM_ENTITY" => "Y",
                "ENTITY_TYPE" => "L",
                "QUATATION_ENTITY_ID" => $arResult["ENTITY_DATA"]["ID"],
                "QUATATION_CLIENT_NAME" => !empty($arResult["ENTITY_DATA"]["FULL_NAME"]) ? $arResult["ENTITY_DATA"]["FULL_NAME"] : "N",
                "QUATATION_COMPANY_TITLE" => !empty($arResult["ENTITY_DATA"]["COMPANY_TITLE"]) ? $arResult["ENTITY_DATA"]["COMPANY_TITLE"] : "N",
                "QUATATION_CLIENT_TEL" => isset($homePhone) && !empty($homePhone) ? $homePhone : "N",
                "QUATATION_CLIENT_WORK_TEL" => isset($workPhone) && !empty($workPhone) ? $workPhone : "N",
                "QUATATION_CLIENT_CELL" => isset($cellPhone) && !empty($cellPhone) ? $cellPhone : "N",
                "QUATATION_CLIENT_EMAIL" => isset($emailData["VALUE"]) && !empty($emailData["VALUE"]) ? $emailData["VALUE"] : "N",
                "QUATATION_CLIENT_CITY" => !empty($arResult["ENTITY_DATA"]["ADDRESS_CITY"]) ? $arResult["ENTITY_DATA"]["ADDRESS_CITY"] : "N",
                "QUATATION_CLIENT_POSTAL_CODE" => !empty($arResult["ENTITY_DATA"]["ADDRESS_POSTAL_CODE"]) ? $arResult["ENTITY_DATA"]["ADDRESS_POSTAL_CODE"] : "N",
                "QUATATION_OWNER" => !empty($arResult["ENTITY_DATA"]["ASSIGNED_BY_ID"]) ? $arResult["ENTITY_DATA"]["ASSIGNED_BY_ID"] : "N",
            )
        )
    )
);