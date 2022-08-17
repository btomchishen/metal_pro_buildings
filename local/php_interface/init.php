<?
if(file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/CHighData.php"))
require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/CHighData.php");

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/forms/classes/PDFForm.php"))
require_once($_SERVER["DOCUMENT_ROOT"]."/forms/classes/PDFForm.php");

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/composer/vendor/fpdm-master/fpdm.php"))
require_once($_SERVER["DOCUMENT_ROOT"]."/composer/vendor/fpdm-master/fpdm.php");

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/constants.php"))
require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/constants.php");

//classes
$directory = new RecursiveDirectoryIterator($_SERVER['DOCUMENT_ROOT'].'/local/php_interface/include/classes');
$iterator = new RecursiveIteratorIterator($directory);
foreach($iterator as $entry)
{
    if($entry->isFile())
        require_once  $entry->getPathname();
}

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/phpQuery/phpQuery.php"))
require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/phpQuery/phpQuery.php");

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/functions.php"))
require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/functions.php");

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/events.php"))
require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/events.php");

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/change_responsible.php"))
require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/change_responsible.php");

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/check_emails.php"))
require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/check_emails.php");

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/phone_formatter.php"))
require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/phone_formatter.php");

// AVIVI SMTP start
$directory      = new RecursiveDirectoryIterator($_SERVER['DOCUMENT_ROOT'].'/local/php_interface/include/event_handlers');
$iterator       = new RecursiveIteratorIterator($directory);
foreach($iterator as $entry)
{
    if($entry->isFile())
        require_once  $entry->getPathname();
}
// AVIVI SMTP end

//fp($_SERVER['SERVER_NAME'], 'aSERVER_NAME', true);

$directory1 = new RecursiveDirectoryIterator($_SERVER['DOCUMENT_ROOT'].'/forms/classes');
$iterator1 = new RecursiveIteratorIterator($directory1);
foreach($iterator1 as $entry)
{
    if($entry->isFile())
        require_once $entry->getPathname();
}

// Avivi #19996 Saving Comment to Lead card
// Custom assets
CJSCore::RegisterExt('custom_assets', [
    'css' => [
//        '/local/assets/css/custom.css',
    ],
    'js' => [
        '/local/assets/js/start-dialing-comment.js',
    ],
    'use' => CJSCore::USE_PUBLIC
]);
CJSCore::Init(['custom_assets']);
