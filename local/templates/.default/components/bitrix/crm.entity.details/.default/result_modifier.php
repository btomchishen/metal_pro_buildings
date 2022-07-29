<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Main\Entity;

$BITRIX_DATETIME_FORMAT = 'm/d/Y h:i:s a';
$now = new DateTime();
$activeYear = $now->format('Y');
$activeMonth = $now->format('m');
$activeDay = $now->format('d');


$dateBegin = new DateTime(sprintf('%1$04d-%2$02d-%3$02d 00:00:00', $activeYear, $activeMonth, $activeDay), new DateTimeZone('UTC'));
$dateBegin->modify('+3 hour');
$dateEnd = clone $dateBegin;
$dateEnd->modify('+1 day -1 second');

$Link_next = '';
$Link_previous = '';

if($arResult['ENTITY_INFO']['ENTITY_TYPE_NAME'] == 'LEAD')
{   
    $filterOption = new Bitrix\Main\UI\Filter\Options('CRM_LEAD_LIST_V12');
    $filterData = $filterOption->getFilter();

    foreach ($filterData as $field => $data) {
        switch ($field) 
        {
            case 'STATUS_ID':
            $filter[$field] = $data;
            break;

            case 'ASSIGNED_BY_ID':
            $filter[$field] = $data;
            break;

            case 'STATUS_SEMANTIC_ID':
            $filter[$field] = $data;
            break;

            case 'COMMUNICATION_TYPE':
            $filter[$field] = $data;
            break;

            case 'UF_CRM_1627068242': // Tag
            $filter[$field] = $data;
            break;

            case 'UF_CRM_6AC1ECC9': // Lead Number
            $filter[$field] = $data;
            break;

            case 'UF_CRM_1635836862': // Last comment
            $filter[$field] = '%'.$data.'%';
            break;

            case 'ID': 
            $filter[$field] = $data;
            break;

            case 'SOURCE_ID': // Source ID
            $filter[$field] = $data;
            break;

            case 'TITLE':
            $filter[$field] = $data;
            break;

            case 'NAME':
            $filter[$field] = $data;
            break;

            case 'SECOND_NAME':
            $filter[$field] = $data;
            break;

            case 'LAST_NAME':
            $filter[$field] = $data;
            break;

            case 'BIRTHDATE_datesel':
            if(!empty($filterData['BIRTHDATE_from']))
                $filter['>=BIRTHDATE'] = ConvertDateTime($filterData['BIRTHDATE_from'], FORMAT_DATETIME);
            if(!empty($filterData["BIRTHDATE_to"]))
                $filter['<=BIRTHDATE'] = ConvertDateTime($filterData['BIRTHDATE_to'], FORMAT_DATETIME);
            break;

            case 'DATE_CREATE_datesel':
            if(!empty($filterData['DATE_CREATE_from']))
                $filter['>=DATE_CREATE'] = ConvertDateTime($filterData['DATE_CREATE_from'], FORMAT_DATETIME);
            if(!empty($filterData["DATE_CREATE_to"]))
                $filter['<=DATE_CREATE'] = ConvertDateTime($filterData['DATE_CREATE_to'], FORMAT_DATETIME);
            break;

            case 'DATE_MODIFY_datesel':
            if(!empty($filterData['DATE_MODIFY_from']))
                $filter['>=DATE_MODIFY'] = ConvertDateTime($filterData['DATE_MODIFY_from'], FORMAT_DATETIME);
            if(!empty($filterData["DATE_MODIFY_to"]))
                $filter['<=DATE_MODIFY'] = ConvertDateTime($filterData['DATE_MODIFY_to'], FORMAT_DATETIME);
            break;

            case 'CURRENCY_ID': // Currency
            $filter[$field] = $data;
            break;

            case 'CREATED_BY_ID': 
            $filter[$field] = $data;
            break;

            case 'CONTACT_ID': 
            $filter[$field] = json_decode($data, true)['CONTACT'];
            break;

            case 'COMPANY_ID': 
            $filter[$field] = json_decode($data, true)['COMPANY'];
            break;

            case 'COMPANY_TITLE': 
            $filter[$field] = $data;
            break;

            case 'POST': 
            $filter[$field] = $data;
            break;

            case 'ADDRESS': 
            $filter[$field] = $data;
            break;

            case 'ADDRESS_PROVINCE': 
            $filter[$field] = '%'.$data.'%';
            break;

            case 'COMMENTS': 
            $filter[$field] = $data;
            break;

            case 'ACTIVITY_COUNTER': 
            $filter[$field] = $data;
            break;

            default:
            // code...
            break;
        }
    }

    if($filter['ADDRESS_PROVINCE'])
    {
        $result = \Bitrix\Crm\AddressTable::getList(
            array(
                'order' => array('ENTITY_ID' => 'ASC'),
                'filter' => array(
                    'ENTITY_TYPE_ID' => 1, 
                    array(
                        'LOGIC' => 'OR',
                        array(
                            'LOGIC' => 'AND',
                            'REGION' => $filter['ADDRESS_PROVINCE'],
                            'PROVINCE' => $filter['ADDRESS_PROVINCE'],
                        ),
                        array(
                            'LOGIC' => 'AND',
                            'REGION' => '',
                            'PROVINCE' => $filter['ADDRESS_PROVINCE'],
                        ),
                    ),
                ),
                'select' => array('*')
            )
        );

        while($item = $result->fetch())
        {
            $idWithRegionFilter[] = $item['ENTITY_ID'];
        }
    }

    if($filter['ACTIVITY_COUNTER'])
    {
        if($filter['ACTIVITY_COUNTER'][0] == 2){ // For today
            if($idWithRegionFilter){
                $dbRes = CCrmActivity::GetList(array(), 
                    array(
                        'OWNER_ID' => $idWithRegionFilter,
                        'OWNER_TYPE_ID' => 1,
                        'COMPLETED' => 'N',
                        '>DEADLINE' => $dateBegin->format($BITRIX_DATETIME_FORMAT),
                        '<DEADLINE' => $dateEnd->format($BITRIX_DATETIME_FORMAT)
                    ), false, false, array('*')
                );
            } else {
                $dbRes = CCrmActivity::GetList(array(), 
                    array(
                        'OWNER_TYPE_ID' => 1,
                        'COMPLETED' => 'N',
                        '>DEADLINE' => $dateBegin->format($BITRIX_DATETIME_FORMAT),
                        '<DEADLINE' => $dateEnd->format($BITRIX_DATETIME_FORMAT)
                    ), false, false, array('*')
                );
            }

            while($arRes = $dbRes->fetch()){
                if(in_array($arRes['OWNER_ID'], $idWithRegionAndActivityFilter))continue;
                $idWithRegionAndActivityFilter[] = $arRes['OWNER_ID'];
            }
        }


        if($filter['ACTIVITY_COUNTER'][0] == 4){ // Overdue
            if($idWithRegionFilter){
                $dbRes = CCrmActivity::GetList(array(), 
                    array(
                        'OWNER_ID' => $idWithRegionFilter,
                        'OWNER_TYPE_ID' => 1,
                        'COMPLETED' => 'N',
                        '!=TYPE_ID' => 6,
                        '<DEADLINE' => $dateBegin->format($BITRIX_DATETIME_FORMAT),
                    ), false, false, array('*')
                );
            } else {
                $dbRes = CCrmActivity::GetList(array(), 
                    array(
                        'OWNER_TYPE_ID' => 1,
                        'COMPLETED' => 'N',
                        '!=TYPE_ID' => 6,
                        '<DEADLINE' => $dateBegin->format($BITRIX_DATETIME_FORMAT),
                    ), false, false, array('*')
                );
            }

            while($arRes = $dbRes->fetch()){
                if(in_array($arRes['OWNER_ID'], $idWithRegionAndActivityFilter))continue;
                $idWithRegionAndActivityFilter[] = $arRes['OWNER_ID'];
            }
        }
    }

    if($idWithRegionAndActivityFilter){
        $filter['ID'] = $idWithRegionAndActivityFilter;
    } else if($idWithRegionFilter){
        $filter['ID'] = $idWithRegionFilter;
    }

    $rsLead = CCrmLead::GetList(
        array('ID' => 'DESC'),
        array($filter),
        array()
    );

    $idArray = [];
    while($item = $rsLead->fetch())
    {
        $idArray[] = $item['ID'];
    }

    $curId = $arResult['ENTITY_ID'];
    $maxId = array_search(max($idArray), $idArray);
    $minId = array_search(min($idArray), $idArray);
    $curIdInArray = array_search($curId, $idArray);

    if($curIdInArray != $maxId && $curIdInArray != $minId){
        $Next_Lead_ID = $idArray[$curIdInArray + 1];
        $Previous_Lead_ID = $idArray[$curIdInArray - 1];
    } else if($curIdInArray == $minId){
        $Next_Lead_ID = $idArray[$maxId];
        $Previous_Lead_ID = $idArray[$curIdInArray - 1];
    } else if($curIdInArray == $maxId){
        $Next_Lead_ID = $idArray[$curIdInArray + 1];
        $Previous_Lead_ID = $idArray[$minId];
    }

    $Link_next = '/crm/lead/details/'.$Next_Lead_ID.'/';
    $Link_previous = '/crm/lead/details/'.$Previous_Lead_ID.'/';
    $arResult['CUSTOM_LINK_NEXT'] = $Link_next;
    $arResult['CUSTOM_LINK_PREVIOUS'] = $Link_previous;


}
elseif ($arResult['ENTITY_INFO']['ENTITY_TYPE_NAME'] == 'DEAL')
{
    $filterOption = new Bitrix\Main\UI\Filter\Options('CRM_DEAL_LIST_V12_C_0');
    $filterData = $filterOption->getFilter();
    
    $Next_Deal_ID = 0;
    $rsDeal = CCrmDeal::GetList(
        array('ID' => 'ASC'),
        array('>ID' => $arResult['ENTITY_ID']),
        array('ID'),
        1);
    if($arTMPDeal = $rsDeal->Fetch())
    {
        $Next_Deal_ID = $arTMPDeal['ID'];
    }
    else
    {
        $rsDeal = CCrmDeal::GetList(
            array('ID' => 'ASC'),
            array('<ID' => $arResult['ENTITY_ID']),
            array('ID'),
            1);
        if($arTMPDeal = $rsDeal->Fetch())
        {
            $Next_Deal_ID = $arTMPDeal['ID'];
        }
    }

    $Previous_Deal_ID = 0;
    $rsDeal = CCrmDeal::GetList(
        array('ID' => 'DESC'),
        array('<ID' => $arResult['ENTITY_ID']),
        array('ID'),
        1);
    if($arTMPDeal = $rsDeal->Fetch())
    {
        $Previous_Deal_ID = $arTMPDeal['ID'];
    }
    else
    {
        $rsDeal = CCrmDeal::GetList(
            array('ID' => 'DESC'),
            array('>ID' => $arResult['ENTITY_ID']),
            array('ID'),
            1);
        if($arTMPDeal = $rsDeal->Fetch())
        {
            $Previous_Deal_ID = $arTMPDeal['ID'];
        }
    }

    $Link_next = '/crm/deal/details/'.$Next_Deal_ID.'/';
    $Link_previous = '/crm/deal/details/'.$Previous_Deal_ID.'/';
    $arResult['CUSTOM_LINK_NEXT'] = $Link_next;
    $arResult['CUSTOM_LINK_PREVIOUS'] = $Link_previous;
}
elseif ($arResult['ENTITY_INFO']['ENTITY_TYPE_NAME'] == 'CONTACT')
{
    $Next_Contact_ID = 0;
    $rsContact = CCrmContact::GetList(
        array('ID' => 'ASC'),
        array('>ID' => $arResult['ENTITY_ID']),
        array('ID'),
        1);
    if($arTMPContact = $rsContact->Fetch())
    {
        $Next_Contact_ID = $arTMPContact['ID'];
    }
    else
    {
        $rsContact = CCrmContact::GetList(
            array('ID' => 'ASC'),
            array('<ID' => $arResult['ENTITY_ID']),
            array('ID'),
            1);
        if($arTMPContact = $rsContact->Fetch())
        {
            $Next_Contact_ID = $arTMPContact['ID'];
        }
    }

    $Previous_Contact_ID = 0;
    $rsContact = CCrmContact::GetList(
        array('ID' => 'DESC'),
        array('<ID' => $arResult['ENTITY_ID']),
        array('ID'),
        1);
    if($arTMPContact = $rsContact->Fetch())
    {
        $Previous_Contact_ID = $arTMPContact['ID'];
    }
    else
    {
        $rsContact = CCrmContact::GetList(
            array('ID' => 'DESC'),
            array('>ID' => $arResult['ENTITY_ID']),
            array('ID'),
            1);
        if($arTMPContact = $rsContact->Fetch())
        {
            $Previous_Contact_ID = $arTMPContact['ID'];
        }
    }

    $Link_next = '/crm/contact/details/'.$Next_Contact_ID.'/';
    $Link_previous = '/crm/contact/details/'.$Previous_Contact_ID.'/';
    $arResult['CUSTOM_LINK_NEXT'] = $Link_next;
    $arResult['CUSTOM_LINK_PREVIOUS'] = $Link_previous;
}