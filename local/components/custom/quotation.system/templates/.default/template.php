<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Context;
global $APPLICATION;
$request = Context::getCurrent()->getRequest();
CJSCore::Init(array("jquery","date"));
$APPLICATION->SetAdditionalCSS('/bitrix/js/crm/css/crm.css');
$APPLICATION->AddHeadScript('/bitrix/js/crm/interface_form.js');
$APPLICATION->AddHeadScript('/bitrix/js/crm/common.js');
$APPLICATION->AddHeadScript('/bitrix/js/main/dd.js');
$APPLICATION->AddHeadScript('/local/assets/js/jquery.maskMoney.js');
$APPLICATION->AddHeadScript('/local/assets/select2/dist/js/select2.js');
$APPLICATION->SetAdditionalCSS('/local/assets/select2/dist/css/select2.css');
\Bitrix\Main\UI\Extension::load("ui.buttons"); 
\Bitrix\Main\UI\Extension::load("ui.notification");
?>
<div class="form-container bx-interface-form bx-crm-edit-form">
    <?
    if (isset($request["MODE_SWITCH"]) && ($request['MODE_SWITCH'] == 'Y')) 
        $APPLICATION->RestartBuffer();
      
    ?>
    <?$file = isset($arResult["QUOTATION_DATA"]["QUOTATION_PDF"]) && !empty($arResult["QUOTATION_DATA"]["QUOTATION_PDF"]) ? CFile::GetPath($arResult["QUOTATION_DATA"]["QUOTATION_PDF"]) : "";?>
    <?\Bitrix\UI\Toolbar\Facade\Toolbar::addButton(array("link" => $file,"text" => "DOWNLOAD QUOTATION", "color" => \Bitrix\UI\Buttons\Color::PRIMARY, 'dataset' => array("role" =>"download_quotation")));?>
    <?\Bitrix\UI\Toolbar\Facade\Toolbar::addButton(array("link" => "","text" => "SHOW CALCULATION", "color" => \Bitrix\UI\Buttons\Color::PRIMARY,'dataset' => array("role" => "show_calculation")));?>
    <form method="POST" class="quatation-form<?=$request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"? " crm-show-quatation" : ""?>" enctype="multipart/form-data" data="<?=isset($arResult["QUOTATION_DATA"]["ID"]) && !empty($arResult["QUOTATION_DATA"]["ID"]) ? $arResult["QUOTATION_DATA"]["ID"] : '';?>"
    data-entity="<?=isset($arResult["QUOTATION_DATA"]["ENTITY_TYPE"]) && !empty($arResult["QUOTATION_DATA"]["ENTITY_TYPE"]) ? $arResult["QUOTATION_DATA"]["ENTITY_TYPE"] : '';?>">
            <table>
                <tbody>
                    <tr class="crm-offer-row" data-dragdrop-context="field" >
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("DATE_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span"><?=isset($arResult["QUOTATION_DATA"]["DATE"]) && !empty($arResult["QUOTATION_DATA"]["DATE"]) ? $arResult["QUOTATION_DATA"]["DATE"] : '';?></span>
                                <?else:?>
                                    <input id="DATE" name="DATE" class="crm-offer-item-inp crm-item-table-date" type="text" value="<?=isset($arResult["QUOTATION_DATA"]["DATE"]) && !empty($arResult["QUOTATION_DATA"]["DATE"]) ? $arResult["QUOTATION_DATA"]["DATE"] : '';?>">
                                    <script type="text/javascript">
                                        BX.ready(function(){ BX.CrmDateLinkField.create(BX('DATE'), null, { showTime: true, setFocusOnShow: false }); });
                                    </script>
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
				            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("CUSTOMER_ID_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <span style="<?=$request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"? "" : "display:none;"?>" class="crm-offer-item-span"><?=isset($arResult["QUOTATION_DATA"]["CUSTOMER_NAME"]) && !empty($arResult["QUOTATION_DATA"]["CUSTOMER_NAME"]) ? $arResult["QUOTATION_DATA"]["CUSTOMER_NAME"] : '';?></span>
                
                                <div class="<?=$request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"? "hide_selector" : ""?>">
                                    <?
                                    $fields = array(
                                        "FIELD_NAME" => "CUSTOMER_ID",
                                        "USER_TYPE_ID" => "crm",
                                        "MULTIPLE" => "N",
                                        "MANDATORY" => "N",
                                        "VALUE" =>  isset($arResult["QUOTATION_DATA"]["CUSTOMER_ID"]) && !empty($arResult["QUOTATION_DATA"]["CUSTOMER_ID"]) ? $arResult["QUOTATION_DATA"]["CUSTOMER_ID"] : '',
                                        "SETTINGS" => array(
                                        "LEAD" => "Y", "CONTACT" => "Y",
                                        "COMPANY" => "N", "DEAL" => "N",
                                        "ORDER" => "N"
                                        ),
                                        "USER_TYPE" => array(
                                        "USER_TYPE_ID" => "crm",
                                        "CLASS_NAME" => "CUserTypeCrm",
                                        "BASE_TYPE" => "string"
                                        )
                                    );
                                    $APPLICATION->includeComponent(
                                        "bitrix:system.field.edit",
                                        "crm-picker",
                                        array(
                                        "arUserField" => $fields,
                                        "bVarsFromForm" => false,
                                        "createNewEntity" => false
                                        ),
                                        false,
                                        array("HIDE_ICONS" => "Y")
                                    );
                                    ?>
                                </div>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("PURCHASE_ORDER_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <?foreach($arResult["LIST"]["PURCHASE_ORDER"] as $purchase):?>
                                        <?
                                            if($purchase["ID"] == $arResult["QUOTATION_DATA"]["PURCHASE_ORDER"])
                                                $value = $purchase["UF_PURCHASE_ORDER"];
                                        ?>
                                    <?endforeach;?>
                                    <span class="crm-offer-item-span"><?=!empty($value) ? $value : '';?></span>
                                <?else:?>
                                    <select class="crm-item-table-select select2-list" name="PURCHASE_ORDER" sale_order_marker="Y" style="width: 100%;">
                                        <option value=""><?=GetMessage("NOT_SELECTED_INPUT_TITLE");?></option>
                                        <?foreach($arResult["LIST"]["PURCHASE_ORDER"] as $purchase):?>
                                            <option value="<?=$purchase["ID"]?>" <?=isset($arResult["QUOTATION_DATA"]["PURCHASE_ORDER"]) && $purchase["ID"] == $arResult["QUOTATION_DATA"]["PURCHASE_ORDER"] ? "selected" : "";?>><?=$purchase["UF_PURCHASE_ORDER"]?></option>
                                        <?endforeach?>
                                    </select>
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
				            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("QUOATION_OWNER_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <span class="crm-offer-item-span" style="<?=$request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"? "" : "display:none;"?>"><?=isset($arResult["QUOTATION_DATA"]["OWNER"]) && !empty($arResult["QUOTATION_DATA"]["OWNER"]) ? $arResult["QUOTATION_DATA"]["OWNER"] : '';?></span>
                                <div class="<?=$request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"? "hide_selector" : ""?>">
                                    <?
                                        global $USER;
                                        if($request["ACTION"] == "NEW")
                                            $userId = array("U".$USER->GetID());
                                        else
                                            $userId = isset($arResult["QUOTATION_DATA"]["OWNER_ID"]) && !empty($arResult["QUOTATION_DATA"]["OWNER_ID"]) ? array("U".$arResult["QUOTATION_DATA"]["OWNER_ID"]) : "";
                                        $APPLICATION->IncludeComponent(
                                            "bitrix:main.user.selector",
                                            "",
                                            array(
                                            "ID" => 'QUOATION_OWNER_ID',
                                            "LIST" => $userId,
                                            "INPUT_NAME" => 'QUOATION_OWNER',
                                            "USE_SYMBOLIC_ID" => "Y",
                                            "BUTTON_SELECT_CAPTION" => "select",
                                            "API_VERSION" => 3,
                                            "AJAX_MODE" => "Y ",
                                            "SELECTOR_OPTIONS" => array(
                                                'allowUserSearch' => 'Y'
                                            )
                                        ));
                                    ?>
                                </div>
                            </div>
                        </td> 
                    </tr>
                </tbody>
            </table>
            <div style="width:100%">
                <div class="crm-offer-title">
                    <span class="crm-offer-title-text"><?=GetMessage("CLIENT_INFO_BLOCK");?></span>
                </div>
            </div>
            <table>
                <tbody>
                    <tr class="crm-offer-row" data-dragdrop-context="field">
			            <td class="crm-offer-info-left">
				            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("CLIENT_NAME_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["CLIENT_NAME"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_NAME"]) ? $arResult["QUOTATION_DATA"]["CLIENT_NAME"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp" name="CUSTOMER_NAME" 
                                    value="<?=isset($arResult["QUOTATION_DATA"]["CLIENT_NAME"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_NAME"]) ? $arResult["QUOTATION_DATA"]["CLIENT_NAME"] : '';?>" size="50"> 
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
				            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("TEL_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["CLIENT_TEL"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_TEL"]) ? $arResult["QUOTATION_DATA"]["CLIENT_TEL"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp" name="CUSTOMER_TEL" 
                                    value="<?=isset($arResult["QUOTATION_DATA"]["CLIENT_TEL"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_TEL"]) ? $arResult["QUOTATION_DATA"]["CLIENT_TEL"] : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
				            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("CELL_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["CLIENT_CELL"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_CELL"]) ? $arResult["QUOTATION_DATA"]["CLIENT_CELL"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp" name="CUSTOMER_CELL" 
                                    value="<?=isset($arResult["QUOTATION_DATA"]["CLIENT_CELL"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_CELL"]) ? $arResult["QUOTATION_DATA"]["CLIENT_CELL"] : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>
                    </tr>
                    <tr class="crm-offer-row" data-dragdrop-context="field">
			            <td class="crm-offer-info-left">
				            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("COMPANY_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["COMPANY_TITLE"]) && !empty($arResult["QUOTATION_DATA"]["COMPANY_TITLE"]) ? $arResult["QUOTATION_DATA"]["COMPANY_TITLE"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp" name="CUSTOMER_COMPANY" 
                                    value="<?=isset($arResult["QUOTATION_DATA"]["COMPANY_TITLE"]) && !empty($arResult["QUOTATION_DATA"]["COMPANY_TITLE"]) ? $arResult["QUOTATION_DATA"]["COMPANY_TITLE"] : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
				            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("WORK_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["CLIENT_WORK_TEL"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_WORK_TEL"]) ? $arResult["QUOTATION_DATA"]["CLIENT_WORK_TEL"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp" name="CUSTOMER_WORK_TEL" 
                                    value="<?=isset($arResult["QUOTATION_DATA"]["CLIENT_WORK_TEL"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_WORK_TEL"]) ? $arResult["QUOTATION_DATA"]["CLIENT_WORK_TEL"] : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
				            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("CLIENT_EMAIL_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["CLIENT_EMAIL"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_EMAIL"]) ? $arResult["QUOTATION_DATA"]["CLIENT_EMAIL"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp" name="CUSTOMER_EMAIL" 
                                    value="<?=isset($arResult["QUOTATION_DATA"]["CLIENT_EMAIL"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_EMAIL"]) ? $arResult["QUOTATION_DATA"]["CLIENT_EMAIL"] : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div style="width:100%">
                <div class="crm-offer-title">
                    <span class="crm-offer-title-text"><?=GetMessage("MAILING_ADDRESS_BLOCK");?></span>
                </div>
            </div>
            <table>
                <tbody>
                    <tr class="crm-offer-row" data-dragdrop-context="field">
                        <td class="crm-offer-info-left">
				            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("PROV_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["CLIENT_PROVINCE"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_PROVINCE"]) ? $arResult["QUOTATION_DATA"]["CLIENT_PROVINCE"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp" name="CUSTOMER_PROVINCE" value="<?=isset($arResult["QUOTATION_DATA"]["CLIENT_PROVINCE"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_PROVINCE"]) ? $arResult["QUOTATION_DATA"]["CLIENT_PROVINCE"] : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
				            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("CITY_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["CLIENT_CITY"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_CITY"]) ? $arResult["QUOTATION_DATA"]["CLIENT_CITY"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp" name="CUSTOMER_CITY" 
                                    value="<?=isset($arResult["QUOTATION_DATA"]["CLIENT_CITY"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_CITY"]) ? $arResult["QUOTATION_DATA"]["CLIENT_CITY"] : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
				            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("POSTAL_CODE_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["CLIENT_POSTAL_CODE"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_POSTAL_CODE"]) ? $arResult["QUOTATION_DATA"]["CLIENT_POSTAL_CODE"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp" name="CUSTOMER_POSTAL_CODE" 
                                    value="<?=isset($arResult["QUOTATION_DATA"]["CLIENT_POSTAL_CODE"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_POSTAL_CODE"]) ? $arResult["QUOTATION_DATA"]["CLIENT_POSTAL_CODE"] : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
				            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("ADDRESS_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span"><?=isset($arResult["QUOTATION_DATA"]["CLIENT_ADDRESS"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_ADDRESS"]) ? $arResult["QUOTATION_DATA"]["CLIENT_ADDRESS"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp" name="CUSTOMER_ADDRESS" 
                                    value="<?=isset($arResult["QUOTATION_DATA"]["CLIENT_ADDRESS"]) && !empty($arResult["QUOTATION_DATA"]["CLIENT_ADDRESS"]) ? $arResult["QUOTATION_DATA"]["CLIENT_ADDRESS"] : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div style="width:100%">
                <div class="crm-offer-title">
                    <span class="crm-offer-title-text"><?=GetMessage("BUILDING_ADDRESS_BLOCK");?></span>
                </div>
            </div>
            <table>
                <tbody>
                    <tr class="crm-offer-row" data-dragdrop-context="field">
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("PROV_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <?foreach($arResult["PROVINCIES"] as $province):?>
                                        <?
                                            if($province["ID"] == $arResult["QUOTATION_DATA"]["BUILDING_PROVINCE"])
                                                $value = $province["UF_PROVINCE_NAME"];
                                        ?>
                                    <?endforeach;?>
                                    <span class="crm-offer-item-span"><?=!empty($value) ? $value : '';?></span>
                                <?else:?>
                                    <select class="crm-item-table-select select2-list" id="building_province" name="BUILDING_PROVINCE" sale_order_marker="Y" style="width: 100%;">
                                        <option value=""><?=GetMessage("NOT_SELECTED_INPUT_TITLE");?></option>
                                        <?foreach($arResult["PROVINCIES"] as $province):?>
                                            <option <?=isset($arResult["QUOTATION_DATA"]["BUILDING_PROVINCE"]) && $province["ID"] == $arResult["QUOTATION_DATA"]["BUILDING_PROVINCE"] ? "selected" : "";?> value="<?=$province["ID"]?>"><?=$province["UF_PROVINCE_NAME"]?></option>
                                        <?endforeach?>
                                    <select>
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("CITY_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <?foreach($arResult["CITIES"] as $city):?>
                                        <?
                                            if($arResult["QUOTATION_DATA"]["BUILDING_CITY"] == $city["ID"])
                                                $cityValue = $city["UF_CITY"];
                                        ?>
                                    <?endforeach;?>
                                    <span class="crm-offer-item-span"><?=!empty($cityValue) ? $cityValue : '';?></span>
                                <?else:?>
                                    <select class="crm-item-table-select select2-list" id="building_city" name="BUILDING_CITY" sale_order_marker="Y" style="width: 100%;">
                                        <option value=""><?=GetMessage("NOT_SELECTED_INPUT_TITLE");?></option>
                                        <?foreach($arResult["CITIES"] as $city):?>
                                            <option <?=isset($arResult["QUOTATION_DATA"]["BUILDING_CITY"]) && $city["ID"] == $arResult["QUOTATION_DATA"]["BUILDING_CITY"] ? "selected" : "";?> value="<?=$city["ID"]?>"><?=$city["UF_CITY"]?></option>
                                        <?endforeach?>
                                    <select>
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("POSTAL_CODE_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["BUILDING_POSTAL_CODE"]) && !empty($arResult["QUOTATION_DATA"]["BUILDING_POSTAL_CODE"]) ? $arResult["QUOTATION_DATA"]["BUILDING_POSTAL_CODE"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp" name="BUILDING_POSTAL_CODE" value="<?=isset($arResult["QUOTATION_DATA"]["BUILDING_POSTAL_CODE"]) && !empty($arResult["QUOTATION_DATA"]["BUILDING_POSTAL_CODE"]) ? $arResult["QUOTATION_DATA"]["BUILDING_POSTAL_CODE"] : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
                                <div class="crm-offer-info-label-wrap">
                                    <span class="crm-offer-info-label"><?=GetMessage("ADDRESS_INPUT");?>:</span>
                                </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span"><?=isset($arResult["QUOTATION_DATA"]["BUILDING_ADDRESS"]) && !empty($arResult["QUOTATION_DATA"]["BUILDING_ADDRESS"]) ? $arResult["QUOTATION_DATA"]["BUILDING_ADDRESS"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp" name="BUILDING_ADDRESS" 
                                    value="<?=isset($arResult["QUOTATION_DATA"]["BUILDING_ADDRESS"]) && !empty($arResult["QUOTATION_DATA"]["BUILDING_ADDRESS"]) ? $arResult["QUOTATION_DATA"]["BUILDING_ADDRESS"] : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>
                        <!-- BUILDING_COUNTRY INPUT -->
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("COUNTRY_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span"><?=isset($arResult["QUOTATION_DATA"]["BUILDING_COUNTRY"]) && !empty($arResult["QUOTATION_DATA"]["BUILDING_COUNTRY"]) ? $arResult["QUOTATION_DATA"]["BUILDING_COUNTRY"] : '';?></span>
                                <?else:?>
                                    <select class="crm-item-table-select select2-list" id="building_country" name="BUILDING_COUNTRY" sale_order_marker="Y" style="width: 100%;">
                                        <option <?=isset($arResult["QUOTATION_DATA"]["BUILDING_COUNTRY"]) && $arResult["QUOTATION_DATA"]["BUILDING_COUNTRY"] == "CA" ? "selected" : "";?> value="СA"><?=GetMessage("СA_OPTION");?></option>
                                        <option <?=isset($arResult["QUOTATION_DATA"]["BUILDING_COUNTRY"]) && $arResult["QUOTATION_DATA"]["BUILDING_COUNTRY"] == "US" ? "selected" : "";?> value="US"><?=GetMessage("US_OPTION");?></option>
                                    <select>
                                <?endif;?>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div style="width:100%">
                <div class="crm-offer-title">
                    <span class="crm-offer-title-text"><?=GetMessage('BUILDING_INFORMATION_BLOCK');?></span>
                </div>
            </div>
            <table>
                <tbody>
                    <tr class="crm-offer-row" data-dragdrop-context="field">
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("USE_EXPOSURE_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <?foreach($arResult["LIST"]["USE_EXPOSURE"] as $exposure):?>
                                        <?
                                            if($exposure["ID"] == $arResult["QUOTATION_DATA"]["USE_EXPOSURE"] )
                                                $value = $exposure["UF_USE_EXPOSURE"];
                                        ?>
                                    <?endforeach;?>
                                    <span class="crm-offer-item-span"><?=!empty($value) ? $value : '';?></span>
                                <?else:?>
                                    <select class="crm-item-table-select select2-list" name="USE_EXPOSURE" sale_order_marker="Y" style="width: 100%;">
                                        <option value=""><?=GetMessage("NOT_SELECTED_INPUT_TITLE");?></option>
                                        <?foreach($arResult["LIST"]["USE_EXPOSURE"] as $exposure):?>
                                            <option <?=isset($arResult["QUOTATION_DATA"]["USE_EXPOSURE"]) && $exposure["ID"] == $arResult["QUOTATION_DATA"]["USE_EXPOSURE"] ? "selected" : "";?> value="<?=$exposure["ID"]?>"><?=$exposure["UF_USE_EXPOSURE"]?></option>
                                        <?endforeach?>
                                    <select>
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("SERIES_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <?foreach($arResult["LIST"]["SERIES"] as $series):?>
                                        <?
                                            if($series["ID"] == $arResult["QUOTATION_DATA"]["SERIES"])
                                                $value = $series["UF_SERIES"];
                                        ?>
                                    <?endforeach;?>
                                    <span class="crm-offer-item-span"><?=!empty($value) ? $value : '';?></span>
                                <?else:?>
                                    <select class="crm-item-table-select select2-list" name="SERIES" sale_order_marker="Y" style="width: 100%;">
                                        <option value=""><?=GetMessage("NOT_SELECTED_INPUT_TITLE");?></option>
                                        <?foreach($arResult["LIST"]["SERIES"] as $series):?>
                                            <option <?=isset($arResult["QUOTATION_DATA"]["SERIES"]) && $series["ID"] == $arResult["QUOTATION_DATA"]["SERIES"] ? "selected" : "";?> value="<?=$series["ID"]?>"><?=$series["UF_SERIES"]?></option>
                                        <?endforeach?>
                                    <select>
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("MODEL_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <?foreach($arResult["MODELS"] as $model):?>
                                        <?
                                            if($model["ID"] == $arResult["QUOTATION_DATA"]["MODEL"])
                                                $valueModel = $model["UF_MODEL"];
                                        ?>
                                    <?endforeach;?>
                                    <span class="crm-offer-item-span"><?=!empty($valueModel) ? $valueModel : '';?></span>
                                <?else:?>
                                    <select class="crm-item-table-select select2-list" name="MODEL" sale_order_marker="Y" style="width: 100%;">
                                        <option value=""><?=GetMessage("NOT_SELECTED_INPUT_TITLE");?></option>
                                        <?foreach($arResult["MODELS"] as $model):?>
                                            <??>
                                            <option <?=isset($arResult["QUOTATION_DATA"]["MODEL"]) && $model["ID"] == $arResult["QUOTATION_DATA"]["MODEL"] ? "selected" : "";?> value="<?=$model["ID"]?>"><?=$model["UF_MODEL"]?></option>
                                        <?endforeach?>
                                    <select>
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("FOUNDATION_SYSTEM_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <?foreach($arResult["LIST"]["FOUNDATION_SYSTEM"] as $system):?>
                                        <?
                                            if($system["ID"] == $arResult["QUOTATION_DATA"]["FOUNDATION_SYSTEM"])
                                                $value = $system["UF_FOUNDATION_SYSTEM"];
                                        ?>
                                    <?endforeach;?>
                                    <span class="crm-offer-item-span"><?=!empty($value) ? $value : '';?></span>
                                <?else:?>
                                    <select id="foundation_system" class="crm-item-table-select select2-list" name="FOUNDATION_SYSTEM" sale_order_marker="Y" style="width: 100%;">
                                        <option value=""><?=GetMessage("NOT_SELECTED_INPUT_TITLE");?></option>
                                        <?foreach($arResult["LIST"]["FOUNDATION_SYSTEM"] as $system):?>
                                            <option <?=isset($arResult["QUOTATION_DATA"]["FOUNDATION_SYSTEM"]) && $system["ID"] == $arResult["QUOTATION_DATA"]["FOUNDATION_SYSTEM"] ? "selected" : "";?> value="<?=$system["ID"]?>"><?=$system["UF_FOUNDATION_SYSTEM"]?></option>
                                        <?endforeach?>
                                    <select>
                                <?endif;?>
                            </div>
                        </td>
                    <tr>
                    <tr class="crm-offer-row" data-dragdrop-context="field">
                        <td class="crm-offer-info-left">
				            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("WIDTH_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["WIDTH"]) && !empty($arResult["QUOTATION_DATA"]["WIDTH"]) ? $arResult["QUOTATION_DATA"]["WIDTH"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp narrow-input" name="WIDTH" value="<?=isset($arResult["QUOTATION_DATA"]["WIDTH"]) && !empty($arResult["QUOTATION_DATA"]["WIDTH"]) ? $arResult["QUOTATION_DATA"]["WIDTH"] : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
				            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("LENGTH_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["LENGTH"]) && !empty($arResult["QUOTATION_DATA"]["LENGTH"]) ? $arResult["QUOTATION_DATA"]["LENGTH"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp narrow-input" name="LENGTH" value="<?=isset($arResult["QUOTATION_DATA"]["LENGTH"]) && !empty($arResult["QUOTATION_DATA"]["LENGTH"]) ? $arResult["QUOTATION_DATA"]["LENGTH"] : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
				            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("HEIGHT_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["HEIGHT"]) && !empty($arResult["QUOTATION_DATA"]["HEIGHT"]) ? $arResult["QUOTATION_DATA"]["HEIGHT"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp narrow-input" name="HEIGHT" value="<?=isset($arResult["QUOTATION_DATA"]["HEIGHT"]) && !empty($arResult["QUOTATION_DATA"]["HEIGHT"]) ? $arResult["QUOTATION_DATA"]["HEIGHT"] : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-right" style="margin-left: 20px;">
                            <div class="crm-offer-info-data-wrap">
                                <input class="crm-offer-checkbox" id="anchors" type="checkbox" name="ANCHORS" value="Y" 
                                <?=$request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"? "disabled='disabled'" : ""?>
                                <?=isset($arResult["QUOTATION_DATA"]["ANCHORS"]) && $arResult["QUOTATION_DATA"]["ANCHORS"] == "Y" ? "checked" : "";?>>
                                <label class="crm-offer-label" for="anchors"><?=GetMessage("ANCHORS_INPUT");?></label>
                            </div>
                        </td>

                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label">PSF:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right psf" >
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["PSF"]) && !empty($arResult["QUOTATION_DATA"]["PSF"]) ? $arResult["QUOTATION_DATA"]["PSF"] : '';?></span>
                                <?else:?>
                                    <input id="PSF" readonly type="text" class="crm-offer-item-inp narrow-input" name="PSF" value="<?=isset($arResult["QUOTATION_DATA"]["PSF"]) && !empty($arResult["QUOTATION_DATA"]["PSF"]) ? $arResult["QUOTATION_DATA"]["PSF"] : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>

                    </tr>
                </tbody>
            </table>
            <div style="width:100%">
                <div class="crm-offer-title">
                    <span class="crm-offer-title-text"><?=GetMessage("FRONT_WALL_BLOCK");?></span>
                </div>
            </div>
            <table>
                <tbody> 
                    <tr class="crm-offer-row" data-dragdrop-context="field">
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("TYPE_INPUT");?></span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <?foreach($arResult["LIST"]["WALL_TYPE"] as $wallType):?>
                                        <?
                                            if($wallType["ID"] == $arResult["QUOTATION_DATA"]["FRONT_WALL_TYPE"])
                                                $value = $wallType["UF_WALL_TYPE"];
                                        ?>
                                    <?endforeach;?>
                                    <span class="crm-offer-item-span"><?=!empty($value) ? $value : '';?></span>
                                <?else:?>
                                    <select id="front_wall" class="crm-item-table-select select2-list" name="FRONT_WALL_TYPE" sale_order_marker="Y">
                                        <option value=""><?=GetMessage("NOT_SELECTED_INPUT_TITLE");?></option>
                                        <?foreach($arResult["LIST"]["WALL_TYPE"] as $wallType):?>
                                            <option <?=isset($arResult["QUOTATION_DATA"]["FRONT_WALL_TYPE"]) && $wallType["ID"] == $arResult["QUOTATION_DATA"]["FRONT_WALL_TYPE"] ? "selected" : "";?> value="<?=$wallType["ID"]?>"><?=$wallType["UF_WALL_TYPE"]?></option>
                                        <?endforeach?>
                                    <select>
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("QUANTITY_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["FRONT_WALL_QUANTITY"]) && !empty($arResult["QUOTATION_DATA"]["FRONT_WALL_QUANTITY"]) ? $arResult["QUOTATION_DATA"]["FRONT_WALL_QUANTITY"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp narrow-input" name="FRONT_WALL_QUANTITY" value="<?=isset($arResult["QUOTATION_DATA"]["FRONT_WALL_QUANTITY"]) && !empty($arResult["QUOTATION_DATA"]["FRONT_WALL_QUANTITY"]) ? $arResult["QUOTATION_DATA"]["FRONT_WALL_QUANTITY"] : '';?>" size="50">  
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("WIDTH_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["FRONT_WALL_WIDTH"]) && !empty($arResult["QUOTATION_DATA"]["FRONT_WALL_WIDTH"]) ? $arResult["QUOTATION_DATA"]["FRONT_WALL_WIDTH"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp narrow-input" name="FRONT_WALL_WIDTH" value="<?=isset($arResult["QUOTATION_DATA"]["FRONT_WALL_WIDTH"]) && !empty($arResult["QUOTATION_DATA"]["FRONT_WALL_WIDTH"]) ? $arResult["QUOTATION_DATA"]["FRONT_WALL_WIDTH"] : '';?>" size="50">  
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("HEIGHT_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["FRONT_WALL_HEIGHT"]) && !empty($arResult["QUOTATION_DATA"]["FRONT_WALL_HEIGHT"]) ? $arResult["QUOTATION_DATA"]["FRONT_WALL_HEIGHT"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp narrow-input" name="FRONT_WALL_HEIGHT" value="<?=isset($arResult["QUOTATION_DATA"]["FRONT_WALL_HEIGHT"]) && !empty($arResult["QUOTATION_DATA"]["FRONT_WALL_HEIGHT"]) ? $arResult["QUOTATION_DATA"]["FRONT_WALL_HEIGHT"] : '';?>" size="50">  
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("SEA_CONTAINER_HEIGHT_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["FRONT_WALL_SEA_HEIGHT"]) && !empty($arResult["QUOTATION_DATA"]["FRONT_WALL_SEA_HEIGHT"]) ? $arResult["QUOTATION_DATA"]["FRONT_WALL_SEA_HEIGHT"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp narrow-input" name="FRONT_WALL_SEA_HEIGHT" value="<?=isset($arResult["QUOTATION_DATA"]["FRONT_WALL_SEA_HEIGHT"]) && !empty($arResult["QUOTATION_DATA"]["FRONT_WALL_SEA_HEIGHT"]) ? $arResult["QUOTATION_DATA"]["FRONT_WALL_SEA_HEIGHT"] : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("ENDWALL_OFFSET_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span"><?=isset($arResult["QUOTATION_DATA"]["FRONT_WALL_OFFSET"]) && !empty($arResult["QUOTATION_DATA"]["FRONT_WALL_OFFSET"]) ? $arResult["QUOTATION_DATA"]["FRONT_WALL_OFFSET"] : '';?></span>
                                <?else:?>
                                <select class="crm-item-table-select select2-list" id="front_wall_offset" name="FRONT_WALL_OFFSET" sale_order_marker="Y" style="width: 100%;">
                                    <option <?=isset($arResult["QUOTATION_DATA"]["FRONT_WALL_OFFSET"]) && $arResult["QUOTATION_DATA"]["FRONT_WALL_OFFSET"] == "NO" ? "selected" : "";?> value="NO">No</option>
                                    <option <?=isset($arResult["QUOTATION_DATA"]["FRONT_WALL_OFFSET"]) && $arResult["QUOTATION_DATA"]["FRONT_WALL_OFFSET"] == "YES" ? "selected" : "";?> value="YES">Yes</option>
                                    <select>
                                        <?endif;?>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div style="width:100%">
                <div class="crm-offer-title">
                    <span class="crm-offer-title-text"><?=GetMessage("REAR_WALL_BLOCK");?></span>
                </div>
            </div>
            <table>
                <tbody> 
                    <tr class="crm-offer-row" data-dragdrop-context="field">
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("TYPE_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <?foreach($arResult["LIST"]["WALL_TYPE"] as $wallType):?>
                                        <?
                                            if($wallType["ID"] == $arResult["QUOTATION_DATA"]["REAR_WALL_TYPE"])
                                                $value = $wallType["UF_WALL_TYPE"];
                                        ?>
                                    <?endforeach;?>
                                    <span class="crm-offer-item-span"><?=!empty($value) ? $value : '';?></span>
                                <?else:?>
                                    <select id="rear_wall" class="crm-item-table-select select2-list" name="REAR_WALL_TYPE" sale_order_marker="Y" >
                                        <option value=""><?=GetMessage("NOT_SELECTED_INPUT_TITLE");?></option>
                                        <?foreach($arResult["LIST"]["WALL_TYPE"] as $wallType):?>
                                            <option <?=isset($arResult["QUOTATION_DATA"]["REAR_WALL_TYPE"]) && $wallType["ID"] == $arResult["QUOTATION_DATA"]["REAR_WALL_TYPE"] ? "selected" : "";?> value="<?=$wallType["ID"]?>"><?=$wallType["UF_WALL_TYPE"]?></option>
                                        <?endforeach?>
                                    <select>
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">  
                                <span class="crm-offer-info-label"><?=GetMessage("QUANTITY_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["REAR_WALL_QUANTITY"]) && !empty($arResult["QUOTATION_DATA"]["REAR_WALL_QUANTITY"]) ? $arResult["QUOTATION_DATA"]["REAR_WALL_QUANTITY"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp narrow-input" name="REAR_WALL_QUANTITY" value="<?=isset($arResult["QUOTATION_DATA"]["REAR_WALL_QUANTITY"]) && !empty($arResult["QUOTATION_DATA"]["REAR_WALL_QUANTITY"]) ? $arResult["QUOTATION_DATA"]["REAR_WALL_QUANTITY"] : '';?>" size="50">  
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("WIDTH_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["REAR_WALL_WIDTH"]) && !empty($arResult["QUOTATION_DATA"]["REAR_WALL_WIDTH"]) ? $arResult["QUOTATION_DATA"]["REAR_WALL_WIDTH"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp narrow-input" name="REAR_WALL_WIDTH" value="<?=isset($arResult["QUOTATION_DATA"]["REAR_WALL_WIDTH"]) && !empty($arResult["QUOTATION_DATA"]["REAR_WALL_WIDTH"]) ? $arResult["QUOTATION_DATA"]["REAR_WALL_WIDTH"] : '';?>" size="50">  
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("HEIGHT_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["REAR_WALL_HEIGHT"]) && !empty($arResult["QUOTATION_DATA"]["REAR_WALL_HEIGHT"]) ? $arResult["QUOTATION_DATA"]["REAR_WALL_HEIGHT"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp narrow-input" name="REAR_WALL_HEIGHT" value="<?=isset($arResult["QUOTATION_DATA"]["REAR_WALL_HEIGHT"]) && !empty($arResult["QUOTATION_DATA"]["REAR_WALL_HEIGHT"]) ? $arResult["QUOTATION_DATA"]["REAR_WALL_HEIGHT"] : '';?>" size="50">  
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("SEA_CONTAINER_HEIGHT_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["REAR_WALL_SEA_HEIGHT"]) && !empty($arResult["QUOTATION_DATA"]["REAR_WALL_SEA_HEIGHT"]) ? $arResult["QUOTATION_DATA"]["REAR_WALL_SEA_HEIGHT"] : '';?></span>
                                <?else:?>
                                    <input type="text" class="crm-offer-item-inp narrow-input" name="REAR_WALL_SEA_HEIGHT" value="<?=isset($arResult["QUOTATION_DATA"]["REAR_WALL_SEA_HEIGHT"]) && !empty($arResult["QUOTATION_DATA"]["REAR_WALL_SEA_HEIGHT"]) ? $arResult["QUOTATION_DATA"]["REAR_WALL_SEA_HEIGHT"] : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("ENDWALL_OFFSET_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span"><?=isset($arResult["QUOTATION_DATA"]["REAR_WALL_OFFSET"]) && !empty($arResult["QUOTATION_DATA"]["REAR_WALL_OFFSET"]) ? $arResult["QUOTATION_DATA"]["REAR_WALL_OFFSET"] : '';?></span>
                                <?else:?>
                                <select class="crm-item-table-select select2-list" id="rear_wall_offset" name="REAR_WALL_OFFSET" sale_order_marker="Y" style="width: 100%;">
                                    <option <?=isset($arResult["QUOTATION_DATA"]["REAR_WALL_OFFSET"]) && $arResult["QUOTATION_DATA"]["REAR_WALL_OFFSET"] == "NO" ? "selected" : "";?> value="NO">No</option>
                                    <option <?=isset($arResult["QUOTATION_DATA"]["REAR_WALL_OFFSET"]) && $arResult["QUOTATION_DATA"]["REAR_WALL_OFFSET"] == "YES" ? "selected" : "";?> value="YES">Yes</option>
                                    <select>
                                        <?endif;?>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div style="width:100%">
                <div class="crm-offer-title">
                    <span class="crm-offer-title-text"><?=GetMessage("ACCESSORIES_BLOCK");?></span>
                </div>
            </div>
            <table style="width: 98%;">
                <tbody class="accessories-block"> 
                    <?if(isset($arResult["QUOTATION_DATA"]["ACCESSORIES"]) && count($arResult["QUOTATION_DATA"]["ACCESSORIES"]) > 0):?>
                        <?foreach($arResult["QUOTATION_DATA"]["ACCESSORIES"] as $searchIndex => &$accessoryUnit):?>
                            <?$num = $searchIndex == 0 ? "" : "_".$searchIndex;?>
                            <tr class="crm-offer-row accessories-tr" data-dragdrop-context="field">    
                                <td class="crm-offer-info-left">
                                    <div class="crm-offer-info-label-wrap">
                                        <span class="crm-offer-info-label"><?=GetMessage("TYPE_INPUT");?>:</span>
                                    </div>
                                </td>
                                <td class="crm-offer-info-right">
                                    <div class="crm-offer-info-data-wrap accessory-type-wrap">
                                    <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                        <?if(!empty($accessoryUnit["ACCESSORIES_TYPE"])):?>
                                            <?foreach($arResult["LIST"]["ACCESSORIES_TYPE"] as $type):?>
                                                <?
                                                    if($type["ID"] == $accessoryUnit["ACCESSORIES_TYPE"])
                                                        $value = $type["UF_ACCESSORIES_TYPE_LIST"];
                                                ?>
                                            <?endforeach;?>
                                            <span class="crm-offer-item-span"><?=!empty($value) ? $value : '';?></span>
                                        <?endif;?>
                                    <?else:?>
                                        <select style="width:100%;" class="crm-item-table-select select2-list accessories-type" name="ACCESSORIES_TYPE<?=$num?>" sale_order_marker="Y" style="width: 100%;">
                                            <option value=""><?=GetMessage("NOT_SELECTED_INPUT_TITLE");?></option>
                                            <?foreach($arResult["LIST"]["ACCESSORIES_TYPE"] as $type):?>
                                                <option <?=isset($accessoryUnit["ACCESSORIES_TYPE"]) && $type["ID"] == $accessoryUnit["ACCESSORIES_TYPE"] ? "selected" : "";?> value="<?=$type["ID"]?>"><?=$type["UF_ACCESSORIES_TYPE_LIST"]?></option>
                                            <?endforeach?>
                                        <select>
                                    <?endif;?>
                                    </div>
                                </td>
                                <td class="crm-offer-info-left">
                                    <div class="crm-offer-info-label-wrap">
                                        <span class="crm-offer-info-label"><?=GetMessage("ACCESSORY_INPUT");?>:</span>
                                    </div>
                                </td>
                                <td class="crm-offer-info-right">
                                    <div class="crm-offer-info-data-wrap accessory-list-wrap">
                                        <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                            <?if(!empty($accessoryUnit["ACCESSORY"])):?>
                                                <?foreach($arResult["ACCESSORIES"] as $accessory):?>
                                                    <?
                                                        if($accessory["ID"] == $accessoryUnit["ACCESSORY"])
                                                            $value = $accessory["UF_ACCESSORIES_TYPE"] . (!empty($accessory["UF_WIDTH"]) || !empty($accessory["UF_HEIGHT"]) ? " " .$accessory["UF_WIDTH"] ."/". $accessory["UF_HEIGHT"] : ""); 
                                                            $accessoryUnit["VALUE"] = $value;
                                                    ?>
                                                <?endforeach;?>
                                                <span class="crm-offer-item-span"><?=!empty($value) ? $value : '';?></span>
                                            <?endif;?>
                                        <?else:?>
                                            <select style="width:100%" class="crm-item-table-select select2-list accessories-list" name="ACCESSORY<?=$num?>" sale_order_marker="Y">
                                                <option value=""><?=GetMessage("NOT_SELECTED_INPUT_TITLE");?></option>
                                                <?foreach($arResult["ACCESSORIES"] as $accessory):?>
                                                    <option <?=isset($accessoryUnit["ACCESSORY"]) && $accessory["ID"] == $accessoryUnit["ACCESSORY"] ? "selected" : "";?> data-width="<?=$accessory["UF_WIDTH"]?>" data-height="<?=$accessory["UF_HEIGHT"]?>" value="<?=$accessory["ID"]?>"><?=$accessory["UF_ACCESSORIES_TYPE"]?> 
                                                        <?if(!empty($accessory["UF_WIDTH"]) || !empty($accessory["UF_HEIGHT"])):?>
                                                            <?=$accessory["UF_WIDTH"]?>/<?=$accessory["UF_HEIGHT"]?>
                                                        <?endif;?>
                                                    </option>
                                                <?endforeach?>
                                            <select>
                                        <?endif;?>
                                    </div>
                                </td>
                                <td class="crm-offer-info-left">
                                    <div class="crm-offer-info-label-wrap">
                                        <span class="crm-offer-info-label"><?=GetMessage("QUANTITY_INPUT");?>:</span>
                                    </div>
                                </td>
                                <td class="crm-offer-info-right">
                                    <div class="crm-offer-info-data-wrap">
                                    <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                        <span class="crm-offer-item-span "><?=isset($accessoryUnit["ACCESSORIES_QUANTITY"]) && !empty($accessoryUnit["ACCESSORIES_QUANTITY"]) ? $accessoryUnit["ACCESSORIES_QUANTITY"] : '';?></span>
                                    <?else:?>
                                        <input type="number" min="1" class="crm-offer-item-inp narrow-input accessory-quantity" name="ACCESSORIES_QUANTITY<?=$num?>" value="<?=isset($accessoryUnit["ACCESSORIES_QUANTITY"]) && !empty($accessoryUnit["ACCESSORIES_QUANTITY"]) ? $accessoryUnit["ACCESSORIES_QUANTITY"] : '';?>" size="50"> 
                                    <?endif;?> 
                                    </div>
                                </td>
                                <td class="crm-offer-info-left">
                                    <div class="crm-offer-info-label-wrap">
                                        <span class="crm-offer-info-label"><?=GetMessage("WIDTH_INPUT");?>:</span>
                                    </div>
                                </td>
                                <td class="crm-offer-info-right">
                                    <div class="crm-offer-info-data-wrap">
                                        <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                            <span class="crm-offer-item-span "><?=isset($accessoryUnit["ACCESSORIES_WIDTH"]) && !empty($accessoryUnit["ACCESSORIES_WIDTH"]) ? $accessoryUnit["ACCESSORIES_WIDTH"] : '';?></span>
                                        <?else:?>
                                            <input type="text" class="crm-offer-item-inp narrow-input accessory-width" name="ACCESSORIES_WIDTH<?=$num?>" value="<?=isset($accessoryUnit["ACCESSORIES_WIDTH"]) && !empty($accessoryUnit["ACCESSORIES_WIDTH"]) ? $accessoryUnit["ACCESSORIES_WIDTH"] : '';?>" size="50"> 
                                        <?endif;?> 
                                    </div>
                                </td>
                                <td class="crm-offer-info-left">
                                    <div class="crm-offer-info-label-wrap">
                                        <span class="crm-offer-info-label"><?=GetMessage("HEIGHT_INPUT");?>:</span>
                                    </div>
                                </td>
                                <td class="crm-offer-info-right">
                                    <div class="crm-offer-info-data-wrap">
                                        <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                            <span class="crm-offer-item-span "><?=isset($accessoryUnit["ACCESSORIES_HEIGHT"]) && !empty($accessoryUnit["ACCESSORIES_HEIGHT"]) ? $accessoryUnit["ACCESSORIES_HEIGHT"] : '';?></span>
                                        <?else:?>
                                            <input type="text" class="crm-offer-item-inp narrow-input accessory-height" name="ACCESSORIES_HEIGHT<?=$num?>" value="<?=isset($accessoryUnit["ACCESSORIES_HEIGHT"]) && !empty($accessoryUnit["ACCESSORIES_HEIGHT"]) ? $accessoryUnit["ACCESSORIES_HEIGHT"] : '';?>" size="50">
                                        <?endif;?>
                                    </div>
                                </td>
                                <td class="crm-offer-info-left">
                                    <div class="crm-offer-info-label-wrap">
                                        <span class="crm-offer-info-label accessory-amount"><?=GetMessage("AMOUNT_INPUT_TITLE");?>:</span>
                                    </div>
                                </td>
                                <td class="crm-offer-info-right">
                                    <div class="crm-offer-info-data-wrap">
                                        <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                            <span class="crm-offer-item-span "><?=isset($accessoryUnit["ACCESSORIES_AMOUNT"]) && !empty($accessoryUnit["ACCESSORIES_AMOUNT"]) ? '$'.number_format($accessoryUnit["ACCESSORIES_AMOUNT"], 2, '.', ',') : '';?></span>
                                        <?else:?>
                                            <input type="text" class="crm-offer-item-inp narrow-input money-input accessory-amount-input" name="ACCESSORIES_AMOUNT<?=$num?>" value="<?=isset($accessoryUnit["ACCESSORIES_AMOUNT"]) && !empty($accessoryUnit["ACCESSORIES_AMOUNT"]) ? '$'.number_format($accessoryUnit["ACCESSORIES_AMOUNT"], 2, '.', ',') : '';?>" size="50">  
                                            <span class="crm-offer-title-del del-accessories-btn" data-block="accessory" style="display: none;"></span>
                                        <?endif;?>
                                    </div>
                                </td>
                            </tr>
                        <?endforeach;?>
                        <?unset($accessoryUnit);?>
                    <?else:?>
                        <tr class="crm-offer-row accessories-tr" data-dragdrop-context="field">    
                            <td class="crm-offer-info-left">
                                <div class="crm-offer-info-label-wrap">
                                    <span class="crm-offer-info-label"><?=GetMessage("TYPE_INPUT");?>:</span>
                                </div>
                            </td>
                            <td class="crm-offer-info-right">
                                <div class="crm-offer-info-data-wrap accessory-type-wrap">
                                    <select style="width:100%;" class="crm-item-table-select select2-list accessories-type" name="ACCESSORIES_TYPE" sale_order_marker="Y" style="width: 100%;">
                                        <option value=""><?=GetMessage("NOT_SELECTED_INPUT_TITLE");?></option>
                                        <?foreach($arResult["LIST"]["ACCESSORIES_TYPE"] as $type):?>
                                            <option value="<?=$type["ID"]?>"><?=$type["UF_ACCESSORIES_TYPE_LIST"]?></option>
                                        <?endforeach?>
                                    <select>
                                </div>
                            </td>
                            <td class="crm-offer-info-left">
                                <div class="crm-offer-info-label-wrap">
                                    <span class="crm-offer-info-label"><?=GetMessage("ACCESSORY_INPUT");?></span>
                                </div>
                            </td>
                            <td class="crm-offer-info-right">
                                <div class="crm-offer-info-data-wrap accessory-list-wrap">
                                    <select style="width:100%" class="crm-item-table-select select2-list accessories-list" name="ACCESSORY" sale_order_marker="Y">
                                        <option value=""><?=GetMessage("NOT_SELECTED_INPUT_TITLE");?></option>
                                        <?foreach($arResult["ACCESSORIES"] as $accessory):?>
                                            <option data-width="<?=$accessory["UF_WIDTH"]?>" data-height="<?=$accessory["UF_HEIGHT"]?>" value="<?=$accessory["ID"]?>"><?=$accessory["UF_ACCESSORIES_TYPE"]?> 
                                                <?if(!empty($accessory["UF_WIDTH"]) || !empty($accessory["UF_HEIGHT"])):?>
                                                    <?=$accessory["UF_WIDTH"]?>/<?=$accessory["UF_HEIGHT"]?>
                                                <?endif;?>
                                            </option>
                                        <?endforeach?>
                                    <select>
                                </div>
                            </td>
                            <td class="crm-offer-info-left">
                                <div class="crm-offer-info-label-wrap">
                                    <span class="crm-offer-info-label"><?=GetMessage("QUANTITY_INPUT");?></span>
                                </div>
                            </td>
                            <td class="crm-offer-info-right">
                                <div class="crm-offer-info-data-wrap">
                                    <input type="number" min="1" class="crm-offer-item-inp narrow-input accessory-quantity" name="ACCESSORIES_QUANTITY" value="" size="50">  
                                </div>
                            </td>
                            <td class="crm-offer-info-left">
                                <div class="crm-offer-info-label-wrap">
                                    <span class="crm-offer-info-label"><?=GetMessage("WIDTH_INPUT");?></span>
                                </div>
                            </td>
                            <td class="crm-offer-info-right">
                                <div class="crm-offer-info-data-wrap">
                                    <input type="text" class="crm-offer-item-inp narrow-input accessory-width" name="ACCESSORIES_WIDTH" value="" size="50">  
                                </div>
                            </td>
                            <td class="crm-offer-info-left">
                                <div class="crm-offer-info-label-wrap">
                                    <span class="crm-offer-info-label"><?=GetMessage("HEIGHT_INPUT");?></span>
                                </div>
                            </td>
                            <td class="crm-offer-info-right">
                                <div class="crm-offer-info-data-wrap">
                                    <input type="text" class="crm-offer-item-inp narrow-input accessory-height" name="ACCESSORIES_HEIGHT" value="" size="50">  
                                </div>
                            </td>
                            <td class="crm-offer-info-left">
                                <div class="crm-offer-info-label-wrap">
                                    <span class="crm-offer-info-label accessory-amount"><?=GetMessage("AMOUNT_INPUT_TITLE");?></span>
                                </div>
                            </td>
                            <td class="crm-offer-info-right">
                                <div class="crm-offer-info-data-wrap">
                                    <input type="text" class="crm-offer-item-inp narrow-input money-input accessory-amount-input" name="ACCESSORIES_AMOUNT" value="" size="50">  
                                    <span class="crm-offer-title-del del-accessories-btn" data-block="accessory" style="display: none;"></span>
                                </div>
                            </td>
                        </tr>
                    <?endif;?>
                </tbody>
            </table>
            <div class="add-doors-container">
                <?if($request["ACTION"] == "EDIT" || $request["ACTION"] == "NEW"):?>
                    <button class="ui-btn ui-btn-xs add-accessories"><?=GetMessage("ADD_ACCESSORIES_BTN");?></button>
                <?endif;?>
                <div class="total-amount">
                    <div class="crm-offer-info-label-wrap total-amount-label">
                        <span class="crm-offer-info-label"><?=GetMessage("TOTAL_AMOUNT_INPUT_TITLE");?>:</span>
                    </div>
                    <div class="crm-offer-info-data-wrap total-amount-input-container">
                        <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                            <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["ACCESSORIES_TOTAL_COST"]) && !empty($arResult["QUOTATION_DATA"]["ACCESSORIES_TOTAL_COST"])? '$'.number_format($arResult["QUOTATION_DATA"]["ACCESSORIES_TOTAL_COST"], 2, '.', ',') : '';?></span>
                        <?else:?>
                            <input type="text" class="crm-offer-item-inp total-amount-input money-input" name="TOTAL_ACCESSORIES_AMOUNT" value="<?=isset($arResult["QUOTATION_DATA"]["ACCESSORIES_TOTAL_COST"]) && !empty($arResult["QUOTATION_DATA"]["ACCESSORIES_TOTAL_COST"])? '$'.number_format($arResult["QUOTATION_DATA"]["ACCESSORIES_TOTAL_COST"], 2, '.', ',') : '';?>" size="50">
                        <?endif;?>
                    </div>
                </div>  
            </div>
            <div style="width:100%">
                <div class="crm-offer-title">
                    <span class="crm-offer-title-text"><?=GetMessage("GARAGE_DOORS_BLOCK");?></span>
                </div>
            </div>
            <table style="width: 98%">
                <tbody class="doors-block"> 
                    <?if(isset($arResult["QUOTATION_DATA"]["DOORS"]) && count($arResult["QUOTATION_DATA"]["DOORS"]) > 0):?>
                        <?foreach($arResult["QUOTATION_DATA"]["DOORS"] as $searchIndex => &$doorsUnit):?>
                            <?$num = $searchIndex == 0 ? "" : "_".$searchIndex;?>
                            <tr class="crm-offer-row doors-tr" data-dragdrop-context="field">
                                <td class="crm-offer-info-left">
                                    <div class="crm-offer-info-label-wrap">
                                        <span class="crm-offer-info-label"><?=GetMessage("DOOR_INPUT");?>:</span>
                                    </div>
                                </td>
                                <td class="crm-offer-info-right">
                                    <div id="doors" class="crm-offer-info-data-wrap doors-list-wrap">
                                        <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                            <?if(!empty($doorsUnit["DOOR"])):?>
                                                <?foreach($arResult["DOORS"] as $door):?>
                                                    <?
                                                        if($door["ID"] == $doorsUnit["DOOR"])
                                                            $value = $door["UF_ACCESSORIES_TYPE"] . (!empty($door["UF_WIDTH"]) || !empty($door["UF_HEIGHT"]) ? " " . $door["UF_WIDTH"] . "/" . $door["UF_HEIGHT"] : "");  
                                                        $doorsUnit["VALUE"] = $value;
                                                    ?>
                                                <?endforeach;?>
                                                <span class="crm-offer-item-span"><?=!empty($value) ? $value : '';?></span>
                                            <?endif;?>
                                        <?else:?>
                                            <select style="width:100%" class="crm-item-table-select select2-list doors-list" name="DOOR<?=$num?>" sale_order_marker="Y">
                                                <option value=""><?=GetMessage("NOT_SELECTED_INPUT_TITLE");?></option>
                                                <?foreach($arResult["DOORS"] as $door):?>
                                                    <option <?=isset($doorsUnit["DOOR"]) && $door["ID"] == $doorsUnit["DOOR"] ? "selected" : "";?> data-width="<?=$door["UF_WIDTH"]?>" data-height="<?=$door["UF_HEIGHT"]?>" value="<?=$door["ID"]?>"><?=$door["UF_ACCESSORIES_TYPE"]?>
                                                        <?if(!empty($door["UF_WIDTH"]) || !empty($door["UF_HEIGHT"])):?>
                                                            <?=$door["UF_WIDTH"]?>/<?=$door["UF_HEIGHT"]?>
                                                        <?endif;?>
                                                    </option>
                                                <?endforeach?>
                                            <select>
                                        <?endif;?>
                                    </div>
                                </td>
                                <td class="crm-offer-info-left">
                                    <div class="crm-offer-info-label-wrap">
                                        <span class="crm-offer-info-label"><?=GetMessage("QUANTITY_INPUT");?>:</span>
                                    </div>
                                </td>
                                <td class="crm-offer-info-right">
                                    <div class="crm-offer-info-data-wrap">
                                        <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                            <span class="crm-offer-item-span "><?=isset($doorsUnit["DOOR_QUANTITY"]) && !empty($doorsUnit["DOOR_QUANTITY"]) ? $doorsUnit["DOOR_QUANTITY"] : '';?></span>
                                        <?else:?>
                                            <input type="number" min="1" class="crm-offer-item-inp narrow-input door-quantity" name="DOOR_QUANTITY<?=$num?>" value="<?=isset($doorsUnit["DOOR_QUANTITY"]) && !empty($doorsUnit["DOOR_QUANTITY"]) ? $doorsUnit["DOOR_QUANTITY"] : '';?>" size="50">
                                        <?endif;?>  
                                    </div>
                                </td>
                                <td class="crm-offer-info-left">
                                    <div class="crm-offer-info-label-wrap">
                                        <span class="crm-offer-info-label"><?=GetMessage("WIDTH_INPUT");?>:</span>
                                    </div>
                                </td>
                                <td class="crm-offer-info-right">
                                    <div class="crm-offer-info-data-wrap">
                                        <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                            <span class="crm-offer-item-span "><?=isset($doorsUnit["DOOR_WIDTH"]) && !empty($doorsUnit["DOOR_WIDTH"]) ? $doorsUnit["DOOR_WIDTH"] : '';?></span>
                                        <?else:?>
                                            <input type="text" class="crm-offer-item-inp doors-width narrow-input" name="DOOR_WIDTH<?=$num?>" value="<?=isset($doorsUnit["DOOR_WIDTH"]) && !empty($doorsUnit["DOOR_WIDTH"]) ? $doorsUnit["DOOR_WIDTH"] : '';?>" size="50">  
                                        <?endif;?> 
                                    </div>
                                </td>
                                <td class="crm-offer-info-left">
                                    <div class="crm-offer-info-label-wrap">
                                        <span class="crm-offer-info-label"><?=GetMessage("HEIGHT_INPUT");?>:</span>
                                    </div>
                                </td>
                                <td class="crm-offer-info-right">
                                    <div class="crm-offer-info-data-wrap">
                                        <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                            <span class="crm-offer-item-span"><?=isset($doorsUnit["DOOR_HEIGHT"]) && !empty($doorsUnit["DOOR_HEIGHT"]) ? $doorsUnit["DOOR_HEIGHT"] : '';?></span>
                                        <?else:?>
                                            <input type="text" class="crm-offer-item-inp doors-height narrow-input" name="DOOR_HEIGHT<?=$num?>" value="<?=isset($doorsUnit["DOOR_HEIGHT"]) && !empty($doorsUnit["DOOR_HEIGHT"]) ? $doorsUnit["DOOR_HEIGHT"] : '';?>" size="50">  
                                        <?endif;?> 
                                    </div>
                                </td>
                                <td class="crm-offer-info-left">
                                    <div class="crm-offer-info-label-wrap">
                                        <span class="crm-offer-info-label"><?=GetMessage("AMOUNT_INPUT_TITLE");?>:</span>
                                    </div>
                                </td>
                                <td class="crm-offer-info-right">
                                    <div class="crm-offer-info-data-wrap">
                                        <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                            <span class="crm-offer-item-span"><?=isset($doorsUnit["DOOR_AMOUNT"]) && !empty($doorsUnit["DOOR_AMOUNT"]) ? $doorsUnit["DOOR_AMOUNT"] : '';?></span>
                                        <?else:?>
                                            <input type="text" class="crm-offer-item-inp narrow-input doors-amount-input money-input" name="DOOR_AMOUNT<?=$num?>" value="<?=isset($doorsUnit["DOOR_AMOUNT"]) && !empty($doorsUnit["DOOR_AMOUNT"]) ? $doorsUnit["DOOR_AMOUNT"] : '';?>" size="50">
                                            <span class="crm-offer-title-del del-doors-btn" data-block="door" style="display: none;"></span> 
                                        <?endif;?>  
                                    </div>
                                </td>
                            </tr>
                        <?endforeach;?>
                        <?unset($doorsUnit);?>
                    <?else:?>
                        <tr class="crm-offer-row doors-tr" data-dragdrop-context="field">
                            <td class="crm-offer-info-left">
                                <div class="crm-offer-info-label-wrap">
                                    <span class="crm-offer-info-label"><?=GetMessage("DOOR_INPUT");?></span>
                                </div>
                            </td>
                            <td class="crm-offer-info-right">
                                <div class="crm-offer-info-data-wrap doors-list-wrap">
                                    <select style="width:100%" class="crm-item-table-select select2-list doors-list" name="DOOR" sale_order_marker="Y">
                                        <option value=""><?=GetMessage("NOT_SELECTED_INPUT_TITLE");?></option>
                                        <?foreach($arResult["DOORS"] as $door):?>
                                            <option data-width="<?=$door["UF_WIDTH"]?>" data-height="<?=$door["UF_HEIGHT"]?>" value="<?=$door["ID"]?>"><?=$door["UF_ACCESSORIES_TYPE"]?>
                                                <?if(!empty($door["UF_WIDTH"]) || !empty($door["UF_HEIGHT"])):?>
                                                        <?=$door["UF_WIDTH"]?>/<?=$door["UF_HEIGHT"]?>
                                                <?endif;?>
                                            </option>
                                        <?endforeach?>
                                    <select>
                                </div>
                            </td>
                            <td class="crm-offer-info-left">
                                <div class="crm-offer-info-label-wrap">
                                    <span class="crm-offer-info-label"><?=GetMessage("QUANTITY_INPUT");?></span>
                                </div>
                            </td>
                            <td class="crm-offer-info-right">
                                <div class="crm-offer-info-data-wrap">
                                    <input type="number" min="1" class="crm-offer-item-inp narrow-input door-quantity" name="DOOR_QUANTITY" value="" size="50">  
                                </div>
                            </td>
                            <td class="crm-offer-info-left">
                                <div class="crm-offer-info-label-wrap">
                                    <span class="crm-offer-info-label"><?=GetMessage("WIDTH_INPUT");?></span>
                                </div>
                            </td>
                            <td class="crm-offer-info-right">
                                <div class="crm-offer-info-data-wrap">
                                    <input type="text" class="crm-offer-item-inp doors-width narrow-input" name="DOOR_WIDTH" value="" size="50">  
                                </div>
                            </td>
                            <td class="crm-offer-info-left">
                                <div class="crm-offer-info-label-wrap">
                                    <span class="crm-offer-info-label"><?=GetMessage("HEIGHT_INPUT");?></span>
                                </div>
                            </td>
                            <td class="crm-offer-info-right">
                                <div class="crm-offer-info-data-wrap">
                                    <input type="text" class="crm-offer-item-inp doors-height narrow-input" name="DOOR_HEIGHT" value="" size="50">  
                                </div>
                            </td>
                            <td class="crm-offer-info-left">
                                <div class="crm-offer-info-label-wrap">
                                    <span class="crm-offer-info-label"><?=GetMessage("AMOUNT_INPUT_TITLE");?></span>
                                </div>
                            </td>
                            <td class="crm-offer-info-right">
                                <div class="crm-offer-info-data-wrap">
                                    <input type="text" class="crm-offer-item-inp narrow-input doors-amount-input money-input" name="DOOR_AMOUNT" value="" size="50">
                                    <span class="crm-offer-title-del del-doors-btn" data-block="door" style="display: none;"></span>  
                                </div>
                            </td>
                        </tr>
                    <?endif;?>
                </tbody>
            </table>
            <div class="add-doors-container">
                <?if($request["ACTION"] == "EDIT" || $request["ACTION"] == "NEW"):?>
                    <button class="ui-btn ui-btn-xs add-doors"><?=GetMessage("ADD_DOORS_BTN");?></button>                     
                <?endif;?>
                <div class="total-amount">
                    <div class="crm-offer-info-label-wrap total-amount-label">
                        <span class="crm-offer-info-label"><?=GetMessage("TOTAL_AMOUNT_INPUT_TITLE");?>:</span>
                    </div>
                    <div class="crm-offer-info-data-wrap total-amount-input-container">
                        <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                            <span class="crm-offer-item-span"><?=isset($arResult["QUOTATION_DATA"]["DOORS_TOTAL_COST"]) && !empty($arResult["QUOTATION_DATA"]["DOORS_TOTAL_COST"])? '$'.number_format($arResult["QUOTATION_DATA"]["DOORS_TOTAL_COST"], 2, '.', ',') : '';?></span>
                        <?else:?>
                            <input type="text" class="crm-offer-item-inp total-amount-input money-input" name="TOTAL_DOOR_AMOUNT" value="<?=isset($arResult["QUOTATION_DATA"]["DOORS_TOTAL_COST"]) && !empty($arResult["QUOTATION_DATA"]["DOORS_TOTAL_COST"])? '$'.number_format($arResult["QUOTATION_DATA"]["DOORS_TOTAL_COST"], 2, '.', ',') : '';?>" size="50">
                        <?endif;?>
                    </div>
                </div>  
            </div>
            <div style="width:100%">
                <div class="crm-offer-title">
                    <span class="crm-offer-title-text"><?=GetMessage("FREIGHT_BLOCK");?></span>
                </div>
            </div>
            <table>
                <tbody>
                    <tr>
                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"></span>
                            </div>
                        </td>





                        <td class="crm-offer-info-left">
                            <div class="crm-offer-info-label-wrap">
                                <span class="crm-offer-info-label"><?=GetMessage("COST_INPUT");?>:</span>
                            </div>
                        </td>
                        <td class="crm-offer-info-right">
                            <div class="crm-offer-info-data-wrap">
                                <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                    <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["COST"]) && !empty($arResult["QUOTATION_DATA"]["COST"])? '$'.number_format($arResult["QUOTATION_DATA"]["COST"], 2, '.', ',') : '';?></span>
                                <?else:?>
                                    <!-- #19854 Removed Freight Logic - Quotation System - Zero fix -->
                                    <input type="text" class="crm-offer-item-inp custom-money-input" name="COST" value="<?=isset($arResult["QUOTATION_DATA"]["COST"]) && !empty($arResult["QUOTATION_DATA"]["COST"])? '$'.number_format($arResult["QUOTATION_DATA"]["COST"], 2, '.', ',') : '';?>" size="50">
                                <?endif;?>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div style="width:100%">
                <div class="crm-offer-title">
                    <span class="crm-offer-title-text"><?=GetMessage("OTHER_BLOCK");?></span>
                </div>
            </div>
            <table class="other-block-table">
                <tbody>
                <tr>
                    <td class="crm-offer-info-left">
                        <div class="crm-offer-info-label-wrap">
                            <span class="crm-offer-info-label">Building Retail Price:</span>
                        </div>
                    </td>
                    <td class="crm-offer-info-right">
                        <div class="crm-offer-info-data-wrap">
                            <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                <span class="crm-offer-item-span money-span"><?=isset($arResult["QUOTATION_DATA"]["SOLD_FOR"]) && !empty($arResult["QUOTATION_DATA"]["SOLD_FOR"])? '$'.number_format($arResult["QUOTATION_DATA"]["SOLD_FOR"], 2, '.', ',') : '';?></span>
                            <?else:?>
                                <input type="text" class="crm-offer-item-inp money-input" name="SOLD_FOR" value="<?=isset($arResult["QUOTATION_DATA"]["SOLD_FOR"]) && !empty($arResult["QUOTATION_DATA"]["SOLD_FOR"])? '$'.number_format($arResult["QUOTATION_DATA"]["SOLD_FOR"], 2, '.', ',') : '';?>" size="50">  
                            <?endif;?>
                        </div>
                    </td>
                    <td class="crm-offer-info-left">
                        <div class="crm-offer-info-label-wrap">
                            <span class="crm-offer-info-label"></span>
                        </div>
                    </td>
                    <td class="crm-edit-price-right">
                        <div class="crm-offer-info-data-wrap">
                            <input class="crm-offer-checkbox" id="edit_sold_for" type="checkbox" name="EDIT_SOLD_FOR" 
                            <?=$request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW" ? "disabled='disabled'" : ""?> value="Y"
                            <?=isset($arResult["QUOTATION_DATA"]["EDIT_SOLD_FOR"]) && $arResult["QUOTATION_DATA"]["EDIT_SOLD_FOR"] == "Y" ? "checked" : "";?>>
                            <label class="crm-offer-label" for="edit_sold_for"><?=GetMessage('EDIT_SOLD_FOR_CHECKBOX');?></label>
                        </div>
                    </td>
                    <td class="crm-offer-info-left">
                        <div class="crm-offer-info-label-wrap"> 
                            <span class="crm-offer-info-label">Suggested Sale Price:</span>
                        </div>
                    </td>
                    <td class="crm-offer-info-right">
                        <div class="crm-offer-info-data-wrap">
                            <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                <span class="crm-offer-item-span money-span"><?=isset($arResult["QUOTATION_DATA"]["ASKING"]) && !empty($arResult["QUOTATION_DATA"]["ASKING"])? '$'.number_format($arResult["QUOTATION_DATA"]["ASKING"], 2, '.', ',') : '';?></span>
                            <?else:?>
                                <input type="text" class="crm-offer-item-inp money-input" name="ASKING" value="<?=isset($arResult["QUOTATION_DATA"]["ASKING"]) && !empty($arResult["QUOTATION_DATA"]["ASKING"])? '$'.number_format($arResult["QUOTATION_DATA"]["ASKING"], 2, '.', ',') : '';?>" size="50">  
                            <?endif;?>
                        </div>
                    </td>
                    <td class="crm-offer-info-left">
                        <div class="crm-offer-info-label-wrap">
                            <span class="crm-offer-info-label"></span>
                        </div>
                    </td>
                    <td class="crm-edit-price-right">
                        <div class="crm-offer-info-data-wrap">
                            <input class="crm-offer-checkbox" id="edit_asking" type="checkbox" name="EDIT_ASKING" 
                            <?=$request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW" ? "disabled='disabled'" : ""?> value="Y"
                            <?=isset($arResult["QUOTATION_DATA"]["EDIT_ASKING"]) && $arResult["QUOTATION_DATA"]["EDIT_ASKING"] == "Y" ? "checked" : "";?>>
                            <label class="crm-offer-label" for="edit_asking"><?=GetMessage('EDIT_ASKING_CHECKBOX');?></label>
                        </div>
                    </td>
                    <td class="crm-offer-info-left">
                        <div class="crm-offer-info-label-wrap"> 
							<span class="crm-offer-info-label">Vendor Building Cost:</span>
                        </div>
                    </td>
                    <td class="crm-offer-info-right">
                        <div class="crm-offer-info-data-wrap">
                            <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                <span class="crm-offer-item-span money-span"><?=isset($arResult["QUOTATION_DATA"]["BUILDING_TOTAL_СOST"]) && !empty($arResult["QUOTATION_DATA"]["BUILDING_TOTAL_СOST"])? '$'.number_format($arResult["QUOTATION_DATA"]["BUILDING_TOTAL_СOST"], 2, '.', ',') : '';?></span>
                            <?else:?>
                                <input type="text" class="crm-offer-item-inp money-input" name="BUILDING_TOTAL_СOST" value="<?=isset($arResult["QUOTATION_DATA"]["BUILDING_TOTAL_СOST"]) && !empty($arResult["QUOTATION_DATA"]["BUILDING_TOTAL_СOST"])? '$'.number_format($arResult["QUOTATION_DATA"]["BUILDING_TOTAL_СOST"], 2, '.', ',') : '';?>" size="50">  
                            <?endif;?>
                        </div>
                    </td>
                    <td class="crm-offer-info-left">
                        <div class="crm-offer-info-label-wrap">
                            <span class="crm-offer-info-label"></span>
                        </div>
                    </td>
                    <td class="crm-edit-price-right">
                        <div class="crm-offer-info-data-wrap">
                            <input class="crm-offer-checkbox" id="edit_building_total_cost" type="checkbox" name="EDIT_BUILDING_TOTAL_COST" 
                            <?=$request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW" ? "disabled='disabled'" : ""?> value="Y"
                            <?=isset($arResult["QUOTATION_DATA"]["EDIT_BUILDING_TOTAL_COST"]) && $arResult["QUOTATION_DATA"]["EDIT_BUILDING_TOTAL_COST"] == "Y" ? "checked" : "";?>>
                            <label class="crm-offer-label" for="edit_building_total_cost"><?=GetMessage('EDIT_BUILDING_TOTAL_COST_CHECKBOX');?></label>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="crm-offer-info-left">
                        <div class="crm-offer-info-label-wrap">
                            <span class="crm-offer-info-label"></span>
                        </div>
                    </td>
                    <td class="crm-offer-info-right">
                        <div class="crm-offer-info-data-wrap">
                            <input class="crm-offer-checkbox" id="drawings" value="Y" type="checkbox" name="DRAWINGS"   
                            <?=$request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW" ? "disabled='disabled'" : ""?>
                            <?=$request["ACTION"] != "EDIT" && $request["ACTION"] == "NEW" ?  "checked" : (isset($arResult["QUOTATION_DATA"]["DRAWINGS"]) && $arResult["QUOTATION_DATA"]["DRAWINGS"] == "Y" ? "checked" : "");?>>
                            <label class="crm-offer-label" for="drawings"><?=GetMessage("DRAWINGS_INPUT");?></label>
                        </div>
                    </td>
                    <td class="crm-offer-info-left">
                        <div class="crm-offer-info-label-wrap">
                            <span class="crm-offer-info-label"><?=GetMessage('ESTIMATED_DELIVERY_INPUT');?></span>
                        </div>
                    </td>
                    <td class="crm-offer-info-right">
                        <div class="crm-offer-info-data-wrap">
                            <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                <span class="crm-offer-item-span "><?=isset($arResult["QUOTATION_DATA"]["ESTIMATED_DELIVERY"]) && !empty($arResult["QUOTATION_DATA"]["ESTIMATED_DELIVERY"]) ? $arResult["QUOTATION_DATA"]["ESTIMATED_DELIVERY"] : '';?></span>
                            <?else:?>
                                <input id="ESTIMATED_DELIVERY" name="ESTIMATED_DELIVERY" class="crm-offer-item-inp crm-item-table-date" type="text" value="<?=isset($arResult["QUOTATION_DATA"]["ESTIMATED_DELIVERY"]) && !empty($arResult["QUOTATION_DATA"]["ESTIMATED_DELIVERY"]) ? $arResult["QUOTATION_DATA"]["ESTIMATED_DELIVERY"] : '';?>">
                                <script type="text/javascript">
                                    BX.ready(function(){ 
                                        BX.CrmDateLinkField.create(BX('ESTIMATED_DELIVERY'), null, { showTime: false, setFocusOnShow: false }); 
                                    });
                                </script>
                            <?endif;?>
                        </div>
                    </td>
                    <td class="crm-offer-info-left">
                        <div class="crm-offer-info-label-wrap">
                            <span class="crm-offer-info-label"><?=GetMessage('INSULATION_INPUT');?></span>
                        </div>
                    </td>
                    <td class="crm-offer-info-right">
                        <div class="crm-offer-info-data-wrap">
                            <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                <?
                                    if(!empty($arResult["QUOTATION_DATA"]["INSULATION"]))
                                        $value = $arResult["QUOTATION_DATA"]["INSULATION"] == "Y" ? GetMessage("INCLUDED_VALUE") : GetMessage("NOT_INCLUDED_VALUE");
                                    else
                                    $value = "";
                                ?>
                                <span class="crm-offer-item-span"><?=!empty($value) ? $value : '';?></span>
                            <?else:?>
                                <select class="crm-item-table-select select2-list" name="INSULATION" sale_order_marker="Y" style="width: 100%;">
                                    <option <?=isset($arResult["QUOTATION_DATA"]["INSULATION"]) && $arResult["QUOTATION_DATA"]["INSULATION"] == "" ? "selected" : "";?> value=""><?=GetMessage("NOT_SELECTED_INPUT_TITLE");?></option>
                                    <option <?=isset($arResult["QUOTATION_DATA"]["INSULATION"]) && $arResult["QUOTATION_DATA"]["INSULATION"] == "Y" ? "selected" : "";?> value="Y"><?=GetMessage("INCLUDED_VALUE");?></option>
                                    <option <?=isset($arResult["QUOTATION_DATA"]["INSULATION"]) && $arResult["QUOTATION_DATA"]["INSULATION"] == "N" ? "selected" : "";?> value="N"><?=GetMessage("NOT_INCLUDED_VALUE");?></option>
                                <select>
                            <?endif;?>
                        </div>
                    </td>
                    <td class="crm-offer-info-left">
                        <div class="crm-offer-info-label-wrap">
                            <span class="crm-offer-info-label"><?=GetMessage('CAULKING_INPUT');?></span>
                        </div>
                    </td>
                    <td class="crm-offer-info-right">
                        <div class="crm-offer-info-data-wrap">
                            <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                <?
                                     if(!empty($arResult["QUOTATION_DATA"]["CAULKING"]))
                                        $value = $arResult["QUOTATION_DATA"]["CAULKING"] == "Y" ? GetMessage("INCLUDED_VALUE") : GetMessage("NOT_INCLUDED_VALUE");
                                    else
                                        $value = "";
                                ?>
                                <span class="crm-offer-item-span"><?=!empty($value) ? $value : '';?></span>
                            <?else:?>
                                <select class="crm-item-table-select select2-list" name="CAULKING" sale_order_marker="Y" style="width: 100%;">
                                    <option <?=isset($arResult["QUOTATION_DATA"]["CAULKING"]) && $arResult["QUOTATION_DATA"]["CAULKING"] == "" ? "selected" : "";?> value=""><?=GetMessage("NOT_SELECTED_INPUT_TITLE");?></option>
                                    <option <?=isset($arResult["QUOTATION_DATA"]["CAULKING"]) && $arResult["QUOTATION_DATA"]["CAULKING"] == "Y" ? "selected" : "";?> value="Y"><?=GetMessage("INCLUDED_VALUE");?></option>
                                    <option <?=isset($arResult["QUOTATION_DATA"]["CAULKING"]) && $arResult["QUOTATION_DATA"]["CAULKING"] == "N" ? "selected" : "";?> value="N"><?=GetMessage("NOT_INCLUDED_VALUE");?></option>
                                <select>
                            <?endif;?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="crm-offer-info-left">
                        <div class="crm-offer-info-label-wrap">
                            <span class="crm-offer-info-label">Notes:</span>
                        </div>
                    </td>
                    <td class="crm-offer-info-right">
                        <div class="crm-offer-info-data-wrap">
                            <?if($request["ACTION"] != "EDIT" && $request["ACTION"] != "NEW"):?>
                                <span class="crm-offer-item-span"><?=isset($arResult["QUOTATION_DATA"]["NOTES"]) && !empty($arResult["QUOTATION_DATA"]["NOTES"]) ? $arResult["QUOTATION_DATA"]["NOTES"] : '';?></span>
                            <?else:?>
                                <textarea class="crm-offer-item-textarea" name="NOTES" value="<?=isset($arResult["QUOTATION_DATA"]["NOTES"]) && !empty($arResult["QUOTATION_DATA"]["NOTES"]) ? $arResult["QUOTATION_DATA"]["NOTES"] : '';?>"><?=$arResult["QUOTATION_DATA"]["NOTES"]?></textarea>
                            <?endif;?>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </form>  
    <div class="ui-btn-container ui-btn-container-center">
        <?if(isset($request["ACTION"]) && $request["ACTION"] == "EDIT"):?>
            <button class="ui-btn ui-btn-success quatation-save-btn"  data="UPDATE" title="Update"><?=GetMessage("UPDATE_QUOTATION_BTN");?></button>
        <?else:?>
            <?if($request["ACTION"] != "SHOW" && $arParams["ACTION"] != "SHOW"):?>
                <button class="ui-btn ui-btn-success quatation-save-btn" data="SAVE" title="Save"><?=GetMessage("SAVE_QUOTATION_BTN");?></button>
            <?endif;?>
        <?endif;?>
    </div>
    <script>
        $(document).ready(function(){$('.money-input').maskMoney();});
    </script>
    <?
    if (isset($request["MODE_SWITCH"]) && ($requestT["MODE_SWITCH"] == 'Y')) 
        die();    
    ?> 
</div>
<?
$arResult["QUOTATION_DATA"]["CALCULATION"]["MODEL"] = $valueModel;
$arResult["QUOTATION_DATA"]["CALCULATION"]["ACCESSORIES"] = $arResult["QUOTATION_DATA"]["ACCESSORIES"];
$arResult["QUOTATION_DATA"]["CALCULATION"]["DOORS"] = $arResult["QUOTATION_DATA"]["DOORS"];
$arResult["QUOTATION_DATA"]["CALCULATION"]["СITY"] = $cityValue;
?>
<script>
    component_path = <?=json_encode($componentPath)?>; 
    dataObj = <?=CUtil::PhpToJSObject(array(
        "ACCESSORIES_TYPE" => $arResult["LIST"]["ACCESSORIES_TYPE"],
        'ACCESSORIES' => $arResult["ACCESSORIES"],
        "DOORS" => $arResult["DOORS"],
        "CALCULATION" => $arResult["QUOTATION_DATA"]["CALCULATION"]
    ));?>
</script>