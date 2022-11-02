<?php
define('STOP_STATISTICS', true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");


CJSCore::Init(array("jquery", "ajax"));
$APPLICATION->SetTitle('Leads updater');

//$ch = curl_init("https://www.bennetyee.org/ucsd-pages/area.html");
$ch = curl_init("https://api.phaxio.com/v2/public/area_codes?state=NJ&country=US");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
$content = curl_exec($ch);
curl_close($ch);
p(json_decode($content));

p(AviviAreaCodeAssigner::getArea('+1 (204) 791-3480'));
CModule::IncludeModule("crm");
p(AviviAreaCodeAssigner::getPhoneByLeadId(105866));


$res = CCrmLead::GetList(
    array("ID" => "DESC"), // arSort
    array('ID' => 105866), // arFilter
    array() // arSelect
);

while ($arTask = $res->GetNext()) {
    p($arTask);
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");