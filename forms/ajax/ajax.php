<?
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Context;

Bitrix\Main\Loader::includeModule('crm');
Bitrix\Main\Loader::includeModule('main');

$request = Context::getCurrent()->getRequest();

$action = $request['ACTION'];
$dealID = $request['DEAL_ID'];
$formID = $request['ID'];
$formType = $request['FORM_TYPE'];

switch ($formType) {
    case 'StraightWallForm':
        $form = new StraightWallForm($dealID, $formType, $formID);
        break;
    case 'QuonsetForm':
        $form = new QuonsetForm($dealID, $formType, $formID);
        break;
    case 'QuonsetPartsOrder':
        $form = new QuonsetPartsOrder($dealID, $formType, $formID);
        break;
    case 'RevisionToPurchaseOrder':
        $form = new RevisionToPurchaseOrder($dealID, $formType, $formID);
        break;
    case 'StraightWallPartsOrder':
        $form = new StraightWallPartsOrder($dealID, $formType, $formID);
        break;
}

if ($action == 'GET_DATA') {
    $record = CHighData::IsRecordExist(FORMS_HIGHLOAD, array('UF_DEAL_ID' => $dealID, 'UF_ID' => $formID));
    if (empty($record)) $action = 'FILL_FORM_DATA';
    else $action = 'FILL_EXISTED_FORM';
}

if ($action == 'FILL_FORM_DATA') {
    $result['data'] = $form->getDataFromDeal();
    $result['list'] = $form->getListsFromDeal();
}

if ($action == 'FILL_EXISTED_FORM') {
    $result['data'] = $form->getDataFromHLBT();
    $result['list'] = $form->getListsFromHLBT();
}

if ($action == 'DELETE') {
    $result['isDeleted'] = $form->deleteForm();
}

if ($action == 'RECALCULATE_PRICES') {
    $buildingPrice = $request['BUILDING_PRICE'];
    $taxRate = $request['TAX_RATE'];
    $subTotal = $request['SUB_TOTAL'];

    if ($formType == 'RevisionToPurchaseOrder') {
        $totalAmount = $request['TOTAL_AMOUNT'];
        $originalAmount = $request['ORIGINAL_CONTRACT_AMOUNT'];

        $result['prices'] = $form->calculatePrices($totalAmount, $originalAmount, $taxRate);
    } else {
        if ($request['CALCULATE_BY'] == 'SUB_TOTAL')
            $result['prices'] = $form->calculatePricesBySubTotal($taxRate, $subTotal);
        else
            $result['prices'] = $form->calculatePrices($buildingPrice, $taxRate);
    }
}

if ($action == 'GET_ADDENDUM') {
    $addendum1 = $request['ADDENDUM_1'];

    switch ($addendum1) {
        case 'NO':
            $result['addendum'] = '';
            $result['addendumLabel'] = 'Addendum';
            break;
        case 'PERMIT_APPROVAL':
            $result['addendum'] = ADDENDUM_PERMIT_APPROVAL;
            $result['addendumLabel'] = 'Permit Approval (DO NOT CHANGE)';
            break;
        case 'FINANCING_APPROVAL':
            $result['addendum'] = ADDENDUM_FINANCING_APPROVAL;
            $result['addendumLabel'] = 'Financing Approval (DO NOT CHANGE)';
            break;
        case 'BUYER_APPROVAL':
            $result['addendum'] = ADDENDUM_BUYER_APPROVAL;
            $result['addendumLabel'] = 'Buyer Approval (DO NOT CHANGE)';
            break;
    }
}

if ($action == 'SAVE_DATA') {
    $action1 = $request['ACTION1'];

    $savingData = $form->makeSavingDataArray($request);

    if ($action1 == 'UPDATE') {
        $record = CHighData::IsRecordExist(FORMS_HIGHLOAD, array('UF_DEAL_ID' => $dealID, 'UF_ID' => $formID));
        if (empty($record)) {
            $savingData["UF_FORM_DATE"] = date('m/d/Y h:i:s a', time());
            $result['createdFormID'] = CHighData::AddRecord(FORMS_HIGHLOAD, $savingData);
        } else {
            $result['isFormUpdated'] = CHighData::UpdateRecord(FORMS_HIGHLOAD, $record, $savingData);
        }

        $result['isMailSent'] = $form->processPDF();
    } else if ($action1 == 'NEW') {
        $newFormID = $form->getNewFormID();
        $savingData["UF_FORM_DATE"] = date('m/d/Y h:i:s a', time());
        $savingData['UF_ID'] = $newFormID;
        $form->formID = $newFormID;

        $result['createdFormID'] = CHighData::AddRecord(FORMS_HIGHLOAD, $savingData);

        $result['isMailSent'] = $form->processPDF();
    }

    $result['filePath'] = $form->getFilePath();
}

if (!empty($result)) $result['status'] = 'success';
else $result['status'] = 'error';

echo json_encode($result);