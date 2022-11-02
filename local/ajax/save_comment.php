<? // Avivi #19996 Saving Comment to Lead card
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

$response = [
    'status' => 'error'
];

if (!empty($_REQUEST['lead_id'])
    && !empty($_REQUEST['comment'])
) {
    global $DB;
    CModule::IncludeModule('crm');
    $curUser = CCrmSecurityHelper::GetCurrentUser();
    $AUTHOR_ID = $curUser->getId();
    $date_time_format = \Bitrix\Main\Type\DateTime::getFormat();
    $CREATED = date($date_time_format);
    $serverZone = COption::GetOptionString("main", "default_time_zone");
    $location_date_time = new DateTime($CREATED, $date_time_format);
    $location_date_time_zone = new DateTimeZone($serverZone);
    $location_date_time->setTimeZone($location_date_time_zone);
    $CREATED = $location_date_time->format($date_time_format);

    $b_crm_timeline_data = [
        'TYPE_ID' => 7,
        'TYPE_CATEGORY_ID' => 0,
        'CREATED' => $CREATED,
        'AUTHOR_ID' => $AUTHOR_ID,
        'ASSOCIATED_ENTITY_ID' => 0,
        'ASSOCIATED_ENTITY_TYPE_ID' => 0,
        'ASSOCIATED_ENTITY_CLASS_NAME' => '',
        'COMMENT' => $_REQUEST['comment'],
        'SETTINGS' => [],
    ];
    $b_crm_timeline_insert = $DB->PrepareInsert("b_crm_timeline", $b_crm_timeline_data);
    $strSql = "INSERT INTO b_crm_timeline (".$b_crm_timeline_insert[0].") ".
        "VALUES(".$b_crm_timeline_insert[1].")";
    $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
    $OWNER_ID = intval($DB->LastID());

    if ($OWNER_ID != 0) {
        $b_crm_timeline_bind_data = [
            'OWNER_ID' => $OWNER_ID,
            'ENTITY_ID' => $_REQUEST['lead_id'],
            'ENTITY_TYPE_ID' => 1,
        ];
        $b_crm_timeline_bind_insert = $DB->PrepareInsert("b_crm_timeline_bind", $b_crm_timeline_bind_data);
        $strSql = "INSERT INTO b_crm_timeline_bind (" . $b_crm_timeline_bind_insert[0] . ") " .
            "VALUES(" . $b_crm_timeline_bind_insert[1] . ")";
        $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);

        $response['status'] = 'success';
    }
}

echo json_encode($response);
