<?php

require $_SERVER["DOCUMENT_ROOT"] . '/composer/vendor/autoload.php';
require $_SERVER["DOCUMENT_ROOT"] . '/composer/vendor/fpdm-master/fpdm.php';

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class QuonsetForm extends from PDFForm
 *
 * All used constants located in /home/bitrix/www/local/php_interface/include/constants.php
 */
class QuonsetForm extends PDFForm
{
    protected const PATH_TO_PDF_TEMPLATE = '/home/bitrix/www/forms/pdf_templates/quonset.pdf';
    protected const PATH_TO_FILES = '/home/bitrix/www/forms/files/quonset/';

    public $dealID;
    public $formType;
    public $formID;
    public $pathToFilesFolder;
    public $pathToTemplate;

    public function __construct($dealID, $formType, $formID)
    {
        $this->dealID = $dealID;
        $this->formType = $formType;
        $this->formID = $formID;
        $this->pathToFilesFolder = self::PATH_TO_FILES;
        $this->pathToTemplate = self::PATH_TO_PDF_TEMPLATE;
    }

    /**
     * Get data for filing new form fields from Deal
     *
     * @return array Filled fields
     */
    public function getDataFromDeal()
    {
        $dealData = parent::getDealData();

        $result['CUSTOMER'] = $dealData['TITLE'];
        $result['COMPANY'] = parent::getCompanyFields()['TITLE'];
        $result['ACCOUNT_NUMBER'] = $dealData[LEAD_ID];
        $result['MAILING_ADDRESS'] = $dealData[MAILING_ADDRESS];
        $result['SHIPPING_ADDRESS'] = $dealData[SHIPPING_ADDRESS];
        $result['PRIMARY_PHONE'] = parent::formatPhone($dealData[PRIMARY_PHONE]);
        $result['SECONDARY_PHONE'] = parent::formatPhone($dealData[SECONDARY_PHONE]);
        $result['EMAIL'] = parent::getContactFMFields('EMAIL');
        $result['BUILDING_WIDTH'] = $dealData[BUILDING_WIDTH];
        $result['BUILDING_LENGTH'] = $dealData[BUILDING_LENGTH];
        $result['BUILDING_HEIGHT'] = $dealData[BUILDING_HEIGHT];
        $result['BUILDING_PRICE'] = '$' . number_format($dealData['OPPORTUNITY'], 2);

        return $result;
    }

    /**
     * Get options for filing new form lists from Deal
     *
     * @return array Filled lists
     */
    public function getListsFromDeal()
    {
        $dealData = parent::getDealData();

        $result['SERIES'] = parent::getCustomFieldData(MODEL_TYPE_FIELD_ID, $dealData[MODEL_TYPE], true);
        unset($result['SERIES'][8]);
        unset($result['SERIES'][7]);
        unset($result['SERIES'][6]);
        $result['MODEL'] = parent::getHighLoadList(MODEL_HIGHLOAD, 'none', 'UF_MODEL', true);
        $result['FOUNDATION_SYSTEM'] = parent::getCustomFieldData(FOUNDATION_SYSTEM_FIELD_ID, $dealData[FOUNDATION_SYSTEM], true);
        $result['GAUGE'] = parent::getCustomFieldData(GAUGE_FIELD_ID, $dealData[GAUGE], true);
        $result['SALES_REP'] = parent::getSalesRepList($dealData['ASSIGNED_BY_ID'], true);
        $result['REQUESTED_DELIVERY_MONTH'] = parent::getHighLoadList(REQUESTED_DELIVERY_MONTH_HIGHLOAD, $dealData['REQUESTED_DELIVERY_MONTH'], 'UF_MONTH', true);
        $result['ACCESSORY_1'] = parent::getHighLoadList(FORM_ACCESSORY_HIGHLOAD, 'none', 'UF_VALUE', true);
        $result['ACCESSORY_2'] = parent::getHighLoadList(FORM_ACCESSORY_HIGHLOAD, 'none', 'UF_VALUE', true);
        $result['ACCESSORY_3'] = parent::getHighLoadList(FORM_ACCESSORY_HIGHLOAD, 'none', 'UF_VALUE', true);
        $result['ACCESSORY_4'] = parent::getHighLoadList(FORM_ACCESSORY_HIGHLOAD, 'none', 'UF_VALUE', true);
        $result['ACCESSORY_5'] = parent::getHighLoadList(FORM_ACCESSORY_HIGHLOAD, 'none', 'UF_VALUE', true);
        $result['TAX_RATE'] = parent::getTaxRateList($dealData[PROVINCE], true);

        return $result;
    }

    /**
     * Get options for filing existed form lists from HLBT
     *
     * @return array Filled lists
     */
    public function getListsFromHLBT()
    {
        $HLBTData = self::getDataFromHLBT();

        $result['SERIES'] = parent::getCustomFieldData(MODEL_TYPE_FIELD_ID, $HLBTData['SERIES'], false);
        unset($result['SERIES'][8]);
        unset($result['SERIES'][7]);
        unset($result['SERIES'][6]);
        $result['MODEL'] = parent::getHighLoadList(MODEL_HIGHLOAD, $HLBTData['MODEL'], 'UF_MODEL', false);
        $result['FOUNDATION_SYSTEM'] = parent::getCustomFieldData(FOUNDATION_SYSTEM_FIELD_ID, $HLBTData['FOUNDATION_SYSTEM'], false);
        $result['GAUGE'] = parent::getCustomFieldData(GAUGE_FIELD_ID, $HLBTData['GAUGE'], false);
        $result['SALES_REP'] = parent::getSalesRepList($HLBTData['SALES_REP'], false);
        $result['REQUESTED_DELIVERY_MONTH'] = parent::getHighLoadList(REQUESTED_DELIVERY_MONTH_HIGHLOAD, $HLBTData['REQUESTED_DELIVERY_MONTH'], 'UF_MONTH', false);
        $result['ACCESSORY_1'] = parent::getHighLoadList(FORM_ACCESSORY_HIGHLOAD, $HLBTData['ACCESSORY_1'], 'UF_VALUE', false);
        $result['ACCESSORY_2'] = parent::getHighLoadList(FORM_ACCESSORY_HIGHLOAD, $HLBTData['ACCESSORY_2'], 'UF_VALUE', false);
        $result['ACCESSORY_3'] = parent::getHighLoadList(FORM_ACCESSORY_HIGHLOAD, $HLBTData['ACCESSORY_3'], 'UF_VALUE', false);
        $result['ACCESSORY_4'] = parent::getHighLoadList(FORM_ACCESSORY_HIGHLOAD, $HLBTData['ACCESSORY_4'], 'UF_VALUE', false);
        $result['ACCESSORY_5'] = parent::getHighLoadList(FORM_ACCESSORY_HIGHLOAD, $HLBTData['ACCESSORY_5'], 'UF_VALUE', false);
        $result['TAX_RATE'] = parent::getTaxRateList($HLBTData['TAX_RATE'], false);

        return $result;
    }

    /**
     * Create an array of Form Data and save to HighLoadBlock with ID = FORMS_HIGHLOAD
     *
     * @param array $request Data from Form
     * @return array
     */
    public function makeSavingDataArray($request)
    {
        global $USER;
        $currentUserID = $USER->GetID();

        $salesRep = parent::divideSalesRepInfo($request['SALES_REP']);

        $savingData = array(
//            "UF_FORM_DATE" => date('m/d/Y h:i:s a', time()),
            "UF_FORM_MODIFIED" => date('m/d/Y h:i:s a', time()),
            "UF_FORM_TYPE" => isset($request["FORM_TYPE"]) && !empty($request["FORM_TYPE"]) ? $request["FORM_TYPE"] : "",
            "UF_DEAL_ID" => isset($request["DEAL_ID"]) && !empty($request["DEAL_ID"]) ? $request["DEAL_ID"] : "",
            "UF_FORM_OWNER" => $currentUserID,
            "UF_ID" => $this->formID,
            "UF_FORM_DEAL_INFORMATION" => serialize(
                array(
                    "SALES_REP" => $salesRep[0],
                    "SALES_REP_ID" => $salesRep[1],
                    "CUSTOMER" => isset($request["CUSTOMER"]) && !empty($request["CUSTOMER"]) ? $request["CUSTOMER"] : "",
                    "COMPANY" => isset($request["COMPANY"]) && !empty($request["COMPANY"]) ? $request["COMPANY"] : "",
                    "ACCOUNT_NUMBER" => isset($request["ACCOUNT_NUMBER"]) && !empty($request["ACCOUNT_NUMBER"]) ? $request["ACCOUNT_NUMBER"] : "",
                    "ORDER_STATUS" => isset($request["ORDER_STATUS"]) && !empty($request["ORDER_STATUS"]) ? $request["ORDER_STATUS"] : "",
                    "PRIMARY_PHONE" => isset($request["PRIMARY_PHONE"]) && !empty($request["PRIMARY_PHONE"]) ? ($request["PRIMARY_PHONE"]) : "",
                    "SECONDARY_PHONE" => isset($request["SECONDARY_PHONE"]) && !empty($request["SECONDARY_PHONE"]) ? ($request["SECONDARY_PHONE"]) : "",
                    "WORK" => isset($request["WORK"]) && !empty($request["WORK"]) ? $request["WORK"] : "",
                    "EMAIL" => isset($request["EMAIL"]) && !empty($request["EMAIL"]) ? $request["EMAIL"] : "",
                )
            ),
            "UF_FORM_BUILDING" => serialize(
                array(
                    "BUILDING_USE" => isset($request["BUILDING_USE"]) && !empty($request["BUILDING_USE"]) ? $request["BUILDING_USE"] : "",
                    "IS_USA_ORDER" => isset($request["IS_USA_ORDER"]) && !empty($request["IS_USA_ORDER"]) ? 'Yes' : 'No',
                    "USA_BUILDING_DRAWINGS" => isset($request["USA_BUILDING_DRAWINGS"]) && !empty($request["USA_BUILDING_DRAWINGS"]) ? $request["USA_BUILDING_DRAWINGS"] : "",
                    "USA_BUILDING_USE" => isset($request["USA_BUILDING_USE"]) && !empty($request["USA_BUILDING_USE"]) ? $request["USA_BUILDING_USE"] : "",
                    "SERIES" => isset($request["SERIES"]) && !empty($request["SERIES"]) ? $request["SERIES"] : "",
                    "MODEL" => isset($request["MODEL"]) && !empty($request["MODEL"]) ? $request["MODEL"] : "",
                    "BUILDING_WIDTH" => isset($request["BUILDING_WIDTH"]) && !empty($request["BUILDING_WIDTH"]) ? $request["BUILDING_WIDTH"] : "",
                    "BUILDING_LENGTH" => isset($request["BUILDING_LENGTH"]) && !empty($request["BUILDING_LENGTH"]) ? $request["BUILDING_LENGTH"] : "",
                    "BUILDING_HEIGHT" => isset($request["BUILDING_HEIGHT"]) && !empty($request["BUILDING_HEIGHT"]) ? $request["BUILDING_HEIGHT"] : "",
                    "GAUGE" => isset($request["GAUGE"]) && !empty($request["GAUGE"]) ? $request["GAUGE"] : "",
                    "FOUNDATION_SYSTEM" => isset($request["FOUNDATION_SYSTEM"]) && !empty($request["FOUNDATION_SYSTEM"]) ? $request["FOUNDATION_SYSTEM"] : "",
                    "EXPOSURE_CONDITIONS" => isset($request["EXPOSURE_CONDITIONS"]) && !empty($request["EXPOSURE_CONDITIONS"]) ? $request["EXPOSURE_CONDITIONS"] : "",
                    "SHADOW_DRIFT" => isset($request["SHADOW_DRIFT"]) && !empty($request["SHADOW_DRIFT"]) ? $request["SHADOW_DRIFT"] : "",
                    "FRONT_WALL_FRAME" => isset($request["FRONT_WALL_FRAME"]) && !empty($request["FRONT_WALL_FRAME"]) ? $request["FRONT_WALL_FRAME"] : "",
                    "FRONT_WALL_FRAME_QTY1" => isset($request["FRONT_WALL_FRAME_QTY1"]) && !empty($request["FRONT_WALL_FRAME_QTY1"]) ? $request["FRONT_WALL_FRAME_QTY1"] : "",
                    "FRONT_WALL_FRAME_1" => isset($request["FRONT_WALL_FRAME_1"]) && !empty($request["FRONT_WALL_FRAME_1"]) ? $request["FRONT_WALL_FRAME_1"] : "",
                    "FRONT_WALL_FRAME_QTY2" => isset($request["FRONT_WALL_FRAME_QTY2"]) && !empty($request["FRONT_WALL_FRAME_QTY2"]) ? $request["FRONT_WALL_FRAME_QTY2"] : "",
                    "FRONT_WALL_FRAME_2" => isset($request["FRONT_WALL_FRAME_2"]) && !empty($request["FRONT_WALL_FRAME_2"]) ? $request["FRONT_WALL_FRAME_2"] : "",
                    "REAR_WALL_FRAME" => isset($request["REAR_WALL_FRAME"]) && !empty($request["REAR_WALL_FRAME"]) ? $request["REAR_WALL_FRAME"] : "",
                    "REAR_WALL_FRAME_QTY1" => isset($request["REAR_WALL_FRAME_QTY1"]) && !empty($request["REAR_WALL_FRAME_QTY1"]) ? $request["REAR_WALL_FRAME_QTY1"] : "",
                    "REAR_WALL_FRAME_1" => isset($request["REAR_WALL_FRAME_1"]) && !empty($request["REAR_WALL_FRAME_1"]) ? $request["REAR_WALL_FRAME_1"] : "",
                    "REAR_WALL_FRAME_QTY2" => isset($request["REAR_WALL_FRAME_QTY2"]) && !empty($request["REAR_WALL_FRAME_QTY2"]) ? $request["REAR_WALL_FRAME_QTY2"] : "",
                    "REAR_WALL_FRAME_2" => isset($request["REAR_WALL_FRAME_2"]) && !empty($request["REAR_WALL_FRAME_2"]) ? $request["REAR_WALL_FRAME_2"] : "",
                    "IS_SEA_CONTAINER_BUILDING" => isset($request["IS_SEA_CONTAINER_BUILDING"]) && !empty($request["IS_SEA_CONTAINER_BUILDING"]) ? 'Yes' : 'No',
                    "SEA_CONTAINER_STYLE" => isset($request["SEA_CONTAINER_STYLE"]) && !empty($request["SEA_CONTAINER_STYLE"]) ? $request["SEA_CONTAINER_STYLE"] : "",
                    "SEA_CONTAINER_DESIGN" => isset($request["SEA_CONTAINER_DESIGN"]) && !empty($request["SEA_CONTAINER_DESIGN"]) ? $request["SEA_CONTAINER_DESIGN"] : "",
                    "FRONT_WALL_EXTENSION" => isset($request["FRONT_WALL_EXTENSION"]) && !empty($request["FRONT_WALL_EXTENSION"]) ? $request["FRONT_WALL_EXTENSION"] : "",
                    "FRONT_WALL_SEA_CONTAINER_HEIGHT" => isset($request["FRONT_WALL_SEA_CONTAINER_HEIGHT"]) && !empty($request["FRONT_WALL_SEA_CONTAINER_HEIGHT"]) ? $request["FRONT_WALL_SEA_CONTAINER_HEIGHT"] : "",
                    "REAR_WALL_EXTENSION" => isset($request["REAR_WALL_EXTENSION"]) && !empty($request["REAR_WALL_EXTENSION"]) ? $request["REAR_WALL_EXTENSION"] : "",
                    "REAR_WALL_SEA_CONTAINER_HEIGHT" => isset($request["REAR_WALL_SEA_CONTAINER_HEIGHT"]) && !empty($request["REAR_WALL_SEA_CONTAINER_HEIGHT"]) ? $request["REAR_WALL_SEA_CONTAINER_HEIGHT"] : "",
                    "IS_ACCESSORIES" => isset($request["IS_ACCESSORIES"]) && !empty($request["IS_ACCESSORIES"]) ? 'Yes' : 'No',
                    "ACCESSORY_1" => isset($request["ACCESSORY_1"]) && !empty($request["ACCESSORY_1"]) ? $request["ACCESSORY_1"] : "",
                    "ACCESSORY_QTY_1" => isset($request["ACCESSORY_QTY_1"]) && !empty($request["ACCESSORY_QTY_1"]) ? $request["ACCESSORY_QTY_1"] : "",
                    "ACCESSORY_2" => isset($request["ACCESSORY_2"]) && !empty($request["ACCESSORY_2"]) ? $request["ACCESSORY_2"] : "",
                    "ACCESSORY_QTY_2" => isset($request["ACCESSORY_QTY_2"]) && !empty($request["ACCESSORY_QTY_2"]) ? $request["ACCESSORY_QTY_2"] : "",
                    "ACCESSORY_3" => isset($request["ACCESSORY_3"]) && !empty($request["ACCESSORY_3"]) ? $request["ACCESSORY_3"] : "",
                    "ACCESSORY_QTY_3" => isset($request["ACCESSORY_QTY_3"]) && !empty($request["ACCESSORY_QTY_3"]) ? $request["ACCESSORY_QTY_3"] : "",
                    "ACCESSORY_4" => isset($request["ACCESSORY_4"]) && !empty($request["ACCESSORY_4"]) ? $request["ACCESSORY_4"] : "",
                    "ACCESSORY_QTY_4" => isset($request["ACCESSORY_QTY_4"]) && !empty($request["ACCESSORY_QTY_4"]) ? $request["ACCESSORY_QTY_4"] : "",
                    "ACCESSORY_5" => isset($request["ACCESSORY_5"]) && !empty($request["ACCESSORY_5"]) ? $request["ACCESSORY_5"] : "",
                    "ACCESSORY_QTY_5" => isset($request["ACCESSORY_QTY_5"]) && !empty($request["ACCESSORY_QTY_5"]) ? $request["ACCESSORY_QTY_5"] : "",
                )
            ),
            "UF_FORM_PAYMENT" => serialize(
                array(
                    "REQUESTED_DELIVERY_MONTH" => isset($request["REQUESTED_DELIVERY_MONTH"]) && !empty($request["REQUESTED_DELIVERY_MONTH"]) ? $request["REQUESTED_DELIVERY_MONTH"] : "",
                    "PAYMENT_METHOD" => isset($request["PAYMENT_METHOD"]) && !empty($request["PAYMENT_METHOD"]) ? $request["PAYMENT_METHOD"] : "",
                    "IS_PICK_UP" => isset($request["IS_PICK_UP"]) && !empty($request["IS_PICK_UP"]) ? 'Yes' : 'No',
                    "BUILDING_PRICE" => isset($request["BUILDING_PRICE"]) && !empty($request["BUILDING_PRICE"]) ? $request["BUILDING_PRICE"] : "",
                    "TAX_RATE" => isset($request["TAX_RATE"]) && !empty($request["TAX_RATE"]) ? $request["TAX_RATE"] : "",
                    "TAX" => isset($request["TAX"]) && !empty($request["TAX"]) ? $request["TAX"] : "",
                    "SUB_TOTAL" => isset($request["SUB_TOTAL"]) && !empty($request["SUB_TOTAL"]) ? $request["SUB_TOTAL"] : "",
                    "FIRST_DEPOSIT" => isset($request["FIRST_DEPOSIT"]) && !empty($request["FIRST_DEPOSIT"]) ? $request["FIRST_DEPOSIT"] : "",
                    "FIRST_DEPOSIT_STATUS" => isset($request["FIRST_DEPOSIT_STATUS"]) && !empty($request["FIRST_DEPOSIT_STATUS"]) ? $request["FIRST_DEPOSIT_STATUS"] : "",
                    "SECOND_DEPOSIT" => isset($request["SECOND_DEPOSIT"]) && !empty($request["SECOND_DEPOSIT"]) ? $request["SECOND_DEPOSIT"] : "",
                    "SECOND_DEPOSIT_STATUS" => isset($request["SECOND_DEPOSIT_STATUS"]) && !empty($request["SECOND_DEPOSIT_STATUS"]) ? $request["SECOND_DEPOSIT_STATUS"] : "",
                    "BALANCE_REMAINING" => isset($request["BALANCE_REMAINING"]) && !empty($request["BALANCE_REMAINING"]) ? $request["BALANCE_REMAINING"] : "",
                    "MAILING_ADDRESS" => isset($request["MAILING_ADDRESS"]) && !empty($request["MAILING_ADDRESS"]) ? $request["MAILING_ADDRESS"] : "",
                    "SITE_ADDRESS" => isset($request["SITE_ADDRESS"]) && !empty($request["SITE_ADDRESS"]) ? $request["SITE_ADDRESS"] : "",
                    "SHIPPING_ADDRESS" => isset($request["SHIPPING_ADDRESS"]) && !empty($request["SHIPPING_ADDRESS"]) ? $request["SHIPPING_ADDRESS"] : "",
                )
            ),
            "UF_FORM_ADDITIONAL" => serialize(
                array(
                    "NOTES" => isset($request["NOTES"]) && !empty($request["NOTES"]) ? $request["NOTES"] : "",
                    "ADDENDUM_1" => isset($request["ADDENDUM_1"]) && !empty($request["ADDENDUM_1"]) ? $request["ADDENDUM_1"] : "",
                    "ADDENDUM" => isset($request["ADDENDUM"]) && !empty($request["ADDENDUM"]) ? $request["ADDENDUM"] : "",
                    "NOTES_TO_OFFICE" => isset($request["NOTES_TO_OFFICE"]) && !empty($request["NOTES_TO_OFFICE"]) ? $request["NOTES_TO_OFFICE"] : "",
                )
            ),
        );

        return $savingData;
    }

    /**
     * Get data from HighLoadBlock with ID = FORMS_HIGHLOAD to fill existed form
     *
     * @return array
     */
    public function getDataFromHLBT()
    {
        $formData = array_shift(CHighData::GetList(FORMS_HIGHLOAD, array('UF_DEAL_ID' => $this->dealID, 'UF_ID' => $this->formID)));

        $HLBTData = array(
            "FORM_DATE" => $formData['UF_FORM_DATE'],
            "FORM_MODIFIED" => $formData['UF_FORM_MODIFIED'],
            "FORM_TYPE" => $formData['UF_FORM_TYPE'],
            "DEAL_ID" => $formData['UF_DEAL_ID'],
            "FORM_OWNER" => $formData['UF_FORM_OWNER'],
            // UF_FORM_DEAL_INFORMATION
            "SALES_REP" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['SALES_REP'] . '_' . unserialize($formData['UF_FORM_DEAL_INFORMATION'])['SALES_REP_ID'],
            "SALES_REP_ID" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['SALES_REP_ID'],
            "CUSTOMER" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['CUSTOMER'],
            "COMPANY" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['COMPANY'],
            "ACCOUNT_NUMBER" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['ACCOUNT_NUMBER'],
            "ORDER_STATUS" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['ORDER_STATUS'],
            "PRIMARY_PHONE" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['PRIMARY_PHONE'],
            "SECONDARY_PHONE" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['SECONDARY_PHONE'],
            "WORK" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['WORK'],
            "EMAIL" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['EMAIL'],
            // UF_FORM_BUILDING
            "BUILDING_USE" => unserialize($formData['UF_FORM_BUILDING'])['BUILDING_USE'],
            "IS_USA_ORDER" => unserialize($formData['UF_FORM_BUILDING'])['IS_USA_ORDER'],
            "USA_BUILDING_DRAWINGS" => unserialize($formData['UF_FORM_BUILDING'])['USA_BUILDING_DRAWINGS'],
            "USA_BUILDING_USE" => unserialize($formData['UF_FORM_BUILDING'])['USA_BUILDING_USE'],
            "SERIES" => unserialize($formData['UF_FORM_BUILDING'])['SERIES'],
            "MODEL" => unserialize($formData['UF_FORM_BUILDING'])['MODEL'],
            "BUILDING_WIDTH" => unserialize($formData['UF_FORM_BUILDING'])['BUILDING_WIDTH'],
            "BUILDING_LENGTH" => unserialize($formData['UF_FORM_BUILDING'])['BUILDING_LENGTH'],
            "BUILDING_HEIGHT" => unserialize($formData['UF_FORM_BUILDING'])['BUILDING_HEIGHT'],
            "GAUGE" => unserialize($formData['UF_FORM_BUILDING'])['GAUGE'],
            "FOUNDATION_SYSTEM" => unserialize($formData['UF_FORM_BUILDING'])['FOUNDATION_SYSTEM'],
            "EXPOSURE_CONDITIONS" => unserialize($formData['UF_FORM_BUILDING'])['EXPOSURE_CONDITIONS'],
            "SHADOW_DRIFT" => unserialize($formData['UF_FORM_BUILDING'])['SHADOW_DRIFT'],
            "FRONT_WALL_FRAME" => unserialize($formData['UF_FORM_BUILDING'])['FRONT_WALL_FRAME'],
            "FRONT_WALL_FRAME_QTY1" => unserialize($formData['UF_FORM_BUILDING'])['FRONT_WALL_FRAME_QTY1'],
            "FRONT_WALL_FRAME_1" => unserialize($formData['UF_FORM_BUILDING'])['FRONT_WALL_FRAME_1'],
            "FRONT_WALL_FRAME_QTY2" => unserialize($formData['UF_FORM_BUILDING'])['FRONT_WALL_FRAME_QTY2'],
            "FRONT_WALL_FRAME_2" => unserialize($formData['UF_FORM_BUILDING'])['FRONT_WALL_FRAME_2'],
            "REAR_WALL_FRAME" => unserialize($formData['UF_FORM_BUILDING'])['REAR_WALL_FRAME'],
            "REAR_WALL_FRAME_QTY1" => unserialize($formData['UF_FORM_BUILDING'])['REAR_WALL_FRAME_QTY1'],
            "REAR_WALL_FRAME_1" => unserialize($formData['UF_FORM_BUILDING'])['REAR_WALL_FRAME_1'],
            "REAR_WALL_FRAME_QTY2" => unserialize($formData['UF_FORM_BUILDING'])['REAR_WALL_FRAME_QTY2'],
            "REAR_WALL_FRAME_2" => unserialize($formData['UF_FORM_BUILDING'])['REAR_WALL_FRAME_2'],
            "IS_SEA_CONTAINER_BUILDING" => unserialize($formData['UF_FORM_BUILDING'])['IS_SEA_CONTAINER_BUILDING'],
            "SEA_CONTAINER_STYLE" => unserialize($formData['UF_FORM_BUILDING'])['SEA_CONTAINER_STYLE'],
            "SEA_CONTAINER_DESIGN" => unserialize($formData['UF_FORM_BUILDING'])['SEA_CONTAINER_DESIGN'],
            "FRONT_WALL_EXTENSION" => unserialize($formData['UF_FORM_BUILDING'])['FRONT_WALL_EXTENSION'],
            "FRONT_WALL_SEA_CONTAINER_HEIGHT" => unserialize($formData['UF_FORM_BUILDING'])['FRONT_WALL_SEA_CONTAINER_HEIGHT'],
            "REAR_WALL_EXTENSION" => unserialize($formData['UF_FORM_BUILDING'])['REAR_WALL_EXTENSION'],
            "REAR_WALL_SEA_CONTAINER_HEIGHT" => unserialize($formData['UF_FORM_BUILDING'])['REAR_WALL_SEA_CONTAINER_HEIGHT'],
            "IS_ACCESSORIES" => unserialize($formData['UF_FORM_BUILDING'])['IS_ACCESSORIES'],
            "ACCESSORY_1" => unserialize($formData['UF_FORM_BUILDING'])['ACCESSORY_1'],
            "ACCESSORY_QTY_1" => unserialize($formData['UF_FORM_BUILDING'])['ACCESSORY_QTY_1'],
            "ACCESSORY_2" => unserialize($formData['UF_FORM_BUILDING'])['ACCESSORY_2'],
            "ACCESSORY_QTY_2" => unserialize($formData['UF_FORM_BUILDING'])['ACCESSORY_QTY_2'],
            "ACCESSORY_3" => unserialize($formData['UF_FORM_BUILDING'])['ACCESSORY_3'],
            "ACCESSORY_QTY_3" => unserialize($formData['UF_FORM_BUILDING'])['ACCESSORY_QTY_3'],
            "ACCESSORY_4" => unserialize($formData['UF_FORM_BUILDING'])['ACCESSORY_4'],
            "ACCESSORY_QTY_4" => unserialize($formData['UF_FORM_BUILDING'])['ACCESSORY_QTY_4'],
            "ACCESSORY_5" => unserialize($formData['UF_FORM_BUILDING'])['ACCESSORY_5'],
            "ACCESSORY_QTY_5" => unserialize($formData['UF_FORM_BUILDING'])['ACCESSORY_QTY_5'],
            // UF_FORM_PAYMENT
            "REQUESTED_DELIVERY_MONTH" => unserialize($formData['UF_FORM_PAYMENT'])['REQUESTED_DELIVERY_MONTH'],
            "PAYMENT_METHOD" => unserialize($formData['UF_FORM_PAYMENT'])['PAYMENT_METHOD'],
            "IS_PICK_UP" => unserialize($formData['UF_FORM_PAYMENT'])['IS_PICK_UP'],
            "BUILDING_PRICE" => unserialize($formData['UF_FORM_PAYMENT'])['BUILDING_PRICE'],
            "TAX_RATE" => unserialize($formData['UF_FORM_PAYMENT'])['TAX_RATE'],
            "TAX" => unserialize($formData['UF_FORM_PAYMENT'])['TAX'],
            "SUB_TOTAL" => unserialize($formData['UF_FORM_PAYMENT'])['SUB_TOTAL'],
            "FIRST_DEPOSIT" => unserialize($formData['UF_FORM_PAYMENT'])['FIRST_DEPOSIT'],
            "FIRST_DEPOSIT_STATUS" => unserialize($formData['UF_FORM_PAYMENT'])['FIRST_DEPOSIT_STATUS'],
            "SECOND_DEPOSIT" => unserialize($formData['UF_FORM_PAYMENT'])['SECOND_DEPOSIT'],
            "SECOND_DEPOSIT_STATUS" => unserialize($formData['UF_FORM_PAYMENT'])['SECOND_DEPOSIT_STATUS'],
            "BALANCE_REMAINING" => unserialize($formData['UF_FORM_PAYMENT'])['BALANCE_REMAINING'],
            "MAILING_ADDRESS" => unserialize($formData['UF_FORM_PAYMENT'])['MAILING_ADDRESS'],
            "SITE_ADDRESS" => unserialize($formData['UF_FORM_PAYMENT'])['SITE_ADDRESS'],
            "SHIPPING_ADDRESS" => unserialize($formData['UF_FORM_PAYMENT'])['SHIPPING_ADDRESS'],
            // UF_FORM_ADDITIONAL
            "NOTES" => unserialize($formData['UF_FORM_ADDITIONAL'])['NOTES'],
            "ADDENDUM_1" => unserialize($formData['UF_FORM_ADDITIONAL'])['ADDENDUM_1'],
            "ADDENDUM" => unserialize($formData['UF_FORM_ADDITIONAL'])['ADDENDUM'],
            "NOTES_TO_OFFICE" => unserialize($formData['UF_FORM_ADDITIONAL'])['NOTES_TO_OFFICE'],
        );

        return $HLBTData;
    }

    /**
     * Create an array of HLBT Data for filling PDF fields
     *
     * @return array Fields
     */
    public function makePDFDataArray()
    {
        $HLBTData = $this->getDataFromHLBT();

        $salesRep = parent::divideSalesRepInfo($HLBTData['SALES_REP']);
        $date = strval(FormatDate("m/d/Y", MakeTimeStamp($HLBTData["FORM_DATE"])));

        $fields = array(
            // UF_FORM_DEAL_INFORMATION
            "Date" => $date,
            "Customer Name" => !empty($HLBTData['CUSTOMER']) ? $HLBTData['CUSTOMER'] : "",
            "Sales Rep Name" => $salesRep[0],
            "Company" => !empty($HLBTData['COMPANY']) ? $HLBTData['COMPANY'] : "",
            "Account No" => !empty($HLBTData['ACCOUNT_NUMBER']) ? $HLBTData['ACCOUNT_NUMBER'] : "",
            "Order Status" => !empty($HLBTData['ORDER_STATUS']) ? $HLBTData['ORDER_STATUS'] : "",
            "Primary Phone" => !empty($HLBTData['PRIMARY_PHONE']) ? $HLBTData['PRIMARY_PHONE'] : "",
            "Secondary Phone" => !empty($HLBTData['SECONDARY_PHONE']) ? $HLBTData['SECONDARY_PHONE'] : "",
            "Work" => !empty($HLBTData['WORK']) ? $HLBTData['WORK'] : "",
            "Email" => !empty($HLBTData['EMAIL']) ? $HLBTData['EMAIL'] : "",
            // UF_FORM_BUILDING
            "Building Use" => !empty($HLBTData['BUILDING_USE']) ? $HLBTData['BUILDING_USE'] : "",
            "Shadow Drift" => !empty($HLBTData['SHADOW_DRIFT']) ? $HLBTData['SHADOW_DRIFT'] : "",
            "USA Building Drawings" => !empty($HLBTData['USA_BUILDING_DRAWINGS']) ? $HLBTData['USA_BUILDING_DRAWINGS'] : "",
            "USA Building Use" => !empty($HLBTData['USA_BUILDING_USE']) ? $HLBTData['USA_BUILDING_USE'] : "",
            "SERIES" => !empty($HLBTData['SERIES']) ? $HLBTData['SERIES'] : "",
            "Model" => !empty($HLBTData['MODEL']) ? $HLBTData['MODEL'] : "",
            "Building Width" => !empty($HLBTData['BUILDING_WIDTH']) ? $HLBTData['BUILDING_WIDTH'] : "",
            "Building Length" => !empty($HLBTData['BUILDING_LENGTH']) ? $HLBTData['BUILDING_LENGTH'] : "",
            "Building Height" => !empty($HLBTData['BUILDING_HEIGHT']) ? $HLBTData['BUILDING_HEIGHT'] : "",
            "Gauge" => !empty($HLBTData['GAUGE']) ? $HLBTData['GAUGE'] : "",
            "Foundation System" => !empty($HLBTData['FOUNDATION_SYSTEM']) ? $HLBTData['FOUNDATION_SYSTEM'] : "",
            "Front Wall Frame" => !empty($HLBTData['FRONT_WALL_FRAME']) ? $HLBTData['FRONT_WALL_FRAME'] : "",
            "Front Wall Frame QTY 1" => !empty($HLBTData['FRONT_WALL_FRAME_QTY1']) ? $HLBTData['FRONT_WALL_FRAME_QTY1'] : "",
            "Front Wall Frame 1 (W x H)" => !empty($HLBTData['FRONT_WALL_FRAME_1']) ? $HLBTData['FRONT_WALL_FRAME_1'] : "",
            "Front Wall Frame QTY 2" => !empty($HLBTData['FRONT_WALL_FRAME_QTY2']) ? $HLBTData['FRONT_WALL_FRAME_QTY2'] : "",
            "Front Wall Frame 2 (W x H)" => !empty($HLBTData['FRONT_WALL_FRAME_2']) ? $HLBTData['FRONT_WALL_FRAME_2'] : "",
            "Rear Wall Frame" => !empty($HLBTData['REAR_WALL_FRAME']) ? $HLBTData['REAR_WALL_FRAME'] : "",
            "Rear Wall Frame QTY 1" => !empty($HLBTData['REAR_WALL_FRAME_QTY1']) ? $HLBTData['REAR_WALL_FRAME_QTY1'] : "",
            "Rear Wall Frame 1 (W x H)" => !empty($HLBTData['REAR_WALL_FRAME_1']) ? $HLBTData['REAR_WALL_FRAME_1'] : "",
            "Rear Wall Frame QTY 2" => !empty($HLBTData['REAR_WALL_FRAME_QTY2']) ? $HLBTData['REAR_WALL_FRAME_QTY2'] : "",
            "Rear Wall Frame 2 (W x H)" => !empty($HLBTData['REAR_WALL_FRAME_2']) ? $HLBTData['REAR_WALL_FRAME_2'] : "",
            "Sea Container Style" => !empty($HLBTData['SEA_CONTAINER_STYLE']) ? $HLBTData['SEA_CONTAINER_STYLE'] : "",
            "Sea Container Design" => !empty($HLBTData['SEA_CONTAINER_DESIGN']) ? $HLBTData['SEA_CONTAINER_DESIGN'] : "",
            "Front Wall Extension" => !empty($HLBTData['FRONT_WALL_EXTENSION']) ? $HLBTData['FRONT_WALL_EXTENSION'] : "",
            "Front Wall Sea Container Height" => !empty($HLBTData['FRONT_WALL_SEA_CONTAINER_HEIGHT']) ? $HLBTData['FRONT_WALL_SEA_CONTAINER_HEIGHT'] : "",
            "Rear Wall Extension" => !empty($HLBTData['REAR_WALL_EXTENSION']) ? $HLBTData['REAR_WALL_EXTENSION'] : "",
            "Rear Wall Sea Container Height" => !empty($HLBTData['REAR_WALL_SEA_CONTAINER_HEIGHT']) ? $HLBTData['REAR_WALL_SEA_CONTAINER_HEIGHT'] : "",
            "Accessory 1" => !empty($HLBTData['ACCESSORY_1']) ? $HLBTData['ACCESSORY_1'] : "",
            "Accessory QTY 1" => !empty($HLBTData['ACCESSORY_QTY_1']) ? $HLBTData['ACCESSORY_QTY_1'] : "",
            "Accessory 2" => !empty($HLBTData['ACCESSORY_2']) ? $HLBTData['ACCESSORY_2'] : "",
            "Accessory QTY 2" => !empty($HLBTData['ACCESSORY_QTY_2']) ? $HLBTData['ACCESSORY_QTY_2'] : "",
            "Accessory 3" => !empty($HLBTData['ACCESSORY_3']) ? $HLBTData['ACCESSORY_3'] : "",
            "Accessory QTY 3" => !empty($HLBTData['ACCESSORY_QTY_3']) ? $HLBTData['ACCESSORY_QTY_3'] : "",
            "Accessory 4" => !empty($HLBTData['ACCESSORY_4']) ? $HLBTData['ACCESSORY_4'] : "",
            "Accessory QTY 4" => !empty($HLBTData['ACCESSORY_QTY_4']) ? $HLBTData['ACCESSORY_QTY_4'] : "",
            "Accessory 5" => !empty($HLBTData['ACCESSORY_5']) ? $HLBTData['ACCESSORY_5'] : "",
            "Accessory QTY 5" => !empty($HLBTData['ACCESSORY_QTY_5']) ? $HLBTData['ACCESSORY_QTY_5'] : "",
            // UF_FORM_PAYMENT
            "Requested Delivery Month" => !empty($HLBTData['REQUESTED_DELIVERY_MONTH']) ? $HLBTData['REQUESTED_DELIVERY_MONTH'] : "",
            "Building Price" => !empty($HLBTData['BUILDING_PRICE']) ? $HLBTData['BUILDING_PRICE'] : "",
            "Sub Total" => !empty($HLBTData['SUB_TOTAL']) ? $HLBTData['SUB_TOTAL'] : "",
            "Pick Up" => !empty($HLBTData['IS_PICK_UP']) && $HLBTData['IS_PICK_UP'] == 'Yes' ? 'Pick Up from Storage Yard' : "",
            "Tax" => !empty($HLBTData['TAX']) ? $HLBTData['TAX'] : "",
            "Tax Rate" => !empty($HLBTData['TAX_RATE']) ? explode("_", $HLBTData['TAX_RATE'])[1] . '%' : "",
            "First Deposit" => !empty($HLBTData['FIRST_DEPOSIT']) ? $HLBTData['FIRST_DEPOSIT'] : "",
            "1st Deposit Status" => !empty($HLBTData['FIRST_DEPOSIT_STATUS']) ? $HLBTData['FIRST_DEPOSIT_STATUS'] : "",
            "Second Deposit" => !empty($HLBTData['SECOND_DEPOSIT']) ? $HLBTData['SECOND_DEPOSIT'] : "",
            "2nd Deposit Status" => !empty($HLBTData['SECOND_DEPOSIT_STATUS']) ? $HLBTData['SECOND_DEPOSIT_STATUS'] : "",
            "Balance Remaining" => !empty($HLBTData['BALANCE_REMAINING']) ? $HLBTData['BALANCE_REMAINING'] : "",
            "Mailing Address" => !empty($HLBTData['MAILING_ADDRESS']) ? $HLBTData['MAILING_ADDRESS'] : "",
            "Site Address" => !empty($HLBTData['SITE_ADDRESS']) ? $HLBTData['SITE_ADDRESS'] : "",
            "Shipping Address" => !empty($HLBTData['SHIPPING_ADDRESS']) ? $HLBTData['SHIPPING_ADDRESS'] : "",
            // UF_FORM_ADDITIONAL
            "Notes" => !empty($HLBTData['NOTES']) ? $HLBTData['NOTES'] : "",
            "Addendum" => !empty($HLBTData['ADDENDUM']) ? parent::changeCharacters($HLBTData['ADDENDUM']) : "",
        );

        return $fields;
    }

    /**
     * Create PDF file and send it to responsible person
     */
    public function processPDF()
    {
        $fields = $this->makePDFDataArray();

        parent::createPDF($fields);
        parent::createChatForDeal();
        parent::sendNotification();
        return parent::sendMail();
    }
}
