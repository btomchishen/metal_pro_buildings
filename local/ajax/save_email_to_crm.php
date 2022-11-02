<? // Avivi #48557 Save email to CRM
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Loader;

CModule::IncludeModule('crm');

$response = [
    'status' => 'error'
];

if (!empty($_REQUEST['email_id'])
    && !empty($_REQUEST['entity_ids'])
) {
    $direction = (isset($_REQUEST['email_outcome']) && $_REQUEST['email_outcome'] === '1') ? 2 : 1;

    global $DB;

    // Remove commented code at top of this line !

    $DB_res = $DB->Query("SELECT b_mail_message.*
FROM b_mail_message
WHERE b_mail_message.ID = {$_REQUEST['email_id']}");

    if ($ar_mail_message = $DB_res->fetch()) {

        $mailbox_email = '';
        $DB_res = $DB->Query("SELECT *
FROM b_mail_mailbox
WHERE ID = {$ar_mail_message['MAILBOX_ID']}");
        if ($ar_mailbox = $DB_res->fetch()) {
            $mailbox_email = $ar_mailbox['EMAIL'];
        }

        $new_act_data = [
            'TYPE_ID' => 4,
            'PROVIDER_ID' => 'CRM_EMAIL',
            'PROVIDER_TYPE_ID' => 'EMAIL',
            'PROVIDER_GROUP_ID' => '',
            'OWNER_ID' => '',
            'OWNER_TYPE_ID' => '',
            'ASSOCIATED_ENTITY_ID' => 0,
            'CALENDAR_EVENT_ID' => 0,
            'SUBJECT' => $ar_mail_message['SUBJECT'],
            'IS_HANDLEABLE' => 'Y',
            'COMPLETED' => 'N',
            'STATUS' => 1,
            'RESPONSIBLE_ID' => '',
            'PRIORITY' => 2,
            'NOTIFY_TYPE' => 0,
            'NOTIFY_VALUE' => 0,
            'DESCRIPTION' => $ar_mail_message['BODY_HTML'],
            'DESCRIPTION_TYPE' => 3,
            'DIRECTION' => $direction,
            'LOCATION' => '',
            'STORAGE_TYPE_ID' => 3,
            'STORAGE_ELEMENT_IDS' => serialize([]),
            'PARENT_ID' => '',
            'THREAD_ID' => '',
            'URN' => '',
            'SETTINGS' => serialize([
                'EMAIL_META' => [
                    '__email' => $mailbox_email,
                    'from' => $ar_mail_message['FIELD_FROM'],
                    'replyTo' => $ar_mail_message['FIELD_REPLY_TO'],
                    'to' => $ar_mail_message['FIELD_TO'],
                    'cc' => $ar_mail_message['FIELD_CC'],
                    'bcc' => $ar_mail_message['FIELD_BCC'],
                ]
            ]),
            'ORIGINATOR_ID' => '',
            'ORIGIN_ID' => '',
            'AUTHOR_ID' => $GLOBALS["USER"]->GetID(),
            'EDITOR_ID' => $GLOBALS["USER"]->GetID(),
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

        if ($direction === 2) {
            // this part does not work, 0 ideas why...
//            $new_act_data['IS_HANDLEABLE'] = 'N';
//            $new_act_data['COMPLETED'] = 'Y';
//            $new_act_data['STATUS'] = 2;
        }

        foreach ($_REQUEST['entity_ids'] as $input_entity_id) {
            $entity_parts = explode('_', $input_entity_id);
            if (count($entity_parts) === 2) {
                $entity_id = $entity_parts[1];
                $entity_type = $entity_parts[0];
                $entity_type_id = false;
                if ($entity_type === 'C') {
                    $entity_type_id = CCrmOwnerType::Contact;
                } else if ($entity_type === 'L') {
                    $entity_type_id = CCrmOwnerType::Lead;
                } else if ($entity_type === 'D') {
                    $entity_type_id = CCrmOwnerType::Deal;
                }
                if ($entity_type_id !== false) {

                    $new_act_data['OWNER_ID'] = $entity_id;
                    $new_act_data['OWNER_TYPE_ID'] = $entity_type_id;

                    $arInsert = $DB->PrepareInsert("b_crm_act", $new_act_data);
                    $strSql = "INSERT INTO b_crm_act (".$arInsert[0].", `CREATED`, `LAST_UPDATED`, `START_TIME`, `END_TIME`, `DEADLINE`) ".
                        "VALUES(".$arInsert[1].", '".$ar_mail_message['FIELD_DATE']."', '".$ar_mail_message['FIELD_DATE']."', '".$ar_mail_message['FIELD_DATE']."', '".$ar_mail_message['FIELD_DATE']."', '".$ar_mail_message['FIELD_DATE']."')";
                    $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__); // Timeline new record
                    $ACTIVITY_ID = intval($DB->LastID());

                    if ($ACTIVITY_ID != 0) {
                        $strSql = "UPDATE b_crm_act SET THREAD_ID={$ACTIVITY_ID} WHERE ID={$ACTIVITY_ID}";
                        $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

                        $new_act_bind_data = [
                            'ACTIVITY_ID' => $ACTIVITY_ID,
                            'OWNER_ID' => $entity_id,
                            'OWNER_TYPE_ID' => $entity_type_id,
                        ];
                        $arInsert = $DB->PrepareInsert("b_crm_act_bind", $new_act_bind_data);
                        $strSql = "INSERT INTO b_crm_act_bind (".$arInsert[0].") ".
                            "VALUES(".$arInsert[1].")";
                        $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__); // Timeline new bind to entity

                        if ($direction === 2) {
                            $VALUE = $mailbox_email;
                        } else {
                            $VALUE = $ar_mail_message['FIELD_TO'];
                        }
                        $ENTITY_SETTINGS = [
                            'HONORIFIC' => '',
                            'NAME' => $VALUE,
                            'SECOND_NAME' => '',
                            'LAST_NAME' => '',
                        ];
                        if ($entity_type === 'L') {
                            $ENTITY_SETTINGS['LEAD_TITLE'] = $VALUE;
                        }
                        $ar_comm_res = [
                            'ACTIVITY_ID' => $ACTIVITY_ID,
                            'ENTITY_ID' => $entity_id,
                            'ENTITY_TYPE_ID' => $entity_type_id,
                            'OWNER_ID' => $entity_id,
                            'OWNER_TYPE_ID' => $entity_type_id,
                            'TYPE' => 'EMAIL',
                            'VALUE' => $VALUE,
                            'ENTITY_SETTINGS' => serialize($ENTITY_SETTINGS),
                        ];
                        $arInsert = $DB->PrepareInsert("b_crm_act_comm", $ar_comm_res);
                        $strSql = "INSERT INTO b_crm_act_comm (".$arInsert[0].") ".
                            "VALUES(".$arInsert[1].")";
                        $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);


                        $response['status'] = 'success';

                    }

                }
            }
        }
    }



}


echo json_encode($response);