<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Main\Context;
global $APPLICATION;
$request = Context::getCurrent()->getRequest();

$requiredModules = array('highloadblock');
foreach ($requiredModules as $requiredModule)
{
	if (!CModule::IncludeModule($requiredModule))
	{
		ShowError(GetMessage("F_NO_MODULE"));
		return false;
	}
}

$arResult["LIST"]["PURCHASE_ORDER"] = CHighData::GetList(PURCHASE_ORDER_LIST);
$arResult["LIST"]["USE_EXPOSURE"] = CHighData::GetList(USE_EXPOSURE_LIST);
$arResult["LIST"]["SERIES"] = CHighData::GetList(SERIES_LIST);
$arResult["LIST"]["FOUNDATION_SYSTEM"] = CHighData::GetList(FOUNDATION_SYSTEM_LIST);
$arResult["LIST"]["WALL_TYPE"] = CHighData::GetList(WALL_TYPE_LIST);
$arResult["LIST"]["ACCESSORIES_TYPE"] = CHighData::GetList(ACCESSORIES_TYPE_LIST, array("!ID" => 1));
$arResult["PROVINCIES"] = CHighData::GetList(PROVINCES_HIGHLOAD);
$arResult["CITIES"] = CHighData::GetList(CITIES_HIGHLOAD);
$arResult["MODELS"] = CHighData::GetList(MODEL_HIGHLOAD);

$arResult["ACCESSORIES_LIST"] = CHighData::GetList(ACCESSORIES_HIGHLOAD, array(), array("UF_ACCESSORIES_TYPE", "ID", "UF_WIDTH", "UF_HEIGHT", "UF_PIONEER", "UF_TYPE_FIELD"));
//Get constants
$constantsData = CHighData::GetList(CONSTANTS_HIGHLOAD);
$constants = array();
foreach ($constantsData as $constant)
   $constants[$constant["ID"]] = $constant["UF_CONSTANT_VALUE"];
foreach($arResult["ACCESSORIES_LIST"] as &$accessory)
{
   $price = explode( '|', $accessory["UF_PIONEER"])[0];
	$accessory["CA_PRICE"] = round($price); //* $constants[CA_VARIABLE_1] * $constants[CA_VARIABLE_2], 2);
	$accessory["US_PRICE"] = round($price); //* $constants[US_VARIABLE_1] * $constants[US_VARIABLE_2], 2);
   if($accessory["UF_TYPE_FIELD"] == 1)
      $arResult["DOORS"][] = $accessory;
   else
      $arResult["ACCESSORIES"][] = $accessory;
}
unset($accessory);
 
$arParams["ACTION"] = isset($arParams["ACTION"]) ? $arParams["ACTION"] : $request["ACTION"];
$arParams["QUATATION_ID"] = isset($arParams["QUATATION_ID"]) ? $arParams["QUATATION_ID"] : $request["QUATATION_ID"];
if(!empty($arParams["ACTION"]))
{
	if($arParams["ACTION"] == "EDIT" || $arParams["ACTION"] == "SHOW")
	{
		$quotation = array_shift(CHighData::GetList(QUOTATION_SYSTEM_HIGHLOAD, array("ID" => $arParams["QUATATION_ID"]),  array("*")));
		$rsUser = CUser::GetByID($quotation["UF_QUOATION_OWNER"]);
		$arUser = $rsUser->Fetch();
		$customerEntity = explode("_" ,$quotation["UF_QUOTATION_CUSTOMER_ID"]);
		if($customerEntity[0] == "L")
		{
			$entity = CCrmLead::GetByID($customerEntity[1]);
			$entityTitle = $entity["TITLE"];
		}
		if($customerEntity[0] == "C")
		{
			$entity = CCrmContact::GetByID($customerEntity[1]);
			$entityTitle = $entity["FULL_NAME"];
		}
			
		$arResult["QUOTATION_DATA"] = array(
			"ID" => $quotation["ID"],
			"DATE" => $quotation["UF_QUOTATION_DATE"],
			"CUSTOMER_ID" => $quotation["UF_QUOTATION_CUSTOMER_ID"],
			"CUSTOMER_NAME" => $entityTitle,
			"PURCHASE_ORDER" => $quotation["UF_QUOTATION_PURCHASE_ORDER"],
			"OWNER" => $arUser["NAME"] . " " . $arUser["LAST_NAME"],
			"OWNER_ID" => $quotation["UF_QUOATION_OWNER"],
			"CLIENT_NAME" => unserialize($quotation["UF_QUOTATION_CUSTOMER_DATA"])["CUSTOMER_NAME"],
			"COMPANY_TITLE" => unserialize($quotation["UF_QUOTATION_CUSTOMER_DATA"])["CUSTOMER_COMPANY"],
			"CLIENT_TEL" => unserialize($quotation["UF_QUOTATION_CUSTOMER_DATA"])["CUSTOMER_TEL"],
			"CLIENT_CELL" => unserialize($quotation["UF_QUOTATION_CUSTOMER_DATA"])["CUSTOMER_CELL"],
			"CLIENT_WORK_TEL" => unserialize($quotation["UF_QUOTATION_CUSTOMER_DATA"])["CUSTOMER_WORK_TEL"],
			"CLIENT_EMAIL" => unserialize($quotation["UF_QUOTATION_CUSTOMER_DATA"])["CUSTOMER_EMAIL"],
			"CLIENT_PROVINCE" => unserialize($quotation["UF_QUOTATION_CUSTOMER_DATA"])["CUSTOMER_PROVINCE"],
			"CLIENT_POSTAL_CODE" => unserialize($quotation["UF_QUOTATION_CUSTOMER_DATA"])["CUSTOMER_POSTAL_CODE"],
			"CLIENT_CITY" => unserialize($quotation["UF_QUOTATION_CUSTOMER_DATA"])["CUSTOMER_CITY"],
			"CLIENT_ADDRESS" => unserialize($quotation["UF_QUOTATION_CUSTOMER_DATA"])["CUSTOMER_ADDRESS"],
			"BUILDING_PROVINCE" => unserialize($quotation["UF_QUOATION_BUILDING_DATA"])["BUILDING_PROVINCE"],
			"BUILDING_CITY" => unserialize($quotation["UF_QUOATION_BUILDING_DATA"])["BUILDING_CITY"],
			"BUILDING_POSTAL_CODE" => unserialize($quotation["UF_QUOATION_BUILDING_DATA"])["BUILDING_POSTAL_CODE"],
			"BUILDING_ADDRESS" => unserialize($quotation["UF_QUOATION_BUILDING_DATA"])["BUILDING_ADDRESS"],
			"BUILDING_COUNTRY" => unserialize($quotation["UF_QUOATION_BUILDING_DATA"])["BUILDING_COUNTRY"],
			"USE_EXPOSURE" => unserialize($quotation["UF_QUOATION_EXPOSURE_DATA"])["USE_EXPOSURE"],
			"SERIES" => unserialize($quotation["UF_QUOATION_EXPOSURE_DATA"])["SERIES"],
			"MODEL" => unserialize($quotation["UF_QUOATION_EXPOSURE_DATA"])["MODEL"],
			"FOUNDATION_SYSTEM" => unserialize($quotation["UF_QUOATION_EXPOSURE_DATA"])["FOUNDATION_SYSTEM"],
			"WIDTH" => unserialize($quotation["UF_QUOATION_EXPOSURE_DATA"])["WIDTH"],
			"LENGTH" => unserialize($quotation["UF_QUOATION_EXPOSURE_DATA"])["LENGTH"],
			"HEIGHT" => unserialize($quotation["UF_QUOATION_EXPOSURE_DATA"])["HEIGHT"],
			"ANCHORS" => unserialize($quotation["UF_QUOATION_EXPOSURE_DATA"])["ANCHORS"],
            "PSF" => unserialize($quotation["UF_QUOATION_EXPOSURE_DATA"])["PSF"],
			"FRONT_WALL_TYPE" => unserialize($quotation["UF_FRONT_WALL_DATA"])["FRONT_WALL_TYPE"],
			"FRONT_WALL_QUANTITY" => unserialize($quotation["UF_FRONT_WALL_DATA"])["FRONT_WALL_QUANTITY"],
			"FRONT_WALL_WIDTH" => unserialize($quotation["UF_FRONT_WALL_DATA"])["FRONT_WALL_WIDTH"],
			"FRONT_WALL_HEIGHT" => unserialize($quotation["UF_FRONT_WALL_DATA"])["FRONT_WALL_HEIGHT"],
			"FRONT_WALL_AMOUNT" => unserialize($quotation["UF_CALCULATION"])["ENDWALLS_FRONT"] * unserialize($quotation["UF_CALCULATION"])["ENDWALL_FRONT_QUANTITY"],
            "FRONT_WALL_SEA_HEIGHT" => unserialize($quotation["UF_FRONT_WALL_DATA"])["FRONT_WALL_SEA_HEIGHT"],
            "FRONT_WALL_OFFSET" => unserialize($quotation["UF_FRONT_WALL_DATA"])["FRONT_WALL_OFFSET"],
            "REAR_WALL_TYPE" => unserialize($quotation["UF_REAR_WALL_DATA"])["REAR_WALL_TYPE"],
			"REAR_WALL_QUANTITY" => unserialize($quotation["UF_REAR_WALL_DATA"])["REAR_WALL_QUANTITY"],
			"REAR_WALL_WIDTH" => unserialize($quotation["UF_REAR_WALL_DATA"])["REAR_WALL_WIDTH"],
			"REAR_WALL_HEIGHT" => unserialize($quotation["UF_REAR_WALL_DATA"])["REAR_WALL_HEIGHT"],
			"REAR_WALL_AMOUNT" => unserialize($quotation["UF_CALCULATION"])["ENDWALLS_REAR"] * unserialize($quotation["UF_CALCULATION"])["ENDWALL_REAR_QUANTITY"],
            "REAR_WALL_SEA_HEIGHT" => unserialize($quotation["UF_REAR_WALL_DATA"])["REAR_WALL_SEA_HEIGHT"],
            "REAR_WALL_OFFSET" => unserialize($quotation["UF_REAR_WALL_DATA"])["REAR_WALL_OFFSET"],
            "ACCESSORIES" => unserialize($quotation["UF_QUOTATION_ACCESSORIES_DATA"]),
			"ACCESSORIES_TOTAL_COST" => unserialize($quotation["UF_CALCULATION"])["ACCESSORIES_BLOCK_TOTAL"],
			"DOORS" => unserialize($quotation["UF_QUOTATION_DOORS_DATA"]),
			"DOORS_TOTAL_COST" => unserialize($quotation["UF_CALCULATION"])["DOORS_BLOCK_TOTAL"],
			"EDIT_FREIGHT_MANUALLY" => unserialize($quotation["UF_QUOTATION_FREIGHT"])["EDIT_FREIGHT_MANUALLY"],
			"COST" => unserialize($quotation["UF_QUOTATION_FREIGHT"])["COST"],
			"BUILDING_TOTAL_СOST" => unserialize($quotation["UF_CALCULATION"])["COST"],
			"EDIT_BUILDING_TOTAL_COST" => unserialize($quotation["UF_QUOTATION_OTHER"])["EDIT_BUILDING_TOTAL_COST"],
			"SOLD_FOR" => unserialize($quotation["UF_QUOTATION_OTHER"])['SOLD_FOR'],
			"ASKING" => unserialize($quotation["UF_QUOTATION_OTHER"])['ASKING'],
			"DRAWINGS" => unserialize($quotation["UF_QUOTATION_OTHER"])['DRAWINGS'],
			"ESTIMATED_DELIVERY" => unserialize($quotation["UF_QUOTATION_OTHER"])['ESTIMATED_DELIVERY'],
			"INSULATION" => unserialize($quotation["UF_QUOTATION_OTHER"])['INSULATION'],
			"NOTES" => unserialize($quotation["UF_QUOTATION_OTHER"])['NOTES'],
			"CALCULATION" => unserialize($quotation["UF_CALCULATION"]),
			"EDIT" => $request["ACTION"] == "EDIT" ? "Y" :  "N",
			"SHOW" => $request["ACTION"] == "SHOW" ? "Y" :  "N",
			"QUOTATION_PDF" => $quotation["UF_DOCUMENT_PDF"],
			"CAULKING" => unserialize($quotation["UF_QUOTATION_OTHER"])['CAULKING'],
			"EDIT_SOLD_FOR" => unserialize($quotation["UF_QUOTATION_OTHER"])["EDIT_SOLD_FOR"],
			"EDIT_ASKING" => unserialize($quotation["UF_QUOTATION_OTHER"])["EDIT_ASKING"],
		);

		$loadingTableData = array_shift(CHighData::GetList(LOADING_TABLE_HIGHLOAD, 
			array(
				"UF_LOADING_TABLE_PROVINCE" => $arResult["QUOTATION_DATA"]["BUILDING_PROVINCE"],
				"UF_LOADING_TABLE_CITY" => $arResult["QUOTATION_DATA"]["BUILDING_CITY"],
			),  
			array("UF_SNOW_LOAD_1", "UF_WIND_LOAD", "UF_SNOW_LOAD_2", "UF_LOW_SHELTERED", "UF_NORMAL_SHELTERED", "UF_LOW_EXPOSED", "UF_NORMAL_EXPOSED"))
		);

		$arResult["QUOTATION_DATA"]["CALCULATION"]["SNOW_LOAD"] = $loadingTableData["UF_SNOW_LOAD_1"];
		$arResult["QUOTATION_DATA"]["CALCULATION"]["WIND_LOAD"] = $loadingTableData["UF_WIND_LOAD"];
		$arResult["QUOTATION_DATA"]["CALCULATION"]["RAIN_LOAD"] = $loadingTableData["UF_SNOW_LOAD_2"];
		//$arResult["QUOTATION_DATA"]["CALCULATION"]["REQUIRED_LIVE_LOAD_CATEGORY_I"] = $loadingTableData["UF_LOW_SHELTERED"];
		//$arResult["QUOTATION_DATA"]["CALCULATION"]["REQUIRED_LIVE_LOAD_CATEGORY_II"] = $loadingTableData["UF_NORMAL_SHELTERED"];

		//Check if set Use/Exposure to Exposed
		if($arResult["QUOTATION_DATA"]["USE_EXPOSURE"] == 1 || $arResult["QUOTATION_DATA"]["USE_EXPOSURE"] == 3)
		{
		$arResult["QUOTATION_DATA"]["CALCULATION"]["REQUIRED_LIVE_LOAD_CATEGORY_I"] = $loadingTableData["UF_LOW_EXPOSED"];
		$arResult["QUOTATION_DATA"]["CALCULATION"]["REQUIRED_LIVE_LOAD_CATEGORY_II"] = $loadingTableData["UF_NORMAL_EXPOSED"];
		}
		else
		{
		$arResult["QUOTATION_DATA"]["CALCULATION"]["REQUIRED_LIVE_LOAD_CATEGORY_I"] = $loadingTableData["UF_LOW_SHELTERED"];
		$arResult["QUOTATION_DATA"]["CALCULATION"]["REQUIRED_LIVE_LOAD_CATEGORY_II"] = $loadingTableData["UF_NORMAL_SHELTERED"];
		}



	}
}

if(isset($request["FROM_ENTITY"]) && $request["FROM_ENTITY"] == "Y")
{
	$arResult["QUOTATION_DATA"] = array(
		"ENTITY_TYPE" => $request['ENTITY_TYPE'],
		"CUSTOMER_ID" => $request["QUATATION_ENTITY_ID"] != "N" ? $request['ENTITY_TYPE']."_".$request["QUATATION_ENTITY_ID"] : "",
		"CLIENT_NAME" => $request["QUATATION_CLIENT_NAME"] != "N" ? $request["QUATATION_CLIENT_NAME"] : "",
		"COMPANY_TITLE" => $request["QUATATION_COMPANY_TITLE"] != "N" ? $request["QUATATION_COMPANY_TITLE"] : "",
		"CLIENT_TEL" => $request["QUATATION_CLIENT_TEL"] != "N" ? $request["QUATATION_CLIENT_TEL"] : "",
		"CLIENT_WORK_TEL" => $request["QUATATION_CLIENT_WORK_TEL"] != "N" ? $request["QUATATION_CLIENT_WORK_TEL"] : "",
		"CLIENT_CELL" => $request["QUATATION_CLIENT_CELL"] != "N" ? $request["QUATATION_CLIENT_CELL"] : "",
		"CLIENT_EMAIL" => $request["QUATATION_CLIENT_EMAIL"] != "N" ? $request["QUATATION_CLIENT_EMAIL"] : "",
		"CLIENT_CITY" => $request["QUATATION_CLIENT_CITY"] != "N" ? $request["QUATATION_CLIENT_CITY"] : "",
		"CLIENT_POSTAL_CODE" => $request["QUATATION_CLIENT_POSTAL_CODE"] != "N" ? $request["QUATATION_CLIENT_POSTAL_CODE"] : "",
		"OWNER_ID" => $request["QUATATION_OWNER"] != "N" ? $request["QUATATION_OWNER"] : "",
	);
}

$this->IncludeComponentTemplate();
