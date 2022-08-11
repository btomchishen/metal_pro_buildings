<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use \Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;
use \Bitrix\Main\Grid\Panel\Snippet\Onchange;
use \Bitrix\Main\Grid\Panel\Snippet;

global $APPLICATION;

$APPLICATION->SetAdditionalCSS('/bitrix/js/crm/css/crm.css');
$APPLICATION->AddHeadScript('/bitrix/js/crm/interface_form.js');
$APPLICATION->AddHeadScript('/bitrix/js/crm/common.js');
$APPLICATION->AddHeadScript('/bitrix/js/main/dd.js');
$APPLICATION->AddHeadScript('/bitrix/js/crm/interface_grid.js');

?>
    <div data-tab-id="tab_quotation1"
         class="crm-entity-section crm-entity-section-info crm-entity-section-above-overlay">
        <div class="crm-list-top-bar">
            <? if (isset($arResult["ENTITY_DATA"]["FROM_ENTITY"]) && $arResult["ENTITY_DATA"]["FROM_ENTITY"] == "Y"): ?>
                <a class="crm-menu-bar-btn btn-new" href="javascript:void(0);" title="Create a new form"
                   onclick="quotationFromEntity();"><span
                            class="crm-toolbar-btn-icon"></span><span>Create New Form</span></a>
                <script>
                    function quotationFromEntity() {
                        BX.SidePanel.Instance.open('/local/components/custom/quotation.system/iframe_component.php',
                            {
                                requestMethod: "post",
                                cacheable: false,
                                allowChangeHistory: false,
                                requestParams: <?=json_encode($arResult["ENTITY_DATA"])?>
                            });
                    }
                </script>
            <? else: ?>
                <a class="crm-menu-bar-btn btn-new" href="javascript:void(0);" title="Create a new form"
                   onclick="openNewForm(<?=$arResult['DEAL_ID'];?>);"><span class="crm-toolbar-btn-icon"></span><span>Create New Form</span></a>
            <?endif; ?>
        </div>
        <div class="">
            <?=$arResult['FORMS_SELECT1'];?>
        </div>
    </div>
<?

$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
    'GRID_ID' => 'forms_list',
    'ROWS' => $arResult["ITEMS"],
    'SHOW_ROW_CHECKBOXES' => true,
    'NAV_OBJECT' => $arResult["NAV"],
    'AJAX_MODE' => 'Y',
    'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
    'PAGE_SIZES' => [
        ['NAME' => '5', 'VALUE' => '5'],
        ['NAME' => '20', 'VALUE' => '20'],
        ['NAME' => '50', 'VALUE' => '50'],
        ['NAME' => '100', 'VALUE' => '100'],
        ['NAME' => '500', 'VALUE' => '500']
    ],
    'AJAX_OPTION_JUMP' => 'N',
    'SHOW_CHECK_ALL_CHECKBOXES' => true,
    'SHOW_ROW_ACTIONS_MENU' => true,
    'SHOW_GRID_SETTINGS_MENU' => true,
    'SHOW_NAVIGATION_PANEL' => true,
    'SHOW_PAGINATION' => true,
    'SHOW_SELECTED_COUNTER' => true,
    'SHOW_TOTAL_COUNTER' => true,
    'SHOW_PAGESIZE' => false,
    'SHOW_ACTION_PANEL' => true,
    'ALLOW_COLUMNS_SORT' => true,
    'ALLOW_COLUMNS_RESIZE' => true,
    'ALLOW_HORIZONTAL_SCROLL' => true,
    'ALLOW_SORT' => true,
    'ALLOW_PIN_HEADER' => true,
    'AJAX_OPTION_HISTORY' => 'N',
    'TOTAL_ROWS_COUNT' => $arResult["ALL_ROWS"],
    'COLUMNS' => array(
        array('id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true),
        array('id' => 'CREATED_DATE', 'name' => 'Created on', 'sort' => 'UF_FORM_DATE', 'default' => true),
        array('id' => 'MODIFIED_DATE', 'name' => 'Modified at', 'sort' => 'UF_FORM_MODIFIED', 'default' => true),
        array('id' => 'FORM_TYPE', 'name' => 'Form Type', 'sort' => 'UF_FORM_TYPE', 'default' => true),
        array('id' => 'ORDER_STATUS', 'name' => 'Order Status', 'default' => true),
        array('id' => 'REQUESTED_DELIVERY_MONTH', 'name' => 'Requested Delivery Month', 'default' => true),
        array('id' => 'LINK', 'name' => 'Edit', 'default' => true),
        array('id' => 'LINK_TO_FILE', 'name' => 'File', 'default' => true),
    ),
    'DEAL_ID' => $arResult['DEAL_ID']
],
    $component
);
