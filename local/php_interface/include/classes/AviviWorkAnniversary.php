<?php

// Avivi #27872
class AviviWorkAnniversary
{
    public const WORK_ANNIVERSARY = 'UF_USR_1660939816444';

    protected const PATH_TO_USER_PROFILE = '/company/personal/user/';
    protected const DOMAIN = 'metalpro.site';
    protected const ADMIN_ID = 1;

    protected static $anniversaries = [];
    protected static $users = [];

    public static function run()
    {
        self::getCurrentDayAnniversaries();

        self::sendMessageToFeed();
        if (!empty(self::$anniversaries)) {
            self::sendNotification();
        }

        return 'AviviWorkAnniversary::run();';
    }

    public static function getAnniversaries()
    {
        return self::$anniversaries;
    }

    public static function getUsers()
    {
        return self::$users;
    }

    public static function setCurrentMonthAnniversaries()
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

        while ($arUser = $dbUsers->fetch()) {
            $userDate = explode('/', $arUser[self::WORK_ANNIVERSARY]);

            if ($currentMonth == $userDate[0]) {
                self::$anniversaries[$arUser['ID']] = $arUser;
            }
        }

        self::getNumberOfYears();
        self::sortAnniversaries();
    }

    protected static function sortAnniversaries()
    {
        $currentDay = date_format(date_create(), 'd');

        /**
         * Removing people who already had an anniversary in that month
         */
        foreach (self::$anniversaries as $anniversary) {
            $anniversaryDate = explode('/', $anniversary[self::WORK_ANNIVERSARY]);

            if ($anniversaryDate[1] < $currentDay) {
                unset(self::$anniversaries[$anniversary['ID']]);
            }
        }

        /**
         * Sort anniversary list by day
         */
        usort(self::$anniversaries, function ($a, $b) {
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

        self::getNumberOfYears();
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
                foreach (self::$anniversaries as $anniversary) {
                    $arFields = array(
                        "MESSAGE_TYPE" => "S",
                        "TO_USER_ID" => $user['ID'],
                        "FROM_USER_ID" => 0, // From system
                        "MESSAGE" => self::getMessage($anniversary),
                        "NOTIFY_TYPE" => 1,
                        "NOTIFY_MODULE" => "main",
                    );

                    $msg = new \CIMMessenger();
                    $msg->Add($arFields);
                }
            }
        }
    }

    protected static function getNumberOfYears()
    {
        $currentDate = date_format(date_create(), 'm/d/Y');

        foreach (self::$anniversaries as $anniversary) {
            $dateDiff = strtotime($currentDate) - strtotime($anniversary[self::WORK_ANNIVERSARY]);
            $workedDays = floor($dateDiff / (60 * 60 * 24));

            self::$anniversaries[$anniversary['ID']]['WORKED_DAYS'] = $workedDays;

            if ($workedDays < 365 && $workedDays > 30) {
                $workedMonth = floor(floor($dateDiff / (60 * 60 * 24)) / 31);

                self::$anniversaries[$anniversary['ID']]['WORKED_TIME'] = $workedMonth . ' month';

            } else if ($workedDays >= 365) {
                $workedYears = floor(floor($dateDiff / (60 * 60 * 24)) / 365);

                self::$anniversaries[$anniversary['ID']]['WORKED_TIME'] = $workedYears . ' year';
            } else {
                self::$anniversaries[$anniversary['ID']]['WORKED_TIME'] = $workedDays . ' days';
            }
        }
    }

    protected static function getMessage($anniversary)
    {
        $message = "Please congratulate ";

        $message .= '<a href="https://' . self::DOMAIN . self::PATH_TO_USER_PROFILE . $anniversary['ID'] . '/">'
            . $anniversary['NAME'] . ' ' . $anniversary['LAST_NAME']
            . '</a>';

        $message .= " on their " . $anniversary['WORKED_YEARS'] . " year Anniversary!";

        return $message;
    }

    protected static function createNewBlogPost($anniversaryName)
    {
        global $DB;

        $arFields = array(
            "TITLE" => 'Anniversary',
            "BLOG_ID" => self::ADMIN_ID,
            "AUTHOR_ID" => self::ADMIN_ID,
            "PREVIEW_TEXT_TYPE" => 'text',
            "DETAIL_TEXT" => '[B]Please congratulate ' . $anniversaryName . '[/B]',
            "=DATE_CREATE" => $DB->GetNowFunction(),
            "=DATE_PUBLISH" => $DB->GetNowFunction(),
            "PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
            "ENABLE_TRACKBACK" => 'Y',
            "ENABLE_COMMENTS" => 'Y',
            "HAS_SOCNET_ALL" => 'Y',
            'PATH' => '/company/personal/user/' . self::ADMIN_ID . '/blog/#post_id#/',
            "MICRO" => 'Y',
            'HAS_PROPS' => 'Y',
            "SOCNET_RIGHTS" => array
            (
                "G2" // All users
            )
        );

        $postID = CBlogPost::Add($arFields);

        return $postID;
    }

    protected static function sendMessageToFeed()
    {
        global $DB;

        if (!\Bitrix\Main\Loader::includeModule('socialnetwork')) {
            throw new \Bitrix\Main\LoaderException('Unable to load SocialNetwork module');
        }

        $blogPostID = self::createNewBlogPost('Test Name');

        $arEvent = array(
            'ENTITY_TYPE' => 'U',
            'ENTITY_ID' => '1',
            'EVENT_ID' => 'blog_post',
            'USER_ID' => self::ADMIN_ID,
            '=LOG_DATE' => $DB->GetNowFunction(),
            'TITLE_TEMPLATE' => '#USER_NAME# has added post "#TITLE#" in blog',
            'TITLE' => "Anniversary",
            'MESSAGE' => "[B]Please congratulate Bohdan[/B]",
            'TEXT_MESSAGE' => "[B]Please congratulate Bohdan[/B]",
            'URL' => '/company/personal/user/' . self::ADMIN_ID . '/blog/' . $blogPostID . '/',
            'MODULE_ID' => 'blog',
            'CALLBACK_FUNC' => false,
            'SOURCE_ID' => $blogPostID,
            'ENABLE_COMMENTS' => 'Y',
            'RATING_TYPE_ID' => 'BLOG_POST',
            'RATING_ENTITY_ID' => $blogPostID,
            'TRANSFORM' => 'N',
        );

        $eventID = CSocNetLog::Add($arEvent);

        CSocNetLog::Update($eventID, array('TMP_ID' => $eventID));
        CSocNetLogRights::Add($eventID, array("G2", "SA", 'U1', "US1"));
        CSocNetLog::SendEvent($eventID, 'SONET_NEW_EVENT');
    }
}