<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

//AddEventHandler("crm", "OnBeforeCrmLeadAdd", array(
//    "CRMHandlers",
//    "OnBeforeLeadAddHandler"
//));
//
//class CRMHandlers
//{
//    function OnBeforeLeadAddHandler(&$arFields)
//    {
//        fp($arFields, '$arFields');
//        global $APPLICATION, $USER;
//        $userId = $USER->GetId();іі
//        Bitrix\Main\Loader::includeModule('crm');
//
//        if ($arFields["FM"]["PHONE"]) {
//            $phoneFields = $arFields["FM"]["PHONE"];
//            $number = array_shift($phoneFields);
//            $chars = array('+', '-', ' ', '(', ')');
//            $number = str_replace($chars, '', $number["VALUE"]);
//        }
//        if ($arFields["FM"]["EMAIL"]) {
//            $emailFields = $arFields["FM"]["EMAIL"];
//            $email = array_shift($emailFields);
//            $email = $email["VALUE"];
//        }
//
//        $dbResMultiFields = CCrmFieldMulti::GetList(
//            array('ID' => 'desc'),
//            array('ENTITY_ID' => 'LEAD', 'VALUE' => '+%' . $number)
//        );
//
//        if ($arMultiFields = $dbResMultiFields->Fetch()) {
//            $leadId = $arMultiFields['ELEMENT_ID'];
//
//            $entity = new CCrmLead(false);
//            $fields = array(
//                'STATUS_ID' => 'NEW'
//            );
//            $entity->update($leadId, $fields);
//
//            $text = $arFields["COMMENTS"];
//            if (!empty($text)) {
//                $resId = \Bitrix\Crm\Timeline\CommentEntry::create(
//                    array(
//                        'TEXT' => $text,
//                        'SETTINGS' => array(),
//                        'AUTHOR_ID' => 0,
//                        'BINDINGS' => array(array('ENTITY_TYPE_ID' => CCrmOwnerType::Lead, 'ENTITY_ID' => $leadId))
//                    )
//                );
//
//                $resultUpdating = Bitrix\Crm\Timeline\Entity\TimelineBindingTable::update(
//                    array('OWNER_ID' => $resId, 'ENTITY_ID' => $leadId, 'ENTITY_TYPE_ID' => CCrmOwnerType::Lead),
//                    array('IS_FIXED' => 'N')
//                );
//            }
//
//
//            $arFields = array(
//                "MESSAGE_TYPE" => "S",
//                "TO_USER_ID" => $userId,
//                "FROM_USER_ID" => 0,
//                "MESSAGE" => "Lead was not created. The system has lead with this phone number or mail - https://metalpro.site/crm/lead/details/" . $leadId . "/",
//                "AUTHOR_ID" => 0,
//
//                "NOTIFY_TYPE" => 1,
//                "NOTIFY_BUTTONS" =>
//                    array(
//                        array('TITLE' => 'OK', 'VALUE' => 'Y', 'TYPE' => 'accept'),
//                    ),
//                "NOTIFY_MODULE" => "main",
//            );
//            CModule::IncludeModule('im');
//            // CIMMessenger::Add($arFields);
//
//            $APPLICATION->ThrowException("Lead was not created");
//            return false;
//        }
//
//        $dbResMultiFields = CCrmFieldMulti::GetList(
//            array('ID' => 'asc'),
//            array('ENTITY_ID' => 'LEAD', 'VALUE' => $email)
//        );
//
//        if ($arMultiFields = $dbResMultiFields->Fetch()) {
//            $leadId = $arMultiFields['ELEMENT_ID'];
//
//            $entity = new CCrmLead(false);
//            $fields = array(
//                'STATUS_ID' => 'NEW'
//            );
//            $entity->update($leadId, $fields);
//
//            $text = $arFields["COMMENTS"];
//
//            if (!empty($text)) {
//                $resId = \Bitrix\Crm\Timeline\CommentEntry::create(
//                    array(
//                        'TEXT' => $text,
//                        'SETTINGS' => array(),
//                        'AUTHOR_ID' => 0,
//                        'BINDINGS' => array(array('ENTITY_TYPE_ID' => CCrmOwnerType::Lead, 'ENTITY_ID' => $leadId))
//                    )
//                );
//
//                $resultUpdating = Bitrix\Crm\Timeline\Entity\TimelineBindingTable::update(
//                    array('OWNER_ID' => $resId, 'ENTITY_ID' => $leadId, 'ENTITY_TYPE_ID' => CCrmOwnerType::Lead),
//                    array('IS_FIXED' => 'N')
//                );
//            }
//
//
//            $arFields = array(
//                "MESSAGE_TYPE" => "S",
//                "TO_USER_ID" => $userId,
//                "FROM_USER_ID" => 0,
//                "MESSAGE" => "Lead was not created. The system has lead with this phone number or mail - https://metalpro.site/crm/lead/details/" . $leadId . "/",
//                "AUTHOR_ID" => 0,
//
//                "NOTIFY_TYPE" => 1,
//                "NOTIFY_BUTTONS" =>
//                    array(
//                        array('TITLE' => 'OK', 'VALUE' => 'Y', 'TYPE' => 'accept'),
//                    ),
//                "NOTIFY_MODULE" => "main",
//            );
//            CModule::IncludeModule('im');
//            // CIMMessenger::Add($arFields);
//
//            $APPLICATION->ThrowException("Lead was not created");
//            return false;
//        }
//
//    }
//}

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

    // fp($ID, 'tomchyshen_id');
    // fp($arFields, 'tomchyshen_arFields');
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
                                    $leadFields["FM"]["PHONE"] = array(
                                        "n0" => array(
                                            "VALUE" => uniTrim($valueArr[$key + 1]),
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
                                $leadFields["NAME"] = uniTrim($textKey[1]);
                                $leadFields["LAST_NAME"] = uniTrim($textKey[2]);
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
                                $leadFields["FM"]["PHONE"] = array(
                                    "n0" => array(
                                        "VALUE" => uniTrim($textKey[1]),
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
                                        $leadFields["FM"]["PHONE"] = array(
                                            "n0" => array(
                                                "VALUE" => uniTrim($valueArr[$key + 1]),
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

