<?php
//Avivi: delete tab "Quotes"
$arDeleteTabs = array('tab_quote');
foreach ($arResult['TABS'] as $key => $tab) {
    if (in_array($tab['id'], $arDeleteTabs))
        unset($arResult['TABS'][$key]);
}

$arResult['TABS'][] = array(
    'id' => 'tab_forms',
    'name' => "PO's",
    'loader' => array(
        'serviceUrl' => '/forms/show.php?&site=' . SITE_ID . '&' . bitrix_sessid_get(),
        'componentData' => array(
            'template' => '.default',
            'params' => array(
                "FROM_ENTITY" => "Y",
                "ENTITY_TYPE" => "L",
                "FORM_ENTITY_ID" => $arResult["ENTITY_DATA"]["ID"],
            )
        )
    )
);