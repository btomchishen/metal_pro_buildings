<?
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("BX_CRONTAB", true);
define('BX_WITH_ON_AFTER_EPILOG', true);
define('BX_NO_ACCELERATOR_RESET', true);
set_time_limit(0);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if (Bitrix\Main\Loader::includeModule('tasks'))
{
	$result = CTasks::GetList(
		Array("TITLE" => "ASC"),
		Array(
			"GROUP_ID" => "14",
			"!=UF_TASK_EMAIL_ID" => '',
			"<REAL_STATUS" => 5,
			"CHECK_PERMISSIONS" => 'N'
		),
		Array("*")
	);
	$now = new DateTime();
	while ($arTask = $result->fetch())
	{
		$deadline = new DateTime($arTask['DEADLINE']);
		$taskId = $arTask['ID'];
		if($arTask['RESPONSIBLE_ID'] == DEBBIE_SIN){
			if($deadline < $now){
				$oTaskItem = new CTaskItem($taskId, 1);
				$rs = $oTaskItem->Update(array("RESPONSIBLE_ID" => KRYSTAL_WILLIAMS));
				$arFields = array(
					"MESSAGE_TYPE" => "S",
					"TO_USER_ID" => DEBBIE_SIN,
					"FROM_USER_ID" => 0,
					"MESSAGE" => "Responsible person for task was changed Link to task: https://metalpro.site/workgroups/group/14/tasks/task/view/" . $taskId . "/",
					"AUTHOR_ID" => 0,

					"NOTIFY_TYPE" => 1,
					"NOTIFY_BUTTONS" =>
					Array(
						Array('TITLE' => 'OK', 'VALUE' => 'Y', 'TYPE' => 'accept'),
					),
					"NOTIFY_MODULE" => "main",
				);
				CModule::IncludeModule('im');
				CIMMessenger::Add($arFields);
			}
		}

		if($arTask['RESPONSIBLE_ID'] == MELVYN_HO){
			if($deadline < $now){
				$oTaskItem = new CTaskItem($taskId, 1);
				$rs = $oTaskItem->Update(array("RESPONSIBLE_ID" => KRYSTAL_WILLIAMS));
				$arFields = array(
					"MESSAGE_TYPE" => "S",
					"TO_USER_ID" => MELVYN_HO,
					"FROM_USER_ID" => 0,
					"MESSAGE" => "Responsible person for task was changed Link to task: https://metalpro.site/workgroups/group/14/tasks/task/view/" . $taskId . "/",
					"AUTHOR_ID" => 0,

					"NOTIFY_TYPE" => 1,
					"NOTIFY_BUTTONS" =>
					Array(
						Array('TITLE' => 'OK', 'VALUE' => 'Y', 'TYPE' => 'accept'),
					),
					"NOTIFY_MODULE" => "main",
				);
				CModule::IncludeModule('im');
				CIMMessenger::Add($arFields);
			}
		}

		if($arTask['RESPONSIBLE_ID'] == LOURDES_MARC){
			if($deadline < $now){
				$oTaskItem = new CTaskItem($taskId, 1);
				$rs = $oTaskItem->Update(array("RESPONSIBLE_ID" => ABBE_SIN));
				$arFields = array(
					"MESSAGE_TYPE" => "S",
					"TO_USER_ID" => LOURDES_MARC,
					"FROM_USER_ID" => 0,
					"MESSAGE" => "Responsible person for task was changed Link to task: https://metalpro.site/workgroups/group/14/tasks/task/view/" . $taskId . "/",
					"AUTHOR_ID" => 0,

					"NOTIFY_TYPE" => 1,
					"NOTIFY_BUTTONS" =>
					Array(
						Array('TITLE' => 'OK', 'VALUE' => 'Y', 'TYPE' => 'accept'),
					),
					"NOTIFY_MODULE" => "main",
				);
				CModule::IncludeModule('im');
				CIMMessenger::Add($arFields);
			}
		}

	}
}
?>