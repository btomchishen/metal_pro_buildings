<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

global $DB;
/*
$emails_from = [
    'docusign@metalprobuildings.com',
    'engineering@metalprobuildings.com',
    'delivery@metalprobuildings.com',
    'Drawings@metalprobuildings.com',
    'Costing@metalprobuildings.com',
    'cmb@metalprobuildings.com',
    'sbc-orders@metalprobuildings.com',
    'iq@metalprobuildings.com',
    'pioneerinvoice@metalprobuildings.com',
];

foreach ($emails_from as $email_from) {
    $DB_res = $DB->Query("SELECT COUNT(*) as total_count
FROM b_mail_message
WHERE b_mail_message.MAILBOX_ID = 1
AND b_mail_message.FIELD_FROM LIKE('%<{$email_from}>')");
    WHILE ($ar_res = $DB_res->fetch()) {
        p($email_from . ' - ' . $ar_res['total_count']);
    }
}
*/

//$DB_res = $DB->Query("SELECT b_mail_message.*
//FROM b_mail_message
//WHERE b_mail_message.MAILBOX_ID = 1
//
//LIMIT 10");

//AND b_mail_message.ID = 252599

//while ($ar_res = $DB_res->fetch()) {
//    unset($ar_res['HEADER']);
//    unset($ar_res['BODY']);
//    unset($ar_res['BODY_HTML']);
//    unset($ar_res['SEARCH_CONTENT']);
//    p($ar_res);
//}

//$DB_res = CHighData::GetList(HB_CRM_IEA_EMAIL_COMPANY_BIND);
//foreach ($DB_res as $ar_email_company_bind) {
//    p($ar_email_company_bind);
//}
//if (empty($DB_res)) {
//    foreach (CRM_INCOMING_EMAIL_ACTIVITY_EMAIL_FROM_FILTER as $company_email) {
//        p($company_email);
//    }
//}