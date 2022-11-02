<?php

// Avivi #33674 Auto Assign Prov/State based on Area Code
class AviviAreaCodeAssigner
{
    protected const AREA_CODES_FILENAME = 'data.json';
    protected const PATH_TO_FOLDER = '/home/bitrix/www/lead_updater/';
    protected const AREA_CODES_SITE_URL = 'https://www.bennetyee.org/ucsd-pages/area.html';
    protected const AREA_CODES_API_URL = 'https://api.phaxio.com/v2/public/area_codes';

    public const PROVINCE_FIELD = 'UF_CRM_1607507237';
    public const STATE_FIELD = 'UF_CRM_1620224095';

    /**
     * HighLoadBlockTable structure:
     * ID | UF_AREA_CODE | UF_STATE_CODE | UF_COUNTRY | UF_STATE_ID | UF_STATE_TITLE
     */
    protected const HLBT_ID = 40;

    protected static $areas = [];

    /**
     * @param $url
     * @param string $params Should be in format: name=Test&country=CA
     * @return bool|string
     */
    protected static function sendCurl($url, $params = '')
    {
        $ch = curl_init($url . '?' . $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        $content = curl_exec($ch);
        curl_close($ch);

        return $content;
    }

    protected static function getPageHTML($url)
    {
        return self::sendCurl($url);
    }

    public static function getAreas()
    {
        return self::$areas;
    }

    protected static function addAreaToHLBT($area)
    {
        $isExist = CHighData::IsRecordExist(self::HLBT_ID, array('UF_AREA_CODE' => $area['UF_AREA_CODE']));

        if (!$isExist) {
            CHighData::AddRecord(self::HLBT_ID, $area);
        }
    }

    protected static function getAreaByCode($code)
    {
        return CHighData::GetList(self::HLBT_ID, array('UF_AREA_CODE' => $code));
    }

    public static function processAreas()
    {
        self::setAreas();

        foreach (self::$areas as $key => $area) {
            self::$areas[$key] = self::addCountryToArea($area);
            self::$areas[$key]['UF_AREA_CODE'] = $key;

            if (self::$areas[$key]['UF_COUNTRY'] == 'USA') {
                self::$areas[$key]['UF_STATE_ID'] = self::getStateId(self::$areas[$key]['UF_STATE_TITLE'], self::STATE_FIELD);
            } else if (self::$areas[$key]['UF_COUNTRY'] == 'CA') {
                self::$areas[$key]['UF_STATE_ID'] = self::getStateId(self::$areas[$key]['UF_STATE_CODE'], self::PROVINCE_FIELD);
            }

            self::addAreaToHLBT(self::$areas[$key]);
        }
    }

    protected static function setAreas()
    {
        $pageContent = self::getPageHTML(self::AREA_CODES_SITE_URL);

        self::$areas = self::parseHTMLPage($pageContent);
    }

    protected static function parseHTMLPage($pageContent)
    {
        $dom = new DomDocument();
        $dom->loadHTML($pageContent);

        $xpath = new DOMXpath($dom);

        return self::formatAreas(self::parseAreasTable($xpath));
    }

    protected static function parseAreasTable($xpath)
    {
        $elements = $xpath->query("//table/tr");

        if (!is_null($elements)) {
            $result = array();
            foreach ($elements as $element) {
                $nodes = $element->childNodes;
                foreach ($nodes as $node) {
                    $result[] = $node->nodeValue;
                }
            }
            return $result;
        }
    }

    protected static function formatAreas($pageContent)
    {
        $result = array();

        for ($i = 4; $i < count($pageContent) - 4; $i += 4) {
            $exploded = explode(' ', $pageContent[$i]);

            foreach ($exploded as $item) {
                $result[$item]['UF_STATE_CODE'] = $pageContent[$i + 1] != '--' ? $pageContent[$i + 1] : '';
            }
        }

        return $result;
    }

    protected static function addCountryToArea($area)
    {
        $params = 'country=US&state=' . $area['UF_STATE_CODE'];
        $pageContent = json_decode(self::sendCurl(self::AREA_CODES_API_URL, $params));

        if ($pageContent->success) {
            if ($pageContent->paging->total > 0) {
                $area['UF_COUNTRY'] = 'USA';
                $area['UF_STATE_TITLE'] = $pageContent->data[0]->state;
            } else {
                $params = 'country=CA&state=' . $area['UF_STATE_CODE'];
                $pageContent = json_decode(self::sendCurl(self::AREA_CODES_API_URL, $params));

                $area['UF_COUNTRY'] = 'CA';
                $area['UF_STATE_TITLE'] = $pageContent->data[0]->state;
            }
        }

        return $area;
    }

    protected static function getStateId($stateCode, $fieldName)
    {
        $rsEnum = CUserFieldEnum::GetList(array(), array("USER_FIELD_NAME" => $fieldName, "VALUE" => $stateCode));
        $arEnum = $rsEnum->GetNext();

        return $arEnum['ID'];
    }

    protected static function getAreaCodeByNumber($phoneNumber)
    {
        return preg_replace('~.*(\d{3})[^\d]{0,7}(\d{3})[^\d]{0,7}(\d{4}).*~', '$1', $phoneNumber);
    }

    public static function getArea($phoneNumber)
    {
        $areaCode = self::getAreaCodeByNumber($phoneNumber);

        return array_shift(CHighData::GetList(self::HLBT_ID, array('UF_AREA_CODE' => $areaCode)));
    }

    public static function getPhoneByLeadId($leadId)
    {
        CModule::IncludeModule("crm");

        $dbResMultiFields = CCrmFieldMulti::GetList(
            array('ID' => 'desc'),
            array('ENTITY_ID' => 'LEAD', 'TYPE_ID' => 'PHONE', 'ELEMENT_ID' => $leadId)
        );

        return $dbResMultiFields->Fetch()['VALUE'];
    }

    public static function updateLead($leadId, $area = array())
    {
        $res = CCrmLead::GetList(
            array("ID" => "DESC"), // arSort
            array('ID' => $leadId), // arFilter
            array() // arSelect
        );

        $arLead = $res->GetNext();

        if (!empty($area)) {
            if ((empty($arLead[AviviAreaCodeAssigner::STATE_FIELD]) && empty($arLead[AviviAreaCodeAssigner::PROVINCE_FIELD])) || empty($leadId)) {
                if ($area['UF_COUNTRY'] == 'USA') {
                    $arFields[AviviAreaCodeAssigner::STATE_FIELD] = $area['UF_STATE_ID'];
                    $arFields[AviviAreaCodeAssigner::PROVINCE_FIELD] = '';

                } else if ($area['UF_COUNTRY'] == 'CA') {
                    $arFields[AviviAreaCodeAssigner::PROVINCE_FIELD] = $area['UF_STATE_ID'];
                    $arFields[AviviAreaCodeAssigner::STATE_FIELD] = '';
                }
            }

            return $arFields;
        } else {
            $phone = self::getPhoneByLeadId($leadId);
            $area = AviviAreaCodeAssigner::getArea($phone);

            if (!empty($area)) {
                if ((empty($arLead[AviviAreaCodeAssigner::STATE_FIELD]) && empty($arLead[AviviAreaCodeAssigner::PROVINCE_FIELD]))) {
                    if ($area['UF_COUNTRY'] == 'USA') {
                        $arFields[AviviAreaCodeAssigner::STATE_FIELD] = $area['UF_STATE_ID'];
                        $arFields[AviviAreaCodeAssigner::PROVINCE_FIELD] = '';

                    } else if ($area['UF_COUNTRY'] == 'CA') {
                        $arFields[AviviAreaCodeAssigner::PROVINCE_FIELD] = $area['UF_STATE_ID'];
                        $arFields[AviviAreaCodeAssigner::STATE_FIELD] = '';
                    }

                    $entity = new CCrmLead(false);

                    if (!empty($arFields))
                        $entity->update($leadId, $arFields);
                }
            }
        }
    }
}