<?php
define('STOP_STATISTICS', true);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

global $USER;
$result['userId'] = $USER->GetId();
$result['departments'][SALES_DEPARTMENT] = getUsersByDepartmentId(SALES_DEPARTMENT);

echo json_encode($result);