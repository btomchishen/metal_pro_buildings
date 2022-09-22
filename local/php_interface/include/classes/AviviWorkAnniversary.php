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
        self::fetchCurrentDayAnniversaries();

        if (!empty(self::$anniversaries)) {
            self::sendNotification();
            foreach (self::$anniversaries as $anniversary) {
                self::sendMessageToFeed($anniversary);
            }
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

    public static function fetchWidgetAnniversaries()
    {
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
            $anniversaryInfo = self::checkWidgetAnniversaries($arUser[self::WORK_ANNIVERSARY]);

            if ($anniversaryInfo['isAnniversary'] == true) {
                self::$anniversaries[$arUser['ID']] = $arUser;
                self::$anniversaries[$arUser['ID']]['WORKED_TIME'] = $anniversaryInfo['title'];

                if ($anniversaryInfo['title'] == '90 days') {
                    self::$anniversaries[$arUser['ID']][self::WORK_ANNIVERSARY] = $anniversaryInfo['newDate'];
                    self::$anniversaries[$arUser['ID']]['isAnniversary'] = $anniversaryInfo['isAnniversary'];
                }
            }
        }

        self::sortAnniversaries();
    }

    protected static function checkWidgetAnniversaries($anniversaryDate)
    {
        $userDate = explode('/', $anniversaryDate);

        $currentMonth = date_format(date_create(), 'm');
        $currentYear = date_format(date_create(), 'Y');

        switch ($userDate[0]) {
            case 10:
                $newMonth = 1;
                break;
            case 11:
                $newMonth = 2;
                break;
            case 12:
                $newMonth = 3;
                break;
            default:
                $newMonth = $userDate[0] + 3;
                break;
        }

        $result['isAnniversary'] = false;

        if ($currentMonth == $newMonth) {
            $result['isAnniversary'] = true;
            $result['title'] = '90 days';

            $result['newDate'] = $newMonth . '/' . $userDate[1] . '/' . $userDate[2];
        } elseif ($currentMonth == $userDate[0] && $currentYear > $userDate[2]) {
            $yearDiff = $currentYear - $userDate[2];

            $result['isAnniversary'] = true;
            $result['title'] = $yearDiff . ' year';
        }

        return $result;
    }

    protected static function checkCurrentDayAnniversaries($anniversaryDate)
    {
        $userDate = explode('/', $anniversaryDate);

        $currentDay = date_format(date_create(), 'd');
        $currentMonth = date_format(date_create(), 'm');
        $currentYear = date_format(date_create(), 'Y');

        switch ($userDate[0]) {
            case 10:
                $newMonth = 1;
                break;
            case 11:
                $newMonth = 2;
                break;
            case 12:
                $newMonth = 3;
                break;
            default:
                $newMonth = $userDate[0] + 3;
                break;
        }

        $result['isAnniversary'] = false;

        if ($currentDay == $userDate[1] && $currentMonth == $newMonth) {
            $result['isAnniversary'] = true;
            $result['title'] = '90 days';
        } elseif ($currentDay == $userDate[1] && $currentMonth == $userDate[0] && $currentYear > $userDate[2]) {
            $yearDiff = $currentYear - $userDate[2];

            $result['isAnniversary'] = true;
            $result['title'] = $yearDiff . ' year';
        }

        return $result;
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

    protected static function fetchCurrentDayAnniversaries()
    {
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
            $anniversaryInfo = self::checkCurrentDayAnniversaries($arUser[self::WORK_ANNIVERSARY]);

            if ($anniversaryInfo['isAnniversary'] == true) {
                self::$anniversaries[$arUser['ID']] = $arUser;
                self::$anniversaries[$arUser['ID']]['WORKED_TIME'] = $anniversaryInfo['title'];
            }
        }
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

    protected static function getMessage($anniversary)
    {
        $message = "[B]Please congratulate ";

        $message .= '<a href="https://' . self::DOMAIN . self::PATH_TO_USER_PROFILE . $anniversary['ID'] . '/">'
            . $anniversary['NAME'] . ' ' . $anniversary['LAST_NAME']
            . '</a>';

        $message .= " on their " . $anniversary['WORKED_TIME'] . " Anniversary![/B]";

        return $message;
    }

    protected static function createNewBlogPost($anniversary)
    {
        global $DB;

        $arFields = array(
            "TITLE" => 'Anniversary',
            "BLOG_ID" => self::ADMIN_ID,
            "AUTHOR_ID" => self::ADMIN_ID,
            "PREVIEW_TEXT_TYPE" => 'text',
            "DETAIL_TEXT" => self::getMessage($anniversary),
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

    protected static function sendMessageToFeed($anniversary)
    {
        global $DB;

        if (!\Bitrix\Main\Loader::includeModule('socialnetwork')) {
            throw new \Bitrix\Main\LoaderException('Unable to load SocialNetwork module');
        }

        $blogPostID = self::createNewBlogPost($anniversary);

        $arEvent = array(
            'ENTITY_TYPE' => 'U',
            'ENTITY_ID' => '1',
            'EVENT_ID' => 'blog_post',
            'USER_ID' => self::ADMIN_ID,
            '=LOG_DATE' => $DB->GetNowFunction(),
            'TITLE_TEMPLATE' => '#USER_NAME# has added post "#TITLE#" in blog',
            'TITLE' => "Anniversary",
            'MESSAGE' => self::getMessage($anniversary),
            'TEXT_MESSAGE' => self::getMessage($anniversary),
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