<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\Context;

$requiredModules = array('highloadblock');
foreach ($requiredModules as $requiredModule) {
    if (!CModule::IncludeModule($requiredModule)) {
        ShowError(GetMessage("F_NO_MODULE"));
        return false;
    }
}

$request = Context::getCurrent()->getRequest();

$session = \Bitrix\Main\Application::getInstance()->getSession();

$grid_options = new Bitrix\Main\Grid\Options('forms_list');
$sort = $grid_options->GetSorting(
    array('sort' => array("ID" => "desc"), 'vars' => array('by' => 'by', 'order' => 'order'))
);

if (!empty($request["PARAMS"]["params"]["FORM_ENTITY_ID"]))
{
    $session->set('DEAL_ID', $request["PARAMS"]["params"]["FORM_ENTITY_ID"]);
    $sort['sort'] = array('ID' => 'DESC');
}
fp($sort, 'tom_sort');
$nav_params = $grid_options->GetNavParams();
$entity = HLBT::compileEntity(FORMS_HIGHLOAD);
$entity_data_class = $entity->getDataClass();
$arResult["NAV"] = new \Bitrix\Main\UI\PageNavigation('forms_list');
$arResult["NAV"]->allowAllRecords(true)
    ->setPageSize(50)
    ->initFromUri();

$dealID = $session['DEAL_ID'];

$filter['UF_DEAL_ID'] = $dealID;

$rsData = $entity_data_class::getList(array(
    "select" => array('*'),
    'filter' => $filter,
    "count_total" => true,
    'order' => $sort["sort"],
    "offset" => $arResult["NAV"]->getOffset(),
    "limit" => $arResult["NAV"]->getLimit(),
));
$arResult["ALL_ROWS"] = $rsData->getCount();
$arResult["NAV"]->setRecordCount($arResult["ALL_ROWS"]);

$forms = array();
$formID = 1;
$arResult["ITEMS"] = array();
$arResult['DEAL_ID'] = $request["PARAMS"]["params"]["FORM_ENTITY_ID"];

while ($elements = $rsData->fetch()) {
    $forms[] = $elements;
}

foreach ($forms as $form) {
    $form['ORDER_STATUS'] = unserialize($form['UF_FORM_DEAL_INFORMATION'])['ORDER_STATUS'];
    $form['REQUESTED_DELIVERY_MONTH'] = unserialize($form['UF_FORM_PAYMENT'])['REQUESTED_DELIVERY_MONTH'];

    $file = CFile::GetByID($form['UF_DOCUMENT_PDF'])->Fetch();
    $pathToFile = 'https://metalpro.site/upload/' . $file['SUBDIR'] . '/' . $file['FILE_NAME'];

    $link = 'https://metalpro.site/forms/index.php?FORM_TYPE=' . $form['UF_FORM_TYPE'] . '&ID=' . $form['UF_ID'] . '&DEAL_ID=' . $form['UF_DEAL_ID'];
    $arResult['ITEMS'][] = [
        'data' => [
            'ID' => $form['UF_ID'],
            'FORM_TYPE' => $form['UF_FORM_TYPE'],
            'CREATED_DATE' => $form['UF_FORM_DATE'],
            'MODIFIED_DATE' => $form['UF_FORM_MODIFIED'],
            'ORDER_STATUS' => $form['ORDER_STATUS'],
            'REQUESTED_DELIVERY_MONTH' => $form['REQUESTED_DELIVERY_MONTH'],
            'LINK' => '<a href="' . $link . '" target="_blank">Link to form edit</a>',
            'LINK_TO_FILE' => '<a href="' . $pathToFile . '" target="_blank">Show Files</a>',
        ]
    ];

    $formID++;

}
$dealID = $request["PARAMS"]["params"]["FORM_ENTITY_ID"];

$arResult['FORMS_SELECT1'] = '<select class="s100" id="FORMS_SELECT1">
            <option value="##">Select Form Type</option>
            <option value="QuonsetForm&ID=' . $formID . '">Quonset</option>
            <option value="StraightWallForm&ID=' . $formID . '">Straight Form</option>
            <option value="QuonsetPartsOrder&ID=' . $formID . '">Quonset Parts Order</option>
            <option value="RevisionToPurchaseOrder&ID=' . $formID . '">Revision to Purchase Order</option>
            </select>';

?>

    <script>
        function showForm(id) {
            BX.SidePanel.Instance.open('/forms/index.php?FORM_TYPE=QuonsetForm&DEAL_ID=' + id,
                {
                    requestMethod: "post",
                    cacheable: false,
                    requestParams:
                        {
                            FORM_TYPE: "QuonsetForm",
                            DEAL_ID: id,
                        }
                });
        }

    </script>

<? $this->IncludeComponentTemplate(); ?>
