<?php
define('STOP_STATISTICS', true);
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Context;

global $USER;
$userId = $USER->GetID();

$request = Context::getCurrent()->getRequest();

if ($request['action'] == 'getLeadsCount') {
    if (CModule::IncludeModule("crm")) {
        $count = 0;
        $res = CCrmLead::GetList(
            array("ID" => "DESC"), // arSort
            array(), // arFilter
            array('ID') // arSelect
        );

        while ($arLead = $res->GetNext()) {
            $count++;
        }

        $result['leadsCount'] = $count;

        echo json_encode($result);
    }
} elseif ($request['action'] == 'leadsUpdate') {
    $startTime = new DateTime('now');
    if (CModule::IncludeModule("crm")) {
        $page = $request['page'];
        $itemsPerPage = $request['itemsPerPage'];

        $res = CCrmLead::GetListEx(
            array("ID" => "ASC"), // arSort
            array(), // arFilter
            false,
            array(
                "nPageSize" => $itemsPerPage, // Count of elements on page
                'iNumPage' => $page
            ),
            array('ID') // arSelect
        );

        while ($arLead = $res->GetNext()) {
            $result['ids'][] = $arLead['ID'];
            try {
                AviviAreaCodeAssigner::updateLead($arLead['ID']);
                $result['status'] = 'Success';
            } catch (Exception $e) {
                $result['status'] = $e;
            }

            $lastID = $arLead['ID'];
        }
    }
    $endTime = new DateTime('now');

    $interval = $startTime->diff($endTime);
    $result['time'] = $interval->format('%S s, %f ms');

    $result['page'] = $page;
    $result['lastId'] = $lastID;

    echo json_encode($result);
}