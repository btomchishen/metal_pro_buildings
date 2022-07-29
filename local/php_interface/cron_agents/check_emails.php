<?
$_SERVER["DOCUMENT_ROOT"] = "/home/bitrix/www";
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define("BX_CRONTAB", true);
define('BX_WITH_ON_AFTER_EPILOG', true);
define('BX_NO_ACCELERATOR_RESET', true);
set_time_limit(0);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Mail;
use Bitrix\Mail\Helper\Mailbox;
use Bitrix\Mail\Internals\MailboxDirectoryTable;
use Bitrix\Mail\Internals\MessageAccessTable;
use Bitrix\Main;
use Bitrix\Main\ORM;
use Datetime;

\Bitrix\Main\Loader::includeModule('mail');
$mailBox = array_shift(\Bitrix\Mail\MailboxTable::getUserMailboxes(ADMIN_MAILBOX_ID));
if($mailBox) {
	$mailboxHelper = Mailbox::createInstance($mailBox["ID"]);
	$incomeMailDir = md5($mailboxHelper->getDirsHelper()->getDefaultDirPath());
	$now = new DateTime();

	$now->modify('-1 day');
	$timeFrom = $now->format('m/d/Y h:i:s a');

	$now->modify('+1 day');
	$timeTo = $now->format('m/d/Y h:i:s a');

	$date = new \Bitrix\Main\Type\DateTime($timeFrom);
	$date1 = new \Bitrix\Main\Type\DateTime($timeTo);

	$filter = array("=MAILBOX_ID" => $mailBox["ID"], '=MESSAGE_UID.DIR_MD5' => $incomeMailDir,
		array(
			'LOGIC' => 'AND',
			array('>DATE_INSERT' =>$date),
			array('<DATE_INSERT' =>$date1)
		)
	);

	$items = Mail\MailMessageTable::getList(
		[
			'runtime' => [
				new ORM\Fields\Relations\Reference(
					'MESSAGE_UID', Bitrix\Mail\MailMessageUidTable::class, [
						'=this.MAILBOX_ID' => 'ref.MAILBOX_ID',
						'=this.ID' => 'ref.MESSAGE_ID',
					], [
						'join_type' => 'INNER',
					]
				),
			],
			'select' => ['ID'],
			'filter' => array_merge(
				$filter,
				[
					'=MESSAGE_UID.DELETE_TIME' => 'IS NUll',
				]
			),
			'order' => [
				'FIELD_DATE' => 'DESC',
				'ID' => 'DESC',
			],
           // 'limit' => 5
		]
	)->fetchAll();

	if (!empty($items)) {

		$select = array(
			'ID',
			'MESSAGE_SIZE',
			'DATE_INSERT',
			'SUBJECT',
			'FIELD_FROM',
			'FIELD_TO',
			'FIELD_DATE',
			'BODY',
			'IS_SEEN' => 'MESSAGE_UID.IS_SEEN',
			'DIR_MD5' => 'MESSAGE_UID.DIR_MD5',
		);
		$res = Mail\MailMessageTable::getList(
			[
				'runtime' => [
					new ORM\Fields\Relations\Reference(
						'MESSAGE_UID', Mail\MailMessageUidTable::class, [
							'=this.MAILBOX_ID' => 'ref.MAILBOX_ID',
							'=this.ID' => 'ref.MESSAGE_ID',
						], [
							'join_type' => 'INNER',
						]
					),
					new ORM\Fields\Relations\Reference(
						'MESSAGE_ACCESS', MessageAccessTable::class, [
							'=this.MAILBOX_ID' => 'ref.MAILBOX_ID',
							'=this.ID' => 'ref.MESSAGE_ID',
						]
					),
				],
				'select' => $select,
				'filter' => array_merge(
					[
						'@ID' => array_column($items, 'ID'),
					],
					$filter,
				),
				'order' => [
					'FIELD_DATE' => 'DESC',
				],
			]
		);

		Bitrix\Main\Loader::includeModule("calendar");
		$Days_Modify = 1;
		$deadline = new DateTime();
		$work_time_end = explode('.', CCalendar::GetSettings()['work_time_end']);
		$work_time_start = explode('.', CCalendar::GetSettings()['work_time_start']);
		$Day_on_Bitrix = mb_strtoupper(mb_substr($deadline->format('D'), 0 , strlen($deadline->format('D')) - 1));
		$year_holidays = explode(',', CCalendar::GetSettings()['year_holidays']);
		$week_holidays = array(
			"SA",
			"SU"
		);

		while($Days_Modify != 0)
		{
			if(in_array($Day_on_Bitrix, $week_holidays) || in_array($deadline->format('d.m'), $year_holidays))
			{
				$deadline->modify('+1 day');
				$Day_on_Bitrix = mb_strtoupper(mb_substr($deadline->format('D'), 0 , strlen($deadline->format('D')) - 1));
			}
			else
			{
				$deadline->modify('+1 day');
				$Day_on_Bitrix = mb_strtoupper(mb_substr($deadline->format('D'), 0 , strlen($deadline->format('D')) - 1));
				$Days_Modify--;
			}
		}
			// $deadline->modify('-1 day');
		$deadline = $deadline->format('m/d/Y h:i:s a');

		
		if (Bitrix\Main\Loader::includeModule("tasks"))
		{

			$result = CTasks::GetList(
				Array("TITLE" => "ASC"),
				Array(
					"GROUP_ID" => "14",
					"!=UF_TASK_EMAIL_ID" => '',
					"CHECK_PERMISSIONS" => 'N'
				),
				Array("UF_TASK_EMAIL_ID")
			);

			$idArray = [];

			while($item = $res->fetch()){
				$result = CTasks::GetList(
					Array("TITLE" => "ASC"),
					Array(
						"GROUP_ID" => "14",
						"UF_TASK_EMAIL_ID" => $item['ID'],
						"CHECK_PERMISSIONS" => 'N'
					),
					Array("UF_TASK_EMAIL_ID")
				);

				if($result->SelectedRowsCount() == 0){
					$orders = stripos($item["FIELD_FROM"], "orders@metalprobuildings.com");
					$sbc_orders = stripos($item["FIELD_FROM"], "sbc-orders@metalprobuildings.com");
					$payments = stripos($item["FIELD_FROM"], "payment@metalprobuildings.com");
					$iq = stripos($item["FIELD_FROM"], "iq@metalprobuildings.com"); 

					if ($orders !== false && $sbc_orders === false) {
						$arFields = array(
							"TITLE" => "General Inquiry",
							"GROUP_ID" => OPERATIONS,
							"RESPONSIBLE_ID" => DEBBIE_SIN,
							"DEADLINE" => $deadline,
							"UF_TASK_EMAIL_ID" => $item["ID"],
							"DESCRIPTION" => $item['BODY']
						);
						$result = CTaskItem::add($arFields, 1);
					}

					if($sbc_orders !== false){
						$arFields = array(
							"TITLE" => "SBC Inquiry",
							"GROUP_ID" => OPERATIONS,
							"RESPONSIBLE_ID" => MELVYN_HO,
							"DEADLINE" => $deadline,
							"UF_TASK_EMAIL_ID" => $item["ID"],
							"DESCRIPTION" => $item['BODY']
						);
						$result = CTaskItem::add($arFields, 1);
					}

					if($payments !== false){
						$arFields = array(
							"TITLE" => "Payments",
							"GROUP_ID" => OPERATIONS,
							"RESPONSIBLE_ID" => LOURDES_MARC,
							"DEADLINE" => $deadline,
							"UF_TASK_EMAIL_ID" => $item["ID"],
							"DESCRIPTION" => $item['BODY']
						);
						$result = CTaskItem::add($arFields, 1);
					}

					if($iq !== false){
						$arFields = array(
							"TITLE" => "Quote Request",
							"GROUP_ID" => OPERATIONS,
							"RESPONSIBLE_ID" => MELVYN_HO,
							"DEADLINE" => $deadline,
							"UF_TASK_EMAIL_ID" => $item["ID"],
							"DESCRIPTION" => $item['BODY']
						);
						$result = CTaskItem::add($arFields, 1);
					}

				}
			}
		}
	}
}
?>