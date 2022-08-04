<?php

require $_SERVER["DOCUMENT_ROOT"] . '/composer/vendor/autoload.php';
require $_SERVER["DOCUMENT_ROOT"] . '/composer/vendor/fpdm-master/fpdm.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/forms/classes/PDFForm.php';

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class StraightWallForm extends from PDFForm
 *
 * All used constants located in /home/bitrix/www/local/php_interface/include/constants.php
 */
class StraightWallForm extends PDFForm
{
    protected const PATH_TO_PDF_TEMPLATE = '/home/bitrix/www/forms/pdf_templates/straight.pdf';
    protected const PATH_TO_FILES = '/home/bitrix/www/forms/files/straight_wall/';

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
        $result['VENDOR_ID'] = $dealData[VENDOR_ID];
        $result['MAILING_ADDRESS'] = $dealData[MAILING_ADDRESS];
        $result['SHIPPING_ADDRESS'] = $dealData[SHIPPING_ADDRESS];
        $result['PRIMARY_PHONE'] = parent::formatPhone($dealData[PRIMARY_PHONE]);
        $result['SECONDARY_PHONE'] = parent::formatPhone($dealData[SECONDARY_PHONE]);
        $result['EMAIL'] = parent::getContactFMFields('EMAIL');
        $result['BUILDING_WIDTH'] = $dealData[BUILDING_WIDTH];
        $result['BUILDING_LENGTH'] = $dealData[BUILDING_LENGTH];
        $result['FRONT_WALL_HEIGHT'] = $dealData[BUILDING_HEIGHT];
        $result['REAR_WALL_HEIGHT'] = $dealData[BUILDING_HEIGHT];
//        $result['BUILDING_PRICE'] = '$' . str_replace('|CAD', '', (number_format($dealData[BUILDING_PRICE], 2)));
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

        $result['SALES_REP'] = parent::getSalesRepList($dealData['ASSIGNED_BY_ID'], true);
        $result['ROOF_COLOR'] = parent::getHighLoadList(COLORS_HIGHLOAD, 0, 'UF_COLOR', true);
        $result['WALL_COLOR'] = parent::getHighLoadList(COLORS_HIGHLOAD, 0, 'UF_COLOR', true);
        $result['TRIM_COLOR'] = parent::getHighLoadList(COLORS_HIGHLOAD, 0, 'UF_COLOR', true);
        $result['REQUESTED_DELIVERY_MONTH'] = parent::getHighLoadList(REQUESTED_DELIVERY_MONTH_HIGHLOAD, $dealData['REQUESTED_DELIVERY_MONTH'], 'UF_MONTH', true);
        $result['GUTTERS_DOWNS_COLOR'] = parent::getHighLoadList(COLORS_HIGHLOAD, 'none', 'UF_COLOR', true);
        array_splice($result['GUTTERS_DOWNS_COLOR'], 0, 0, '<option selected value="N/A">N/A</option>');
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

        $result['ROOF_COLOR'] = parent::getHighLoadList(COLORS_HIGHLOAD, $HLBTData['ROOF_COLOR'], 'UF_COLOR', false);
        $result['WALL_COLOR'] = parent::getHighLoadList(COLORS_HIGHLOAD, $HLBTData['WALL_COLOR'], 'UF_COLOR', false);
        $result['TRIM_COLOR'] = parent::getHighLoadList(COLORS_HIGHLOAD, $HLBTData['TRIM_COLOR'], 'UF_COLOR', false);
        $result['SALES_REP'] = parent::getSalesRepList($HLBTData['SALES_REP'], false);
        $result['REQUESTED_DELIVERY_MONTH'] = parent::getHighLoadList(REQUESTED_DELIVERY_MONTH_HIGHLOAD, $HLBTData['REQUESTED_DELIVERY_MONTH'], 'UF_MONTH', false);
        $result['GUTTERS_DOWNS_COLOR'] = parent::getHighLoadList(COLORS_HIGHLOAD, $HLBTData['GUTTERS_DOWNS_COLOR'], 'UF_COLOR', false);
        array_splice($result['GUTTERS_DOWNS_COLOR'], 0, 0, '<option value="N/A">N/A</option>');
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
                    "PROJECT" => isset($request["PROJECT"]) && !empty($request["PROJECT"]) ? $request["PROJECT"] : "",
                    "ACCOUNT_NUMBER" => isset($request["ACCOUNT_NUMBER"]) && !empty($request["ACCOUNT_NUMBER"]) ? $request["ACCOUNT_NUMBER"] : "",
                    "VENDOR_ID" => isset($request["VENDOR_ID"]) && !empty($request["VENDOR_ID"]) ? $request["VENDOR_ID"] : "",
                    "ORDER_STATUS" => isset($request["ORDER_STATUS"]) && !empty($request["ORDER_STATUS"]) ? $request["ORDER_STATUS"] : "",
                    "PRIMARY_PHONE" => isset($request["PRIMARY_PHONE"]) && !empty($request["PRIMARY_PHONE"]) ? ($request["PRIMARY_PHONE"]) : "",
                    "SECONDARY_PHONE" => isset($request["SECONDARY_PHONE"]) && !empty($request["SECONDARY_PHONE"]) ? ($request["SECONDARY_PHONE"]) : "",
                    "WORK" => isset($request["WORK"]) && !empty($request["WORK"]) ? $request["WORK"] : "",
                    "EMAIL" => isset($request["EMAIL"]) && !empty($request["EMAIL"]) ? $request["EMAIL"] : "",
                )
            ),
            "UF_FORM_BUILDING" => serialize(
                array(
                    "MODEL_TYPE" => isset($request["MODEL_TYPE"]) && !empty($request["MODEL_TYPE"]) ? $request["MODEL_TYPE"] : "",
                    "BUILDING_CODE" => isset($request["BUILDING_CODE"]) && !empty($request["BUILDING_CODE"]) ? $request["BUILDING_CODE"] : "",
                    "RISK_CATEGORY" => isset($request["RISK_CATEGORY"]) && !empty($request["RISK_CATEGORY"]) ? $request["RISK_CATEGORY"] : "",
                    "ROOF_SNOW_LOAD" => isset($request["ROOF_SNOW_LOAD"]) && !empty($request["ROOF_SNOW_LOAD"]) ? $request["ROOF_SNOW_LOAD"] : "",
                    "COLLATERAL" => isset($request["COLLATERAL"]) && !empty($request["COLLATERAL"]) ? $request["COLLATERAL"] : "",
                    "GROUND_SNOW_LOAD" => isset($request["GROUND_SNOW_LOAD"]) && !empty($request["GROUND_SNOW_LOAD"]) ? $request["GROUND_SNOW_LOAD"] : "",
                    "RAIN_LOAD" => isset($request["RAIN_LOAD"]) && !empty($request["RAIN_LOAD"]) ? $request["RAIN_LOAD"] : "",
                    "SA_02" => isset($request["SA_02"]) && !empty($request["SA_02"]) ? $request["SA_02"] : "",
                    "SA_05" => isset($request["SA_05"]) && !empty($request["SA_05"]) ? $request["SA_05"] : "",
                    "SA_1" => isset($request["SA_1"]) && !empty($request["SA_1"]) ? $request["SA_1"] : "",
                    "SA_2" => isset($request["SA_2"]) && !empty($request["SA_2"]) ? $request["SA_2"] : "",
                    "BUILDING_USE" => isset($request["BUILDING_USE"]) && !empty($request["BUILDING_USE"]) ? $request["BUILDING_USE"] : "",
                    "BUILDING_WIDTH" => isset($request["BUILDING_WIDTH"]) && !empty($request["BUILDING_WIDTH"]) ? $request["BUILDING_WIDTH"] : "",
                    "BUILDING_LENGTH" => isset($request["BUILDING_LENGTH"]) && !empty($request["BUILDING_LENGTH"]) ? $request["BUILDING_LENGTH"] : "",
                    "FRONT_WALL_HEIGHT" => isset($request["FRONT_WALL_HEIGHT"]) && !empty($request["FRONT_WALL_HEIGHT"]) ? $request["FRONT_WALL_HEIGHT"] : "",
                    "REAR_WALL_HEIGHT" => isset($request["REAR_WALL_HEIGHT"]) && !empty($request["REAR_WALL_HEIGHT"]) ? $request["REAR_WALL_HEIGHT"] : "",
                    "ROOF_PITCH" => isset($request["ROOF_PITCH"]) && !empty($request["ROOF_PITCH"]) ? $request["ROOF_PITCH"] : "",
                    "PRIMER_COLOR" => isset($request["PRIMER_COLOR"]) && !empty($request["PRIMER_COLOR"]) ? $request["PRIMER_COLOR"] : "",
                    "LEFT_ENDWALL_FRAME" => isset($request["LEFT_ENDWALL_FRAME"]) && !empty($request["LEFT_ENDWALL_FRAME"]) ? $request["LEFT_ENDWALL_FRAME"] : "",
                    "RIGHT_ENDWALL_FRAME" => isset($request["RIGHT_ENDWALL_FRAME"]) && !empty($request["RIGHT_ENDWALL_FRAME"]) ? $request["RIGHT_ENDWALL_FRAME"] : "",
                    "FRAME_COLUMN" => isset($request["FRAME_COLUMN"]) && !empty($request["FRAME_COLUMN"]) ? $request["FRAME_COLUMN"] : "",
                    "FRAME_RAFTER" => isset($request["FRAME_RAFTER"]) && !empty($request["FRAME_RAFTER"]) ? $request["FRAME_RAFTER"] : "",
                    "RIGID_FRAMES" => isset($request["RIGID_FRAMES"]) && !empty($request["RIGID_FRAMES"]) ? $request["RIGID_FRAMES"] : "",
                    "BASE_CONDITIONS" => isset($request["BASE_CONDITIONS"]) && !empty($request["BASE_CONDITIONS"]) ? $request["BASE_CONDITIONS"] : "",
                    "LEW_BRACING" => isset($request["LEW_BRACING"]) && !empty($request["LEW_BRACING"]) ? $request["LEW_BRACING"] : "",
                    "LEW_1_QTY" => isset($request["LEW_1_QTY"]) && !empty($request["LEW_1_QTY"]) ? $request["LEW_1_QTY"] : "",
                    "LEW_1_FRAME" => isset($request["LEW_1_FRAME"]) && !empty($request["LEW_1_FRAME"]) ? $request["LEW_1_FRAME"] : "",
                    "LEW_2_QTY" => isset($request["LEW_2_QTY"]) && !empty($request["LEW_2_QTY"]) ? $request["LEW_2_QTY"] : "",
                    "LEW_2_FRAME" => isset($request["LEW_2_FRAME"]) && !empty($request["LEW_2_FRAME"]) ? $request["LEW_2_FRAME"] : "",
                    "LEW_AREAS" => isset($request["LEW_AREAS"]) && !empty($request["LEW_AREAS"]) ? $request["LEW_AREAS"] : "",
                    "REW_BRACING" => isset($request["REW_BRACING"]) && !empty($request["REW_BRACING"]) ? $request["REW_BRACING"] : "",
                    "REW_1_QTY" => isset($request["REW_1_QTY"]) && !empty($request["REW_1_QTY"]) ? $request["REW_1_QTY"] : "",
                    "REW_1_FRAME" => isset($request["REW_1_FRAME"]) && !empty($request["REW_1_FRAME"]) ? $request["REW_1_FRAME"] : "",
                    "REW_2_QTY" => isset($request["REW_2_QTY"]) && !empty($request["REW_2_QTY"]) ? $request["REW_2_QTY"] : "",
                    "REW_2_FRAME" => isset($request["REW_2_FRAME"]) && !empty($request["REW_2_FRAME"]) ? $request["REW_2_FRAME"] : "",
                    "REW_AREAS" => isset($request["REW_AREAS"]) && !empty($request["REW_AREAS"]) ? $request["REW_AREAS"] : "",
                    "FSW_BRACING" => isset($request["FSW_BRACING"]) && !empty($request["FSW_BRACING"]) ? $request["FSW_BRACING"] : "",
                    "FSW_1_QTY" => isset($request["FSW_1_QTY"]) && !empty($request["FSW_1_QTY"]) ? $request["FSW_1_QTY"] : "",
                    "FSW_1_FRAME" => isset($request["FSW_1_FRAME"]) && !empty($request["FSW_1_FRAME"]) ? $request["FSW_1_FRAME"] : "",
                    "FSW_2_QTY" => isset($request["FSW_2_QTY"]) && !empty($request["FSW_2_QTY"]) ? $request["FSW_2_QTY"] : "",
                    "FSW_2_FRAME" => isset($request["FSW_2_FRAME"]) && !empty($request["FSW_2_FRAME"]) ? $request["FSW_2_FRAME"] : "",
                    "FSW_AREAS" => isset($request["FSW_AREAS"]) && !empty($request["FSW_AREAS"]) ? $request["FSW_AREAS"] : "",
                    "BSW_BRACING" => isset($request["BSW_BRACING"]) && !empty($request["BSW_BRACING"]) ? $request["BSW_BRACING"] : "",
                    "BSW_AREAS" => isset($request["BSW_AREAS"]) && !empty($request["BSW_AREAS"]) ? $request["BSW_AREAS"] : "",
                    "BSW_1_QTY" => isset($request["BSW_1_QTY"]) && !empty($request["BSW_1_QTY"]) ? $request["BSW_1_QTY"] : "",
                    "BSW_1_FRAME" => isset($request["BSW_1_FRAME"]) && !empty($request["BSW_1_FRAME"]) ? $request["BSW_1_FRAME"] : "",
                    "BSW_2_QTY" => isset($request["BSW_2_QTY"]) && !empty($request["BSW_2_QTY"]) ? $request["BSW_2_QTY"] : "",
                    "BSW_2_FRAME" => isset($request["BSW_2_FRAME"]) && !empty($request["BSW_2_FRAME"]) ? $request["BSW_2_FRAME"] : "",
                    "ROOF_COLOR" => isset($request["ROOF_COLOR"]) && !empty($request["ROOF_COLOR"]) ? $request["ROOF_COLOR"] : "",
                    "WALL_COLOR" => isset($request["WALL_COLOR"]) && !empty($request["WALL_COLOR"]) ? $request["WALL_COLOR"] : "",
                    "TRIM_COLOR" => isset($request["TRIM_COLOR"]) && !empty($request["TRIM_COLOR"]) ? $request["TRIM_COLOR"] : "",
                    "GUTTERS_DOWNS" => isset($request["GUTTERS_DOWNS"]) && !empty($request["GUTTERS_DOWNS"]) ? $request["GUTTERS_DOWNS"] : '',
                    "GUTTERS_DOWNS_COLOR" => isset($request["GUTTERS_DOWNS_COLOR"]) && !empty($request["GUTTERS_DOWNS_COLOR"]) ? $request["GUTTERS_DOWNS_COLOR"] : '',
                    "IS_LEW_OPEN" => isset($request["IS_LEW_OPEN"]) && !empty($request["IS_LEW_OPEN"]) ? 'Yes' : 'No',
                    "IS_REW_OPEN" => isset($request["IS_REW_OPEN"]) && !empty($request["IS_REW_OPEN"]) ? 'Yes' : 'No',
                    "IS_FSW_OPEN" => isset($request["IS_FSW_OPEN"]) && !empty($request["IS_FSW_OPEN"]) ? 'Yes' : 'No',
                    "IS_BSW_OPEN" => isset($request["IS_BSW_OPEN"]) && !empty($request["IS_BSW_OPEN"]) ? 'Yes' : 'No',
                    "IS_ACCESSORIES" => isset($request["IS_ACCESSORIES"]) && !empty($request["IS_ACCESSORIES"]) ? 'Yes' : 'No',
                    "SERVICE_DOOR" => isset($request["SERVICE_DOOR"]) && !empty($request["SERVICE_DOOR"]) ? $request["SERVICE_DOOR"] : '',
                    "SERVICE_DOOR_QTY" => isset($request["SERVICE_DOOR_QTY"]) && !empty($request["SERVICE_DOOR_QTY"]) ? $request["SERVICE_DOOR_QTY"] : '',
                    "SERVICE_DOOR_FRAME" => isset($request["SERVICE_DOOR_FRAME"]) && !empty($request["SERVICE_DOOR_FRAME"]) ? $request["SERVICE_DOOR_FRAME"] : '',
                    "SERVICE_DOOR_FRAME_QTY" => isset($request["SERVICE_DOOR_FRAME_QTY"]) && !empty($request["SERVICE_DOOR_FRAME_QTY"]) ? $request["SERVICE_DOOR_FRAME_QTY"] : '',
                    "WINDOW_FRAME" => isset($request["WINDOW_FRAME"]) && !empty($request["WINDOW_FRAME"]) ? $request["WINDOW_FRAME"] : '',
                    "WINDOW_FRAME_QTY" => isset($request["WINDOW_FRAME_QTY"]) && !empty($request["WINDOW_FRAME_QTY"]) ? $request["WINDOW_FRAME_QTY"] : '',
                    "OTHERS_1" => isset($request["OTHERS_1"]) && !empty($request["OTHERS_1"]) ? $request["OTHERS_1"] : '',
                    "OTHERS_1_QTY" => isset($request["OTHERS_1_QTY"]) && !empty($request["OTHERS_1_QTY"]) ? $request["OTHERS_1_QTY"] : '',
                    "OTHERS_2" => isset($request["OTHERS_2"]) && !empty($request["OTHERS_2"]) ? $request["OTHERS_2"] : '',
                    "OTHERS_2_QTY" => isset($request["OTHERS_2_QTY"]) && !empty($request["OTHERS_2_QTY"]) ? $request["OTHERS_2_QTY"] : '',
                    "OTHERS_3" => isset($request["OTHERS_3"]) && !empty($request["OTHERS_3"]) ? $request["OTHERS_3"] : '',
                    "OTHERS_3_QTY" => isset($request["OTHERS_3_QTY"]) && !empty($request["OTHERS_3_QTY"]) ? $request["OTHERS_3_QTY"] : '',
                    "IS_INSULATION" => isset($request["IS_INSULATION"]) && !empty($request["IS_INSULATION"]) ? 'Yes' : 'No',
                    "ROOF_INSULATION" => isset($request["ROOF_INSULATION"]) && !empty($request["ROOF_INSULATION"]) ? $request["ROOF_INSULATION"] : '',
                    "WALL_INSULATION" => isset($request["WALL_INSULATION"]) && !empty($request["WALL_INSULATION"]) ? $request["WALL_INSULATION"] : '',
                    "ROOF_LINER" => isset($request["ROOF_LINER"]) && !empty($request["ROOF_LINER"]) ? $request["ROOF_LINER"] : '',
                    "WALL_LINER" => isset($request["WALL_LINER"]) && !empty($request["WALL_LINER"]) ? $request["WALL_LINER"] : '',
                )
            ),
            "UF_FORM_PAYMENT" => serialize(
                array(
                    "FOUNDATION_DRAWINGS" => isset($request["FOUNDATION_DRAWINGS"]) && !empty($request["FOUNDATION_DRAWINGS"]) ? $request["FOUNDATION_DRAWINGS"] : "",
                    "FOUNDATION_DRAWINGS_SEND" => isset($request["FOUNDATION_DRAWINGS_SEND"]) && !empty($request["FOUNDATION_DRAWINGS_SEND"]) ? $request["FOUNDATION_DRAWINGS_SEND"] : "",
                    "REQUESTED_DELIVERY_MONTH" => isset($request["REQUESTED_DELIVERY_MONTH"]) && !empty($request["REQUESTED_DELIVERY_MONTH"]) ? $request["REQUESTED_DELIVERY_MONTH"] : "",
                    "PAYMENT_METHOD" => isset($request["PAYMENT_METHOD"]) && !empty($request["PAYMENT_METHOD"]) ? $request["PAYMENT_METHOD"] : "",
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
            "PROJECT" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['PROJECT'],
            "ACCOUNT_NUMBER" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['ACCOUNT_NUMBER'],
            "VENDOR_ID" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['VENDOR_ID'],
            "ORDER_STATUS" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['ORDER_STATUS'],
            "PRIMARY_PHONE" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['PRIMARY_PHONE'],
            "SECONDARY_PHONE" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['SECONDARY_PHONE'],
            "WORK" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['WORK'],
            "EMAIL" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['EMAIL'],
            // UF_FORM_BUILDING
            "MODEL_TYPE" => unserialize($formData['UF_FORM_BUILDING'])['MODEL_TYPE'],
            "BUILDING_CODE" => unserialize($formData['UF_FORM_BUILDING'])['BUILDING_CODE'],
            "RISK_CATEGORY" => unserialize($formData['UF_FORM_BUILDING'])['RISK_CATEGORY'],
            "ROOF_SNOW_LOAD" => unserialize($formData['UF_FORM_BUILDING'])['ROOF_SNOW_LOAD'],
            "COLLATERAL" => unserialize($formData['UF_FORM_BUILDING'])['COLLATERAL'],
            "GROUND_SNOW_LOAD" => unserialize($formData['UF_FORM_BUILDING'])['GROUND_SNOW_LOAD'],
            "RAIN_LOAD" => unserialize($formData['UF_FORM_BUILDING'])['RAIN_LOAD'],
            "SA_02" => unserialize($formData['UF_FORM_BUILDING'])['SA_02'],
            "SA_05" => unserialize($formData['UF_FORM_BUILDING'])['SA_05'],
            "SA_1" => unserialize($formData['UF_FORM_BUILDING'])['SA_1'],
            "SA_2" => unserialize($formData['UF_FORM_BUILDING'])['SA_2'],
            "BUILDING_USE" => unserialize($formData['UF_FORM_BUILDING'])['BUILDING_USE'],
            "BUILDING_WIDTH" => unserialize($formData['UF_FORM_BUILDING'])['BUILDING_WIDTH'],
            "BUILDING_LENGTH" => unserialize($formData['UF_FORM_BUILDING'])['BUILDING_LENGTH'],
            "FRONT_WALL_HEIGHT" => unserialize($formData['UF_FORM_BUILDING'])['FRONT_WALL_HEIGHT'],
            "REAR_WALL_HEIGHT" => unserialize($formData['UF_FORM_BUILDING'])['REAR_WALL_HEIGHT'],
            "ROOF_PITCH" => unserialize($formData['UF_FORM_BUILDING'])['ROOF_PITCH'],
            "PRIMER_COLOR" => unserialize($formData['UF_FORM_BUILDING'])['PRIMER_COLOR'],
            "LEFT_ENDWALL_FRAME" => unserialize($formData['UF_FORM_BUILDING'])['LEFT_ENDWALL_FRAME'],
            "RIGHT_ENDWALL_FRAME" => unserialize($formData['UF_FORM_BUILDING'])['RIGHT_ENDWALL_FRAME'],
            "FRAME_COLUMN" => unserialize($formData['UF_FORM_BUILDING'])['FRAME_COLUMN'],
            "FRAME_RAFTER" => unserialize($formData['UF_FORM_BUILDING'])['FRAME_RAFTER'],
            "RIGID_FRAMES" => unserialize($formData['UF_FORM_BUILDING'])['RIGID_FRAMES'],
            "BASE_CONDITIONS" => unserialize($formData['UF_FORM_BUILDING'])['BASE_CONDITIONS'],
            "LEW_BRACING" => unserialize($formData['UF_FORM_BUILDING'])['LEW_BRACING'],
            "LEW_1_QTY" => unserialize($formData['UF_FORM_BUILDING'])['LEW_1_QTY'],
            "LEW_1_FRAME" => unserialize($formData['UF_FORM_BUILDING'])['LEW_1_FRAME'],
            "LEW_2_QTY" => unserialize($formData['UF_FORM_BUILDING'])['LEW_2_QTY'],
            "LEW_2_FRAME" => unserialize($formData['UF_FORM_BUILDING'])['LEW_2_FRAME'],
            "LEW_AREAS" => unserialize($formData['UF_FORM_BUILDING'])['LEW_AREAS'],
            "REW_BRACING" => unserialize($formData['UF_FORM_BUILDING'])['REW_BRACING'],
            "REW_1_QTY" => unserialize($formData['UF_FORM_BUILDING'])['REW_1_QTY'],
            "REW_1_FRAME" => unserialize($formData['UF_FORM_BUILDING'])['REW_1_FRAME'],
            "REW_2_QTY" => unserialize($formData['UF_FORM_BUILDING'])['REW_2_QTY'],
            "REW_2_FRAME" => unserialize($formData['UF_FORM_BUILDING'])['REW_2_FRAME'],
            "REW_AREAS" => unserialize($formData['UF_FORM_BUILDING'])['REW_AREAS'],
            "FSW_BRACING" => unserialize($formData['UF_FORM_BUILDING'])['FSW_BRACING'],
            "FSW_1_QTY" => unserialize($formData['UF_FORM_BUILDING'])['FSW_1_QTY'],
            "FSW_1_FRAME" => unserialize($formData['UF_FORM_BUILDING'])['FSW_1_FRAME'],
            "FSW_2_QTY" => unserialize($formData['UF_FORM_BUILDING'])['FSW_2_QTY'],
            "FSW_2_FRAME" => unserialize($formData['UF_FORM_BUILDING'])['FSW_2_FRAME'],
            "FSW_AREAS" => unserialize($formData['UF_FORM_BUILDING'])['FSW_AREAS'],
            "BSW_BRACING" => unserialize($formData['UF_FORM_BUILDING'])['BSW_BRACING'],
            "BSW_1_QTY" => unserialize($formData['UF_FORM_BUILDING'])['BSW_1_QTY'],
            "BSW_1_FRAME" => unserialize($formData['UF_FORM_BUILDING'])['BSW_1_FRAME'],
            "BSW_2_QTY" => unserialize($formData['UF_FORM_BUILDING'])['BSW_2_QTY'],
            "BSW_2_FRAME" => unserialize($formData['UF_FORM_BUILDING'])['BSW_2_FRAME'],
            "BSW_AREAS" => unserialize($formData['UF_FORM_BUILDING'])['BSW_AREAS'],
            "ROOF_COLOR" => unserialize($formData['UF_FORM_BUILDING'])['ROOF_COLOR'],
            "WALL_COLOR" => unserialize($formData['UF_FORM_BUILDING'])['WALL_COLOR'],
            "TRIM_COLOR" => unserialize($formData['UF_FORM_BUILDING'])['TRIM_COLOR'],
            "GUTTERS_DOWNS" => unserialize($formData['UF_FORM_BUILDING'])['GUTTERS_DOWNS'],
            "GUTTERS_DOWNS_COLOR" => unserialize($formData['UF_FORM_BUILDING'])['GUTTERS_DOWNS_COLOR'],
            "IS_LEW_OPEN" => unserialize($formData['UF_FORM_BUILDING'])['IS_LEW_OPEN'],
            "IS_REW_OPEN" => unserialize($formData['UF_FORM_BUILDING'])['IS_REW_OPEN'],
            "IS_FSW_OPEN" => unserialize($formData['UF_FORM_BUILDING'])['IS_FSW_OPEN'],
            "IS_BSW_OPEN" => unserialize($formData['UF_FORM_BUILDING'])['IS_BSW_OPEN'],
            "IS_ACCESSORIES" => unserialize($formData['UF_FORM_BUILDING'])['IS_ACCESSORIES'],
            "SERVICE_DOOR" => unserialize($formData['UF_FORM_BUILDING'])['SERVICE_DOOR'],
            "SERVICE_DOOR_QTY" => unserialize($formData['UF_FORM_BUILDING'])['SERVICE_DOOR_QTY'],
            "SERVICE_DOOR_FRAME" => unserialize($formData['UF_FORM_BUILDING'])['SERVICE_DOOR_FRAME'],
            "SERVICE_DOOR_FRAME_QTY" => unserialize($formData['UF_FORM_BUILDING'])['SERVICE_DOOR_FRAME_QTY'],
            "WINDOW_FRAME" => unserialize($formData['UF_FORM_BUILDING'])['WINDOW_FRAME'],
            "WINDOW_FRAME_QTY" => unserialize($formData['UF_FORM_BUILDING'])['WINDOW_FRAME_QTY'],
            "OTHERS_1" => unserialize($formData['UF_FORM_BUILDING'])['OTHERS_1'],
            "OTHERS_1_QTY" => unserialize($formData['UF_FORM_BUILDING'])['OTHERS_1_QTY'],
            "OTHERS_2" => unserialize($formData['UF_FORM_BUILDING'])['OTHERS_2'],
            "OTHERS_2_QTY" => unserialize($formData['UF_FORM_BUILDING'])['OTHERS_2_QTY'],
            "OTHERS_3" => unserialize($formData['UF_FORM_BUILDING'])['OTHERS_3'],
            "OTHERS_3_QTY" => unserialize($formData['UF_FORM_BUILDING'])['OTHERS_3_QTY'],
            "IS_INSULATION" => unserialize($formData['UF_FORM_BUILDING'])['IS_INSULATION'],
            "ROOF_INSULATION" => unserialize($formData['UF_FORM_BUILDING'])['ROOF_INSULATION'],
            "WALL_INSULATION" => unserialize($formData['UF_FORM_BUILDING'])['WALL_INSULATION'],
            "ROOF_LINER" => unserialize($formData['UF_FORM_BUILDING'])['ROOF_LINER'],
            "WALL_LINER" => unserialize($formData['UF_FORM_BUILDING'])['WALL_LINER'],
            // UF_FORM_PAYMENT
            "FOUNDATION_DRAWINGS" => unserialize($formData['UF_FORM_PAYMENT'])['FOUNDATION_DRAWINGS'],
            "FOUNDATION_DRAWINGS_SEND" => unserialize($formData['UF_FORM_PAYMENT'])['FOUNDATION_DRAWINGS_SEND'],
            "REQUESTED_DELIVERY_MONTH" => unserialize($formData['UF_FORM_PAYMENT'])['REQUESTED_DELIVERY_MONTH'],
            "PAYMENT_METHOD" => unserialize($formData['UF_FORM_PAYMENT'])['PAYMENT_METHOD'],
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
            "Customer Name" => $HLBTData['CUSTOMER'],
            "Sales Rep" => $salesRep[0],
            "Company" => $HLBTData['COMPANY'],
            "Project Name" => $HLBTData['PROJECT'],
            "Account No" => $HLBTData['ACCOUNT_NUMBER'],
            "Order Status" => $HLBTData['ORDER_STATUS'],
            "Vendor ID" => $HLBTData['VENDOR_ID'],
            "Primary Phone" => $HLBTData['PRIMARY_PHONE'],
            "Secondary Phone" => $HLBTData['SECONDARY_PHONE'],
            "Work" => $HLBTData['WORK'],
            "Email" => $HLBTData['EMAIL'],
            // UF_FORM_BUILDING
            "Building Code" => $HLBTData['BUILDING_CODE'],
            "Category" => $HLBTData['RISK_CATEGORY'],
            "Roof Snow Load" => $HLBTData['ROOF_SNOW_LOAD'],
            "Collateral" => $HLBTData['COLLATERAL'],
            "Ground Snow Load Ss" => $HLBTData['GROUND_SNOW_LOAD'],
            "Rain Load Sr" => $HLBTData['RAIN_LOAD'],
            "Sa 1" => $HLBTData['SA_02'],
            "Sa 2" => $HLBTData['SA_05'],
            "Sa 3" => $HLBTData['SA_1'],
            "Sa 4" => $HLBTData['SA_2'],
            "Building Use" => $HLBTData['BUILDING_USE'],
            "Exterior Building Width LEW and REW FT" => $HLBTData['BUILDING_WIDTH'],
            "Building Length FSW and BSW FT" => $HLBTData['BUILDING_LENGTH'],
            "Front Wall Exterior Eave Height FT" => $HLBTData['FRONT_WALL_HEIGHT'],
            "Rear Wall Exterior Eave Height FT" => $HLBTData['REAR_WALL_HEIGHT'],
            "Roof Pitch" => $HLBTData['ROOF_PITCH'],
            "Primer Color" => $HLBTData['PRIMER_COLOR'],
            "Left Endwall Frame" => $HLBTData['LEFT_ENDWALL_FRAME'],
            "Right Endwall Frame" => $HLBTData['RIGHT_ENDWALL_FRAME'],
            "Frame Column" => $HLBTData['FRAME_COLUMN'],
            "Frame Rafter" => $HLBTData['FRAME_RAFTER'],
            "Rigid Frames" => $HLBTData['RIGID_FRAMES'],
            "Base Conditions" => $HLBTData['BASE_CONDITIONS'],
            "LEW Bracing" => $HLBTData['LEW_BRACING'],
            "Left End Wall EWB Framed Opening 1 QTY" => $HLBTData['LEW_1_QTY'],
            "LEW EWB Frame 1 W X H" => $HLBTData['LEW_1_FRAME'],
            "Left End Wall EWB Framed Opening 2 QTY" => $HLBTData['LEW_2_QTY'],
            "LEW EWB Frame 2 W X H" => $HLBTData['LEW_2_FRAME'],
            "LEW Open Wall Areas" => $HLBTData['LEW_AREAS'],
            "REW Bracing" => $HLBTData['REW_BRACING'],
            "Right End Wall EWD Frame Opening 1 QTY" => $HLBTData['REW_1_QTY'],
            "REW EWD Frame 1 W X H" => $HLBTData['REW_1_FRAME'],
            "Right End Wall EWD Frame Opening 2 QTY" => $HLBTData['REW_2_QTY'],
            "REW EWD Frame 2 W X H" => $HLBTData['REW_2_FRAME'],
            "REW Open Wall Areas" => $HLBTData['REW_AREAS'],
            "FSW Bracing" => $HLBTData['FSW_BRACING'],
            "Front Side Wall SWA Frame Opening 1 QTY" => $HLBTData['FSW_1_QTY'],
            "FSW SWA Frame 1 W X H" => $HLBTData['FSW_1_FRAME'],
            "Front Side Wall SWA Frame Opening 2 QTY" => $HLBTData['FSW_2_QTY'],
            "FSW SWA Frame 2 W X H" => $HLBTData['FSW_2_FRAME'],
            "FSW Open Wall Areas" => $HLBTData['FSW_AREAS'],
            "BSW Bracing" => $HLBTData['BSW_BRACING'],
            "Back Side Wall SWC Frame Opening 1 QTY" => $HLBTData['BSW_1_QTY'],
            "BSW SWC Frame 1 W X H" => $HLBTData['BSW_1_FRAME'],
            "Back Side Wall SWC Frame Opening 2 QTY" => $HLBTData['BSW_2_QTY'],
            "BSW SWC Frame 2 W X H" => $HLBTData['BSW_2_FRAME'],
            "BSW Open Wall Areas" => $HLBTData['BSW_AREAS'],
            "Wall Color" => $HLBTData['WALL_COLOR'],
            "Roof Color" => $HLBTData['ROOF_COLOR'],
            "Trim Color" => $HLBTData['TRIM_COLOR'],
			//"Gutter Downs" => $HLBTData['GUTTERS_DOWNS'],
            "Service Door" => $HLBTData['SERVICE_DOOR'],
            "Service Door QTY" => $HLBTData['SERVICE_DOOR_QTY'],
            "Service Door Frame" => $HLBTData['SERVICE_DOOR_FRAME'],
            "Service Door Frame QTY" => $HLBTData['SERVICE_DOOR_FRAME_QTY'],
            "Window Frame" => $HLBTData['WINDOW_FRAME'],
            "Window Frame QTY" => $HLBTData['WINDOW_FRAME_QTY'],
            "Others 1" => $HLBTData['OTHERS_1'],
            "Others 1 QTY" => $HLBTData['OTHERS_1_QTY'],
            "Others 2" => $HLBTData['OTHERS_2'],
            "Others 2 QTY" => $HLBTData['OTHERS_2_QTY'],
            "Others 3" => $HLBTData['OTHERS_3'],
            "Others 3 QTY" => $HLBTData['OTHERS_3_QTY'],
            "Gutters and Downspouts Color" => !empty($HLBTData['GUTTERS_DOWNS_COLOR']) ? $HLBTData['GUTTERS_DOWNS_COLOR'] : 'N/A',
            "Roof Insulation" => $HLBTData['ROOF_INSULATION'],
            "Wall Insulation" => $HLBTData['WALL_INSULATION'],
            "Roof Liner" => $HLBTData['ROOF_LINER'],
            "Wall Liner" => $HLBTData['WALL_LINER'],
            // UF_FORM_PAYMENT
            "Request Delivery Month" => $HLBTData['REQUESTED_DELIVERY_MONTH'],
            "Foundation Drawings" => $HLBTData['FOUNDATION_DRAWINGS'],
			//"How to send foundation drawings" => $HLBTData['FOUNDATION_DRAWINGS_SEND'],
            "Building Price" => $HLBTData['BUILDING_PRICE'],
            "Total Contract Price" => $HLBTData['SUB_TOTAL'],
            "Tax" => $HLBTData['TAX'],
            "Tax Rate" => explode("_", $HLBTData['TAX_RATE'])[1] . '%',
            "First Deposit" => $HLBTData['FIRST_DEPOSIT'],
            "First Deposit Status" => $HLBTData['FIRST_DEPOSIT_STATUS'],
            "Second Deposit" => $HLBTData['SECOND_DEPOSIT'],
            "Second Deposit Status" => $HLBTData['SECOND_DEPOSIT_STATUS'],
            "Balance Remaining" => $HLBTData['BALANCE_REMAINING'],
            "Mailing Address" => $HLBTData['MAILING_ADDRESS'],
            "Site Address" => $HLBTData['SITE_ADDRESS'],
            "Shipping Address" => $HLBTData['SHIPPING_ADDRESS'],
            // UF_FORM_ADDITIONAL
            "Notes" => $HLBTData['NOTES'],
            "Addendum" => parent::changeCharacters($HLBTData['ADDENDUM']),
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