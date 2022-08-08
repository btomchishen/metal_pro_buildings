<?php
// Avivi #50243 Finance Report
if (file_exists($_SERVER['DOCUMENT_ROOT'].'/local/php_interface/include/google_app/vendor/autoload.php')) {
    require_once($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/google_app/vendor/autoload.php');
}

class AviviGoogleApi
{
    protected const HB_GOOGLE_API_AUTHENTICATION = 34;
    protected const GOOGLE_API_APP_NAME = 'Metal Pro Buildings App';
    protected const GOOGLE_API_APP_SCOPES = [
        'https://www.googleapis.com/auth/userinfo.email',
        Google_Service_Sheets::DRIVE,
        Google_Service_Sheets::SPREADSHEETS,
    ];
    // $_SERVER['DOCUMENT_ROOT'].'/local/php_interface/include/google_app/google_auth/client_secret.json'
    protected const GOOGLE_API_CONFIG = '/local/php_interface/include/google_app/google_auth/client_secret.json';
    protected static $user_id = '';



    public static function get_google_api_token($user_id = '') {
        if (!empty($user_id)) {
            $UF_GAPI_USER_ID = $user_id;
        } else {
            global $USER;
            $UF_GAPI_USER_ID = $USER->GetID();
        }
        $arFilter = array(
            'UF_GAPI_USER_ID' => $UF_GAPI_USER_ID
        );
        $arSelect = array(
            'UF_GAPI_EMAIL',
            'UF_GAPI_TOKEN',
        );
        return CHighData::GetList(self::HB_GOOGLE_API_AUTHENTICATION, $arFilter, $arSelect, [], false, 1);
    }

    public static function save_google_api_token($data) {
        global $USER;
        $UF_GAPI_USER_ID = $USER->GetID();
        $arFilter = array(
            'UF_GAPI_USER_ID' => $UF_GAPI_USER_ID
        );
        $recordID = CHighData::IsRecordExist(self::HB_GOOGLE_API_AUTHENTICATION, $arFilter);
        if (!empty($recordID)) {
            CHighData::UpdateRecord(self::HB_GOOGLE_API_AUTHENTICATION, $recordID, $data);
        } else {
            $data['UF_GAPI_USER_ID'] = $UF_GAPI_USER_ID;
            CHighData::AddRecord(self::HB_GOOGLE_API_AUTHENTICATION, $data);
        }
    }

    public static function get_google_api_client() {
        $client = new Google_Client();
        $client->setApplicationName(self::GOOGLE_API_APP_NAME);
        $client->setScopes(self::GOOGLE_API_APP_SCOPES);
        $client->setAuthConfig($_SERVER['DOCUMENT_ROOT'].self::GOOGLE_API_CONFIG);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        return $client;
    }

    public static function get_google_api_authorization_url() {
        $client = self::get_google_api_client();
        return $client->createAuthUrl();
    }

    public static function set_google_api_access_token($authCode) {
        $client = self::get_google_api_client();
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        $client->setAccessToken($accessToken);
        $UF_GAPI_TOKEN = json_encode($client->getAccessToken());

        $Oauth2 = new Google_Service_Oauth2($client);
        $userinfo = $Oauth2->userinfo_v2_me->get();
        $UF_GAPI_EMAIL = $userinfo->getEmail();

        $data = array(
            'UF_GAPI_TOKEN' => $UF_GAPI_TOKEN,
            'UF_GAPI_EMAIL' => $UF_GAPI_EMAIL
        );
        self::save_google_api_token($data);
    }

    public static function unlink_google_api_token() {
        global $USER;
        $UF_GAPI_USER_ID = $USER->GetID();
        $arFilter = array(
            'UF_GAPI_USER_ID' => $UF_GAPI_USER_ID
        );
        $recordID = CHighData::IsRecordExist(self::HB_GOOGLE_API_AUTHENTICATION, $arFilter);
        if (!empty($recordID)) {
            CHighData::DeleteRecord(self::HB_GOOGLE_API_AUTHENTICATION, $recordID);
            return true;
        }
        return false;
    }

}
