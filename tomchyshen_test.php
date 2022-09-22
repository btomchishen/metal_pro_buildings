<?php
define('STOP_STATISTICS', true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

CModule::IncludeModule('im');
CModule::IncludeModule('socialnetwork');
CModule::IncludeModule("blog");


//p(AviviWorkAnniversary::run());

//
//$arFields = array(
//    "TITLE" => 'Anniversary',
//    "BLOG_ID" => 1,
//    "AUTHOR_ID" => 1,
//    "PREVIEW_TEXT_TYPE" => 'text',
//    "DETAIL_TEXT" => '[B]Please congratulate Bohdan[/B]',
//    "=DATE_CREATE" => $DB->GetNowFunction(),
//    "=DATE_PUBLISH" => $DB->GetNowFunction(),
//    "PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
//    "ENABLE_TRACKBACK" => 'Y',
//    "ENABLE_COMMENTS" => 'Y',
//    "HAS_SOCNET_ALL" => 'Y',
//    'PATH' => '/company/personal/user/1/blog/#post_id#/',
//    "MICRO" => 'Y',
//    'HAS_PROPS' => 'Y',
//    "SOCNET_RIGHTS" => Array
//    (
//        "G2"
//    )
//);
//$newID = CBlogPost::Add($arFields);
//echo $newID . ' ';
////
//$arEvent = array(
//    'ENTITY_TYPE' => 'U',
//    'ENTITY_ID' => '1',
//    'EVENT_ID' => 'blog_post',
//    'USER_ID' => '1',
//    '=LOG_DATE' => $DB->GetNowFunction(),
//    'TITLE_TEMPLATE' => '#USER_NAME# has added post "#TITLE#" in blog',
//    'TITLE' => "Anniversary",
//    'MESSAGE' => "[B]Please congratulate Bohdan[/B]",
//    'TEXT_MESSAGE' => "[B]Please congratulate Bohdan[/B]",
//    'URL' => '/company/personal/user/1/blog/' . $newID . '/',
//    'MODULE_ID' => 'blog',
//    'CALLBACK_FUNC' => false,
//    'SOURCE_ID' => $newID,
//    'ENABLE_COMMENTS' => 'Y',
//    'RATING_TYPE_ID' => 'BLOG_POST',
//    'RATING_ENTITY_ID' => $newID,
//    'TRANSFORM' => 'N',
//);
//
//$eventID = CSocNetLog::Add($arEvent);
//echo $eventID;
//CSocNetLog::Update($eventID, array('TMP_ID' => $eventID));
//// Выдает права
//CSocNetLogRights::Add($eventID, array("G2", "SA", 'U1', "US1"));
//// Отправляет уведомление о новом сообщении
//CSocNetLog::SendEvent($eventID, 'SONET_NEW_EVENT');

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");