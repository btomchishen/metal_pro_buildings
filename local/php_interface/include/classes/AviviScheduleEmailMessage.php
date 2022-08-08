<?php
// Avivi #48683 Schedule email message

use Bitrix\Main\Type\DateTime;

class AviviScheduleEmailMessage
{

    protected const LOG_FILE_DIRECTORY = 'local/log/';
    protected const LOG_FILE_NAME = 'schedule_email_message.txt';
    protected const HB_SCHEDULE_EMAIL_MESSAGE = 25; // ScheduleEmailMessage schedule_email_message
    protected const MESSAGE_SEND_LIMIT = 5; // can`t be less than 2
    protected static $type;
    protected static $user_id = 1;
    protected static $data = [];
    protected static $raw_data = [];
    protected static $schedule = '';

    public function add_message() {
        self::set_type();
        self::set_user();
        self::set_data();
        self::set_schedule();
        return self::save_to_HB();
    }

    protected function set_type() {
        if (isset($_REQUEST['ACTION'])
            && $_REQUEST['ACTION'] === 'SAVE_EMAIL'
        ) {
            self::$type = 'crm';
        } else {
            self::$type = 'mail';
        }
    }

    protected function set_user() {
        $curUser = CCrmSecurityHelper::GetCurrentUser();
        self::$user_id = (int) $curUser->GetID();
    }

    protected function set_data() {
        if (self::$type === 'crm') {
            self::$data = $_POST['DATA'];
            self::$data['message'] = $_POST['dummy_DATA']['message'];
        } else if (self::$type === 'mail') {
            self::$data = $_POST['data'];
            self::$data['message'] = $_POST['dummy_data']['message'];
        }
    }

    protected function set_schedule() {
        $date_time_format = \Bitrix\Main\Type\DateTime::getFormat();
        if (!empty($_POST['schedule'])
            && DateTime::isCorrect($_POST['schedule'], $date_time_format)) {
            self::$schedule = new DateTime($_POST['schedule'], $date_time_format);
        } else {
            self::$schedule = new DateTime(date($date_time_format), $date_time_format);
        }
    }

    protected function set_schedule_old() { // Remove me later
        $from_format = 'm/d/Y h:i:s a';
        $to_format = 'Y-m-d H:i:s';
        if (validate_date($_POST['schedule'], $from_format)) {
            self::$schedule = convert_date($_POST['schedule'], $from_format, $to_format);
        } else {
            self::$schedule = date($to_format);
        }
        self::$schedule = new DateTime(self::$schedule, $to_format);
    }

    protected function save_to_HB() {
        $ID = CHighData::AddRecord(self::HB_SCHEDULE_EMAIL_MESSAGE, [
            'UF_TYPE' => self::$type,
            'UF_USER_ID' => self::$user_id,
            'UF_DATA' => serialize(self::$data),
            'UF_SCHEDULE' => self::$schedule,
        ]);
        return ($ID !== false);
    }

    public function Send() {
        $date_time_format = \Bitrix\Main\Type\DateTime::getFormat();
        $serverZone = COption::GetOptionString("main", "default_time_zone"); // get time zone America/Toronto
        $location_date_time = new DateTime();
        $location_date_time_zone = new DateTimeZone($serverZone);
        $location_date_time->setTimeZone($location_date_time_zone);
        $schedule_filter = $location_date_time->format($date_time_format);
//        self::add_to_log('Run check. Date time: ' . $schedule_filter);
        $arFilter = [
            '<=UF_SCHEDULE' => $schedule_filter // filter for messages should send from this moment
        ];
        $arSelect = ['*'];
        $sort = ['UF_SCHEDULE' => 'ASC'];
        $message_list = CHighData::GetList(
            self::HB_SCHEDULE_EMAIL_MESSAGE,
            $arFilter,
            $arSelect,
            $sort,
            false,
            self::MESSAGE_SEND_LIMIT
        );
        if (!empty($message_list)) {
            $sent = 0;
            foreach ($message_list as $message) {
                self::set_message_data($message);
                if (self::$type === 'crm') {
                    AviviScheduleEmailMessageCRM::send(self::$data, self::$user_id);
                } else if (self::$type === 'mail') {
                    AviviScheduleEmailMessageMail::send(self::$data, self::$user_id);
                }
                CHighData::DeleteRecord(self::HB_SCHEDULE_EMAIL_MESSAGE, $message['ID']);
                $sent++;
            }
            self::add_to_log('Sent ' . $sent . ' Emails');
        }
        return 'AviviScheduleEmailMessage::Send();';
    }

    protected function set_message_data($message) {
        self::$type = $message['UF_TYPE'];
        self::$user_id = $message['UF_USER_ID'];
        self::$data = unserialize($message['UF_DATA']);
//        self::$schedule = $message['UF_SCHEDULE']->format('Y-m-d H:i:s');
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
