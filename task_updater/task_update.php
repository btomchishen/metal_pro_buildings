<?php
define('STOP_STATISTICS', true);
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Context;

global $USER;
$userId = $USER->GetID();

$request = Context::getCurrent()->getRequest();

if ($request['action'] == 'getTasksCount') {
    if (CModule::IncludeModule("tasks")) {
        $count = 0;
        $res = CTasks::GetList(
            array("ID" => "DESC"), // arSort
            array('!UF_CRM_TASK' => ''), // arFilter
            array('ID') // arSelect
        );

        while ($arTask = $res->GetNext()) {
            $count++;
        }

        $result['tasksCount'] = $count;

        echo json_encode($result);
    }
} elseif ($request['action'] == 'tasksUpdate') {
    $startTime = new DateTime('now');
    if (CModule::IncludeModule("tasks")) {
        $page = $request['page'];
        $itemsPerPage = $request['itemsPerPage'];

        $res = CTasks::GetList(
            array("ID" => "ASC"), // arSort
            array('!UF_CRM_TASK' => ''), // arFilter
            array('ID', 'TITLE', 'UF_CRM_TASK'), // arSelect
            array('NAV_PARAMS' =>
                array(    // Navigation
                    "nPageSize" => $itemsPerPage, // Count of elements on page
                    'iNumPage' => $page
                )
            )
        );

        while ($arTask = $res->GetNext()) {
            $titles = getTitlesForTaskFilter($arTask['UF_CRM_TASK']);
            $oTaskItem = new CTaskItem($arTask['ID'], $userId);

            try {
                $rs = $oTaskItem->Update(array("UF_TEST" => $titles));
                $result['status'] = 'Success';
            } catch (Exception $e) {
                $result['status'] = $e;
            }

            $lastID = $arTask['ID'];
        }
    }
    $endTime = new DateTime('now');

    $interval = $startTime->diff($endTime);
    $result['time'] = $interval->format('%S s, %f ms');

    $result['page'] = $page;
    $result['lastId'] = $lastID;

    echo json_encode($result);
}