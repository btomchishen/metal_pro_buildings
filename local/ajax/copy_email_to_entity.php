<? // Avivi #48557 Copy mail to entity
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
    global $DB;
    $DB_res = $DB->Query("SELECT b_crm_act.*
FROM b_crm_act
WHERE b_crm_act.ID = {$_REQUEST['email_id']}");
    if ($ar_res = $DB_res->fetch()) {
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

                    $new_act_data = $ar_res;
                    unset($new_act_data['ID']);
                    unset($new_act_data['CREATED']);
                    unset($new_act_data['LAST_UPDATED']);
                    unset($new_act_data['START_TIME']);
                    unset($new_act_data['END_TIME']);
                    unset($new_act_data['DEADLINE']);
                    $new_act_data['OWNER_ID'] = $entity_id;
                    $new_act_data['OWNER_TYPE_ID'] = $entity_type_id;

                    $arInsert = $DB->PrepareInsert("b_crm_act", $new_act_data);
                    $strSql = "INSERT INTO b_crm_act (".$arInsert[0].", `CREATED`, `LAST_UPDATED`, `START_TIME`, `END_TIME`, `DEADLINE`) ".
                        "VALUES(".$arInsert[1].", '".$ar_res['CREATED']."', '".$ar_res['LAST_UPDATED']."', '".$ar_res['START_TIME']."', '".$ar_res['END_TIME']."', '".$ar_res['DEADLINE']."')";
                    $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__); // Timeline record copy
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
                        $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__); // Timeline record bind to new entity

                        $DB_comm_res = $DB->Query("SELECT b_crm_act_comm.*
FROM b_crm_act_comm
WHERE b_crm_act_comm.ACTIVITY_ID = {$_REQUEST['email_id']}");
                        if ($ar_comm_res = $DB_comm_res->fetch()) {
                            unset($ar_comm_res['ID']);
                            $ar_comm_res['ACTIVITY_ID'] = $ACTIVITY_ID;
                            $ar_comm_res['ENTITY_ID'] = $entity_id;
                            $ar_comm_res['OWNER_ID'] = $entity_id;
                            $ar_comm_res['OWNER_TYPE_ID'] = $entity_type_id;
                            $arInsert = $DB->PrepareInsert("b_crm_act_comm", $ar_comm_res);
                            $strSql = "INSERT INTO b_crm_act_comm (".$arInsert[0].") ".
                                "VALUES(".$arInsert[1].")";
                            $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__); // Timeline record communication (Email From) copy
                        }

                    }

                    $response['status'] = 'success';
                }
            }
        }
    }

}


echo json_encode($response);