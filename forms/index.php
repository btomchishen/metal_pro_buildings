<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Context;

$request = Context::getCurrent()->getRequest();

switch ($request['FORM_TYPE']) {
    case 'StraightWallForm':
        $formType = 'straight_wall_form';
        break;
    case 'QuonsetForm':
        $formType = 'quonset_form';
        break;
    case 'QuonsetPartsOrder':
        $formType = 'quonset_parts_order';
        break;
    case 'RevisionToPurchaseOrder':
        $formType = 'revision_to_purchase_order';
        break;
    case 'StraightWallPartsOrder':
        $formType = 'straight_wall_parts_order';
        break;
}

require_once($_SERVER['DOCUMENT_ROOT'] . '/forms/' . $formType . '.php');

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>

