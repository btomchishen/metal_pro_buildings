<?php

// Avivi #27872
class AviviWorkAnniversary
{
    public const WORK_ANNIVERSARY = 'UF_USR_1661762455688';
    protected const PATH_TO_USER_PROFILE = '/company/personal/user/';

    protected static $anniversaries = [];
    protected static $users = [];

    public static function run()
    {
        self::getCurrentDayAnniversaries();

        if (!empty(self::$anniversaries)) {
            self::sendNotification();
        }

        return 'AviviWorkAnniversary::run();';
    }

    public static function getCurrentMonthAnniversaries()
    {
        $currentMonth = date_format(date_create(), 'm');

        $arFilter['!' . self::WORK_ANNIVERSARY] = '';
        $arSelect = array('*', 'UF_*');

        $dbUsers = CUser::getList(
            self::WORK_ANNIVERSARY, 'ASC',
            $arFilter,
            array(
                'SELECT' => $arSelect,
                'FIELDS' => $arSelect
            )
        );

        $anniversaries = array();
        while ($arUser = $dbUsers->fetch()) {
            $userDate = explode('/', $arUser[self::WORK_ANNIVERSARY]);

            if ($currentMonth == $userDate[0]) {
                $anniversaries[$arUser['ID']] = $arUser;
            }
        }

        self::sortAnniversaries($anniversaries);

        return $anniversaries;
    }

    protected static function sortAnniversaries(&$anniversaries)
    {
        $currentDay = date_format(date_create(), 'd');

        /**
         * Removing people who already had an anniversary in that month
         */
        foreach ($anniversaries as $anniversary) {
            $anniversaryDate = explode('/', $anniversary[self::WORK_ANNIVERSARY]);

            if ($anniversaryDate[1] < $currentDay) {
                unset($anniversaries[$anniversary['ID']]);
            }
        }

        /**
         * Sort anniversary list by day
         */
        usort($anniversaries, function ($a, $b) {
            return (intval(explode('/', $a[self::WORK_ANNIVERSARY])[1]) - intval(explode('/', $b[self::WORK_ANNIVERSARY])[1]));
        });
    }

    protected static function getCurrentDayAnniversaries()
    {
        $currentDay = date_format(date_create(), 'd');
        $currentMonth = date_format(date_create(), 'm');

        $arFilter['!' . self::WORK_ANNIVERSARY] = '';
        $arSelect = array('ID', 'NAME', 'LAST_NAME', self::WORK_ANNIVERSARY);

        $dbUsers = CUser::getList(
            'ID', 'ASC',
            $arFilter,
            array(
                'SELECT' => $arSelect,
                'FIELDS' => $arSelect
            )
        );

        while ($arUser = $dbUsers->fetch()) {
            $userDate = explode('/', $arUser[self::WORK_ANNIVERSARY]);

            if ($currentMonth == $userDate[0] && $currentDay == $userDate[1])
                self::$anniversaries[$arUser['ID']] = $arUser;
        }

        return self::$anniversaries;
    }

    /**
     * If 'anniversaries' list is empty will set all users into 'users' list
     */
    protected static function getNotificationUsersList()
    {
        /**
         * getList filter take arrays in format '1 | 2 | 25', don't work with standard arrays
         */
        $exceptUsers = '';
        foreach (self::$anniversaries as $anniversary) {
            $exceptUsers .= $anniversary['ID'] . ' | ';
        }

        $arFilter['!ID'] = $exceptUsers;
        $arSelect = array('ID');

        $dbUsers = \CUser::getList(
            'ID', 'ASC',
            $arFilter,
            array(
                'SELECT' => $arSelect,
                'FIELDS' => $arSelect
            )
        );

        while ($arUser = $dbUsers->fetch()) {
            self::$users[$arUser['ID']] = $arUser;
        }

        return self::$users;
    }

    protected static function sendNotification()
    {
        self::getNotificationUsersList();

        if (!\Bitrix\Main\Loader::includeModule('im')) {
            throw new \Bitrix\Main\LoaderException('Unable to load IM module');
        }

        if (!empty(self::$users)) {
            foreach (self::$users as $user) {
                $arFields = array(
                    "MESSAGE_TYPE" => "S",
                    "TO_USER_ID" => $user['ID'],
                    "FROM_USER_ID" => 0, // From system
                    "MESSAGE" => self::getMessage(),
                    "NOTIFY_TYPE" => 1,
                    "NOTIFY_MODULE" => "main",
                );

                $msg = new \CIMMessenger();
                $msg->Add($arFields);
            }
        }
    }

    protected function getMessage()
    {
        $message = "Please congratulate ";

        foreach (self::$anniversaries as $anniversary) {
            $message .= '<a href="https://' . $_SERVER['SERVER_NAME'] . self::PATH_TO_USER_PROFILE . $anniversary['ID'] . '/">'
                . $anniversary['NAME'] . ' ' . $anniversary['LAST_NAME']
                . '</a>, ';
        }

        $message .= "on their work anniversary today!";

        return $message;
    }
}