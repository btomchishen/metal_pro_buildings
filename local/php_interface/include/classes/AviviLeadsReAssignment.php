<?php // #21674 Leads Re-assignment

class AviviLeadsReAssignment {

    protected static $debug = false;
    protected const LOG_FILE_DIRECTORY = 'local/log/';
    protected const LOG_FILE_NAME = 'leads_re_assignment.txt';
    protected static $mysql_format = 'Y-m-d H:i:s';
    protected static $tag_field_name = 'UF_CRM_1627068242';
    protected static $tag_field_value = 488; // DO NOT RECYCLE
    protected static $users = [];
    protected static $change_status = [
        '34C5C727', // Hot,
        '20501075', // Still Working,
        '80AF338', // Follow Up,
    ];
    protected static $new_status = '6AEE0B23'; // Aging
    protected static $search_period = '6'; // In month
    protected static $limit = 1000; // Limit leads at one run

    public static function Debug() {
        self::$debug = true;
        p('Debug!');
        self::RunCheck();
    }

    public static function RunCheck() {
        $check_date = date(self::$mysql_format, strtotime('- '.self::$search_period.' month'));
        $update_date = date(self::$mysql_format);
        $status_list = self::getLeadStatusList();
        $status_in = '"' . implode('","', $status_list) . '"';
        $tag_field_id = self::GetUserFieldID(self::$tag_field_name, 'CRM_LEAD');
        $tag_field_value = self::$tag_field_value;
        $limit = self::$limit;

        global $DB;

        $DB_res = $DB->Query("SELECT b_crm_lead.ID,
b_crm_lead.ASSIGNED_BY_ID,
b_crm_lead.STATUS_ID,
b_crm_lead.TITLE,
(SELECT b_crm_event_new.DATE_CREATE
    FROM b_crm_event_relations AS b_crm_event_relations_new
    LEFT JOIN b_crm_event AS b_crm_event_new ON b_crm_event_new.ID = b_crm_event_relations_new.EVENT_ID AND b_crm_event_new.DATE_CREATE > '{$check_date}'
    WHERE b_crm_event_relations_new.ENTITY_ID = b_crm_lead.ID AND b_crm_event_relations_new.ENTITY_TYPE = 'LEAD' AND b_crm_event_relations_new.ENTITY_FIELD = 'ASSIGNED_BY_ID'
    ORDER BY b_crm_event_new.DATE_CREATE DESC
    LIMIT 1) AS DATE_CREATE_NEW
FROM b_crm_lead
LEFT JOIN b_crm_event_relations ON b_crm_event_relations.ENTITY_ID = b_crm_lead.ID AND b_crm_event_relations.ENTITY_TYPE = 'LEAD' AND b_crm_event_relations.ENTITY_FIELD = 'ASSIGNED_BY_ID'
LEFT JOIN b_crm_event ON b_crm_event.ID = b_crm_event_relations.EVENT_ID
LEFT JOIN b_utm_crm_lead ON b_utm_crm_lead.VALUE_ID = b_crm_lead.ID AND b_utm_crm_lead.FIELD_ID = {$tag_field_id}
WHERE b_crm_lead.STATUS_ID IN({$status_in})
AND b_crm_lead.ASSIGNED_BY_ID != 1
AND b_crm_lead.ASSIGNED_BY_ID = 66
AND b_crm_event.DATE_CREATE < '{$check_date}'
AND (b_utm_crm_lead.VALUE_INT != {$tag_field_value} OR b_utm_crm_lead.VALUE_INT IS NULL)
GROUP BY b_crm_lead.ID
HAVING DATE_CREATE_NEW IS NULL
ORDER BY b_crm_lead.ID DESC, b_crm_event.DATE_CREATE DESC
LIMIT {$limit}
");

        while ($ar_res = $DB_res->fetch()) {

            // Log processing lead ID
            $LEAD_ID = $ar_res['ID'];

            if (self::$debug) {
                echo '<p><a href="https://dev.metalpro.site/crm/lead/details/'.$LEAD_ID.'/" target="_blank">'.$ar_res['TITLE'].'</a></p>';
            }

            $status_update = '';
            if (in_array($ar_res['STATUS_ID'], self::$change_status)) {
                // Change status if needed
                $new_status = self::$new_status;
                $status_update = ", STATUS_ID = '{$new_status}'";
            }

            if (!self::$debug) {
                self::add_to_log('Lead ID: ' . $LEAD_ID);
                $DB->Query("UPDATE b_crm_lead
SET b_crm_lead.ASSIGNED_BY_ID = 1 {$status_update}, b_crm_lead.DATE_MODIFY = '{$update_date}', b_crm_lead.MODIFY_BY_ID = 1
WHERE b_crm_lead.ID = {$LEAD_ID}");

                // Add the history record with responsible change event
                $event_data = [
                    'DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
                    'CREATED_BY_ID' => 1,
                    'EVENT_NAME' => 'The "Responsible Person" field was modified.',
                    'EVENT_TEXT_1' => self::GetUserName($ar_res['ASSIGNED_BY_ID']),
                    'EVENT_TEXT_2' => self::GetUserName('1'),
                    'EVENT_TYPE' => 1,
                ];
                $arInsert = $DB->PrepareInsert('b_crm_event', $event_data);
                $strSql = "INSERT INTO b_crm_event (" . $arInsert[0] . ") " .
                    "VALUES(" . $arInsert[1] . ")";
                $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
                $EVENT_ID = intval($DB->LastID());
                if (!empty($EVENT_ID)) {
                    $event_relations_data = [
                        'ASSIGNED_BY_ID' => 1,
                        'ENTITY_TYPE' => 'LEAD',
                        'ENTITY_ID' => $LEAD_ID,
                        'ENTITY_FIELD' => 'ASSIGNED_BY_ID',
                        'EVENT_ID' => $EVENT_ID,
                    ];
                    $arInsert = $DB->PrepareInsert('b_crm_event_relations', $event_relations_data);
                    $strSql = "INSERT INTO b_crm_event_relations (" . $arInsert[0] . ") " .
                        "VALUES(" . $arInsert[1] . ")";
                    $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
                }

                // Add a comment to the lead
                $timeline_data = [
                    'TYPE_ID' => 7,
                    'TYPE_CATEGORY_ID' => 0,
                    'CREATED' => new \Bitrix\Main\Type\DateTime(),
                    'AUTHOR_ID' => 1,
                    'ASSOCIATED_ENTITY_ID' => 0,
                    'ASSOCIATED_ENTITY_TYPE_ID' => 0,
                    'ASSOCIATED_ENTITY_CLASS_NAME' => '',
                    'COMMENT' => 'Lead recycled due to Inactivity for ' . self::$search_period . ' months',
                    'SETTINGS' => [],
                ];
                $timeline_insert = $DB->PrepareInsert("b_crm_timeline", $timeline_data);
                $strSql = "INSERT INTO b_crm_timeline (" . $timeline_insert[0] . ") " .
                    "VALUES(" . $timeline_insert[1] . ")";
                $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
                $OWNER_ID = intval($DB->LastID());

                if ($OWNER_ID != 0) {
                    $timeline_bind_data = [
                        'OWNER_ID' => $OWNER_ID,
                        'ENTITY_ID' => $LEAD_ID,
                        'ENTITY_TYPE_ID' => 1,
                    ];
                    $timeline_bind_insert = $DB->PrepareInsert("b_crm_timeline_bind", $timeline_bind_data);
                    $strSql = "INSERT INTO b_crm_timeline_bind (" . $timeline_bind_insert[0] . ") " .
                        "VALUES(" . $timeline_bind_insert[1] . ")";
                    $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);

                    $response['status'] = 'success';
                }
            }
//            echo $LEAD_ID;
        }

        return 'AviviLeadsReAssignment::RunCheck();';
    }

    protected static function GetUserName($user_id) {
        if (!empty(self::$users[$user_id])) {
            return self::$users[$user_id];
        } else {
            $arFilter = [
                'ID' => $user_id
            ];
            $arParams = [];
            $resUsers = \CUser::GetList('', '', $arFilter, $arParams);
            $name = '';
            if ($arUser = $resUsers->Fetch()) {
                $name = $arUser['NAME'] . ' ' . $arUser['LAST_NAME'];
                self::$users[$user_id] = $name;
            }
            return $name;
        }
    }

    protected static function getLeadStatusList() {
        $list = [];
        \Bitrix\Main\Loader::includeModule('crm');
        $statuses = \CCrmLead::GetStatuses();
        foreach ($statuses as $status) {
            if ($status['SYSTEM'] === 'N') {
                $list[] = $status['STATUS_ID'];
            }
        }
        return $list;
    }

    public static function GetUserFieldID($userFieldName, $entityID) {
        $userFieldID = '';
        if (!empty($userFieldName) && !empty($entityID)) {
            global $DB;
            $query = "SELECT ID FROM b_user_field WHERE ENTITY_ID = '{$entityID}' AND FIELD_NAME = '{$userFieldName}' LIMIT 1";
            $dbResult = $DB->Query($query);
            if($arResult = $dbResult->Fetch()) {
                $userFieldID = $arResult['ID'];
            }
        }
        return $userFieldID;
    }

    protected function add_to_log($message, $error = false) {
        if (gettype($message !== 'string')) {
            $message = print_r($message, true);
        }
        $log_message = date("Y-m-d H:i:s") . ' ';
        if ($error) {
            $log_message.= 'Error ';
        }
        $log_message.= $message.PHP_EOL;
        $log_file_location = $_SERVER['DOCUMENT_ROOT'].'/'.self::LOG_FILE_DIRECTORY;
        $log_file_location.= '/'.date("Y");
        if (!file_exists($log_file_location)) {
            mkdir($log_file_location);
        }
        $log_file_location.= '/'.date("m");
        if (!file_exists($log_file_location)) {
            mkdir($log_file_location);
        }
        $log_file_location.= '/'.date("d");
        if (!file_exists($log_file_location)) {
            mkdir($log_file_location);
        }
        $log_file_location.= '/'.self::LOG_FILE_NAME;
        file_put_contents($log_file_location, $log_message, FILE_APPEND);
    }

}
