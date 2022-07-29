<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
use \Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;

global $APPLICATION;

CJSCore::Init(array("jquery"));
$APPLICATION->SetAdditionalCSS('/bitrix/js/crm/css/crm.css');
$APPLICATION->AddHeadScript('/bitrix/js/crm/interface_form.js');
$APPLICATION->AddHeadScript('/bitrix/js/crm/common.js');
$APPLICATION->AddHeadScript('/bitrix/js/main/dd.js');
$APPLICATION->AddHeadScript('/bitrix/js/main/dd.js');?>

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
$grid_options = new Bitrix\Main\Grid\Options('quotation_list');
$sort = $grid_options->GetSorting(['sort' => ['ID' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
$nav_params = $grid_options->GetNavParams();

$nav = new Bitrix\Main\UI\PageNavigation('quotation_list');
$nav->allowAllRecords(true)
    ->setPageSize($nav_params['nPageSize'])
    ->initFromUri();
if ($nav->allRecordsShown())
    $nav_params = false;
else 
    $nav_params['iNumPage'] = $nav->getCurrentPage();

$ui_filter = array(
    array('id' => 'ID', 'name' => 'Quotation Number', 'type'=>'number', 'default' => true),
    array('id' => 'DATE_CREATE', 'name' => 'Дата создания', 'type'=>'date', 'default' => true),
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

$filterOption = new Bitrix\Main\UI\Filter\Options('quotation_list');
$filterData = $filterOption->getFilter(array());
$r = array();
foreach ($filterData as $k => $v) {
	$r[$k] = $v;
}
file_put_contents($_SERVER["DOCUMENT_ROOT"]."/arr.txt", print_r($filterOption,true));
$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    array(
        'GRID_ID' => 'quotation_list', 
        'COLUMNS' => array(
            array('id' => 'ID', 'name' => 'Quotation Number', 'sort' => 'ID', 'default' => true), 
            array('id' => 'MODEL', 'name' => 'Model', 'sort' => 'MODEL', 'default' => true),
            array('id' => 'OWNER', 'name' => 'Quoation Owner', 'sort' => 'OWNER', 'default' => true),
            array('id' => 'DATE', 'name' => 'Date', 'sort' => 'DATE', 'default' => true),
            array('id' => 'CLIENT', 'name' => 'Client', 'sort' => 'CLIENT', 'default' => true),
            array('id' => 'COMPANY', 'name' => 'Company', 'sort' => 'COMPANY', 'default' => true),
            array('id' => 'ASKING', 'name' => 'Asking', 'sort' => 'ASKING', 'default' => true),
            array('id' => 'COST', 'name' => 'Cost', 'sort' => 'COST', 'default' => true),
            array('id' => 'CITY', 'name' => 'City', 'sort' => 'CITY', 'default' => true),
        ), 
        'SHOW_ROW_CHECKBOXES' => true, 
        'NAV_OBJECT' => $nav, 
        'AJAX_MODE' => 'Y', 
        'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''), 
        'PAGE_SIZES' => array(
            array('NAME' => '20', 'VALUE' => '20'), 
		),
		'ROWS' => $arResult["LIST"], 
        'AJAX_OPTION_JUMP'          => 'N', 
        'SHOW_CHECK_ALL_CHECKBOXES' => true, 
        'SHOW_ROW_ACTIONS_MENU'     => true, 
        'SHOW_GRID_SETTINGS_MENU'   => true, 
        'SHOW_NAVIGATION_PANEL'     => true, 
        'SHOW_PAGINATION'           => true, 
        'SHOW_SELECTED_COUNTER'     => true, 
        'SHOW_TOTAL_COUNTER'        => true, 
        'SHOW_PAGESIZE'             => true, 
        'SHOW_ACTION_PANEL'         => true, 
        'ALLOW_COLUMNS_SORT'        => true, 
        'ALLOW_COLUMNS_RESIZE'      => true, 
        'ALLOW_HORIZONTAL_SCROLL'   => true, 
        'ALLOW_SORT'                => true, 
        'ALLOW_PIN_HEADER'          => true, 
        'AJAX_OPTION_HISTORY'       => 'N',
        
    )
);

?>
<script type="text/javascript">
BX.addCustomEvent('BX.Main.Filter:apply', BX.delegate(function (command, params) { 
    var workarea = $('#' + command); // в command будет храниться GRID_ID из фильтра 

    $.post(window.location.href, function(data){ 
        workarea.html($(data).find('#' + command).html()); 
    }) 
}));
</script>