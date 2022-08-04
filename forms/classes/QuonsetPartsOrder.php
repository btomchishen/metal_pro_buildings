<?php

require $_SERVER["DOCUMENT_ROOT"] . '/composer/vendor/autoload.php';
require $_SERVER["DOCUMENT_ROOT"] . '/composer/vendor/fpdm-master/fpdm.php';

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class QuonsetPartsOrder extends from PDFForm
 *
 * All used constants located in /home/bitrix/www/local/php_interface/include/constants.php
 */
class QuonsetPartsOrder extends PDFForm
{
    protected const PATH_TO_PDF_TEMPLATE = '/home/bitrix/www/forms/pdf_templates/quonset_parts_order.pdf';
    protected const PATH_TO_FILES = '/home/bitrix/www/forms/files/quonset_parts_order/';

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
        $result['BUILDING_PRICE'] = '$' . number_format($dealData['OPPORTUNITY'], 2);
//        $result['PARTS_PRICE'] = '$' . str_replace('|CAD', '', (number_format($dealData[BUILDING_PRICE], 2)));

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

        $result['PARTS_1'] = parent::getHighLoadList(PARTS_HIGHLOAD, 'none', 'UF_VALUE', true);
        $result['PARTS_2'] = parent::getHighLoadList(PARTS_HIGHLOAD, 'none', 'UF_VALUE', true);
        $result['PARTS_3'] = parent::getHighLoadList(PARTS_HIGHLOAD, 'none', 'UF_VALUE', true);
        $result['PARTS_4'] = parent::getHighLoadList(PARTS_HIGHLOAD, 'none', 'UF_VALUE', true);
        $result['PARTS_5'] = parent::getHighLoadList(PARTS_HIGHLOAD, 'none', 'UF_VALUE', true);
        $result['PARTS_6'] = parent::getHighLoadList(PARTS_HIGHLOAD, 'none', 'UF_VALUE', true);

        $result['SALES_REP'] = parent::getSalesRepList($dealData['ASSIGNED_BY_ID'], true);
        $result['REQUESTED_DELIVERY_MONTH'] = parent::getHighLoadList(REQUESTED_DELIVERY_MONTH_HIGHLOAD, $dealData['REQUESTED_DELIVERY_MONTH'], 'UF_MONTH', true);
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

        $result['PARTS_1'] = parent::getHighLoadList(PARTS_HIGHLOAD, $HLBTData['PARTS_1'], 'UF_VALUE', false);
        $result['PARTS_2'] = parent::getHighLoadList(PARTS_HIGHLOAD, $HLBTData['PARTS_2'], 'UF_VALUE', false);
        $result['PARTS_3'] = parent::getHighLoadList(PARTS_HIGHLOAD, $HLBTData['PARTS_3'], 'UF_VALUE', false);
        $result['PARTS_4'] = parent::getHighLoadList(PARTS_HIGHLOAD, $HLBTData['PARTS_4'], 'UF_VALUE', false);
        $result['PARTS_5'] = parent::getHighLoadList(PARTS_HIGHLOAD, $HLBTData['PARTS_5'], 'UF_VALUE', false);
        $result['PARTS_6'] = parent::getHighLoadList(PARTS_HIGHLOAD, $HLBTData['PARTS_6'], 'UF_VALUE', false);

        $result['SALES_REP'] = parent::getSalesRepList($HLBTData['SALES_REP'], false);
        $result['REQUESTED_DELIVERY_MONTH'] = parent::getHighLoadList(REQUESTED_DELIVERY_MONTH_HIGHLOAD, $HLBTData['REQUESTED_DELIVERY_MONTH'], 'UF_MONTH', false);
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
                    "SALES_REP_EMAIL" => isset($request["SALES_REP_EMAIL"]) && !empty($request["SALES_REP_EMAIL"]) ? $request["SALES_REP_EMAIL"] : "",
                    "CUSTOMER" => isset($request["CUSTOMER"]) && !empty($request["CUSTOMER"]) ? $request["CUSTOMER"] : "",
                    "COMPANY" => isset($request["COMPANY"]) && !empty($request["COMPANY"]) ? $request["COMPANY"] : "",
                    "ACCOUNT_NUMBER" => isset($request["ACCOUNT_NUMBER"]) && !empty($request["ACCOUNT_NUMBER"]) ? $request["ACCOUNT_NUMBER"] : "",
                    "PIONEER_ID" => isset($request["PIONEER_ID"]) && !empty($request["PIONEER_ID"]) ? $request["PIONEER_ID"] : "",
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
                    "PARTS_1" => isset($request["PARTS_1"]) && !empty($request["PARTS_1"]) ? $request["PARTS_1"] : "",
                    "PARTS_QTY_1" => isset($request["PARTS_QTY_1"]) && !empty($request["PARTS_QTY_1"]) ? $request["PARTS_QTY_1"] : "",
                    "PARTS_2" => isset($request["PARTS_2"]) && !empty($request["PARTS_2"]) ? $request["PARTS_2"] : "",
                    "PARTS_QTY_2" => isset($request["PARTS_QTY_2"]) && !empty($request["PARTS_QTY_2"]) ? $request["PARTS_QTY_2"] : "",
                    "PARTS_3" => isset($request["PARTS_3"]) && !empty($request["PARTS_3"]) ? $request["PARTS_3"] : "",
                    "PARTS_QTY_3" => isset($request["PARTS_QTY_3"]) && !empty($request["PARTS_QTY_3"]) ? $request["PARTS_QTY_3"] : "",
                    "PARTS_4" => isset($request["PARTS_4"]) && !empty($request["PARTS_4"]) ? $request["PARTS_4"] : "",
                    "PARTS_QTY_4" => isset($request["PARTS_QTY_4"]) && !empty($request["PARTS_QTY_4"]) ? $request["PARTS_QTY_4"] : "",
                    "PARTS_5" => isset($request["PARTS_5"]) && !empty($request["PARTS_5"]) ? $request["PARTS_5"] : "",
                    "PARTS_QTY_5" => isset($request["PARTS_QTY_5"]) && !empty($request["PARTS_QTY_5"]) ? $request["PARTS_QTY_5"] : "",
                    "PARTS_6" => isset($request["PARTS_6"]) && !empty($request["PARTS_6"]) ? $request["PARTS_6"] : "",
                    "PARTS_QTY_6" => isset($request["PARTS_QTY_6"]) && !empty($request["PARTS_QTY_6"]) ? $request["PARTS_QTY_6"] : "",
                    "REVISED_DRAWINGS" => isset($request["REVISED_DRAWINGS"]) && !empty($request["REVISED_DRAWINGS"]) ? $request["REVISED_DRAWINGS"] : "",
                    "EXPOSURE_CONDITIONS" => isset($request["EXPOSURE_CONDITIONS"]) && !empty($request["EXPOSURE_CONDITIONS"]) ? $request["EXPOSURE_CONDITIONS"] : "",
                    "IS_ANCHOR_OR_INSULATION" => isset($request["IS_ANCHOR_OR_INSULATION"]) && !empty($request["IS_ANCHOR_OR_INSULATION"]) ? $request["IS_ANCHOR_OR_INSULATION"] : "",
                )
            ),
            "UF_FORM_PAYMENT" => serialize(
                array(
                    "REQUESTED_DELIVERY_MONTH" => isset($request["REQUESTED_DELIVERY_MONTH"]) && !empty($request["REQUESTED_DELIVERY_MONTH"]) ? $request["REQUESTED_DELIVERY_MONTH"] : "",
                    "PAYMENT_METHOD" => isset($request["PAYMENT_METHOD"]) && !empty($request["PAYMENT_METHOD"]) ? $request["PAYMENT_METHOD"] : "",
                    "IS_PICK_UP" => isset($request["IS_PICK_UP"]) && !empty($request["IS_PICK_UP"]) ? 'Yes' : 'No',
                    "PICK_UP" => isset($request["PICK_UP"]) && !empty($request["IS_PICK_UP"]) ? $request["PICK_UP"] : '',
                    "BUILDING_PRICE" => isset($request["BUILDING_PRICE"]) && !empty($request["BUILDING_PRICE"]) ? $request["BUILDING_PRICE"] : "",
                    "TAX_RATE" => isset($request["TAX_RATE"]) && !empty($request["TAX_RATE"]) ? $request["TAX_RATE"] : "",
                    "TAX" => isset($request["TAX"]) && !empty($request["TAX"]) ? $request["TAX"] : "",
                    "SUB_TOTAL" => isset($request["SUB_TOTAL"]) && !empty($request["SUB_TOTAL"]) ? $request["SUB_TOTAL"] : "",
                    "SUB_TOTAL_STATUS" => isset($request["SUB_TOTAL_STATUS"]) && !empty($request["SUB_TOTAL_STATUS"]) ? $request["SUB_TOTAL_STATUS"] : "",
                    "MAILING_ADDRESS" => isset($request["MAILING_ADDRESS"]) && !empty($request["MAILING_ADDRESS"]) ? $request["MAILING_ADDRESS"] : "",
                    "SITE_ADDRESS" => isset($request["SITE_ADDRESS"]) && !empty($request["SITE_ADDRESS"]) ? $request["SITE_ADDRESS"] : "",
                    "SHIPPING_ADDRESS" => isset($request["SHIPPING_ADDRESS"]) && !empty($request["SHIPPING_ADDRESS"]) ? $request["SHIPPING_ADDRESS"] : "",
                )
            ),
            "UF_FORM_ADDITIONAL" => serialize(
                array(
                    "ADDENDUM_1" => isset($request["ADDENDUM_1"]) && !empty($request["ADDENDUM_1"]) ? $request["ADDENDUM_1"] : "",
                    "ADDENDUM" => isset($request["ADDENDUM"]) && !empty($request["ADDENDUM"]) ? $request["ADDENDUM"] : "",
                    "NOTES" => isset($request["NOTES"]) && !empty($request["NOTES"]) ? $request["NOTES"] : "",
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
            "SALES_REP_EMAIL" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['SALES_REP_EMAIL'],
            "CUSTOMER" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['CUSTOMER'],
            "COMPANY" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['COMPANY'],
            "ACCOUNT_NUMBER" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['ACCOUNT_NUMBER'],
            "PIONEER_ID" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['PIONEER_ID'],
            "ORDER_STATUS" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['ORDER_STATUS'],
            "PRIMARY_PHONE" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['PRIMARY_PHONE'],
            "SECONDARY_PHONE" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['SECONDARY_PHONE'],
            "WORK" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['WORK'],
            "EMAIL" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['EMAIL'],
            // UF_FORM_BUILDING
            "BUILDING_USE" => unserialize($formData['UF_FORM_BUILDING'])['BUILDING_USE'],
            "PARTS_1" => unserialize($formData['UF_FORM_BUILDING'])['PARTS_1'],
            "PARTS_QTY_1" => unserialize($formData['UF_FORM_BUILDING'])['PARTS_QTY_1'],
            "PARTS_2" => unserialize($formData['UF_FORM_BUILDING'])['PARTS_2'],
            "PARTS_QTY_2" => unserialize($formData['UF_FORM_BUILDING'])['PARTS_QTY_2'],
            "PARTS_3" => unserialize($formData['UF_FORM_BUILDING'])['PARTS_3'],
            "PARTS_QTY_3" => unserialize($formData['UF_FORM_BUILDING'])['PARTS_QTY_3'],
            "PARTS_4" => unserialize($formData['UF_FORM_BUILDING'])['PARTS_4'],
            "PARTS_QTY_4" => unserialize($formData['UF_FORM_BUILDING'])['PARTS_QTY_4'],
            "PARTS_5" => unserialize($formData['UF_FORM_BUILDING'])['PARTS_5'],
            "PARTS_QTY_5" => unserialize($formData['UF_FORM_BUILDING'])['PARTS_QTY_5'],
            "PARTS_6" => unserialize($formData['UF_FORM_BUILDING'])['PARTS_6'],
            "PARTS_QTY_6" => unserialize($formData['UF_FORM_BUILDING'])['PARTS_QTY_6'],
            "REVISED_DRAWINGS" => unserialize($formData['UF_FORM_BUILDING'])['REVISED_DRAWINGS'],
            "EXPOSURE_CONDITIONS" => unserialize($formData['UF_FORM_BUILDING'])['EXPOSURE_CONDITIONS'],
            "IS_ANCHOR_OR_INSULATION" => unserialize($formData['UF_FORM_BUILDING'])['IS_ANCHOR_OR_INSULATION'],
            // UF_FORM_PAYMENT
            "REQUESTED_DELIVERY_MONTH" => unserialize($formData['UF_FORM_PAYMENT'])['REQUESTED_DELIVERY_MONTH'],
            "PAYMENT_METHOD" => unserialize($formData['UF_FORM_PAYMENT'])['PAYMENT_METHOD'],
            "IS_PICK_UP" => unserialize($formData['UF_FORM_PAYMENT'])['IS_PICK_UP'],
            "PICK_UP" => unserialize($formData['UF_FORM_PAYMENT'])['PICK_UP'],
            "BUILDING_PRICE" => unserialize($formData['UF_FORM_PAYMENT'])['BUILDING_PRICE'],
            "TAX_RATE" => unserialize($formData['UF_FORM_PAYMENT'])['TAX_RATE'],
            "TAX" => unserialize($formData['UF_FORM_PAYMENT'])['TAX'],
            "SUB_TOTAL" => unserialize($formData['UF_FORM_PAYMENT'])['SUB_TOTAL'],
            "SUB_TOTAL_STATUS" => unserialize($formData['UF_FORM_PAYMENT'])['SUB_TOTAL_STATUS'],
            "MAILING_ADDRESS" => unserialize($formData['UF_FORM_PAYMENT'])['MAILING_ADDRESS'],
            "SITE_ADDRESS" => unserialize($formData['UF_FORM_PAYMENT'])['SITE_ADDRESS'],
            "SHIPPING_ADDRESS" => unserialize($formData['UF_FORM_PAYMENT'])['SHIPPING_ADDRESS'],
            // UF_FORM_ADDITIONAL
            "ADDENDUM" => unserialize($formData['UF_FORM_ADDITIONAL'])['ADDENDUM'],
            "ADDENDUM_1" => unserialize($formData['UF_FORM_ADDITIONAL'])['ADDENDUM_1'],
            "NOTES" => unserialize($formData['UF_FORM_ADDITIONAL'])['NOTES'],
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
            "Customer" => !empty($HLBTData['CUSTOMER']) ? $HLBTData['CUSTOMER'] : "",
            "Sales Rep Name" => $salesRep[0],
            "Company" => !empty($HLBTData['COMPANY']) ? $HLBTData['COMPANY'] : "",
            "Account No" => !empty($HLBTData['ACCOUNT_NUMBER']) ? $HLBTData['ACCOUNT_NUMBER'] : "",
            "Old Vendor ID" => !empty($HLBTData['PIONEER_ID']) ? $HLBTData['PIONEER_ID'] : "",
            "Order Status" => !empty($HLBTData['ORDER_STATUS']) ? $HLBTData['ORDER_STATUS'] : "",
            "Primary Number" => !empty($HLBTData['PRIMARY_PHONE']) ? $HLBTData['PRIMARY_PHONE'] : "",
            "Secondary Number" => !empty($HLBTData['SECONDARY_PHONE']) ? $HLBTData['SECONDARY_PHONE'] : "",
            "Work" => !empty($HLBTData['WORK']) ? $HLBTData['WORK'] : "",
            "Email" => !empty($HLBTData['EMAIL']) ? $HLBTData['EMAIL'] : "",
            // UF_FORM_BUILDING
			//"Building Use" => !empty($HLBTData['BUILDING_USE']) ? $HLBTData['BUILDING_USE'] : "",
            "Revised Drawings" => !empty($HLBTData['REVISED_DRAWINGS']) ? $HLBTData['REVISED_DRAWINGS'] : "",
			//"Anchor Wedges, Pins & Caps" => !empty($HLBTData['IS_ANCHOR_OR_INSULATION']) ? $HLBTData['IS_ANCHOR_OR_INSULATION'] : "",
            "Parts 1" => !empty($HLBTData['PARTS_1']) ? $HLBTData['PARTS_1'] : "",
            "Parts 1 QTY" => !empty($HLBTData['PARTS_QTY_1']) ? $HLBTData['PARTS_QTY_1'] : "",
            "Parts 2" => !empty($HLBTData['PARTS_2']) ? $HLBTData['PARTS_2'] : "",
            "Parts 2 QTY" => !empty($HLBTData['PARTS_QTY_2']) ? $HLBTData['PARTS_QTY_2'] : "",
            "Parts 3" => !empty($HLBTData['PARTS_3']) ? $HLBTData['PARTS_3'] : "",
            "Parts 3 QTY" => !empty($HLBTData['PARTS_QTY_3']) ? $HLBTData['PARTS_QTY_3'] : "",
            "Parts 4" => !empty($HLBTData['PARTS_4']) ? $HLBTData['PARTS_4'] : "",
            "Parts 4 QTY" => !empty($HLBTData['PARTS_QTY_4']) ? $HLBTData['PARTS_QTY_4'] : "",
            "Parts 5" => !empty($HLBTData['PARTS_5']) ? $HLBTData['PARTS_5'] : "",
            "Parts 5 QTY" => !empty($HLBTData['PARTS_QTY_5']) ? $HLBTData['PARTS_QTY_5'] : "",
            "Parts 6" => !empty($HLBTData['PARTS_6']) ? $HLBTData['PARTS_6'] : "",
            "Parts 6 QTY" => !empty($HLBTData['PARTS_QTY_6']) ? $HLBTData['PARTS_QTY_6'] : "",
            // UF_FORM_PAYMENT
            "Requested Delivery Month" => !empty($HLBTData['REQUESTED_DELIVERY_MONTH']) ? $HLBTData['REQUESTED_DELIVERY_MONTH'] : "",
            "Parts Price" => !empty($HLBTData['BUILDING_PRICE']) ? $HLBTData['BUILDING_PRICE'] : "",
            "Sub Total" => !empty($HLBTData['SUB_TOTAL']) ? $HLBTData['SUB_TOTAL'] : "",
            "Sub Total Status" => !empty($HLBTData['SUB_TOTAL_STATUS']) ? $HLBTData['SUB_TOTAL_STATUS'] : "",
            "Pick Up" => !empty($HLBTData['PICK_UP']) ? $HLBTData['PICK_UP'] : "",
            "Tax" => !empty($HLBTData['TAX']) ? $HLBTData['TAX'] : "",
            "Tax Rate" => !empty($HLBTData['TAX_RATE']) ? explode("_", $HLBTData['TAX_RATE'])[1] . '%' : "",
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
