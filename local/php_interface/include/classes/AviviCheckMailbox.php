<?php
// Avivi #48605 Check Mailbox and add to the Company card
class AviviCheckMailbox
{

    protected const LOG_FILE_DIRECTORY = 'local/log/';
    protected const LOG_FILE_NAME = 'check_mailbox.txt';
    protected const MAILBOX_EMAIL = 'bitrix@metalprobuildings.com';
    protected const USER_ID = 1;
    protected const LAST_ID_OPTION_NAME = 'avivi_check_mailbox_last_id';
    protected static $last_id = '';
    protected static $processed_emails = 0;
    protected static $found_in_filter = 0;
    protected static $companies = [];
    protected static $found_company_id = '';
    protected static $HB_companies = [];

    public function RunProcessing() {
        try {
            CModule::IncludeModule("crm");

            self::get_companies();

            self::$last_id = COption::GetOptionString('main', self::LAST_ID_OPTION_NAME);
            if (empty(self::$last_id)) {
                $DATE_INSERT = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:00") . ' - 1 minute'));
                $where_condition = "b_mail_message.DATE_INSERT > '{$DATE_INSERT}'";
            } else {
                $where_condition = "b_mail_message.ID > ".self::$last_id;
            }
            global $DB;
            $MAILBOX_ID = CRM_INCOMING_EMAIL_ACTIVITY_MAILBOX_ID;
            $DB_res = $DB->Query("SELECT b_mail_message.*
FROM b_mail_message
WHERE b_mail_message.MAILBOX_ID = {$MAILBOX_ID}
AND {$where_condition}
ORDER BY b_mail_message.ID ASC
");

            while ($ar_res = $DB_res->fetch()) {
                self::$processed_emails++;
                self::check_email($ar_res);
                self::$last_id = $ar_res['ID'];
            }

            COption::SetOptionString('main', self::LAST_ID_OPTION_NAME, self::$last_id);
            self::add_to_log('Processed emails ' . self::$processed_emails . ' Found in filter ' . self::$found_in_filter . ' Last ID ' . self::$last_id);
        } catch (Exception $e) {
            self::add_to_log($e->getMessage(), true);
        }
        return 'AviviCheckMailbox::RunProcessing();';
    }

    protected function get_companies() {
        self::get_HB_companies();

        foreach (CRM_INCOMING_EMAIL_ACTIVITY_EMAIL_FROM_FILTER as $company_email) {
            if (!isset(self::$HB_companies[$company_email]) || empty(self::$HB_companies[$company_email])) {
                $company_id = self::get_company_id($company_email);
                if ($company_id != false) {
                    self::$companies[$company_email] = $company_id;
                    self::add_HB_company($company_email, $company_id);
                }
            } else {
                self::$companies[$company_email] = self::$HB_companies[$company_email];
            }
        }
    }

    protected function get_HB_companies() {
        $DB_res = CHighData::GetList(HB_CRM_IEA_EMAIL_COMPANY_BIND);
        foreach ($DB_res as $ar_email_company_bind) {
            self::$HB_companies[$ar_email_company_bind['UF_COMPANY_EMAIL']] = $ar_email_company_bind['UF_COMPANY_ID'];
        }
    }

    protected function get_company_id($company_email) {
        $arFilter = [
            'ENTITY_ID' => 'COMPANY',
            'TYPE_ID' => 'EMAIL',
            'VALUE' => $company_email,
        ];
        $arSelectFields = [ 'ELEMENT_ID' ];
        $resFieldMulti = \CCrmFieldMulti::GetListEx([], $arFilter, false, false, $arSelectFields);
        if ($arFieldMulti = $resFieldMulti->fetch()) {
            return $arFieldMulti['ELEMENT_ID'];
        }
        return false;
    }

    protected function add_HB_company($company_email, $company_id) {
        CHighData::AddRecord(HB_CRM_IEA_EMAIL_COMPANY_BIND, [
            'UF_COMPANY_EMAIL' => $company_email,
            'UF_COMPANY_ID' => $company_id,
        ]);
    }

    protected function check_email($email) {
        self::$found_company_id = '';
        if (self::need_to_add($email)) {
            self::$found_in_filter++;
            self::add_email_to_CRM($email);
        }
    }

    protected function add_email_to_CRM($email) {
        $OWNER_ID = self::$found_company_id;
        if (!empty($OWNER_ID)) {
            $OWNER_TYPE_ID = CCrmOwnerType::Company;
            $new_act_data = [
                'TYPE_ID' => 4,
                'PROVIDER_ID' => 'CRM_EMAIL',
                'PROVIDER_TYPE_ID' => 'EMAIL',
                'PROVIDER_GROUP_ID' => '',
                'OWNER_ID' => $OWNER_ID,
                'OWNER_TYPE_ID' => $OWNER_TYPE_ID,
                'ASSOCIATED_ENTITY_ID' => 0,
                'CALENDAR_EVENT_ID' => 0,
                'SUBJECT' => $email['SUBJECT'],
                'IS_HANDLEABLE' => 'Y',
                'COMPLETED' => 'N',
                'STATUS' => 1,
                'RESPONSIBLE_ID' => '',
                'PRIORITY' => 2,
                'NOTIFY_TYPE' => 0,
                'NOTIFY_VALUE' => 0,
                'DESCRIPTION' => $email['BODY_HTML'],
                'DESCRIPTION_TYPE' => 3,
                'DIRECTION' => 1,
                'LOCATION' => '',
                'STORAGE_TYPE_ID' => 3,
                'STORAGE_ELEMENT_IDS' => serialize([]),
                'PARENT_ID' => '',
                'THREAD_ID' => '',
                'URN' => '',
                'SETTINGS' => serialize([
                    'EMAIL_META' => [
                        '__email' => self::MAILBOX_EMAIL,
                        'from' => $email['FIELD_FROM'],
                        'replyTo' => $email['FIELD_REPLY_TO'],
                        'to' => $email['FIELD_TO'],
                        'cc' => $email['FIELD_CC'],
                        'bcc' => $email['FIELD_BCC'],
                    ]
                ]),
                'ORIGINATOR_ID' => '',
                'ORIGIN_ID' => '',
                'AUTHOR_ID' => self::USER_ID,
                'EDITOR_ID' => self::USER_ID,
                'PROVIDER_PARAMS' => '',
                'PROVIDER_DATA' => '',
                'SEARCH_CONTENT' => '',
                'RESULT_STATUS' => 0,
                'RESULT_STREAM' => 0,
                'RESULT_SOURCE_ID' => '',
                'RESULT_MARK' => 0,
                'RESULT_VALUE' => '',
                'RESULT_SUM' => '',
                'RESULT_CURRENCY_ID' => '',
                'AUTOCOMPLETE_RULE' => 0,
            ];

            global $DB;
            $arInsert = $DB->PrepareInsert("b_crm_act", $new_act_data);
            $strSql = "INSERT INTO b_crm_act (" . $arInsert[0] . ", `CREATED`, `LAST_UPDATED`, `START_TIME`, `END_TIME`, `DEADLINE`) " .
                "VALUES(" . $arInsert[1] . ", '" . $email['FIELD_DATE'] . "', '" . $email['FIELD_DATE'] . "', '" . $email['FIELD_DATE'] . "', '" . $email['FIELD_DATE'] . "', '" . $email['FIELD_DATE'] . "')";
            $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__); // Timeline new record
            $ACTIVITY_ID = intval($DB->LastID());

            if ($ACTIVITY_ID != 0) {
                self::add_to_log('Activity ID ' . $ACTIVITY_ID . ' Company ID ' . $OWNER_ID);
                $strSql = "UPDATE b_crm_act SET THREAD_ID={$ACTIVITY_ID} WHERE ID={$ACTIVITY_ID}";
                $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);

                $new_act_bind_data = [
                    'ACTIVITY_ID' => $ACTIVITY_ID,
                    'OWNER_ID' => $OWNER_ID,
                    'OWNER_TYPE_ID' => $OWNER_TYPE_ID,
                ];
                $arInsert = $DB->PrepareInsert("b_crm_act_bind", $new_act_bind_data);
                $strSql = "INSERT INTO b_crm_act_bind (" . $arInsert[0] . ") " .
                    "VALUES(" . $arInsert[1] . ")";
                $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__); // Timeline new bind to entity

                $VALUE = $email['FIELD_TO'];
                $ENTITY_SETTINGS = [
                    'HONORIFIC' => '',
                    'NAME' => $VALUE,
                    'SECOND_NAME' => '',
                    'LAST_NAME' => '',
                ];

                $ar_comm_res = [
                    'ACTIVITY_ID' => $ACTIVITY_ID,
                    'ENTITY_ID' => $OWNER_ID,
                    'ENTITY_TYPE_ID' => $OWNER_TYPE_ID,
                    'OWNER_ID' => $OWNER_ID,
                    'OWNER_TYPE_ID' => $OWNER_TYPE_ID,
                    'TYPE' => 'EMAIL',
                    'VALUE' => $VALUE,
                    'ENTITY_SETTINGS' => serialize($ENTITY_SETTINGS),
                ];
                $arInsert = $DB->PrepareInsert("b_crm_act_comm", $ar_comm_res);
                $strSql = "INSERT INTO b_crm_act_comm (" . $arInsert[0] . ") " .
                    "VALUES(" . $arInsert[1] . ")";
                $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
            }
        }
    }

    protected function need_to_add($email) {
        foreach (self::$companies as $company_email => $company_id) {
            foreach (CRM_INCOMING_EMAIL_ACTIVITY_EMAIL_FROM_FILTER_FIELDS as $filter_field) {
                if (!empty($email[$filter_field])
                    && strpos($email[$filter_field], $company_email) !== false
                ) {
                    self::$found_company_id = $company_id;
                    self::add_to_log('Found email ' . $filter_field . ' ' . $email[$filter_field]);
                    return true;
                }
            }
        }
        return false;
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