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
} elseif ($request['action'] == 'getHighestId') {
    if (CModule::IncludeModule("tasks")) {

        $res = CTasks::GetList(
            array("ID" => "DESC"), // arSort
            array('!UF_CRM_TASK' => ''), // arFilter
            array('ID') // arSelect
        );

        while ($arTask = $res->GetNext()) {
            $highestID = $arTask['ID'];
            break;
        }

        $result['highestId'] = $highestID;

        echo json_encode($result);
    }
} elseif ($request['action'] == 'update') {
    $startTime = new DateTime('now');
    if (CModule::IncludeModule("tasks")) {
        /**
         * Get last task ID
         */
        $res = CTasks::GetList(
            array("ID" => "DESC"), // arSort
            array('!UF_CRM_TASK' => ''), // arFilter
            array('ID') // arSelect
        );

        while ($arTask = $res->GetNext()) {
            $highestID = $arTask['ID'];
            break;
        }

        if (!empty($highestID)) {
            $lastID = 1;
            $page = 1;

            while ($lastID != $highestID) {
                $res = CTasks::GetList(
                    array("ID" => "ASC"), // arSort
                    array('!UF_CRM_TASK' => ''), // arFilter
                    array('ID', 'TITLE', 'UF_CRM_TASK'), // arSelect
                    array('NAV_PARAMS' =>
                        array(    // Navigation
                            "nPageSize" => 100, // Count of elements on page
                            'iNumPage' => $page
                        )
                    )
                );

                while ($arTask = $res->GetNext()) {
                    $titles = getTitlesForTaskFilter($arTask['UF_CRM_TASK']);
                    $oTaskItem = new CTaskItem($arTask['ID'], $userId);

                    try {
//                    $rs = $oTaskItem->Update(array("UF_TEST" => $titles));
                    } catch (Exception $e) {
                        print('Error');
                    }

                    $lastID = $arTask['ID'];
                }

                $page++;
            }
        }
    }
    $endTime = new DateTime('now');

    $interval = $startTime->diff($endTime);
    echo $interval->format('%S секунд, %f  микросекунд');
} elseif ($request['action'] == 'test') {

}