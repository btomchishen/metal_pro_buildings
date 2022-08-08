<?php // Avivi #48683 Schedule email message

define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;

CModule::IncludeModule('crm');

$response = [
    'status' => 'error'
];


//p($_REQUEST);

$success = AviviScheduleEmailMessage::add_message();
if ($success) {
    $response['status'] = 'success';
}

echo json_encode($response);
