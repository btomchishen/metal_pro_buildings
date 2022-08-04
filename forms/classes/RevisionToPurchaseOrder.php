<?php

require $_SERVER["DOCUMENT_ROOT"] . '/composer/vendor/autoload.php';
require $_SERVER["DOCUMENT_ROOT"] . '/composer/vendor/fpdm-master/fpdm.php';

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class RevisionToPurchaseOrder extends from PDFForm
 *
 * All used constants located in /home/bitrix/www/local/php_interface/include/constants.php
 */
class RevisionToPurchaseOrder extends PDFForm
{
    protected const PATH_TO_PDF_TEMPLATE = '/home/bitrix/www/forms/pdf_templates/revision_to_purchase_order.pdf';
    protected const PATH_TO_FILES = '/home/bitrix/www/forms/files/revision_to_purchase_order/';

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
//        $result['PARTS_PRICE'] = '$' . str_replace('|CAD', '', (number_format($dealData[BUILDING_PRICE], 2)));
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

        $result['SALES_REP'] = parent::getSalesRepList($HLBTData['SALES_REP'], false);
        $result['REQUESTED_DELIVERY_MONTH'] = parent::getHighLoadList(REQUESTED_DELIVERY_MONTH_HIGHLOAD, $HLBTData['REQUESTED_DELIVERY_MONTH'], 'UF_MONTH', false);
        $result['TAX_RATE'] = parent::getTaxRateList($HLBTData['TAX_RATE'], false);

        return $result;
    }

    /**
     * Calculate all prices by Building Price and Tax Rate
     *
     * @param string $totalAmount Total Amount of Revision
     * @param string $originalAmount Original Contract Amount
     * @param int $taxRate Tax Rate
     * @return array
     */
    public function calculatePrices($totalAmount, $originalAmount, $taxRate)
    {
        $totalAmount = str_replace(array('$', ','), '', $totalAmount);
        $originalAmount = str_replace(array('$', ','), '', $originalAmount);
        $buildingPrice = $totalAmount + $originalAmount;
        $result['BUILDING_PRICE'] = '$' . number_format($buildingPrice, 2);

        $taxRate = explode('_', $taxRate)[1];

        $tax = $buildingPrice * ($taxRate / 100);
        $result['TAX'] = '$' . number_format($tax, 2);

        $subTotal = $buildingPrice + $tax;
        $result['SUB_TOTAL'] = '$' . number_format($subTotal, 2);

        $firstDeposit = ($subTotal * 0.25);
        $result['FIRST_DEPOSIT'] = '$' . number_format($firstDeposit, 2);

        $secondDeposit = ($subTotal * 0.25);
        $result['SECOND_DEPOSIT'] = '$' . number_format($secondDeposit, 2);

        $balanceRemaining = ($subTotal * 0.5);
        $result['BALANCE_REMAINING'] = '$' . number_format($balanceRemaining, 2);

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
                    "REVISED_DRAWINGS" => isset($request["REVISED_DRAWINGS"]) && !empty($request["REVISED_DRAWINGS"]) ? $request["REVISED_DRAWINGS"] : "",
                    "CHANGE_1" => isset($request["CHANGE_1"]) && !empty($request["CHANGE_1"]) ? $request["CHANGE_1"] : "",
                    "DESCRIPTION_1" => isset($request["DESCRIPTION_1"]) && !empty($request["DESCRIPTION_1"]) ? $request["DESCRIPTION_1"] : "",
                    "CHANGE_2" => isset($request["CHANGE_2"]) && !empty($request["CHANGE_2"]) ? $request["CHANGE_2"] : "",
                    "DESCRIPTION_2" => isset($request["DESCRIPTION_2"]) && !empty($request["DESCRIPTION_2"]) ? $request["DESCRIPTION_2"] : "",
                    "CHANGE_3" => isset($request["CHANGE_3"]) && !empty($request["CHANGE_3"]) ? $request["CHANGE_3"] : "",
                    "DESCRIPTION_3" => isset($request["DESCRIPTION_3"]) && !empty($request["DESCRIPTION_3"]) ? $request["DESCRIPTION_3"] : "",
                    "CHANGE_4" => isset($request["CHANGE_4"]) && !empty($request["CHANGE_4"]) ? $request["CHANGE_4"] : "",
                    "DESCRIPTION_4" => isset($request["DESCRIPTION_4"]) && !empty($request["DESCRIPTION_4"]) ? $request["DESCRIPTION_4"] : "",
                    "CHANGE_5" => isset($request["CHANGE_5"]) && !empty($request["CHANGE_5"]) ? $request["CHANGE_5"] : "",
                    "DESCRIPTION_5" => isset($request["DESCRIPTION_5"]) && !empty($request["DESCRIPTION_5"]) ? $request["DESCRIPTION_5"] : "",
                    "EXPOSURE_CONDITIONS" => isset($request["EXPOSURE_CONDITIONS"]) && !empty($request["EXPOSURE_CONDITIONS"]) ? $request["EXPOSURE_CONDITIONS"] : "",
                )
            ),
            "UF_FORM_PAYMENT" => serialize(
                array(
                    "REQUESTED_DELIVERY_MONTH" => isset($request["REQUESTED_DELIVERY_MONTH"]) && !empty($request["REQUESTED_DELIVERY_MONTH"]) ? $request["REQUESTED_DELIVERY_MONTH"] : "",
                    "PAYMENT_METHOD" => isset($request["PAYMENT_METHOD"]) && !empty($request["PAYMENT_METHOD"]) ? $request["PAYMENT_METHOD"] : "",
                    "TOTAL_AMOUNT" => isset($request["TOTAL_AMOUNT"]) && !empty($request["TOTAL_AMOUNT"]) ? $request["TOTAL_AMOUNT"] : "",
                    "ORIGINAL_CONTRACT_AMOUNT" => isset($request["ORIGINAL_CONTRACT_AMOUNT"]) && !empty($request["ORIGINAL_CONTRACT_AMOUNT"]) ? $request["ORIGINAL_CONTRACT_AMOUNT"] : "",
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
            "VENDOR_ID" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['VENDOR_ID'],
            "ORDER_STATUS" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['ORDER_STATUS'],
            "PRIMARY_PHONE" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['PRIMARY_PHONE'],
            "SECONDARY_PHONE" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['SECONDARY_PHONE'],
            "WORK" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['WORK'],
            "EMAIL" => unserialize($formData['UF_FORM_DEAL_INFORMATION'])['EMAIL'],
            // UF_FORM_BUILDING
            "MODEL_TYPE" => unserialize($formData['UF_FORM_BUILDING'])['MODEL_TYPE'],
            "REVISED_DRAWINGS" => unserialize($formData['UF_FORM_BUILDING'])['REVISED_DRAWINGS'],
            "CHANGE_1" => unserialize($formData['UF_FORM_BUILDING'])['CHANGE_1'],
            "DESCRIPTION_1" => unserialize($formData['UF_FORM_BUILDING'])['DESCRIPTION_1'],
            "CHANGE_2" => unserialize($formData['UF_FORM_BUILDING'])['CHANGE_2'],
            "DESCRIPTION_2" => unserialize($formData['UF_FORM_BUILDING'])['DESCRIPTION_2'],
            "CHANGE_3" => unserialize($formData['UF_FORM_BUILDING'])['CHANGE_3'],
            "DESCRIPTION_3" => unserialize($formData['UF_FORM_BUILDING'])['DESCRIPTION_3'],
            "CHANGE_4" => unserialize($formData['UF_FORM_BUILDING'])['CHANGE_4'],
            "DESCRIPTION_4" => unserialize($formData['UF_FORM_BUILDING'])['DESCRIPTION_4'],
            "CHANGE_5" => unserialize($formData['UF_FORM_BUILDING'])['CHANGE_5'],
            "DESCRIPTION_5" => unserialize($formData['UF_FORM_BUILDING'])['DESCRIPTION_5'],
            "EXPOSURE_CONDITIONS" => unserialize($formData['UF_FORM_BUILDING'])['EXPOSURE_CONDITIONS'],
            // UF_FORM_PAYMENT
            "REQUESTED_DELIVERY_MONTH" => unserialize($formData['UF_FORM_PAYMENT'])['REQUESTED_DELIVERY_MONTH'],
            "PAYMENT_METHOD" => unserialize($formData['UF_FORM_PAYMENT'])['PAYMENT_METHOD'],
            "TOTAL_AMOUNT" => unserialize($formData['UF_FORM_PAYMENT'])['TOTAL_AMOUNT'],
            "ORIGINAL_CONTRACT_AMOUNT" => unserialize($formData['UF_FORM_PAYMENT'])['ORIGINAL_CONTRACT_AMOUNT'],
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
            "Vendor ID" => !empty($HLBTData['VENDOR_ID']) ? $HLBTData['VENDOR_ID'] : "",
            "Primary Number" => !empty($HLBTData['PRIMARY_PHONE']) ? $HLBTData['PRIMARY_PHONE'] : "",
            "Secondary Number" => !empty($HLBTData['SECONDARY_PHONE']) ? $HLBTData['SECONDARY_PHONE'] : "",
            "Work" => !empty($HLBTData['WORK']) ? $HLBTData['WORK'] : "",
            "Email" => !empty($HLBTData['EMAIL']) ? $HLBTData['EMAIL'] : "",
            "Order Status" => !empty($HLBTData['ORDER_STATUS']) ? $HLBTData['ORDER_STATUS'] : "",
            // UF_FORM_BUILDING
            "Model Type" => !empty($HLBTData['MODEL_TYPE']) ? $HLBTData['MODEL_TYPE'] : "",
            "Revised Drawing" => !empty($HLBTData['REVISED_DRAWINGS']) ? $HLBTData['REVISED_DRAWINGS'] : "",
            "Change 1" => !empty($HLBTData['CHANGE_1']) ? $HLBTData['CHANGE_1'] : "",
            "Description 1" => !empty($HLBTData['DESCRIPTION_1']) ? $HLBTData['DESCRIPTION_1'] : "",
            "Change 2" => !empty($HLBTData['CHANGE_2']) ? $HLBTData['CHANGE_2'] : "",
            "Description 2" => !empty($HLBTData['DESCRIPTION_2']) ? $HLBTData['DESCRIPTION_2'] : "",
            "Change 3" => !empty($HLBTData['CHANGE_3']) ? $HLBTData['CHANGE_3'] : "",
            "Description 3" => !empty($HLBTData['DESCRIPTION_3']) ? $HLBTData['DESCRIPTION_3'] : "",
            "Change 4" => !empty($HLBTData['CHANGE_4']) ? $HLBTData['CHANGE_4'] : "",
            "Description 4" => !empty($HLBTData['DESCRIPTION_4']) ? $HLBTData['DESCRIPTION_4'] : "",
            "Change 5" => !empty($HLBTData['CHANGE_5']) ? $HLBTData['CHANGE_5'] : "",
            "Description 5" => !empty($HLBTData['DESCRIPTION_5']) ? $HLBTData['DESCRIPTION_5'] : "",
            // UF_FORM_PAYMENT
            "Total Amount of Revision" => !empty($HLBTData['TOTAL_AMOUNT']) ? $HLBTData['TOTAL_AMOUNT'] : "",
            "Original Contract Amount" => !empty($HLBTData['ORIGINAL_CONTRACT_AMOUNT']) ? $HLBTData['ORIGINAL_CONTRACT_AMOUNT'] : "",
            "Revised Building Price" => !empty($HLBTData['BUILDING_PRICE']) ? $HLBTData['BUILDING_PRICE'] : "",
            "Revised Total Contract Price" => !empty($HLBTData['SUB_TOTAL']) ? $HLBTData['SUB_TOTAL'] : "",
            "Tax" => !empty($HLBTData['TAX']) ? $HLBTData['TAX'] : "",
            "Tax Rate" => !empty($HLBTData['TAX_RATE']) ? explode("_", $HLBTData['TAX_RATE'])[1] . '%' : "",
            "First Deposit" => !empty($HLBTData['FIRST_DEPOSIT']) ? $HLBTData['FIRST_DEPOSIT'] : "",
            "First Deposit Status" => !empty($HLBTData['FIRST_DEPOSIT_STATUS']) ? $HLBTData['FIRST_DEPOSIT_STATUS'] : "",
            "Second Deposit" => !empty($HLBTData['SECOND_DEPOSIT']) ? $HLBTData['SECOND_DEPOSIT'] : "",
            "Second Deposit Status" => !empty($HLBTData['SECOND_DEPOSIT_STATUS']) ? $HLBTData['SECOND_DEPOSIT_STATUS'] : "",
            "Remaining Balance" => !empty($HLBTData['BALANCE_REMAINING']) ? $HLBTData['BALANCE_REMAINING'] : "",
            "Mailing Address" => !empty($HLBTData['MAILING_ADDRESS']) ? $HLBTData['MAILING_ADDRESS'] : "",
            "Site Address" => !empty($HLBTData['SITE_ADDRESS']) ? $HLBTData['SITE_ADDRESS'] : "",
            "Shipping Address" => !empty($HLBTData['SHIPPING_ADDRESS']) ? $HLBTData['SHIPPING_ADDRESS'] : "",
            // UF_FORM_ADDITIONAL
            "Notes" => !empty($HLBTData['NOTES']) ? $HLBTData['NOTES'] : "",
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
