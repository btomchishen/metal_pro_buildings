<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
use \Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;
use \Bitrix\Main\Grid\Panel\Snippet\Onchange;

global $APPLICATION;

CJSCore::Init(array("jquery"));
$APPLICATION->SetAdditionalCSS('/bitrix/js/crm/css/crm.css');
$APPLICATION->AddHeadScript('/bitrix/js/crm/interface_form.js');
$APPLICATION->AddHeadScript('/bitrix/js/crm/common.js');
$APPLICATION->AddHeadScript('/bitrix/js/main/dd.js');
$APPLICATION->AddHeadScript('/bitrix/js/crm/interface_grid.js');?>


<div data-tab-id="tab_quotation" class="crm-entity-section crm-entity-section-info crm-entity-section-above-overlay">
    <div class="crm-list-top-bar">
        <?if(isset($arResult["ENTITY_DATA"]["FROM_ENTITY"]) && $arResult["ENTITY_DATA"]["FROM_ENTITY"] == "Y"):?>
            <a class="crm-menu-bar-btn btn-new" href = "javascript:void(0);" title="Create a new quote" onclick="quotationFromEntity();"><span class="crm-toolbar-btn-icon"></span><span>Create Quotation</span></a>
            <script>
                function quotationFromEntity()
                {
                    BX.SidePanel.Instance.open('/local/components/custom/quotation.system/iframe_component.php',
                    {
                        requestMethod: "post",
                        cacheable: false,
                        allowChangeHistory: false,
                        requestParams: <?=json_encode($arResult["ENTITY_DATA"])?>
                    });
                }
            </script>
        <?else:?>
            <a class="crm-menu-bar-btn btn-new" href = "javascript:void(0);" title="Create a new quote" onclick="openNewQuotation();"><span class="crm-toolbar-btn-icon"></span><span>Create Quotation</span></a>
        <?endif;?>
    </div>
</div>
<?
if(!isset($arParams["FROM_ENTITY"]) && $arParams["FROM_ENTITY"] != "Y")
{
    $ui_filter = array(
        array('id' => 'ID', 'name' => 'Quotation Number', 'type'=>'text', 'default' => true),
        array('id' => 'DATE', 'name' => 'Modified Time', 'type'=>'date', 'default' => true),
        array('id' => 'OWNER', 'name' => 'Quoation Owner', 'type'=>'list', 'default' => true, "items" => $arResult["USERS"]),
        array('id' => 'ASKING', 'name' => 'Asking', 'type'=>'number', 'default' => true),
        array('id' => 'COST', 'name' => 'Cost', 'type'=>'number', 'default' => true),
        array('id' => 'MODEL', 'name' => 'Model', 'type'=>'list', 'default' => true, "items" => $arResult["MODELS"]),
        array('id' => 'CITY', 'name' => 'City', 'type'=>'list', 'default' => true, "items" => $arResult["CITIES"]),
    );

    $APPLICATION->IncludeComponent(
        'bitrix:main.ui.filter', 
        '', 
        array(
        'FILTER_ID' => 'quotation_list',
        'GRID_ID' => 'quotation_list',
        'FILTER' => $ui_filter,
        'ENABLE_LIVE_SEARCH' => true,
        'ENABLE_LABEL' => true
    ));
}
$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    array(
        'GRID_ID' => 'quotation_list', 
        'COLUMNS' => array(
            array('id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true), 
            array('id' => 'QUOTATION_NUMBER', 'name' => 'Quotation Number', 'sort' => 'ID', 'default' => true), 
            array('id' => 'MODEL', 'name' => 'Model', 'sort' => 'UF_SELECTED_MODEL', 'default' => true),
            array('id' => 'OWNER', 'name' => 'Quoation Owner', 'sort' => 'UF_QUOATION_OWNER', 'default' => true),
            array('id' => 'DATE', 'name' => 'Modified Time', 'sort' => 'UF_QUOTATION_MODIFIED', 'default' => true),
            array('id' => 'ASKING', 'name' => 'Asking', 'sort' => 'UF_ASKING', 'default' => true),
            array('id' => 'COST', 'name' => 'Cost', 'sort' => 'UF_COST', 'default' => true),
            array('id' => 'CITY', 'name' => 'City', 'sort' => 'UF_SELECTED_CITY', 'default' => true),
        ), 
        'SHOW_ROW_CHECKBOXES' => false, 
        'NAV_OBJECT' => $arResult["NAV"], 
        'AJAX_MODE' => 'Y', 
        'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''), 
		'ROWS' => $arResult["LIST"], 
        'AJAX_OPTION_JUMP'          => 'Y', 
        "AJAX_OPTION_STYLE" => "Y",
        'SHOW_ROW_ACTIONS_MENU'     => true, 
        'SHOW_NAVIGATION_PANEL'     => true, 
        'SHOW_PAGINATION'           => true, 
        'SHOW_TOTAL_COUNTER'        => true, 
        'ALLOW_COLUMNS_SORT'        => true, 
        'ALLOW_COLUMNS_RESIZE'      => true, 
        'ALLOW_HORIZONTAL_SCROLL'   => true, 
        'ALLOW_SORT'                => isset($arParams["ENTITY_TYPE"]) && !empty($arParams["ENTITY_TYPE"]) ? false : true, 
        'ALLOW_PIN_HEADER'          => true, 
        'AJAX_OPTION_HISTORY'       => 'N',
        'TOTAL_ROWS_COUNT' => $arResult["ALL_ROWS"],
    ),
    $component
);?>
