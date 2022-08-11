<?php

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class PDFForm
 */
class PDFForm
{
    /**
     * Make new Form ID (+1)
     *
     * @return int|mixed New form ID
     */
    public function getNewFormID()
    {
        $forms = CHighData::GetList(FORMS_HIGHLOAD, array('UF_DEAL_ID' => $this->dealID));

        $lastFormID = 0;
        foreach ($forms as $form) {
            $lastFormID = $form['UF_ID'];
        }

        $newFormID = $lastFormID + 1;

        return $newFormID;
    }

    /**
     * Calculate all prices by Building Price and Tax Rate
     *
     * @param string $buildingPrice Building Price
     * @param int $taxRate Tax Rate
     * @return array
     */
    public function calculatePrices($buildingPrice, $taxRate)
    {
        $buildingPrice = str_replace(array('$', ','), '', $buildingPrice);
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

    public function calculatePricesBySubTotal($taxRate, $subTotal)
    {
        $subTotal = str_replace(array('$', ','), '', $subTotal);
        $taxRate = explode('_', $taxRate)[1];

        $buildingPrice = $subTotal * 100 / ($taxRate + 100);
        $result['BUILDING_PRICE'] = '$' . number_format($buildingPrice, 2);

        $tax = $subTotal - $buildingPrice;
        $result['TAX'] = '$' . number_format($tax, 2);

        $firstDeposit = ($subTotal * 0.25);
        $result['FIRST_DEPOSIT'] = '$' . number_format($firstDeposit, 2);

        $secondDeposit = ($subTotal * 0.25);
        $result['SECOND_DEPOSIT'] = '$' . number_format($secondDeposit, 2);

        $balanceRemaining = ($subTotal * 0.5);
        $result['BALANCE_REMAINING'] = '$' . number_format($balanceRemaining, 2);

        return $result;
    }

    public function getFilePath()
    {
        $form = array_shift(CHighData::GetList(FORMS_HIGHLOAD, array('UF_DEAL_ID' => $this->dealID, 'UF_ID' => $this->formID)));

        $file = CFile::GetByID($form['UF_DOCUMENT_PDF'])->Fetch();
        $pathToFile = 'https://dev.metalpro.site/upload/' . $file['SUBDIR'] . '/' . $file['FILE_NAME'];

        return $pathToFile;
    }

    /**
     * Delete Form
     */
    public function deleteForm()
    {
        $formData = array_shift(CHighData::GetList(FORMS_HIGHLOAD, array('UF_DEAL_ID' => $this->dealID, 'UF_ID' => $this->formID)));

        return CHighData::DeleteRecord(FORMS_HIGHLOAD, $formData['ID']);
    }

    /**
     * Get Deal Fields by ID
     *
     * @return array Deal Fields
     */
    protected function getDealData()
    {
        return CCrmDeal::GetList(array(), array('ID' => $this->dealID), array())->Fetch();
    }

    /**
     * Get Company Name by Deal to filling PDF field
     *
     * @return string Company Name
     */
    protected function getCompanyFields()
    {
        $dealData = $this->getDealData();
        $companyInfo = CCrmCompany::GetByID($dealData['COMPANY_ID']);

        return $companyInfo;
    }

    /**
     * Get Email by Contact to filling PDF field
     *
     * @param string $typeID EMAIL or PHONE type
     * @return string Client email
     */
    protected function getContactFMFields($typeID)
    {
        $dealData = $this->getDealData();
        $contact = CCrmFieldMulti::GetListEx(array(), array('ENTITY_ID' => 'CONTACT', 'TYPE_ID' => $typeID, 'ELEMENT_ID' => $dealData['CONTACT_ID']));

        $fmField = $contact->Fetch()['VALUE'];

        return $fmField;
    }

    /**
     * Formatting phone number to format (XXX) XXX-XXXX
     *
     * @param int $phoneNumber Phone Number
     * @return string Formatted phone number
     */
    protected function formatPhone($phoneNumber)
    {
        return preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '($1) $2-$3', $phoneNumber);
    }

    /**
     * @param string $text Input text with
     * @return string|string[] Formatted text
     */
    protected function changeCharacters($text)
    {
        return str_replace('’', '\'', $text);
    }

    /**
     * Create file name for saving pdf file
     *
     * @return string File name
     */
    protected function createFileName()
    {
        $dealData = $this->getDealData($this->dealID);

        return $dealData['TITLE'] . '-' . $this->formType . '-' . $this->formID . '.pdf';
    }

    /**
     * Attach file to record in HLBT
     *
     * @param string $filePath File name with folder name
     */
    protected function addFileToRecord($filePath)
    {
        $formData = array_shift(CHighData::GetList(FORMS_HIGHLOAD, array('UF_DEAL_ID' => $this->dealID, 'UF_ID' => $this->formID)));

        $file = CFile::MakeFileArray($filePath);

        CHighData::UpdateRecord(FORMS_HIGHLOAD, $formData['ID'], array("UF_DOCUMENT_PDF" => $file));
        unlink($filePath);
    }

    /**
     * Create PDF file and fill fields
     *
     * @param array $fields Fields to filling form
     */
    protected function createPDF($fields)
    {
        $pdf = new FPDM($this->pathToTemplate);

        $pdf->useCheckboxParser = true;
        $pdf->Load($fields, false); // second parameter: false if field values are in ISO-8859-1, true if UTF-8
        $pdf->Merge();
        $pdf->Output('F', $this->pathToFilesFolder . $this->createFileName());

        $this->addFileToRecord($this->pathToFilesFolder . $this->createFileName());
    }

    /**
     * Send email with attached file to Client email
     *
     * @return boolean Is Mail sent
     */
    protected function sendMail()
    {
        $responsibleID = $this->getDealData()['ASSIGNED_BY_ID'];
        $responsible = \CUser::GetByID($responsibleID)->Fetch();

        $mail = new PHPMailer;

        $mail->setFrom('bitrix@metalprobuildings.com', 'Admin MetalPro');
        if (!empty($responsible))
            $mail->addAddress($responsible['EMAIL'], $responsible['NAME'] . $responsible['LAST_NAME']);
        $mail->addAddress('orders@metalprobuildings.com', 'Orders MetalPro');
        $mail->Subject = 'PO created';
        $mail->msgHTML($this->createFileName());
        $mail->addAttachment($this->pathToFilesFolder . $this->createFileName());

        return $mail->send();
    }

    /**
     * Send notification to responsible person and current user about PO creating
     */
    protected function sendNotification()
    {
        global $USER;
        $currentUserID = $USER->GetID();

        $responsibleID = $this->getDealData()['ASSIGNED_BY_ID'];

        $arFields = array(
            "MESSAGE_TYPE" => "S",
            "TO_USER_ID" => array($currentUserID, $responsibleID),
            "FROM_USER_ID" => 0,
            "MESSAGE" => "PO Created for <a href='https://dev.metalpro.site/crm/deal/details/" . $this->dealID . "/'>Deal #" . $this->dealID . "</a>",
            "AUTHOR_ID" => 0,

            "NOTIFY_TYPE" => 1,
            "NOTIFY_BUTTONS" =>
                array(
                    array('TITLE' => 'OK', 'VALUE' => 'Y', 'TYPE' => 'accept'),
                ),
            "NOTIFY_MODULE" => "main",
        );
        CModule::IncludeModule('im');

        CIMMessenger::Add($arFields);
    }

    /**
     * Get all employees ID from department
     *
     * @param $departmentID
     * @return array
     */
    protected function getDepartmentEmployeesID($departmentID)
    {
        $dbRes = \Bitrix\Intranet\Util::GetDepartmentEmployees([
            'DEPARTMENTS' => $departmentID,
            'RECURSIVE' => 'Y',
            'ACTIVE' => 'Y',
            'SELECT' => [
                'ID'
            ]
        ]);

        $IDs = array();
        while ($arRes = $dbRes->Fetch()) {
            $IDs[] = $arRes['ID'];
        }

        return $IDs;
    }

    /**
     * Get Chat ID from HLBT
     *
     * @return int|mixed Chat ID
     */
    protected function getChatID()
    {
        $formData = array_shift(CHighData::GetList(FORMS_HIGHLOAD, array('UF_DEAL_ID' => $this->dealID)));

        if (!empty($formData['UF_CHAT_ID']))
            return $formData['UF_CHAT_ID'];
        else
            return 0;
    }

    /**
     * Add Chat ID to HLBT
     *
     * @param int $chatID Chat ID
     */
    protected function addChatIDToHLBT($chatID)
    {
        $formData = array_shift(CHighData::GetList(FORMS_HIGHLOAD, array('UF_DEAL_ID' => $this->dealID, 'UF_ID' => $this->formID)));

        CHighData::UpdateRecord(FORMS_HIGHLOAD, $formData['ID'], array("UF_CHAT_ID" => $chatID));
    }

    /**
     * Get folder name with files by FormType
     *
     * @return string Folder Name
     */
    protected function getFolderName()
    {
        switch ($this->formType) {
            case 'QuonsetForm':
                return 'quonset';
                break;
            case 'QuonsetPartsOrder':
                return 'quonset_parts_order';
                break;
            case 'RevisionToPurchaseOrder':
                return 'revision_to_purchase_order';
                break;
            case 'StraightWallForm':
                return 'straight_wall';
                break;
        }
    }

    /**
     * Create chat for Deal with responsible and Pre-Eng department employees
     */
    protected function createChatForDeal()
    {
        CModule::IncludeModule('im');

        $employeesID = $this->getDepartmentEmployeesID(27);
        $dealData = $this->getDealData();
        $responsibleID = array($dealData['ASSIGNED_BY_ID']);

        $users = array_merge($employeesID, $responsibleID);

        $chatID = $this->getChatID();

        $chat = new \CIMChat;

        if ($chatID == 0) {
            $chatID = $chat->Add(array(
                'TITLE' => 'Deal: ' . $dealData['TITLE'],
                'DESCRIPTION' => 'Deal #' . $this->dealID,
                'COLOR' => 'RED',
                'TYPE' => IM_MESSAGE_OPEN,
                'AUTHOR_ID' => '0',
                'USERS' => $users,
                'ENTITY_TYPE' => 'CRM',
                'ENTITY_ID' => 'DEAL|' . $this->dealID,
            ));
        }

        $chat->AddMessage(array(
            'FROM_USER_ID' => 1,
            'TO_CHAT_ID' => $chatID,
            'MESSAGE' => '[URL=https://dev.metalpro.site/forms/files/' . $this->getFolderName() . '/' . $this->createFileName() . ']Order In (Click to see file)[/URL]',
        ));

        $this->addChatIDToHLBT($chatID);
    }

    /**
     * Get list with options from Custom Fields
     *
     * @param int $fieldID Custom Field ID
     * @param int $dealFieldID ID of element in list
     * @param boolean $isNewForm New form or existed
     * @return array Array of Options for filling list
     */
    protected function getCustomFieldData($fieldID, $dealFieldID, $isNewForm)
    {
        $field = new \CUserFieldEnum;
        $dbRes = $field->GetList(array(), array("USER_FIELD_ID" => $fieldID));

        $result[] = '<option value=""></option>';

        while ($arRes = $dbRes->Fetch()) {
            if ($isNewForm == true) {
                if ($dealFieldID == $arRes['ID'])
                    $result[] = '<option selected value="' . $arRes['VALUE'] . '">' . $arRes['VALUE'] . '</option>';
                else
                    $result[] = '<option value="' . $arRes['VALUE'] . '">' . $arRes['VALUE'] . '</option>';
            } else {
                if ($dealFieldID == $arRes['VALUE'])
                    $result[] = '<option selected value="' . $arRes['VALUE'] . '">' . $arRes['VALUE'] . '</option>';
                else
                    $result[] = '<option value="' . $arRes['VALUE'] . '">' . $arRes['VALUE'] . '</option>';
            }
        }

        return $result;
    }

    /**
     * Get options list of employees and select responsible person by ID
     *
     * @param int $responsibleID Responsible ID
     * @param bollean $isNewForm Is new or existed form
     * @return array Options list
     */
    protected function getSalesRepList($responsibleID, $isNewForm)
    {
        $dbRes = \Bitrix\Intranet\Util::GetDepartmentEmployees([
            'DEPARTMENTS' => 3,
            'RECURSIVE' => 'Y',
            'ACTIVE' => 'Y',
            'SELECT' => [
                'ID', 'NAME', 'LAST_NAME'
            ]
        ]);

        $result[] = '<option value=""></option>';

        while ($arRes = $dbRes->Fetch()) {
            if ($isNewForm == true) {
                if ($responsibleID == $arRes['ID']) {
                    $result[] = '<option selected value="' . $arRes['NAME'] . ' ' . $arRes['LAST_NAME'] . '_' . $arRes['ID'] . '">' . $arRes['NAME'] . ' ' . $arRes['LAST_NAME'] . '</option>';
                } else
                    $result[] = '<option value="' . $arRes['NAME'] . ' ' . $arRes['LAST_NAME'] . '_' . $arRes['ID'] . '">' . $arRes['NAME'] . ' ' . $arRes['LAST_NAME'] . '</option>';
            } else {
                $responsibleID1 = self::divideSalesRepInfo($responsibleID);
                if ($responsibleID1[0] == $arRes['NAME'] . ' ' . $arRes['LAST_NAME'])
                    $result[] = '<option selected value="' . $arRes['NAME'] . ' ' . $arRes['LAST_NAME'] . '_' . $arRes['ID'] . '">' . $arRes['NAME'] . ' ' . $arRes['LAST_NAME'] . '</option>';
                else
                    $result[] = '<option value="' . $arRes['NAME'] . ' ' . $arRes['LAST_NAME'] . '_' . $arRes['ID'] . '">' . $arRes['NAME'] . ' ' . $arRes['LAST_NAME'] . '</option>';
            }

        }

        return $result;
    }

    /**
     * Get responsible name and ID
     *
     * @param string $responsible like: JOHN_WATSON_83
     * @return array [JOHN_WATSON, 83]
     */
    protected function divideSalesRepInfo($responsible)
    {
        $resArray = explode('_', $responsible);

        return [$resArray[0], $resArray[1]];
    }

    /**
     * Get options list from HLBT with ID = $highLoadID
     *
     * @param string $highLoadID HLBT ID
     * @param int $currentID ID of actual selected data from deal or HLBT
     * @param string $ufField Field name in HLBT
     * @param bollean $isNewForm Is new or existed form
     * @return array Options list
     */
    protected function getHighLoadList($highLoadID, $currentID, $ufField, $isNewForm)
    {
        $dbRes = CHighData::GetList($highLoadID, array());

        foreach ($dbRes as $arRes) {
            if ($isNewForm == true) {
                if ($currentID == $arRes['ID'])
                    $result[] = '<option selected value="' . $arRes[$ufField] . '">' . $arRes[$ufField] . '</option>';
                else
                    $result[] = '<option value="' . $arRes[$ufField] . '">' . $arRes[$ufField] . '</option>';
            } else {
                if ($currentID == $arRes[$ufField])
                    $result[] = '<option selected value="' . $arRes[$ufField] . '">' . $arRes[$ufField] . '</option>';
                else
                    $result[] = '<option value="' . $arRes[$ufField] . '">' . $arRes[$ufField] . '</option>';
            }
        }

        return $result;
    }

    /**
     * Get options list of Tax Rates from HLBT with ID = TAX_RATE_HIGHLOAD
     *
     * @param int $taxRate Tax Rate
     * @param bollean $isNewForm Is new or existed form
     * @return array Options list
     */
    protected function getTaxRateList($taxRate, $isNewForm)
    {
        $dealData = $this->getDealData();

        $leadId = $dealData['LEAD_ID'];
        $lead = CCrmLead::GetByID($leadId, false);
        $region = $lead['ADDRESS_REGION'];

        $dbRes = CHighData::GetList(TAX_RATE_HIGHLOAD, array());

        foreach ($dbRes as $arRes) {
            if ($isNewForm == true) {
                if (strtoupper($region) == $arRes['UF_PROVINCE'])
                    $result[] = '<option selected value="' . $arRes['UF_PROVINCE'] . '_' . $arRes['UF_TAX_RATE'] . '">' . $arRes['UF_PROVINCE'] . '</option>';
                else
                    $result[] = '<option value="' . $arRes['UF_PROVINCE'] . '_' . $arRes['UF_TAX_RATE'] . '">' . $arRes['UF_PROVINCE'] . '</option>';
            } else {
                if ($taxRate == $arRes['UF_PROVINCE'] . '_' . $arRes['UF_TAX_RATE'])
                    $result[] = '<option selected value="' . $arRes['UF_PROVINCE'] . '_' . $arRes['UF_TAX_RATE'] . '">' . $arRes['UF_PROVINCE'] . '</option>';
                else
                    $result[] = '<option value="' . $arRes['UF_PROVINCE'] . '_' . $arRes['UF_TAX_RATE'] . '">' . $arRes['UF_PROVINCE'] . '</option>';
            }
        }

        return $result;
    }
}