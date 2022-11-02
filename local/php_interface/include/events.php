<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Type\DateTime;

// Avivi #49545 CRM Analytics Report

// Preventing Duplication
AddEventHandler("crm", "OnBeforeCrmLeadAdd", array(
    "CRMHandlers",
    "OnBeforeLeadAddHandler"
));

class CRMHandlers
{
    function OnBeforeLeadAddHandler(&$arFields)
    {
        global $APPLICATION, $USER;
        $userId = $USER->GetId();
        Bitrix\Main\Loader::includeModule('crm');

        if ($arFields['ORIGINATOR_ID'] != 'email-tracker') {
            $phoneFields = $arFields["FM"]["PHONE"];
            $number = array_shift($phoneFields);

            if (!empty($number['VALUE'])) {
                $chars = array('+', '-', ' ', '(', ')');
                $number = str_replace($chars, '', $number["VALUE"]);

                $number = '1' . $number;

                $dbResMultiFields = CCrmFieldMulti::GetListEx(
                    array('ID' => 'desc'),
                    array('ENTITY_ID' => 'LEAD', 'VALUE' => '+' . $number)
                );

                if ($arMultiFields = $dbResMultiFields->Fetch()) {
                    $leadId = $arMultiFields['ELEMENT_ID'];

                    $lead = CCrmLead::GetByID($leadId);
                    $responsibleId = $lead['ASSIGNED_BY'];

                    $entity = new CCrmLead(false);
                    $fields = array(
                        'STATUS_ID' => 'E4B0A778' // Incoming New Leads
                    );
                    $entity->update($leadId, $fields);

                    $text = 'Lead contacted again - Matched by PHONE - Lead moved to Incoming New Leads';
                    if (!empty($text)) {
                        $resId = \Bitrix\Crm\Timeline\CommentEntry::create(
                            array(
                                'TEXT' => $text,
                                'SETTINGS' => array(),
                                'AUTHOR_ID' => 0,
                                'BINDINGS' => array(array('ENTITY_TYPE_ID' => CCrmOwnerType::Lead, 'ENTITY_ID' => $leadId))
                            )
                        );

                        $resultUpdating = Bitrix\Crm\Timeline\Entity\TimelineBindingTable::update(
                            array('OWNER_ID' => $resId, 'ENTITY_ID' => $leadId, 'ENTITY_TYPE_ID' => CCrmOwnerType::Lead),
                            array('IS_FIXED' => 'N')
                        );
                    }


                    $arFields = array(
                        "MESSAGE_TYPE" => "S",
                        "TO_USER_ID" => $responsibleId,
                        "FROM_USER_ID" => 0,
                        "MESSAGE" => "Lead contacted again - Matched by PHONE - Lead moved to Incoming New Leads - https://metalpro.site/crm/lead/details/" . $leadId . "/",
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

                    $APPLICATION->ThrowException("Lead was not created");
                    return false;
                }
            }

            $emailFields = $arFields["FM"]["EMAIL"];
            $email = array_shift($emailFields);
            if (!empty($email['VALUE'])) {
                $email = $email["VALUE"];

                $dbResMultiFields = CCrmFieldMulti::GetList(
                    array('ID' => 'asc'),
                    array('ENTITY_ID' => 'LEAD', 'VALUE' => $email)
                );

                if ($arMultiFields = $dbResMultiFields->Fetch()) {
                    $leadId = $arMultiFields['ELEMENT_ID'];

                    $lead = CCrmLead::GetByID($leadId);
                    $responsibleId = $lead['ASSIGNED_BY'];

                    $entity = new CCrmLead(false);
                    $fields = array(
                        'STATUS_ID' => 'E4B0A778' // Incoming New Leads
                    );
                    $entity->update($leadId, $fields);

                    $text = 'Lead contacted again - Matched by EMAIL - Lead moved to Incoming New Leads';

                    if (!empty($text)) {
                        $resId = \Bitrix\Crm\Timeline\CommentEntry::create(
                            array(
                                'TEXT' => $text,
                                'SETTINGS' => array(),
                                'AUTHOR_ID' => 0,
                                'BINDINGS' => array(array('ENTITY_TYPE_ID' => CCrmOwnerType::Lead, 'ENTITY_ID' => $leadId))
                            )
                        );

                        $resultUpdating = Bitrix\Crm\Timeline\Entity\TimelineBindingTable::update(
                            array('OWNER_ID' => $resId, 'ENTITY_ID' => $leadId, 'ENTITY_TYPE_ID' => CCrmOwnerType::Lead),
                            array('IS_FIXED' => 'N')
                        );
                    }

                    $arFields = array(
                        "MESSAGE_TYPE" => "S",
                        "TO_USER_ID" => $responsibleId,
                        "FROM_USER_ID" => 0,
                        "MESSAGE" => "Lead contacted again - Matched by EMAIL - Lead moved to Incoming New Leads - https://metalpro.site/crm/lead/details/" . $leadId . "/",
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

                    $APPLICATION->ThrowException("Lead was not created");
                    return false;
                }
            }
        }
    }
}

//Avivi: set last call time and duration to custom fields in lead
AddEventHandler("voximplant", "onCallEnd", "onAfterCallEnd");
function onAfterCallEnd(&$arFields)
{
    $callId = $arFields['CALL_ID'];

    $arFilter['CALL_ID'] = $callId;

    $parameters = array('order' => array('CALL_START_DATE' => 'DESC'), 'filter' => $arFilter, 'select' => array('*'));

    $dbStat = Bitrix\Voximplant\StatisticTable::getList($parameters);

    if ($arData = $dbStat->fetch()) {
        if ($arData['CRM_ENTITY_TYPE'] == 'LEAD') {
            $durationMinutes = floor($arData['CALL_DURATION'] / 60);
            $durationSeconds = $arData['CALL_DURATION'] % 60;
            $duration = $durationMinutes . ' min. ' . $durationSeconds . ' sec.';

            $entityId = $arData['CRM_ENTITY_ID'];

            $entity = new CCrmLead();
            $fields = array(
                'UF_CRM_1635844183' => $arData['CALL_START_DATE'],
                'UF_CRM_1635862614' => $duration
            );

            $entity->update($entityId, $fields);
        }
    }
}

//Avivi: parcing email for filling lead fields
AddEventHandler("crm", "OnActivityAdd", 'onAfterLetterReceive');
function onAfterLetterReceive($ID, &$arFields)
{
    $chars = array('+', '-', ' ', '(', ')');
    if ($arFields['OWNER_TYPE_ID'] == \CCrmOwnerType::Lead && $arFields["PROVIDER_ID"] == "CRM_EMAIL") {
        if (CModule::IncludeModule("crm")) {
            if (!empty($arFields["DESCRIPTION"])) {
                $leadMultiFieldObject = CCrmFieldMulti::GetList(
                    array('ID' => 'ASC'),
                    array(
                        'ENTITY_ID' => "LEAD",
                        'ELEMENT_ID' => $arFields["OWNER_ID"],
                        'TYPE_ID' => 'EMAIL'
                    )
                );
                while ($fields = $leadMultiFieldObject->Fetch()) {
                    if ($fields["TYPE_ID"] == "EMAIL")
                        $email = $fields["ID"];
                }
                $dom = phpQuery::newDocument($arFields["DESCRIPTION"]);
                $valueArr = array();
                $leadFields = array();
                $leadWithDots = false;
                foreach ($dom->find("td") as $value) {
                    $str = pq($value)->text();
                    $valueArr[] = $str;
                }
                if (count($valueArr) > 0) {
                    foreach ($valueArr as $key => $str) {
                        switch ($str) {
                            case "From:":
                            case "Name":
                                if ($valueArr[$key + 1] != "") {
                                    $textKey = explode(" ", $valueArr[$key + 1]);
                                    $leadFields["NAME"] = uniTrim($textKey[0]);
                                    $leadFields["LAST_NAME"] = uniTrim($textKey[1]);
                                }
                                if (empty($leadFields["NAME"])) {
                                    $text = trim($valueArr[$key + 1]);
                                    $textKey = explode(" ", $text);
                                    $leadFields["NAME"] = uniTrim($textKey[0]);
                                    $leadFields["LAST_NAME"] = uniTrim($textKey[1]);
                                }
                                break;
                            case "Company":
                            case "Company:":
                                if ($valueArr[$key + 1] != "")
                                    $leadFields["COMPANY_TITLE"] = uniTrim($valueArr[$key + 1]);
                                break;
                            case "Address:":
                                if ($valueArr[$key + 1] != "")
                                    $leadFields["ADDRESS"] = uniTrim($valueArr[$key + 1]);
                                break;
                            case "City":
                            case "City:":
                                if ($valueArr[$key + 1] != "")
                                    $leadFields["ADDRESS_CITY"] = uniTrim($valueArr[$key + 1]);
                                break;
                            case "Province":
                            case "State:":
                            case "Location":
                                if ($valueArr[$key + 1] != "")
                                    $leadFields["ADDRESS_PROVINCE"] = uniTrim($valueArr[$key + 1]);
                                break;
                            case "ZIP Code:":
                                if ($valueArr[$key + 1] != "")
                                    $leadFields["ADDRESS_POSTAL_CODE"] = uniTrim($valueArr[$key + 1]);
                                break;
                            case "Phone:":
                            case "Phone":
                            case "Phone number":
                                if ($valueArr[$key + 1] != "") {
                                    $phoneNumber = uniTrim($valueArr[$key + 1]);
                                    $phoneNumber = str_replace($chars, '', $phoneNumber);
                                    $phoneNumber = (strlen($phoneNumber) <= 10) ? '+1' . $phoneNumber : '+' . $phoneNumber;

                                    $leadFields["FM"]["PHONE"] = array(
                                        "n0" => array(
                                            "VALUE" => $phoneNumber,
                                            "VALUE_TYPE" => "WORK",
                                        ),
                                    );
                                }
                                break;
                            case "Email:":
                            case "Email":
                            case "Email address":
                                if ($valueArr[$key + 1] != "" && check_email($valueArr[$key + 1])) {
                                    $leadFields["FM"]["EMAIL"] = array(
                                        $email => array(
                                            "VALUE" => uniTrim($valueArr[$key + 1]),
                                            "VALUE_TYPE" => "WORK",
                                        ),
                                    );
                                }
                                // Avivi Task #18452 Email parsing small issue
                                if (!isset($leadFields['FM']['EMAIL'])) {
                                    $leadFields["FM"]["EMAIL"] = array(
                                        $email => array(
                                            "VALUE" => $leadFields['NAME'] . '@pioneersteel.com',
                                            "VALUE_TYPE" => "WORK",
                                        ),
                                    );
                                }
                                break;
                            case "Building Type:":
                            case "Project Time Frame:":
                            case "Comments:":
                            case "Comments":
                            case "Use For":
                            case "Accessories":
                            case "When Build":
                            case "Usage":
                            case "Message":
                                if ($valueArr[$key + 1] != "")
                                    $leadFields[PROJECT_DETAILS_FIELD] .= $str . ": " . uniTrim($valueArr[$key + 1]) . ";\n";
                                break;
                            case "Project Width:":
                            case "Width":
                                if ($valueArr[$key + 1] != "")
                                    $leadFields[WIDTH_FIELD] = floatval(uniTrim($valueArr[$key + 1]));
                                break;
                            case "Project Length:":
                            case "Length":
                                if ($valueArr[$key + 1] != "")
                                    $leadFields[LENGTH_FIELD] = floatval(uniTrim($valueArr[$key + 1]));
                                break;
                            case "Project Height:":
                            case "Height":
                                if ($valueArr[$key + 1] != "")
                                    $leadFields[HEIGHT_FIELD] = floatval(uniTrim($valueArr[$key + 1]));
                                break;
                            case "Country":
                                if ($valueArr[$key + 1] != "")
                                    $leadFields["ADDRESS_COUNTRY"] = uniTrim($valueArr[$key + 1]);
                                break;
                            case "Model":
                                if ($valueArr[$key + 1] != "")
                                    $leadFields[MODEL_FIELD] = uniTrim($valueArr[$key + 1]);
                                break;
                        }
                    }
                } else {
                    foreach ($dom->find("p") as $value) {
                        $str = pq($value)->text();
                        $valueArr[] = $str;
                    }
                    if (count($valueArr) == 0) {
                        foreach ($dom->find("span") as $value) {
                            $str = pq($value)->text();
                            $valueArr[] = $str;
                        }
                    }
                    if (count($valueArr) == 0) {
                        foreach ($dom as $value) {
                            $str = pq($value)->text();
                            $valueArr = explode("\n", $str);
                        }
                    }
                    foreach ($valueArr as $key => $str) {
                        if (in_array("------------------------", $valueArr)) {
                            $leadWithDots = true;
                            if ($str == "------------------------")
                                unset($valueArr[$key]);
                        } else {
                            $textKey = explode(" ", $str);
                            if (
                                strpos($str, "Project Time Frame") !== false ||
                                strpos($str, "Comments") !== false ||
                                strpos($str, "Best Time To Contact") !== false ||
                                strpos($str, "Project City") !== false ||
                                strpos($str, "Project Province") !== false ||
                                strpos($str, "Construction Type") !== false ||
                                strpos($str, "Use") !== false ||
                                strpos($str, "Accessories") !== false ||
                                strpos($str, "When") !== false
                            ) {
                                $leadFields[PROJECT_DETAILS_FIELD] .= uniTrim($str) . ";\n";
                                $leadFields[PROJECT_DETAILS_FIELD] = trim($leadFields[PROJECT_DETAILS_FIELD]);
                                continue;
                            }
                            if (strpos($str, "From") !== false || strpos($str, "Name") !== false) {
                                $text = $arFields['SETTINGS']['EMAIL_META']['from'];
                                $explodedText = explode('<', $text);
                                $email1 = str_replace('>', '', $explodedText[1]);

                                if ($email1 == 'sales@pioneersteel.com' || $email1 == 'jbrenzil@braemarbuildings.com') {
                                    $leadFields["NAME"] = uniTrim($textKey[1]);
                                    $leadFields["LAST_NAME"] = uniTrim($textKey[2]);
                                }
                                continue;
                            }
                            if (strpos($str, "Company") !== false) {
                                foreach ($textKey as $key => $text) {
                                    if ($key != 0)
                                        $leadFields["COMPANY_TITLE"] .= uniTrim($text) . " ";
                                }
                                $leadFields["COMPANY_TITLE"] = trim($leadFields["COMPANY_TITLE"]);
                                continue;
                            }
                            if (strpos($str, "Address") !== false) {
                                foreach ($textKey as $key => $text) {
                                    if ($key != 0)
                                        $leadFields["ADDRESS"] .= uniTrim($text) . " ";
                                }
                                $leadFields["ADDRESS"] = trim($leadFields["ADDRESS"]);
                                continue;
                            }
                            if (strpos($str, "City") !== false) {
                                foreach ($textKey as $key => $text) {
                                    if ($key != 0)
                                        $leadFields["ADDRESS_CITY"] .= uniTrim($text) . " ";
                                }
                                $leadFields["ADDRESS_CITY"] = trim($leadFields["ADDRESS_CITY"]);
                                continue;
                            }
                            if (strpos($str, "State") !== false || strpos($str, "Province") !== false) {
                                foreach ($textKey as $key => $text) {
                                    if ($key != 0)
                                        $leadFields["ADDRESS_PROVINCE"] .= uniTrim($text) . " ";
                                }
                                $leadFields["ADDRESS_PROVINCE"] = trim($leadFields["ADDRESS_PROVINCE"]);
                                continue;
                            }
                            if (strpos($str, "ZIP Code") !== false) {
                                foreach ($textKey as $key => $text) {
                                    if ($key != 0 && $key != 1)
                                        $leadFields["ADDRESS_POSTAL_CODE"] .= uniTrim($text) . " ";
                                }
                                $leadFields["ADDRESS_POSTAL_CODE"] = trim($leadFields["ADDRESS_POSTAL_CODE"]);
                                continue;
                            }
                            if (strpos($str, "Country") !== false) {
                                if (empty($leadFields["ADDRESS_COUNTRY"])) {
                                    foreach ($textKey as $key => $text) {
                                        if ($key != 0)
                                            $leadFields["ADDRESS_COUNTRY"] .= uniTrim($text) . " ";
                                    }
                                    $leadFields["ADDRESS_COUNTRY"] = trim($leadFields["ADDRESS_COUNTRY"]);
                                }
                                continue;
                            }
                            if (strpos($str, "Phone") !== false) {
                                $text = str_replace("Phone:", '', $textKey[0]);
                                $text = trim($text);

                                $phoneNumber = uniTrim($text);
                                $phoneNumber = str_replace($chars, '', $phoneNumber);
                                $phoneNumber = (strlen($phoneNumber) <= 10) ? '+1' . $phoneNumber : '+' . $phoneNumber;

                                $leadFields["FM"]["PHONE"] = array(
                                    "n0" => array(
                                        "VALUE" => $phoneNumber,
                                        "VALUE_TYPE" => "WORK",
                                    ),
                                );
                                continue;
                            }
                            if (strpos($str, "Email") !== false) {

                                $leadFields["FM"]["EMAIL"] = array(
                                    $email => array(
                                        "VALUE" => uniTrim($textKey[1]),
                                        "VALUE_TYPE" => "WORK",
                                    ),
                                );
                                continue;
                            }
                            if (strpos($str, "Building Type") !== false) {
                                foreach ($textKey as $key => $text) {
                                    if ($key != 0 && $key != 1)
                                        $leadFields[STYLE_FIELD] .= uniTrim($text) . " ";
                                }
                                $leadFields[STYLE_FIELD] = trim($leadFields[STYLE_FIELD]);
                                continue;
                            }
                            if (strpos($str, "Project Width") !== false) {
                                $leadFields[WIDTH_FIELD] = uniTrim($textKey[2]);
                                continue;
                            }
                            if (strpos($str, "Width") !== false) {
                                if (empty($leadFields[WIDTH_FIELD])) {
                                    $leadFields[WIDTH_FIELD] = uniTrim($textKey[1]);
                                    continue;
                                }
                            }
                            if (strpos($str, "Project Length") !== false) {
                                $leadFields[LENGTH_FIELD] = uniTrim($textKey[2]);
                                continue;
                            }
                            if (strpos($str, "Length") !== false) {
                                if (empty($leadFields[LENGTH_FIELD])) {
                                    $leadFields[LENGTH_FIELD] = uniTrim($textKey[1]);
                                    continue;
                                }
                            }
                            if (strpos($str, "Project Height") !== false) {
                                $leadFields[HEIGHT_FIELD] = uniTrim($textKey[2]);
                                continue;
                            }
                            if (strpos($str, "Height") !== false) {
                                if (empty($leadFields[HEIGHT_FIELD])) {
                                    $leadFields[HEIGHT_FIELD] = uniTrim($textKey[1]);
                                    continue;
                                }
                            }
                            if (strpos($str, "Model") !== false) {
                                $leadFields[MODEL_FIELD] = uniTrim($textKey[1]);
                                continue;
                            }
                        }
                    }
                    if ($leadWithDots) {
                        $fieldsArr = array(
                            "Name",
                            "Company",
                            "Address",
                            "Country",
                            "Location",
                            "Location2",
                            "City",
                            "Email address",
                            "Phone number",
                            "Subject",
                            "Width",
                            "Height",
                            "Length",
                            "Usage",
                            "Accessories",
                            "When",
                            "Magazine",
                            "Other Media",
                            "Newspaper",
                            "Other",
                            "Message",
                            "Sent from:"
                        );
                        $valueArr = array_values($valueArr);
                        foreach ($valueArr as $key => $str) {
                            switch ($str) {
                                case "Name":
                                    if (!in_array($valueArr[$key + 1], $fieldsArr)) {
                                        $textKey = explode(" ", $valueArr[$key + 1]);
                                        $leadFields["NAME"] = uniTrim($textKey[0]);
                                        $leadFields["LAST_NAME"] = uniTrim($textKey[1]);
                                    }
                                    break;
                                case "Company":
                                    if (!in_array($valueArr[$key + 1], $fieldsArr))
                                        $leadFields["COMPANY_TITLE"] = uniTrim($valueArr[$key + 1]);
                                    break;
                                case "Address":
                                    if (!in_array($valueArr[$key + 1], $fieldsArr))
                                        $leadFields["ADDRESS"] = uniTrim($valueArr[$key + 1]);
                                    break;
                                case "Country":
                                    if (!in_array($valueArr[$key + 1], $fieldsArr))
                                        $leadFields["ADDRESS_COUNTRY"] = uniTrim($valueArr[$key + 1]);
                                    break;
                                case "Location":
                                    if (!in_array($valueArr[$key + 1], $fieldsArr))
                                        $leadFields["ADDRESS_PROVINCE"] = uniTrim($valueArr[$key + 1]);
                                    break;
                                case "City":
                                    if (!in_array($valueArr[$key + 1], $fieldsArr))
                                        $leadFields["ADDRESS_CITY"] = uniTrim($valueArr[$key + 1]);
                                    break;
                                case "Email address":
                                    if (!in_array($valueArr[$key + 1], $fieldsArr)) {
                                        $leadFields["FM"]["EMAIL"] = array(
                                            $email => array(
                                                "VALUE" => uniTrim($valueArr[$key + 1]),
                                                "VALUE_TYPE" => "WORK",
                                            ),
                                        );
                                    }
                                    break;
                                case "Phone number":
                                    if (!in_array($valueArr[$key + 1], $fieldsArr)) {

                                        $phoneNumber = uniTrim($valueArr[$key + 1]);
                                        $phoneNumber = str_replace($chars, '', $phoneNumber);
                                        $phoneNumber = (strlen($phoneNumber) <= 10) ? '+1' . $phoneNumber : '+' . $phoneNumber;

                                        $leadFields["FM"]["PHONE"] = array(
                                            "n0" => array(
                                                "VALUE" => $phoneNumber,
                                                "VALUE_TYPE" => "WORK",
                                            ),
                                        );
                                    }
                                    break;
                                case "Width":
                                    if (!in_array($valueArr[$key + 1], $fieldsArr))
                                        $leadFields[WIDTH_FIELD] = uniTrim($valueArr[$key + 1]);
                                    break;
                                case "Height":
                                    if (!in_array($valueArr[$key + 1], $fieldsArr))
                                        $leadFields[HEIGHT_FIELD] = uniTrim($valueArr[$key + 1]);
                                    break;
                                case "Length":
                                    if (!in_array($valueArr[$key + 1], $fieldsArr))
                                        $leadFields[LENGTH_FIELD] = uniTrim($valueArr[$key + 1]);
                                    break;
                                case "Message":
                                    if (!in_array($valueArr[$key + 1], $fieldsArr)) {
                                        if (empty($leadFields[PROJECT_DETAILS_FIELD]))
                                            $leadFields[PROJECT_DETAILS_FIELD] = uniTrim($valueArr[$key + 1]);
                                    }
                                    break;
                            }
                        }
                    }
                }
                if (!empty($leadFields)) {
                    if (isset($leadFields["NAME"]) || isset($leadFields["LAST_NAME"]))
                        $leadFields["TITLE"] = trim($leadFields["NAME"] . ' ' . $leadFields["LAST_NAME"]);

                    if (isset($leadFields["FM"]["EMAIL"]) && !empty($leadFields["FM"]["EMAIL"])) {
                        $emailValue = reset($leadFields["FM"]["EMAIL"]);
                        $leadRes = CCrmFieldMulti::GetList(
                            array(),
                            array(
                                "ENTITY_ID" => "LEAD",
                                "VALUE" => $emailValue["VALUE"],
                                "TYPE_ID" => "EMAIL",
                            )
                        );
                        while ($leadArr = $leadRes->Fetch())
                            $leadCheck = $leadArr;
                    }

                    if (isset($leadCheck["ELEMENT_ID"]) && !empty($leadCheck["ELEMENT_ID"]))
                        $leadID = $leadCheck["ELEMENT_ID"];
                    else
                        $leadID = $arFields["OWNER_ID"];
                    $leadData = array();
                    $res = CCrmLead::GetList(array('DATE_CREATE' => 'DESC'), array("ID" => $leadID), array("UF_*", "ID"), false);
                    while ($ar = $res->GetNext())
                        $leadData = $ar;
                    $lead = CCrmLead::GetByID($leadID);
                    $leadData = array_merge($lead, $leadData);

                    $fieldsName = array(
                        "NAME" => "Name",
                        "LAST_NAME" => "Last Name",
                        "COMPANY_TITLE" => "Company name",
                        "ADDRESS" => "Address",
                        "ADDRESS_CITY" => "City",
                        "ADDRESS_PROVINCE" => "State / Province",
                        "ADDRESS_POSTAL_CODE" => "Zip",
                        "ADDRESS_COUNTRY" => "Country",
                        "PHONE" => "Phone",
                        "EMAIL" => "Email",
                    );
                    $userFields = CCrmLead::GetUserFields("en");
                    foreach ($userFields as $fieldCode => $field)
                        $fieldsName[$fieldCode] = $field["EDIT_FORM_LABEL"];
                    $leadMultiFieldObject = CCrmFieldMulti::GetList(
                        array('ID' => 'ASC'),
                        array(
                            'ENTITY_ID' => "LEAD",
                            'ELEMENT_ID' => $leadID,
                            'TYPE_ID' => 'PHONE'
                        )
                    );
                    while ($fields = $leadMultiFieldObject->Fetch()) {
                        if ($fields["TYPE_ID"] == "PHONE") {
                            $phone = $fields["ID"];
                            $leadData["PHONE"] = $fields["VALUE"];
                        }
                    }
                    if (isset($phone) && !empty($phone)) {
                        $phoneValue = array_shift($leadFields["FM"]["PHONE"]);
                        $leadFields["FM"]["PHONE"] = array(
                            $phone => $phoneValue
                        );
                    }
                    $changedFields = array();
                    foreach ($leadFields as $field => $value) {
                        if (isset($leadData[$field]) && $leadData[$field] != $value)
                            $changedFields[] = array("FIELD_NAME" => $fieldsName[$field], "VALUE" => $value);
                        if ($field == "FM") {
                            foreach ($value as $key => $data) {
                                $multiField = reset($data);
                                if (isset($leadData[$key]) && $leadData[$key] != $multiField["VALUE"])
                                    $changedFields[] = array("FIELD_NAME" => $fieldsName[$key], "VALUE" => $multiField["VALUE"]);
                            }
                        }
                    }

                    $text = $arFields['SETTINGS']['EMAIL_META']['from'];

                    $explodedText = explode(' ', $text);

                    $charsArray = array('<', '>', '&#60;', '&#62;', '&lt;', '&gt;');

                    foreach ($explodedText as $key => $value) {
                        if (strpos($value, '@') !== false) $res = $key;
                    }

                    $email = str_replace($charsArray, '', $explodedText[$res]);

                    if ($email == 'sales@pioneersteel.com' || $email == 'jbrenzil@braemarbuildings.com') {
                        $entity = new CCrmLead(false);
                        $entity->update($leadID, $leadFields);
                        if ($leadID != $arFields["OWNER_ID"]) {
                            $entity->delete($arFields["OWNER_ID"]);
                        }
                        foreach ($changedFields as $field) {
                            if (!empty($field["VALUE"])) {
                                \Bitrix\Crm\Timeline\CommentEntry::create(
                                    array(
                                        'TEXT' => $field["FIELD_NAME"] . " field " . $field["VALUE"] . " was updated",
                                        'SETTINGS' => array('HAS_FILES' => 'N'),
                                        'AUTHOR_ID' => 1,
                                        'BINDINGS' => array(array('ENTITY_TYPE_ID' => \CCrmOwnerType::Lead, 'ENTITY_ID' => $leadID))
                                    ));
                            }
                        }
                    }
                }
            }
        }
    }

}

AddEventHandler("crm", "OnBeforeCrmLeadAdd", 'OnBeforeCrmLeadAddEvent');

function OnBeforeCrmLeadAddEvent(&$arFields)
{
    //Avivi: adding information to Project details from Source Information
    if (!empty($arFields["SOURCE_DESCRIPTION"]))
        $arFields[PROJECT_DETAILS_FIELD] .= "\n" . $arFields["SOURCE_DESCRIPTION"];
    //Avivi: set Source (if source is empty)
    if ($arFields["SOURCE_ID"] == "") {
        if ($arFields["UTM_SOURCE"] != "" && $arFields["UTM_SOURCE"] == "GoogleAds")
            $arFields["SOURCE_ID"] = "F6F4CB01";
        elseif ($arFields["UTM_SOURCE"] != "" && $arFields["UTM_SOURCE"] == "Bing")
            $arFields["SOURCE_ID"] = "7FF70DD7";
        elseif ($arFields["UTM_SOURCE"] != "" && $arFields["UTM_SOURCE"] == "(direct)")
            $arFields["SOURCE_ID"] = "9FBC074B";
        else
            $arFields["SOURCE_ID"] = "11D89556";
    }
    //Avivi: set Braemar or Pioneer lead sources
    if ($arFields["SOURCE_ID"] == "EMAIL") {
        $emailAdress = CHighData::GetList(EMAILS_SOURCES_HIGHLOAD,
            array(),
            array("UF_EMAIL_ADDRESS", "ID"));
        foreach ($emailAdress as $email) {
            if ($arFields["FM"]["EMAIL"]["n1"]["VALUE"] == $email["UF_EMAIL_ADDRESS"] && $email["ID"] == BRAEMAR_EMAIL_ADRESS)
                $arFields["SOURCE_ID"] = "225A5CAD";
            if ($arFields["FM"]["EMAIL"]["n1"]["VALUE"] == $email["UF_EMAIL_ADDRESS"] && $email["ID"] == PIONEER_EMAIL_ADRESS)
                $arFields["SOURCE_ID"] = "B9448ECE";
        }
    }
    //Avivi: set Region field
    if ($arFields["ADDRESS_PROVINCE"] == "") {
        $obEnum = new \CUserFieldEnum;
        $rsEnum = $obEnum->GetList(array(), array("USER_FIELD_ID" => array(STATE_FIELD_ID, PROVINCE_FIELD_ID)));
        $enumProvince = array();
        $enumState = array();
        while ($arEnum = $rsEnum->Fetch()) {
            if ($arEnum["USER_FIELD_ID"] == STATE_FIELD_ID)
                $enumState[$arEnum["ID"]] = $arEnum["VALUE"];
            if ($arEnum["USER_FIELD_ID"] == PROVINCE_FIELD_ID)
                $enumProvince[$arEnum["ID"]] = $arEnum["VALUE"];
        }
        if ($arFields[STATE_FIELD] != "")
            $arFields["ADDRESS_PROVINCE"] = $enumState[$arFields[STATE_FIELD]];
        elseif ($arFields[PROVINCE_FIELD] != "")
            $arFields["ADDRESS_PROVINCE"] = $enumProvince [$arFields[PROVINCE_FIELD]];
    }
    //Avivi: set Lead Number
    $res = CCrmLead::GetListEx(
        array('ID' => 'DESC'),
        array("CHECK_PERMISSIONS" => 'N'),
        false,
        array('nTopCount' => 1),
        array(LEAD_NUMBER_FIELD)
    );
    if ($ar = $res->GetNext())
        $lastLeadNumber = $ar[LEAD_NUMBER_FIELD];
    $newLeadNumber = $lastLeadNumber + 1;
    $arFields[LEAD_NUMBER_FIELD] = $newLeadNumber;
}

AddEventHandler("crm", "OnAfterCrmLeadUpdate", 'OnAfterCrmLeadUpdateEvent');
function OnAfterCrmLeadUpdateEvent($arFields)
{
    //Avivi: set Region field
    $res = CCrmLead::GetListEx(
        array('ID' => 'DESC'),
        array("ID" => $arFields["ID"]),
        false,
        false,
        array(STATE_FIELD, PROVINCE_FIELD, "ID", "ADDRESS_PROVINCE")
    );
    while ($ar = $res->GetNext())
        $leadData = $ar;
    if ($leadData["ADDRESS_PROVINCE"] == "") {
        $obEnum = new \CUserFieldEnum;
        $rsEnum = $obEnum->GetList(array(), array("USER_FIELD_ID" => array(STATE_FIELD_ID, PROVINCE_FIELD_ID)));
        $enumProvince = array();
        $enumState = array();
        while ($arEnum = $rsEnum->Fetch()) {
            if ($arEnum["USER_FIELD_ID"] == STATE_FIELD_ID)
                $enumState[$arEnum["ID"]] = $arEnum["VALUE"];
            if ($arEnum["USER_FIELD_ID"] == PROVINCE_FIELD_ID)
                $enumProvince[$arEnum["ID"]] = $arEnum["VALUE"];
        }
        if ($leadData[STATE_FIELD] != "")
            $update["ADDRESS_PROVINCE"] = $enumState[$leadData[STATE_FIELD]];
        elseif ($leadData[PROVINCE_FIELD] != "")
            $update["ADDRESS_PROVINCE"] = $enumProvince[$leadData[PROVINCE_FIELD]];

        if (isset($update) && !empty($update)) {
            $entity = new CCrmLead(false);
            $entity->update($arFields["ID"], $update);
        }
    }
}

AddEventHandler("crm", "OnBeforeCrmLeadAdd", 'createNewLeadByEmail');
function createNewLeadByEmail($arFields)
{
    $emailFields = $arFields["FM"]["EMAIL"];
    $email = array_shift($emailFields);
    $email = $email["VALUE"];

    if ($arFields['ORIGINATOR_ID'] == 'email-tracker') {
        if ($email == "sales@pioneersteel.com" && $arFields['TITLE'] == 'Lead Assignment')
            return true;
        else if ($email == 'jbrenzil@braemarbuildings.com' && $arFields['TITLE'] == 'FW: Steel Building Canada Inc. Quote Request')
            return true;
        else if ($email == 'b.tomchyshen@avivi.com.ua')
            return true;
        else if ($email == 'b.tomchishen2003@gmail.com')
            return true;
        else
            return false;
    }
}

AddEventHandler("crm", "OnAfterCrmLeadAdd", array(
    "PhoneFormatter",
    "formattingPhoneNumber"
));

/**
 * Class PhoneFormatter
 */
class PhoneFormatter
{
    protected const CHARS = array('+', '-', ' ', '(', ')');
    protected const TYPE_ID = 'PHONE';

    /**
     * Formatting phone number to +countryXXXXXXXXXX
     * @param $arFields
     */
    public function formattingPhoneNumber(&$arFields)
    {
        Bitrix\Main\Loader::includeModule('crm');

        $phoneNumber = self::getNewPhoneNumber($arFields);
        self::updatePhoneNumber($phoneNumber, $arFields);
    }

    /**
     * @param $arFields
     * @return string
     */
    protected function getNewPhoneNumber($arFields)
    {
        $phoneNumber = self::getPhoneNumber($arFields);
        $phoneNumber = self::clearPhoneNumber($phoneNumber);
        $phoneNumber = self::addFormatToNumber($phoneNumber);

        return $phoneNumber;
    }

    /**
     * Get incoming phone number from $arFields
     * @param $arFields
     * @return string
     */
    protected function getPhoneNumber($arFields)
    {
        return array_shift($arFields['FM']['PHONE'])['VALUE'];
    }

    /**
     * Delete all characters from phone number
     * @param $phoneNumber
     * @return string
     */
    protected function clearPhoneNumber($phoneNumber)
    {
        return str_replace(self::CHARS, '', $phoneNumber);
    }

    /**
     * Set phone format +1XXXXXXXXXX
     * @param $phoneNumber
     * @return string
     */
    protected function addFormatToNumber($phoneNumber)
    {
        return (strlen($phoneNumber) <= 10) ? '+1' . $phoneNumber : '+' . $phoneNumber;
    }

    /**
     * Get lead ID
     * @param $arFields
     * @return int
     */
    protected function getLeadID($arFields)
    {
        return intval($arFields['ID']);
    }

    /**
     * Get record ID in b_crm_field_multi
     * @param $arFields
     * @return int
     */
    protected function getFieldMultiID($arFields)
    {
        $leadID = self::getLeadID($arFields);
        $dbRes = CCrmFieldMulti::GetList(array(), array('TYPE_ID' => 'PHONE', 'ELEMENT_ID' => $leadID));

        $arRes = $dbRes->Fetch();

        return intval($arRes['ID']);
    }

    /**
     * Get phone Value Type, for example - WORK
     * @param $arFields
     * @return string
     */
    protected function getValueType($arFields)
    {
        return array_shift($arFields['FM']['PHONE'])['VALUE_TYPE'];
    }

    /**
     * Update phone number in b_crm_field_multi
     * @param $phoneNumber
     * @param $arFields
     * @return int
     */
    protected function updatePhoneNumber($phoneNumber, $arFields)
    {
        $fieldMultiID = self::getFieldMultiID($arFields);
        $valueType = self::getValueType($arFields);
        $complexID = self::TYPE_ID . '_' . $valueType;

        $fieldsToUpdate = array(
            'TYPE_ID' => self::TYPE_ID,
            'VALUE_TYPE' => $valueType,
            'COMPLEX_ID' => $complexID,
            'VALUE' => $phoneNumber,
        );
        fp($fieldsToUpdate, 'fieldsToUpdate');
        $cfm = new CCrmFieldMulti();

        return $cfm->Update($fieldMultiID, $fieldsToUpdate);
    }
}

// Avivi #49545 CRM Analytics Report
AddEventHandler("crm", "OnAfterCrmLeadAdd", 'countNewLead');
AddEventHandler("crm", "OnAfterCrmLeadUpdate", 'countNewLead');
function countNewLead($arFields)
{
    if (!empty($arFields['STATUS_ID'])
        && $arFields['STATUS_ID'] == NEW_LEAD_COUNT_STATUS_ID) {
        $arFilter = ['UF_LEAD_ID' => $arFields['ID']];
        $recordID = CHighData::IsRecordExist(HB_NEW_LEADS, $arFilter);
        if (empty($recordID)) {
            $data = [
                'UF_CREATED' => new DateTime(),
                'UF_LEAD_ID' => $arFields['ID'],
            ];
            CHighData::AddRecord(HB_NEW_LEADS, $data);
        }
    }
}

/*
// Avivi #18300 Auto assigning leads in Bitrix Live Chat
AddEventHandler("crm", "OnBeforeCrmLeadUpdate", 'checkResponsibleBeforeUpdate');
function checkResponsibleBeforeUpdate($arFields) {
    if (!empty($arFields['ASSIGNED_BY_ID'])
        && $arFields['ASSIGNED_BY_ID'] == 1
        && !empty($arFields['MODIFY_BY_ID'])
        && $arFields['MODIFY_BY_ID'] == 1
        && CModule::IncludeModule('crm')
    ) {
        $dbRes = CCrmLead::GetListEx([], ['ID' => $arFields['ID']], false, false, ['STATUS_ID', 'SOURCE_ID']);
        if ($arRes = $dbRes->Fetch()) {
//            fp($arRes, 'checkResponsibleArFields', true);
            if ($arRes['STATUS_ID'] === 'NEW'
                && $arRes['SOURCE_ID'] === 'WEBFORM'
            ) {
                return false;
            }
        }
    }
}
*/

AddEventHandler("crm", "OnBeforeCrmLeadUpdate", 'copyAddress');

function copyAddress(&$arFields)
{
    \Bitrix\Main\Loader::includeModule('location');
    $entity = new CCrmLead(false);

    if (isset($arFields['ADDRESS_LOC_ADDR'])) {
        $address = $arFields['ADDRESS_LOC_ADDR']->getAllFieldsValues();
        $finalAddress = '';

        if(isset($address['410']) && !empty($address['410']))
            $finalAddress .= $address['410'] . ', ';
        if(isset($address['300']) && !empty($address['300']))
            $finalAddress .= $address['300'] . ', ';
        if(isset($address['50']) && !empty($address['50']))
            $finalAddress .= $address['50'] . ', ';
        if(isset($address['100']) && !empty($address['100']))
            $finalAddress .= $address['100'];

        $fields['UF_CRM_1641930030902'] = $finalAddress;
        $fields['UF_CRM_1641930153621'] = $finalAddress;

        $entity->update($arFields['ID'], $fields);
    } else {
        $lead = CCrmLead::GetById($arFields['ID'], false);
        $finalAddress = '';

        if(isset($lead['ADDRESS']) && !empty($lead['ADDRESS']))
            $finalAddress .= $lead['ADDRESS'] . ', ';
        if(isset($lead['ADDRESS_CITY']) && !empty($lead['ADDRESS_CITY']))
            $finalAddress .= $lead['ADDRESS_CITY'] . ', ';
        if(isset($lead['ADDRESS_POSTAL_CODE']) && !empty($lead['ADDRESS_POSTAL_CODE']))
            $finalAddress .= $lead['ADDRESS_POSTAL_CODE'] . ', ';
        if(isset($lead['ADDRESS_COUNTRY']) && !empty($lead['ADDRESS_COUNTRY']))
            $finalAddress .= $lead['ADDRESS_COUNTRY'];

        $arFields['UF_CRM_1641930030902'] = $finalAddress;
        $arFields['UF_CRM_1641930153621'] = $finalAddress;
    }
}

// Avivi #29941 Custom Search Filter by Attached CRM Item for Tasks
AddEventHandler("tasks", "OnBeforeTaskAdd", 'copyCRMItemsForFilterOnCreate');
AddEventHandler("tasks", "OnBeforeTaskUpdate", 'copyCRMItemsForFilterOnUpdate');
function copyCRMItemsForFilterOnCreate(&$arFields)
{
    if (isset($arFields['UF_CRM_TASK']) && !empty($arFields['UF_CRM_TASK'])) {
        $ufCRMTask = $arFields['UF_CRM_TASK'];

        $titles = getTitlesForTaskFilter($ufCRMTask);

        $arFields['UF_CRM_ITEM_COPY'] = $titles;
    }
}

function copyCRMItemsForFilterOnUpdate($ID, &$arFields)
{
    if (isset($arFields['UF_CRM_TASK']) && !empty($arFields['UF_CRM_TASK'])) {
        $ufCRMTask = $arFields['UF_CRM_TASK'];

        $titles = getTitlesForTaskFilter($ufCRMTask);

        $arFields['UF_CRM_ITEM_COPY'] = $titles;
    }
}


// Avivi #33674 Auto Assign Prov/State based on Area Code
AddEventHandler("crm", "OnBeforeCrmLeadAdd", 'assignStateOnCreate');
AddEventHandler("crm", "OnBeforeCrmLeadUpdate", 'assignStateOnUpdate');
function assignStateOnCreate(&$arFields)
{
    if ($arFields["FM"]["PHONE"]) {
        $phoneFields = $arFields["FM"]["PHONE"];
        $number = array_shift($phoneFields);

        $area = AviviAreaCodeAssigner::getArea($number);

        $fieldsForUpdate = AviviAreaCodeAssigner::updateLead($arFields['ID'], $area);
        if (!empty($fieldsForUpdate)) {
            $arFields[AviviAreaCodeAssigner::STATE_FIELD] = $fieldsForUpdate[AviviAreaCodeAssigner::STATE_FIELD];
            $arFields[AviviAreaCodeAssigner::PROVINCE_FIELD] = $fieldsForUpdate[AviviAreaCodeAssigner::PROVINCE_FIELD];
        }
    }
}

function assignStateOnUpdate(&$arFields)
{
    if ($arFields["FM"]["PHONE"]) {
        $phoneFields = $arFields["FM"]["PHONE"];
        $number = array_shift($phoneFields);

        $area = AviviAreaCodeAssigner::getArea($number);

        $fieldsForUpdate = AviviAreaCodeAssigner::updateLead($arFields['ID'], $area);
        if (!empty($fieldsForUpdate)) {
            $arFields[AviviAreaCodeAssigner::STATE_FIELD] = $fieldsForUpdate[AviviAreaCodeAssigner::STATE_FIELD];
            $arFields[AviviAreaCodeAssigner::PROVINCE_FIELD] = $fieldsForUpdate[AviviAreaCodeAssigner::PROVINCE_FIELD];
        }
    }
}
