<?require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;

require $_SERVER["DOCUMENT_ROOT"] . '/composer/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
//Comment
global $APPLICATION;
$request = Context::getCurrent()->getRequest();

if(isset($request["SELECT_CITY"]) && $request["SELECT_CITY"] == "Y")
{
	$arFilter = !empty($request["PROVINCE_ID"]) ? array("UF_PROVINCE" => $request["PROVINCE_ID"]) : array();
	$cities = CHighData::GetList(CITIES_HIGHLOAD, $arFilter);
	echo json_encode($cities);
}

if(isset($request["SELECT_PSF"]) && $request["SELECT_PSF"] == "Y")
{
	$useExposure = array_shift(CHighData::GetList(USE_EXPOSURE_LIST_HIGHLOAD, array("ID" => $request["USE_EXPOSURE"])));
	$PSFdata = array_shift(CHighData::GetList(
		LOADING_TABLE_HIGHLOAD, 
		array(
			'>='.$useExposure["UF_LOADING_TABLE_FIELD_NAME"] => $request["PSF"],
		),
		array("*"),
		array(
			$useExposure["UF_LOADING_TABLE_FIELD_NAME"] => "ASC"
		),
	));
	$array = array(
		$PSFdata["UF_LOADING_TABLE_PROVINCE"], $PSFdata["UF_LOADING_TABLE_CITY"]
	);
	echo json_encode($array);
}

if(isset($request["SELECT_ACCESSORIES"]) && $request["SELECT_ACCESSORIES"] == "Y")
{
	$arFilter = !empty($request["ACCESSORY_TYPE_ID"]) ? array("UF_TYPE_FIELD" => $request["ACCESSORY_TYPE_ID"]) : array("!UF_TYPE_FIELD" => 1);
	$accessories = CHighData::GetList(ACCESSORIES_HIGHLOAD, $arFilter, array("UF_ACCESSORIES_TYPE", "ID", "UF_WIDTH", "UF_HEIGHT"));
	echo json_encode($accessories);
}

if(isset($request["SELECT_BUILDING_CITY"]) && $request["SELECT_BUILDING_CITY"] == "Y")
{
	$shippingWeight = array_shift(CHighData::GetList(SHIPPING_WEIGHT_HIGHLOAD, array("UF_SHIPPING_MODEL" => $request["MODEL"], "UF_SHIPPING_GAUGE" => $arResult["GAUGE_INDEX"]), array("UF_SHIPPING_PER_ARCH_NO_CAULK","UF_SHIPPING_SOLID_END_WALL", "UF_SHIPPING_1_OUTER")));

	$arResult["ARCH_UNIT_LBS"] = isset($shippingWeight["UF_SHIPPING_PER_ARCH_NO_CAULK"]) ? $shippingWeight["UF_SHIPPING_PER_ARCH_NO_CAULK"] : 0;
	$arResult["ACTUAL_ARCHES_WEIGHT"] = $arResult["ARCHES"] * $arResult["ARCH_UNIT_LBS"];

	$weightData = CHighData::GetList(WEIGHT_MEASURES_HIGHLOAD);
	$weightMeasures = array();
	$skid = array();
	foreach($weightData as $weight)
	{
		$weightMeasures[] = $weight["UF_WEIGHT_MEASURE"];
		$skid[$weight["UF_WEIGHT_MEASURE"]] = $weight["UF_SKID"];
	}

	$arResult["RATED_ARCHES_WEIGHT"] = findNext($weightMeasures, $arResult["ACTUAL_ARCHES_WEIGHT"]);

	$arResult["SHIPPING_ZONE"] = array_shift(array_shift(CHighData::GetList(CITIES_HIGHLOAD, array("ID" => $request['BUILDING_CITY']), array("UF_ZONE"))));
			//$weightPrice = array_shift(CHighData::GetList(WEIGHT_HIGHLOAD, array("UF_WEIGHT_PROVINCE" => $request["BUILDING_PROVINCE"], "UF_WEIGHT_ZONE" => $arResult["SHIPPING_ZONE"])));
	$obEnum = new \CUserFieldEnum;
	$rsEnum = $obEnum->GetList(array(), array("USER_FIELD_NAME" => "UF_ZONE"));
	$UF_ZONE = array();
	while($arEnum = $rsEnum->Fetch())
		$UF_ZONE[$arEnum['VALUE']] = $arEnum['ID'];

	$weightPrice = array_shift(CHighData::GetList(FREIGHT_COST_HIGHLOAD, array("UF_PROVINCE_FREIGHTCOST" => $request["BUILDING_PROVINCE"], "UF_ZONE" => $UF_ZONE[$arResult["SHIPPING_ZONE"]])));
	if(!empty($weightPrice))
	{
		foreach($weightPrice as $key => $price)
		{
			$weightMeasure = explode("_", $key);
			if($arResult["RATED_ARCHES_WEIGHT"] == 5000)
				$weight = 500;
			else
				$weight = $arResult["RATED_ARCHES_WEIGHT"];
			if(in_array($weight,$weightMeasure))
				$arResult["ARCHES_FREIGHT_COST"] = floatval($price);
			if($key == "UF_ADDITIONAL")
				break;
		}
		if($request["FRONT_WALL_TYPE"] == 2 || $request["REAR_WALL_TYPE"] == 2){
			$arResult["ARCHES_FREIGHT_COST"] += $weightPrice["UF_ADDITIONAL"];
		}
		if($request["FOUNDATION_SYSTEM"] == 2 || $request["FOUNDATION_SYSTEM"] == 3){
			$arResult["ARCHES_FREIGHT_COST"] += $weightPrice["UF_ADDITIONAL"];
		}
		$doorAmount = $request["TOTAL_DOOR_AMOUNT"];
		$chars = array("$", ",");
		$doorAmount = floatval(str_replace($chars, '', $doorAmount));
		if($doorAmount > 0){
			$arResult["ARCHES_FREIGHT_COST"] += $weightPrice["UF_ADDITIONAL"];
		}

	}
	else
		$arResult["ARCHES_FREIGHT_COST"] = 0;
	echo $arResult["ARCHES_FREIGHT_COST"];
}

if(isset($request["SELECT_FOUNDATION"]) && $request["SELECT_FOUNDATION"] == "Y")
{
	$shippingWeight = array_shift(CHighData::GetList(SHIPPING_WEIGHT_HIGHLOAD, array("UF_SHIPPING_MODEL" => $request["MODEL"], "UF_SHIPPING_GAUGE" => $arResult["GAUGE_INDEX"]), array("UF_SHIPPING_PER_ARCH_NO_CAULK","UF_SHIPPING_SOLID_END_WALL", "UF_SHIPPING_1_OUTER")));

	$arResult["ARCH_UNIT_LBS"] = isset($shippingWeight["UF_SHIPPING_PER_ARCH_NO_CAULK"]) ? $shippingWeight["UF_SHIPPING_PER_ARCH_NO_CAULK"] : 0;
	$arResult["ACTUAL_ARCHES_WEIGHT"] = $arResult["ARCHES"] * $arResult["ARCH_UNIT_LBS"];

	$weightData = CHighData::GetList(WEIGHT_MEASURES_HIGHLOAD);
	$weightMeasures = array();
	$skid = array();
	foreach($weightData as $weight)
	{
		$weightMeasures[] = $weight["UF_WEIGHT_MEASURE"];
		$skid[$weight["UF_WEIGHT_MEASURE"]] = $weight["UF_SKID"];
	}

	$arResult["RATED_ARCHES_WEIGHT"] = findNext($weightMeasures, $arResult["ACTUAL_ARCHES_WEIGHT"]);

	$arResult["SHIPPING_ZONE"] = array_shift(array_shift(CHighData::GetList(CITIES_HIGHLOAD, array("ID" => $request['BUILDING_CITY']), array("UF_ZONE"))));
			//$weightPrice = array_shift(CHighData::GetList(WEIGHT_HIGHLOAD, array("UF_WEIGHT_PROVINCE" => $request["BUILDING_PROVINCE"], "UF_WEIGHT_ZONE" => $arResult["SHIPPING_ZONE"])));
	$obEnum = new \CUserFieldEnum;
	$rsEnum = $obEnum->GetList(array(), array("USER_FIELD_NAME" => "UF_ZONE"));
	$UF_ZONE = array();
	while($arEnum = $rsEnum->Fetch())
		$UF_ZONE[$arEnum['VALUE']] = $arEnum['ID'];

	$weightPrice = array_shift(CHighData::GetList(FREIGHT_COST_HIGHLOAD, array("UF_PROVINCE_FREIGHTCOST" => $request["BUILDING_PROVINCE"], "UF_ZONE" => $UF_ZONE[$arResult["SHIPPING_ZONE"]])));
	if(!empty($weightPrice))
	{
		foreach($weightPrice as $key => $price)
		{
			$weightMeasure = explode("_", $key);
			if($arResult["RATED_ARCHES_WEIGHT"] == 5000)
				$weight = 500;
			else
				$weight = $arResult["RATED_ARCHES_WEIGHT"];
			if(in_array($weight,$weightMeasure))
				$arResult["ARCHES_FREIGHT_COST"] = floatval($price);
			if($key == "UF_ADDITIONAL")
				break;
		}
		if($request["FRONT_WALL_TYPE"] == 2 || $request["REAR_WALL_TYPE"] == 2){
			$arResult["ARCHES_FREIGHT_COST"] += $weightPrice["UF_ADDITIONAL"];
		}
		if($request["FOUNDATION_SYSTEM"] == 2 || $request["FOUNDATION_SYSTEM"] == 3){
			$arResult["ARCHES_FREIGHT_COST"] += $weightPrice["UF_ADDITIONAL"];
		}
		$doorAmount = $request["TOTAL_DOOR_AMOUNT"];
		$chars = array("$", ",");
		$doorAmount = floatval(str_replace($chars, '', $doorAmount));
		if($doorAmount > 0){
			$arResult["ARCHES_FREIGHT_COST"] += $weightPrice["UF_ADDITIONAL"];
		}

	}
	else
		$arResult["ARCHES_FREIGHT_COST"] = 0;
	echo $arResult["ARCHES_FREIGHT_COST"];
}

if(isset($request["SELECT_WALL"]) && $request["SELECT_WALL"] == "Y")
{
	$shippingWeight = array_shift(CHighData::GetList(SHIPPING_WEIGHT_HIGHLOAD, array("UF_SHIPPING_MODEL" => $request["MODEL"], "UF_SHIPPING_GAUGE" => $arResult["GAUGE_INDEX"]), array("UF_SHIPPING_PER_ARCH_NO_CAULK","UF_SHIPPING_SOLID_END_WALL", "UF_SHIPPING_1_OUTER")));

	$arResult["ARCH_UNIT_LBS"] = isset($shippingWeight["UF_SHIPPING_PER_ARCH_NO_CAULK"]) ? $shippingWeight["UF_SHIPPING_PER_ARCH_NO_CAULK"] : 0;
	$arResult["ACTUAL_ARCHES_WEIGHT"] = $arResult["ARCHES"] * $arResult["ARCH_UNIT_LBS"];

	$weightData = CHighData::GetList(WEIGHT_MEASURES_HIGHLOAD);
	$weightMeasures = array();
	$skid = array();
	foreach($weightData as $weight)
	{
		$weightMeasures[] = $weight["UF_WEIGHT_MEASURE"];
		$skid[$weight["UF_WEIGHT_MEASURE"]] = $weight["UF_SKID"];
	}

	$arResult["RATED_ARCHES_WEIGHT"] = findNext($weightMeasures, $arResult["ACTUAL_ARCHES_WEIGHT"]);

	$arResult["SHIPPING_ZONE"] = array_shift(array_shift(CHighData::GetList(CITIES_HIGHLOAD, array("ID" => $request['BUILDING_CITY']), array("UF_ZONE"))));
			//$weightPrice = array_shift(CHighData::GetList(WEIGHT_HIGHLOAD, array("UF_WEIGHT_PROVINCE" => $request["BUILDING_PROVINCE"], "UF_WEIGHT_ZONE" => $arResult["SHIPPING_ZONE"])));
	$obEnum = new \CUserFieldEnum;
	$rsEnum = $obEnum->GetList(array(), array("USER_FIELD_NAME" => "UF_ZONE"));
	$UF_ZONE = array();
	while($arEnum = $rsEnum->Fetch())
		$UF_ZONE[$arEnum['VALUE']] = $arEnum['ID'];

	$weightPrice = array_shift(CHighData::GetList(FREIGHT_COST_HIGHLOAD, array("UF_PROVINCE_FREIGHTCOST" => $request["BUILDING_PROVINCE"], "UF_ZONE" => $UF_ZONE[$arResult["SHIPPING_ZONE"]])));
	if(!empty($weightPrice))
	{
		foreach($weightPrice as $key => $price)
		{
			$weightMeasure = explode("_", $key);
			if($arResult["RATED_ARCHES_WEIGHT"] == 5000)
				$weight = 500;
			else
				$weight = $arResult["RATED_ARCHES_WEIGHT"];
			if(in_array($weight,$weightMeasure))
				$arResult["ARCHES_FREIGHT_COST"] = floatval($price);
			if($key == "UF_ADDITIONAL")
				break;
		}
		if($request["FRONT_WALL_TYPE"] == 2 || $request["REAR_WALL_TYPE"] == 2){
			$arResult["ARCHES_FREIGHT_COST"] += $weightPrice["UF_ADDITIONAL"];
		}
		if($request["FOUNDATION_SYSTEM"] == 2 || $request["FOUNDATION_SYSTEM"] == 3){
			$arResult["ARCHES_FREIGHT_COST"] += $weightPrice["UF_ADDITIONAL"];
		}
		$doorAmount = $request["TOTAL_DOOR_AMOUNT"];
		$chars = array("$", ",");
		$doorAmount = floatval(str_replace($chars, '', $doorAmount));
		if($doorAmount > 0){
			$arResult["ARCHES_FREIGHT_COST"] += $weightPrice["UF_ADDITIONAL"];
		}

	}
	else
		$arResult["ARCHES_FREIGHT_COST"] = 0;
	echo $arResult["ARCHES_FREIGHT_COST"];
}

if(isset($request["SELECT_DOOR"]) && $request["SELECT_DOOR"] == "Y")
{
	$shippingWeight = array_shift(CHighData::GetList(SHIPPING_WEIGHT_HIGHLOAD, array("UF_SHIPPING_MODEL" => $request["MODEL"], "UF_SHIPPING_GAUGE" => $arResult["GAUGE_INDEX"]), array("UF_SHIPPING_PER_ARCH_NO_CAULK","UF_SHIPPING_SOLID_END_WALL", "UF_SHIPPING_1_OUTER")));

	$arResult["ARCH_UNIT_LBS"] = isset($shippingWeight["UF_SHIPPING_PER_ARCH_NO_CAULK"]) ? $shippingWeight["UF_SHIPPING_PER_ARCH_NO_CAULK"] : 0;
	$arResult["ACTUAL_ARCHES_WEIGHT"] = $arResult["ARCHES"] * $arResult["ARCH_UNIT_LBS"];

	$weightData = CHighData::GetList(WEIGHT_MEASURES_HIGHLOAD);
	$weightMeasures = array();
	$skid = array();
	foreach($weightData as $weight)
	{
		$weightMeasures[] = $weight["UF_WEIGHT_MEASURE"];
		$skid[$weight["UF_WEIGHT_MEASURE"]] = $weight["UF_SKID"];
	}

	$arResult["RATED_ARCHES_WEIGHT"] = findNext($weightMeasures, $arResult["ACTUAL_ARCHES_WEIGHT"]);

	$arResult["SHIPPING_ZONE"] = array_shift(array_shift(CHighData::GetList(CITIES_HIGHLOAD, array("ID" => $request['BUILDING_CITY']), array("UF_ZONE"))));
			//$weightPrice = array_shift(CHighData::GetList(WEIGHT_HIGHLOAD, array("UF_WEIGHT_PROVINCE" => $request["BUILDING_PROVINCE"], "UF_WEIGHT_ZONE" => $arResult["SHIPPING_ZONE"])));
	$obEnum = new \CUserFieldEnum;
	$rsEnum = $obEnum->GetList(array(), array("USER_FIELD_NAME" => "UF_ZONE"));
	$UF_ZONE = array();
	while($arEnum = $rsEnum->Fetch())
		$UF_ZONE[$arEnum['VALUE']] = $arEnum['ID'];

	$weightPrice = array_shift(CHighData::GetList(FREIGHT_COST_HIGHLOAD, array("UF_PROVINCE_FREIGHTCOST" => $request["BUILDING_PROVINCE"], "UF_ZONE" => $UF_ZONE[$arResult["SHIPPING_ZONE"]])));
	if(!empty($weightPrice))
	{
		foreach($weightPrice as $key => $price)
		{
			$weightMeasure = explode("_", $key);
			if($arResult["RATED_ARCHES_WEIGHT"] == 5000)
				$weight = 500;
			else
				$weight = $arResult["RATED_ARCHES_WEIGHT"];
			if(in_array($weight,$weightMeasure))
				$arResult["ARCHES_FREIGHT_COST"] = floatval($price);
			if($key == "UF_ADDITIONAL")
				break;
		}
		if($request["FRONT_WALL_TYPE"] == 2 || $request["REAR_WALL_TYPE"] == 2){
			$arResult["ARCHES_FREIGHT_COST"] += $weightPrice["UF_ADDITIONAL"];
		}
		if($request["FOUNDATION_SYSTEM"] == 2 || $request["FOUNDATION_SYSTEM"] == 3){
			$arResult["ARCHES_FREIGHT_COST"] += $weightPrice["UF_ADDITIONAL"];
		}
		$doorAmount = $request["TOTAL_DOOR_AMOUNT"];
		$chars = array("$", ",");
		$doorAmount = floatval(str_replace($chars, '', $doorAmount));
		if($doorAmount > 0){
			$arResult["ARCHES_FREIGHT_COST"] += $weightPrice["UF_ADDITIONAL"];
		}

	}
	else
		$arResult["ARCHES_FREIGHT_COST"] = 0;
	echo $arResult["ARCHES_FREIGHT_COST"];
}

if((isset($request["SAVE_DATA"]) && $request["SAVE_DATA"] == "Y") || (isset($request["UPDATE_DATA"]) && $request["UPDATE_DATA"] == "Y"))
{
	$requiredFields = array(
		array("FIELD_CODE" => "MODEL", "FIELD_NAME" => Loc::getMessage("MODEL_INPUT")),
		array("FIELD_CODE" => "BUILDING_CITY", "FIELD_NAME" => Loc::getMessage("CITY_INPUT")),
		array("FIELD_CODE" => "BUILDING_PROVINCE", "FIELD_NAME" => Loc::getMessage("PROV_INPUT")),
		array("FIELD_CODE" => "FOUNDATION_SYSTEM", "FIELD_NAME" => Loc::getMessage("FOUNDATION_SYSTEM_INPUT")),
		array("FIELD_CODE" => "FRONT_WALL_TYPE", "FIELD_NAME" => Loc::getMessage("TYPE_INPUT")),
		array("FIELD_CODE" => "REAR_WALL_TYPE", "FIELD_NAME" => Loc::getMessage("TYPE_INPUT")),
		array("FIELD_CODE" => "USE_EXPOSURE", "FIELD_NAME" => Loc::getMessage("USE_EXPOSURE_INPUT")),
		array("FIELD_CODE" => "WIDTH", "FIELD_NAME" => Loc::getMessage("WIDTH_INPUT")),
		array("FIELD_CODE" => "HEIGHT", "FIELD_NAME" => Loc::getMessage("HEIGHT_INPUT")),
		array("FIELD_CODE" => "FRONT_WALL_QUANTITY", "FIELD_NAME" => Loc::getMessage("QUANTITY_INPUT")),
		array("FIELD_CODE" => "REAR_WALL_QUANTITY", "FIELD_NAME" => Loc::getMessage("QUANTITY_INPUT")),
		array("FIELD_CODE" => "BUILDING_COUNTRY", "FIELD_NAME" => Loc::getMessage("COUNTRY_INPUT")),
	);
	$errorResponse = array();
	$emptyFields = array();
	foreach($requiredFields as $field)
	{
		if(!isset($request[$field["FIELD_CODE"]]) || empty($request[$field["FIELD_CODE"]]))
			$emptyFields[] =  $field;
	}
	if(count($emptyFields) > 0)
	{

		$jsonData = json_encode(array(
			"STATUS" => "ERROR",
			"FIELD" => $emptyFields
		));
		echo $jsonData;
		die();
	}
	else
	{
		//Get constants
		$constantsData = CHighData::GetList(CONSTANTS_HIGHLOAD);
		$constants = array();
		foreach ($constantsData as $constant)
			$constants[$constant["ID"]] = $constant["UF_CONSTANT_VALUE"];
		//PSF number
		if(isset($request["PSF"]) && !empty($request["PSF"]))
		{
			$arResult["PSF"] = $request["PSF"];
		}
		else
		{
			$useExposure = array_shift(CHighData::GetList(USE_EXPOSURE_LIST_HIGHLOAD, array("ID" => $request["USE_EXPOSURE"])));
			$PSFdata = array_shift(CHighData::GetList(
				LOADING_TABLE_HIGHLOAD, 
				array(
					"UF_LOADING_TABLE_CITY" => $request["BUILDING_CITY"], 
					"UF_LOADING_TABLE_PROVINCE" => $request["BUILDING_PROVINCE"]
				),
				array($useExposure["UF_LOADING_TABLE_FIELD_NAME"])
			));
			$arResult["PSF"] = array_shift($PSFdata);		
		}
		//Gauge
		$gaugeArray = array();
		$gaugeData = array();

		$findingGauge = array_shift(CHighData::GetList(MODEL_LIVE_LOAD_HIGHLOAD, array("UF_MODEL_FIELD" => $request["MODEL"]), array("*")));
		foreach($findingGauge as $fieldName => $data)
		{
			$liveMeaning = explode("_", $fieldName);
			if(in_array("LIVE", $liveMeaning))
			{
				if(count($liveMeaning) == 4)
					$gaugeIndex = str_replace("GA", "", $liveMeaning[3]);
				elseif(count($liveMeaning) > 4)
					$gaugeIndex = $liveMeaning[3] . "/" . str_replace("GA", "", $liveMeaning[4]);
				$gaugeArray[] = $data;
				$gaugeData[] = array("MEANING" => $data, "INDEX" => $gaugeIndex);
			}
		}
		$gaugeMeaning = findNext($gaugeArray, $arResult["PSF"]);
		if($gaugeMeaning > $arResult["PSF"])
		{
			foreach($gaugeData as $data)
			{
				if($data["MEANING"] == $gaugeMeaning)
					$arResult["GAUGE_INDEX"] = $data["INDEX"];
			}
		}
		else {
			$arResult["GAUGE_INDEX"] = 0;
			$psfError = true;
		}
		//Front/rear wall
		$model = array_shift(array_shift(CHighData::GetList(MODEL_HIGHLOAD, array("ID" => $request["MODEL"]), array("UF_MODEL"))));
		$model = $model[1] . $model[2];
		$price = array_shift(array_shift(CHighData::GetList(ENDWALL_EXTENSION_HIGHLOAD, array("UF_MODEL_WIDTH" => $model), array("UF_ENDWALL_PRICE_PER_VERTICAL"))));
		$arResult["ENDWALL_EXTENSION"] = $price;
		if($request["FRONT_WALL_OFFSET"] == "YES")
			$arResult["FRONT_WALL_OFFSET"] = 1;
		else 
			$arResult["FRONT_WALL_OFFSET"] = 0;
		if($request["REAR_WALL_OFFSET"] == "YES")
			$arResult["REAR_WALL_OFFSET"] = 1;
		else 
			$arResult["REAR_WALL_OFFSET"] = 0;

		$endWallsData = array_shift(CHighData::GetList(RETAILED_PRICE_HIGHLOAD, 
			array("UF_RETAILED_PRICE_MODEL" => $request["MODEL"], "UF_THICKNESS_GAUGE" => $arResult["GAUGE_INDEX"]), 
			array("UF_SOLID_END_WALL", "UF_1_OUTER", "UF_PER_ARCH_NO_CAULK", "UF_CAULK_PER_ARCH", "UF_PIONEER_MODEL_PRICE")));
		$arResult["ENDWALLS_FRONT"] = $endWallsData["UF_SOLID_END_WALL"];
		$arResult["ENDWALLS_REAR"] = $endWallsData["UF_SOLID_END_WALL"];
		$arResult["OUTER_CA_FRONT"] = $endWallsData["UF_1_OUTER"];
		$arResult["OUTER_CA_REAR"] = $endWallsData["UF_1_OUTER"];
		$arResult["FRONT_WALL_SEA_HEIGHT"] = $request["FRONT_WALL_SEA_HEIGHT"];

		$arResult["ENDWALL_FRONT_QUANTITY"] = $request["FRONT_WALL_TYPE"] == OPEN_WALL_TYPE ? 0 : 1;
		$arResult["OUTER_CA_FRONT_QUANTITY"] = $arResult["ENDWALL_FRONT_QUANTITY"] == 1 ? 0 : 1;

		$arResult["ENDWALL_REAR_QUANTITY"] = $request["REAR_WALL_TYPE"] == OPEN_WALL_TYPE ? 0 : 1;
		$arResult["OUTER_CA_REAR_QUANTITY"] = $arResult["ENDWALL_REAR_QUANTITY"] == 1 ? 0 : 1;
		$arResult["REAR_WALL_SEA_HEIGHT"] = $request["REAR_WALL_SEA_HEIGHT"];
		$arResult["WALL_TOTAL"] = 
		($arResult["ENDWALL_FRONT_QUANTITY"] * $arResult["ENDWALLS_FRONT"]) + 
		($arResult["OUTER_CA_FRONT_QUANTITY"] * $arResult["OUTER_CA_FRONT"]) +
		($arResult["ENDWALL_REAR_QUANTITY"] * $arResult["ENDWALLS_REAR"]) +
		($arResult["OUTER_CA_REAR_QUANTITY"] * $arResult["OUTER_CA_REAR"]) +
		($arResult["FRONT_WALL_SEA_HEIGHT"] * $arResult["ENDWALL_EXTENSION"]) +
		($arResult["REAR_WALL_SEA_HEIGHT"] * $arResult["ENDWALL_EXTENSION"]) +
		($arResult["FRONT_WALL_OFFSET"] * ($arResult["ENDWALLS_FRONT"] * 0.1)) +
		($arResult["REAR_WALL_OFFSET"] * ($arResult["ENDWALLS_REAR"] * 0.1));


		//Arches
		$arResult["ARCHES"] = $request["SERIES"] == MINI_SERIE ? $request["LENGTH"] / 1.5 - 1 : $request["LENGTH"] / 2 - 1;

		//Foundation system
		if($request["FOUNDATION_SYSTEM"] != FOUNDATION_SYSTEM_THROUGH && $request["FOUNDATION_SYSTEM"] != FOUNDATION_SYSTEM_MONOLITHIC_POUR)
		{
			$arResult["ARCH_BASEPLATE_UNIT_COST"] = $request["FOUNDATION_SYSTEM"] == FOUNDATION_SYSTEM_INDUSTRIAL_BASEPLATES ? floatval($constants[ARCH_RETAIL_PRICE_FOR_INDUSTRIAL]) : 0;
			$arResult["ENDWALL_UNIT_COST"] = $request["FOUNDATION_SYSTEM"] == FOUNDATION_SYSTEM_INDUSTRIAL_BASEPLATES ? floatval($constants[ENDWALL_RETAIL_PRICE_FOR_INDUSTRIAL]) : 0;

			if($request["FOUNDATION_SYSTEM"] == FOUNDATION_SYSTEM_CHANNEL)
			{
				$arResult["CHANNEL_ARCH_UNIT_COST"] = $request["SERIES"] == MINI_SERIE ? floatval($constants[ARCH_RETAIL_PRICE_FOR_MINI_CHANNEL]) : floatval($constants[ARCH_RETAIL_PRICE_FOR_CHANNEL]);
				$arResult["CHANNEL_ENDWALL_UNIT_COST"] = $request["SERIES"] == MINI_SERIE ? floatval($constants[ENDWALL_RETAIL_PRICE_FOR_MINI_CHANNEL]) : floatval($constants[ENDWALL_RETAIL_PRICE_FOR_CHANNEL]);
			}
			else
			{
				$arResult["CHANNEL_ARCH_UNIT_COST"] = 0;
				$arResult["CHANNEL_ENDWALL_UNIT_COST"] = 0;
			}

			if($arResult["ARCH_BASEPLATE_UNIT_COST"] != 0)
				$arResult["ARCH_BASEPLATE_LENGTH"] = ($arResult["ARCHES"] < 10 ? $arResult["ARCHES"] + 1 : ($arResult["ARCHES"]) * 49 + 24) / 12;
			else
				$arResult["ARCH_BASEPLATE_LENGTH"] = 0;


			if($request["FRONT_WALL_TYPE"] == OPEN_WALL_TYPE && $request["REAR_WALL_TYPE"] == OPEN_WALL_TYPE)
			{
				$arResult["ENDWALL_BASEPLATE_LENGTH"] = 0;
				$arResult["CHANNEL_ENDWALL_LENGTH"] = 0;
			}	
			elseif(($request["FRONT_WALL_TYPE"] == OPEN_WALL_TYPE && $request["REAR_WALL_TYPE"] == SOLID_WALL_TYPE) || ($request["FRONT_WALL_TYPE"] == SOLID_WALL_TYPE && $request["REAR_WALL_TYPE"] == OPEN_WALL_TYPE))
			{
				$arResult["ENDWALL_BASEPLATE_LENGTH"] = floatval($request["WIDTH"]);
				$arResult["CHANNEL_ENDWALL_LENGTH"] = floatval($request["WIDTH"]);
			}
			else
			{
				$arResult["ENDWALL_BASEPLATE_LENGTH"] = floatval($request["WIDTH"]) * 2 - floatval($request["FRONT_WALL_WIDTH"]) - floatval($request["REAR_WALL_WIDTH"]);
				$arResult["CHANNEL_ENDWALL_LENGTH"] = floatval($request["WIDTH"]) * 2 - floatval($request["FRONT_WALL_WIDTH"]) - floatval($request["REAR_WALL_WIDTH"]);
			}

			if($arResult["CHANNEL_ARCH_UNIT_COST"] != 0)
			{
				if($request["SERIES"] == MINI_SERIE)
					$arResult["CHANNEL_ARCH_LENGTH"] = (($arResult["ARCHES"] < 10 ? $arResult["ARCHES"] + 1 : $arResult["ARCHES"]) * 36 + 24) / 12;
				else
					$arResult["CHANNEL_ARCH_LENGTH"] = (($arResult["ARCHES"] < 10 ? $arResult["ARCHES"] + 1 : $arResult["ARCHES"]) * 49 + 24) / 12;
			}
			else
				$arResult["CHANNEL_ARCH_LENGTH"] = 0;

			$arResult["CHANNEL_ARCH_TOTAL"] = $arResult["CHANNEL_ARCH_LENGTH"] * $arResult["CHANNEL_ARCH_UNIT_COST"];
			$arResult["CHANNEL_ENDWALL_TOTAL"] = $arResult["CHANNEL_ENDWALL_LENGTH"] * $arResult["CHANNEL_ENDWALL_UNIT_COST"];
			$arResult["ENDWALL_BASEPLATE_TOTAL"] = $arResult["ENDWALL_BASEPLATE_LENGTH"] * $arResult["ENDWALL_UNIT_COST"];
			$arResult["ARCH_BASEPLATE_TOTAL"] = $arResult["ARCH_BASEPLATE_UNIT_COST"] * $arResult["ARCH_BASEPLATE_LENGTH"];

			$arResult["FOUNDATION_SYSTEM_TOTAL"] = $arResult["CHANNEL_ARCH_TOTAL"] + $arResult["CHANNEL_ENDWALL_TOTAL"] + $arResult["ENDWALL_BASEPLATE_TOTAL"] + $arResult["ARCH_BASEPLATE_TOTAL"];
		}	
		else
		{
			$arResult["FOUNDATION_SYSTEM_TOTAL"] = 0;
		}

		//Arches cost
		//$arResult["ARCH_UNIT_COST"] = $endWallsData["UF_PER_ARCH_NO_CAULK"];
		if($request["BUILDING_COUNTRY"] == "СA")
			$arResult["ARCH_UNIT_COST"] = $endWallsData["UF_PIONEER_MODEL_PRICE"] * $constants[CA_VARIABLE_1] * $constants[CA_VARIABLE_2];
		elseif($request["BUILDING_COUNTRY"] == "US")
			$arResult["ARCH_UNIT_COST"] = $endWallsData["UF_PIONEER_MODEL_PRICE"] * $constants[US_VARIABLE_1] * $constants[US_VARIABLE_2];

		$arResult["ARCHES_COST"] = $arResult["ARCHES"] * $arResult["ARCH_UNIT_COST"];
		$arResult["ARCHES_CAULKING"] = $endWallsData["UF_CAULK_PER_ARCH"];
		if(isset($request["CAULKING"]) && $request["CAULKING"] == "Y" )
			$arResult["ARCHES_TOTAL"] = $arResult["ARCHES_COST"] + $arResult["ARCHES_CAULKING"];
		else
			$arResult["ARCHES_TOTAL"] = $arResult["ARCHES_COST"];
		//Drawings
		if(isset($request["DRAWINGS"]) && $request["DRAWINGS"] == "Y")
			$arResult["DRAWINGS"] = $constants[DRAWINGS];
		else
			$arResult["DRAWINGS"] = 0;

		//Anchors
		$arResult["ARCH_ANCHOR_WEDGES"] = isset($request["ANCHORS"]) && $request["ANCHORS"] == "Y" ? 4.7 : 0;
		$arResult["ARCH_ANCHOR_WEDGES_QUANTITY"] = $arResult["ARCH_ANCHOR_WEDGES"] != 0 ? $arResult["ARCHES"] * 4 + 2 : 0;
		$arResult["ARCH_ANCHOR_WEDGES_COST"] = $arResult["ARCH_ANCHOR_WEDGES"] != 0 ? $arResult["ARCH_ANCHOR_WEDGES_QUANTITY"] * $arResult["ARCH_ANCHOR_WEDGES"] : 0;

		$arResult["ENDWALL_ANCHOR_WEDGES"] = isset($request["ANCHORS"]) && $request["ANCHORS"] == "Y" ? 4.7 : 0;
		$arResult["ENDWALL_ANCHOR_WEDGES_QUANTITY"] = $arResult["ENDWALL_ANCHOR_WEDGES"] != 0 ? $request["WIDTH"] / 1.5 - 1 : 0;
		$arResult["ENDWALL_ANCHOR_WEDGES_COST"] = $arResult["ENDWALL_ANCHOR_WEDGES"] != 0 ? $arResult["ENDWALL_ANCHOR_WEDGES_QUANTITY"] * $arResult["ENDWALL_ANCHOR_WEDGES"] : 0;
		$arResult["ANCHORS_WEDGES_TOTAL"] = $arResult["ARCH_ANCHOR_WEDGES_COST"] + $arResult["ENDWALL_ANCHOR_WEDGES_COST"];

		//Accessory cost
		$arResult["ACCESSORY_TOTAL"] = (isset($request['TOTAL_ACCESSORIES_AMOUNT']) ? str_replace(array("$", ","), array("", "") , $request['TOTAL_ACCESSORIES_AMOUNT']) : 0) + 
		(isset($request['TOTAL_DOOR_AMOUNT']) ? str_replace(array("$", ","), array("", "") , $request['TOTAL_DOOR_AMOUNT']) : 0);
		$arResult["ACCESSORIES_BLOCK_TOTAL"] = isset($request['TOTAL_ACCESSORIES_AMOUNT']) ? str_replace(array("$", ","), array("", "") , $request['TOTAL_ACCESSORIES_AMOUNT']) : 0;
		$arResult["DOORS_BLOCK_TOTAL"] = isset($request['TOTAL_DOOR_AMOUNT']) ? str_replace(array("$", ","), array("", "") , $request['TOTAL_DOOR_AMOUNT']) : 0;
		//Cost
		if(isset($request['EDIT_BUILDING_TOTAL_COST']) && $request['EDIT_BUILDING_TOTAL_COST'] == "Y") 
			$arResult["COST"] = !empty($request['BUILDING_TOTAL_СOST']) ? str_replace(array("$", ","), array("", "") , $request['BUILDING_TOTAL_СOST']) : "";
		else
			$arResult["COST"] = $arResult["ARCHES_TOTAL"] + $arResult["WALL_TOTAL"] + $arResult["FOUNDATION_SYSTEM_TOTAL"] + $arResult["ANCHORS_WEDGES_TOTAL"] + $arResult["ACCESSORY_TOTAL"];

		//Weight
		$shippingWeight = array_shift(CHighData::GetList(SHIPPING_WEIGHT_HIGHLOAD, array("UF_SHIPPING_MODEL" => $request["MODEL"], "UF_SHIPPING_GAUGE" => $arResult["GAUGE_INDEX"]), array("UF_SHIPPING_PER_ARCH_NO_CAULK","UF_SHIPPING_SOLID_END_WALL", "UF_SHIPPING_1_OUTER")));

		$arResult["ARCH_UNIT_LBS"] = isset($shippingWeight["UF_SHIPPING_PER_ARCH_NO_CAULK"]) ? $shippingWeight["UF_SHIPPING_PER_ARCH_NO_CAULK"] : 0;
		$arResult["ACTUAL_ARCHES_WEIGHT"] = $arResult["ARCHES"] * $arResult["ARCH_UNIT_LBS"];

		$weightData = CHighData::GetList(WEIGHT_MEASURES_HIGHLOAD);
		$weightMeasures = array();
		$skid = array();
		foreach($weightData as $weight)
		{
			$weightMeasures[] = $weight["UF_WEIGHT_MEASURE"];
			$skid[$weight["UF_WEIGHT_MEASURE"]] = $weight["UF_SKID"];
		}

		$arResult["RATED_ARCHES_WEIGHT"] = findNext($weightMeasures, $arResult["ACTUAL_ARCHES_WEIGHT"]);

		$arResult["ARCHES_SKID"] = $skid[$arResult["RATED_ARCHES_WEIGHT"]];

		$arResult["ENDWALLS_FRONT_UNIT_LBS"] = isset($shippingWeight["UF_SHIPPING_SOLID_END_WALL"]) ? $shippingWeight["UF_SHIPPING_SOLID_END_WALL"] : 0;
		$arResult["ENDWALLS_REAR_UNIT_LBS"] = isset($shippingWeight["UF_SHIPPING_SOLID_END_WALL"]) ? $shippingWeight["UF_SHIPPING_SOLID_END_WALL"] : 0;

		$arResult["ENDWALLS_FRONT_TOTAL_LBS"] = $arResult["ENDWALLS_FRONT_UNIT_LBS"] * $arResult["ENDWALL_FRONT_QUANTITY"];
		$arResult["ENDWALLS_REAR_TOTAL_LBS"] = $arResult["ENDWALLS_REAR_UNIT_LBS"] * $arResult["ENDWALL_REAR_QUANTITY"];
		$arResult["ENDWALLS_SKID"] = ($arResult["ENDWALL_REAR_QUANTITY"] == 1 || $arResult["ENDWALL_FRONT_QUANTITY"] == 1) ? 1 : 0;

		$arResult["OUTER_CA_FRONT_UNIT_LBS"] = isset($shippingWeight["UF_SHIPPING_1_OUTER"]) ? $shippingWeight["UF_SHIPPING_1_OUTER"] : 0;
		$arResult["OUTER_CA_REAR_UNIT_LBS"] = isset($shippingWeight["UF_SHIPPING_1_OUTER"]) ? $shippingWeight["UF_SHIPPING_1_OUTER"] : 0;

		$arResult["OUTER_CA_FRONT_TOTAL_LBS"] = $arResult["OUTER_CA_FRONT_UNIT_LBS"] * $arResult["OUTER_CA_FRONT_QUANTITY"];
		$arResult["OUTER_CA_REAR_TOTAL_LBS"] = $arResult["OUTER_CA_REAR_UNIT_LBS"] * $arResult["OUTER_CA_REAR_QUANTITY"];

		$arResult["BASEPLATES_MINI_QUANTITY"] = (isset($request["FOUNDATION_SYSTEM"]) && !empty($request["FOUNDATION_SYSTEM"]) && $request["FOUNDATION_SYSTEM"] != FOUNDATION_SYSTEM_THROUGH) ? 1 : 0;
		//??????????????????????????????????
		$arResult["BASEPLATES_QUANTITY"] = 0;
		$arResult["BASEPLATES_MINI_UNIT_LBS"] = 4;
		$arResult["BASEPLATES_UNIT_LBS"] = 6;
		//??????????????????????????????????
		$arResult["BASEPLATES_MINI_TOTAL_LBS"] = $arResult["BASEPLATES_MINI_QUANTITY"] * $arResult["BASEPLATES_MINI_UNIT_LBS"];
		$arResult["BASEPLATES_TOTAL_LBS"] = $arResult["BASEPLATES_UNIT_LBS"] * $arResult["BASEPLATES_QUANTITY"];
		$arResult["BASEPLATES_SKID"] = $arResult["BASEPLATES_QUANTITY"] > 0 || $arResult["BASEPLATES_MINI_QUANTITY"] > 0 ? 1 : 0;

		
			$arResult["ARCHES_FREIGHT_COST"] = isset($request["COST"]) && !empty($request["COST"]) ? floatval(str_replace(array("$", ","), array("", ""), $request["COST"])) : "";




		$arResult["ENDWALL_FREIGHT"] = isset($arResult["ENDWALLS_SKID"]) && !empty($arResult["ENDWALLS_SKID"]) && $arResult["ENDWALLS_SKID"] == 1 ? floatval($constants[ENDWALL_FREIGHT]) : 0;
		$arResult["BASEPLATE_FREIGHT"] = isset($arResult["BASEPLATES_SKID"]) && !empty($arResult["BASEPLATES_SKID"]) && $arResult["BASEPLATES_SKID"] == 1 ? floatval($constants[BASEPLATE_FREIGHT]) : 0;
		$arResult["TOTAL_FREIGHT"] = $arResult["ENDWALL_FREIGHT"] + $arResult["ARCHES_FREIGHT_COST"];
		$arResult["ENDWALL_BASEPLATE_FREIGHT"] = $request['BUILDING_PROVINCE'] == ON_PROVINCE ? 0 : $arResult["ENDWALL_FREIGHT"] + $arResult["BASEPLATE_FREIGHT"];

		//Anchors wedges freight
		$arResult["ANCHORS_SUMMARY_QUANTITY"] = $arResult["ARCH_ANCHOR_WEDGES_QUANTITY"] + $arResult["ENDWALL_ANCHOR_WEDGES_QUANTITY"];
		//Totals
		$arResult["SUB_TOTAL"] = $arResult["DRAWINGS"] + $arResult["ARCHES_FREIGHT_COST"] + $arResult["ENDWALL_BASEPLATE_FREIGHT"];
		$arResult["TOTAL_COST"] = $arResult["SUB_TOTAL"] + $arResult["COST"];
		//Sold for 
		if(isset($request['EDIT_SOLD_FOR']) && $request['EDIT_SOLD_FOR'] == "Y") 
			$arResult["SOLD_FOR"] = !empty($request['SOLD_FOR']) ? str_replace(array("$", ","), array("", "") , $request['SOLD_FOR']) : "";
		else
			$arResult["SOLD_FOR"] = $arResult["TOTAL_COST"] * 1.9;
		//Asking
		if(isset($request['EDIT_ASKING']) && $request['EDIT_ASKING'] == "Y") 
			$arResult["ASKING"] = !empty($request['ASKING']) ? str_replace(array("$", ","), array("", "") , $request['ASKING']) : "";
		else
			$arResult["ASKING"] = $arResult["SOLD_FOR"] / 1.43;
		$arResult["DEPOSIT_REQUIRED"] = $arResult['ASKING'] * 0.25;
		$arResult["PROFIT"] = $arResult['SOLD_FOR'] - $arResult['ASKING'];
		$arAccessories = array();
		$accessoriesID = array();
		if(isset($request["ACCESSORIES_COUNT"]) && !empty($request["ACCESSORIES_COUNT"]))
		{
			for($searchIndex = 0; $searchIndex < $request["ACCESSORIES_COUNT"]; $searchIndex++)
			{
				$num = $searchIndex == 0 ? "" : "_".$searchIndex;
				$arAccessories[] = array(
					"ACCESSORIES_TYPE" => isset($request["ACCESSORIES_TYPE".$num]) && !empty($request["ACCESSORIES_TYPE".$num]) ? $request["ACCESSORIES_TYPE".$num] : "",
					"ACCESSORY" => isset($request["ACCESSORY".$num]) && !empty($request["ACCESSORY".$num]) ? $request["ACCESSORY".$num] : "",
					"ACCESSORIES_QUANTITY" => isset($request["ACCESSORIES_QUANTITY".$num]) && !empty($request["ACCESSORIES_QUANTITY".$num]) ? $request["ACCESSORIES_QUANTITY".$num] : "",
					"ACCESSORIES_WIDTH" => isset($request["ACCESSORIES_WIDTH".$num]) && !empty($request["ACCESSORIES_WIDTH".$num]) ? $request["ACCESSORIES_WIDTH".$num] : "",
					"ACCESSORIES_HEIGHT" => isset($request["ACCESSORIES_HEIGHT".$num]) && !empty($request["ACCESSORIES_HEIGHT".$num]) ? $request["ACCESSORIES_HEIGHT".$num] : "",
					"ACCESSORIES_AMOUNT" => isset($request["ACCESSORIES_AMOUNT".$num]) && !empty($request["ACCESSORIES_AMOUNT".$num]) ? str_replace(array("$", ","), array("", "") , $request["ACCESSORIES_AMOUNT".$num]) : "",
				);
				$accessoriesID[] = $request["ACCESSORY".$num];
			}
		}
		$arDoors = array();
		$doorsID = array();
		if(isset($request["DOORS_COUNT"]) && !empty($request["DOORS_COUNT"]))
		{
			for($searchIndex = 0; $searchIndex < $request["DOORS_COUNT"]; $searchIndex++)
			{
				$num = $searchIndex == 0 ? "" : "_".$searchIndex;
				$arDoors[] = array(
					"DOOR" => isset($request["DOOR".$num]) && !empty($request["DOOR".$num]) ? $request["DOOR".$num] : "",
					"DOOR_QUANTITY" => isset($request["DOOR_QUANTITY".$num]) && !empty($request["DOOR_QUANTITY".$num]) ? $request["DOOR_QUANTITY".$num] : "",
					"DOOR_WIDTH" => isset($request["DOOR_WIDTH".$num]) && !empty($request["DOOR_WIDTH".$num]) ? $request["DOOR_WIDTH".$num] : "",
					"DOOR_HEIGHT" => isset($request["DOOR_HEIGHT".$num]) && !empty($request["DOOR_HEIGHT".$num]) ? $request["DOOR_HEIGHT".$num] : "",
					"DOOR_AMOUNT" => isset($request["DOOR_AMOUNT".$num]) && !empty($request["DOOR_AMOUNT".$num]) ? str_replace(array("$", ","), array("", "") , $request["DOOR_AMOUNT".$num]) : "",
				);
				$doorsID[] =  $request["DOOR".$num];
			}
		}
		if($psfError == true)
		{
			$arResult["SOLD_FOR"] = 0;
			$arResult["ASKING"] = 0;
			$arResult["COST"] = 0;
			$arResult["ARCHES_FREIGHT_COST"] = 0;
		}
		//Saving
		$savingData = array(
			"UF_QUOTATION_MODIFIED" => date('m/d/Y h:i:s a', time()),
			"UF_QUOTATION_ENTITY_TYPE" => isset($request["ENTITY_TYPE"]) && !empty($request["ENTITY_TYPE"]) ? $request["ENTITY_TYPE"] : "",
			"UF_QUOTATION_DATE" => isset($request["DATE"]) && !empty($request["DATE"]) ? $request["DATE"] : "",
			"UF_QUOTATION_CUSTOMER_ID" => isset($request["CUSTOMER_ID"]) && !empty($request["CUSTOMER_ID"]) ? $request["CUSTOMER_ID"] : "",
			"UF_QUOTATION_PURCHASE_ORDER" => isset($request["PURCHASE_ORDER"]) && !empty($request["PURCHASE_ORDER"]) ? $request["PURCHASE_ORDER"] : "",
			"UF_QUOATION_OWNER" => isset($request["QUOATION_OWNER"]) && !empty($request["QUOATION_OWNER"]) ? str_replace("U", "", $request["QUOATION_OWNER"]) : "",
			"UF_QUOTATION_CUSTOMER_DATA" => serialize(
				array(
					"CUSTOMER_NAME" => isset($request["CUSTOMER_NAME"]) && !empty($request["CUSTOMER_NAME"]) ? $request["CUSTOMER_NAME"] : "",
					"CUSTOMER_TEL" => isset($request["CUSTOMER_TEL"]) && !empty($request["CUSTOMER_TEL"]) ? $request["CUSTOMER_TEL"] : "",
					"CUSTOMER_CELL" => isset($request["CUSTOMER_CELL"]) && !empty($request["CUSTOMER_CELL"]) ? $request["CUSTOMER_CELL"] : "",
					"CUSTOMER_COMPANY" => isset($request["CUSTOMER_COMPANY"]) && !empty($request["CUSTOMER_COMPANY"]) ? $request["CUSTOMER_COMPANY"] : "",
					"CUSTOMER_WORK_TEL" => isset($request["CUSTOMER_WORK_TEL"]) && !empty($request["CUSTOMER_WORK_TEL"]) ? $request["CUSTOMER_WORK_TEL"] : "",
					"CUSTOMER_EMAIL" => isset($request["CUSTOMER_EMAIL"]) && !empty($request["CUSTOMER_EMAIL"]) ? $request["CUSTOMER_EMAIL"] : "",
					"CUSTOMER_PROVINCE" => isset($request["CUSTOMER_PROVINCE"]) && !empty($request["CUSTOMER_PROVINCE"]) ? $request["CUSTOMER_PROVINCE"] : "",
					"CUSTOMER_CITY" => isset($request["CUSTOMER_CITY"]) && !empty($request["CUSTOMER_CITY"]) ? $request["CUSTOMER_CITY"] : "",
					"CUSTOMER_POSTAL_CODE" => isset($request["CUSTOMER_POSTAL_CODE"]) && !empty($request["CUSTOMER_POSTAL_CODE"]) ? $request["CUSTOMER_POSTAL_CODE"] : "",
					"CUSTOMER_ADDRESS" => isset($request["CUSTOMER_ADDRESS"]) && !empty($request["CUSTOMER_ADDRESS"]) ? $request["CUSTOMER_ADDRESS"] : "",
				)
			),
			"UF_QUOATION_BUILDING_DATA" => serialize(
				array(
					"BUILDING_PROVINCE" => isset($request["BUILDING_PROVINCE"]) && !empty($request["BUILDING_PROVINCE"]) ? $request["BUILDING_PROVINCE"] : "",
					"BUILDING_CITY" => isset($request["BUILDING_CITY"]) && !empty($request["BUILDING_CITY"]) ? $request["BUILDING_CITY"] : "",
					"BUILDING_POSTAL_CODE" => isset($request["BUILDING_POSTAL_CODE"]) && !empty($request["BUILDING_POSTAL_CODE"]) ? $request["BUILDING_POSTAL_CODE"] : "",
					"BUILDING_ADDRESS" => isset($request["BUILDING_ADDRESS"]) && !empty($request["BUILDING_ADDRESS"]) ? $request["BUILDING_ADDRESS"] : "",
					"BUILDING_COUNTRY" => isset($request["BUILDING_COUNTRY"]) && !empty($request["BUILDING_COUNTRY"]) ? $request["BUILDING_COUNTRY"] : "",
				)
			),
			"UF_QUOATION_EXPOSURE_DATA" => serialize(
				array(
					"USE_EXPOSURE" => isset($request["USE_EXPOSURE"]) && !empty($request["USE_EXPOSURE"]) ? $request["USE_EXPOSURE"] : "",
					"SERIES" => isset($request["SERIES"]) && !empty($request["SERIES"]) ? $request["SERIES"] : "",
					"MODEL" => isset($request["MODEL"]) && !empty($request["MODEL"]) ? $request["MODEL"] : "",
					"FOUNDATION_SYSTEM" => isset($request["FOUNDATION_SYSTEM"]) && !empty($request["FOUNDATION_SYSTEM"]) ? $request["FOUNDATION_SYSTEM"] : "",
					"WIDTH" => isset($request["WIDTH"]) && !empty($request["WIDTH"]) ? $request["WIDTH"] : "",
					"LENGTH" => isset($request["LENGTH"]) && !empty($request["LENGTH"]) ? $request["LENGTH"] : "",
					"HEIGHT" => isset($request["HEIGHT"]) && !empty($request["HEIGHT"]) ? $request["HEIGHT"] : "",
					"ANCHORS" => isset($request["ANCHORS"]) && !empty($request["ANCHORS"]) ? "Y" : "N",
					"PSF" => isset($request["PSF"]) && !empty($request["PSF"]) ? $request["PSF"] : "",
				)
			),
			"UF_FRONT_WALL_DATA" => serialize(
				array(
					"FRONT_WALL_TYPE" => isset($request["FRONT_WALL_TYPE"]) && !empty($request["FRONT_WALL_TYPE"]) ? $request["FRONT_WALL_TYPE"] : "",
					"FRONT_WALL_QUANTITY" => isset($request["FRONT_WALL_QUANTITY"]) && !empty($request["FRONT_WALL_QUANTITY"]) ? $request["FRONT_WALL_QUANTITY"] : "",
					"FRONT_WALL_WIDTH" => isset($request["FRONT_WALL_WIDTH"]) && !empty($request["FRONT_WALL_WIDTH"]) ? $request["FRONT_WALL_WIDTH"] : "",
					"FRONT_WALL_HEIGHT" => isset($request["FRONT_WALL_HEIGHT"]) && !empty($request["FRONT_WALL_HEIGHT"]) ? $request["FRONT_WALL_HEIGHT"] : "",
					"FRONT_WALL_SEA_HEIGHT" => isset($request["FRONT_WALL_SEA_HEIGHT"]) && !empty($request["FRONT_WALL_SEA_HEIGHT"]) ? $request["FRONT_WALL_SEA_HEIGHT"] : "",
					"FRONT_WALL_OFFSET" => isset($request["FRONT_WALL_OFFSET"]) && !empty($request["FRONT_WALL_OFFSET"]) ? $request["FRONT_WALL_OFFSET"] : "",
				)
			),
			"UF_REAR_WALL_DATA" => serialize(
				array(
					"REAR_WALL_TYPE" => isset($request["REAR_WALL_TYPE"]) && !empty($request["REAR_WALL_TYPE"]) ? $request["REAR_WALL_TYPE"] : "",
					"REAR_WALL_QUANTITY" => isset($request["REAR_WALL_QUANTITY"]) && !empty($request["REAR_WALL_QUANTITY"]) ? $request["REAR_WALL_QUANTITY"] : "",
					"REAR_WALL_WIDTH" => isset($request["REAR_WALL_WIDTH"]) && !empty($request["REAR_WALL_WIDTH"]) ? $request["REAR_WALL_WIDTH"] : "",
					"REAR_WALL_HEIGHT" => isset($request["REAR_WALL_HEIGHT"]) && !empty($request["REAR_WALL_HEIGHT"]) ? $request["REAR_WALL_HEIGHT"] : "",
					"REAR_WALL_SEA_HEIGHT" => isset($request["REAR_WALL_SEA_HEIGHT"]) && !empty($request["REAR_WALL_SEA_HEIGHT"]) ? $request["REAR_WALL_SEA_HEIGHT"] : "",
					"REAR_WALL_OFFSET" => isset($request["REAR_WALL_OFFSET"]) && !empty($request["REAR_WALL_OFFSET"]) ? $request["REAR_WALL_OFFSET"] : "",
				)
			),
			"UF_QUOTATION_ACCESSORIES_DATA" => serialize($arAccessories),
			"UF_QUOTATION_DOORS_DATA" => serialize($arDoors),
			"UF_QUOTATION_FREIGHT" => serialize(
				array(
					"EDIT_FREIGHT_MANUALLY" => isset($request["EDIT_FREIGHT_MANUALLY"]) && !empty($request["EDIT_FREIGHT_MANUALLY"]) ? $request["EDIT_FREIGHT_MANUALLY"] : "",
					// "COST" => isset($request["COST"]) && !empty($request["COST"]) ? floatval(str_replace(array("$", ","), array("", ""), $request["COST"])) : "",
					"COST" => isset($request["EDIT_FREIGHT_MANUALLY"]) && empty($request["EDIT_FREIGHT_MANUALLY"] == "Y") ? $request["COST"] : $arResult["ARCHES_FREIGHT_COST"],
				)
			),
			"UF_QUOTATION_OTHER" => serialize(
				array(
					"EDIT_FREIGHT_MANUALLY" => isset($request["EDIT_FREIGHT_MANUALLY"]) && !empty($request["EDIT_FREIGHT_MANUALLY"]) ? $request["EDIT_FREIGHT_MANUALLY"] : "",
					"SOLD_FOR" => $arResult["SOLD_FOR"],
					"ASKING" => $arResult["ASKING"],
					"DRAWINGS" => isset($request["DRAWINGS"]) && !empty($request["DRAWINGS"]) ? "Y" : "N",
					"ESTIMATED_DELIVERY" => isset($request["ESTIMATED_DELIVERY"]) && !empty($request["ESTIMATED_DELIVERY"]) ? $request["ESTIMATED_DELIVERY"] : "",
					"INSULATION" => isset($request["INSULATION"]) && !empty($request["INSULATION"]) ? $request["INSULATION"] : "",
					"NOTES" => isset($request["NOTES"]) && !empty($request["NOTES"]) ? $request["NOTES"] : "",
					"EDIT_BUILDING_TOTAL_COST" => isset($request["EDIT_BUILDING_TOTAL_COST"]) && !empty($request["EDIT_BUILDING_TOTAL_COST"]) ? "Y" : "N",
					"EDIT_SOLD_FOR" => isset($request["EDIT_SOLD_FOR"]) && !empty($request["EDIT_SOLD_FOR"]) ? "Y" : "N",
					"EDIT_ASKING" => isset($request["EDIT_ASKING"]) && !empty($request["EDIT_ASKING"]) ? "Y" : "N",
					"CAULKING" => isset($request["CAULKING"]) && !empty($request["CAULKING"]) ? $request["CAULKING"] : "",
				)
			),
			"UF_CALCULATION" => serialize($arResult),
			"UF_ASKING" => $arResult["ASKING"],
			"UF_COST" => $arResult["COST"],
			"UF_SELECTED_CITY" => isset($request["BUILDING_CITY"]) && !empty($request["BUILDING_CITY"]) ? $request["BUILDING_CITY"] : "",
			"UF_SELECTED_MODEL" => isset($request["MODEL"]) && !empty($request["MODEL"]) ? $request["MODEL"] : "",

		); 
if(isset($request["SAVE_DATA"]) && $request["SAVE_DATA"] == "Y")
	$res = CHighData::AddRecord(QUOTATION_SYSTEM_HIGHLOAD, $savingData);
elseif(isset($request["UPDATE_DATA"]) && $request["UPDATE_DATA"] == "Y")
	$res = CHighData::UpdateRecord(QUOTATION_SYSTEM_HIGHLOAD, $request["QUOTATION_ID"], $savingData);
		//Exel
if(!empty($request["PURCHASE_ORDER"]))
	$purchaseOrder = array_shift(array_shift(CHighData::GetList(PURCHASE_ORDER_LIST, array("ID" => $request["PURCHASE_ORDER"]), array("UF_PURCHASE_ORDER"))));
if(!empty($request["BUILDING_PROVINCE"]))
	$buildingProvince = array_shift(array_shift(CHighData::GetList(PROVINCES_HIGHLOAD, array("ID" => $request["BUILDING_PROVINCE"]), array("UF_PROVINCE_NAME"))));
if(!empty($request["BUILDING_CITY"]))
	$buildingCity = array_shift(array_shift(CHighData::GetList(CITIES_HIGHLOAD, array("ID" => $request["BUILDING_CITY"]), array("UF_CITY"))));
if(!empty($request["SERIES"]))
	$serie = array_shift(array_shift(CHighData::GetList(SERIES_LIST, array("ID" => $request["SERIES"]), array("UF_SERIES"))));
if(!empty($request["USE_EXPOSURE"]))
	$use = array_shift(array_shift(CHighData::GetList(USE_EXPOSURE_LIST, array("ID" => $request["USE_EXPOSURE"]), array("UF_USE_EXPOSURE"))));
if(!empty($request["MODEL"]))
	$model = array_shift(array_shift(CHighData::GetList(MODEL_HIGHLOAD, array("ID" => $request["MODEL"]), array("UF_MODEL"))));
if(!empty($request["FOUNDATION_SYSTEM"]))
	$foundationSystem = array_shift(array_shift(CHighData::GetList(FOUNDATION_SYSTEM_LIST, array("ID" => $request["FOUNDATION_SYSTEM"]), array("UF_FOUNDATION_SYSTEM"))));
if(!empty($request["FRONT_WALL_TYPE"]))
	$frontWall = array_shift(array_shift(CHighData::GetList(WALL_TYPE_LIST, array("ID" => $request["FRONT_WALL_TYPE"]), array("UF_WALL_TYPE"))));
if(!empty($request["REAR_WALL_TYPE"]))
	$rearWall = array_shift(array_shift(CHighData::GetList(WALL_TYPE_LIST, array("ID" => $request["REAR_WALL_TYPE"]), array("UF_WALL_TYPE"))));

$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($_SERVER['DOCUMENT_ROOT'].'/local/components/custom/quotation.system/quatation_example.xlsx');
$worksheet = $spreadsheet->getActiveSheet();
		//Quotation data
$worksheet->getCell('C6')->setValue(isset($request["DATE"]) && !empty($request["DATE"]) ? $request["DATE"]: "");
$worksheet->getCell('E6')->setValue(!empty($purchaseOrder) ? $purchaseOrder : "");
$worksheet->getCell('H6')->setValue("Quote №.".$res);
		//Customer data
$worksheet->getCell('J6')->setValue(isset($request["CUSTOMER_ID"]) && !empty($request["CUSTOMER_ID"]) ? $request["CUSTOMER_ID"] : "");
$worksheet->getCell('A8')->setValue(isset($request["CUSTOMER_NAME"]) && !empty($request["CUSTOMER_NAME"]) ? $request["CUSTOMER_NAME"] : "");
$worksheet->getCell('D8')->setValue(isset($request["CUSTOMER_COMPANY"]) && !empty($request["CUSTOMER_COMPANY"]) ? $request["CUSTOMER_COMPANY"] : "");
$worksheet->getCell('F8')->setValue(isset($request["CUSTOMER_TEL"]) && !empty($request["CUSTOMER_TEL"]) ? $request["CUSTOMER_TEL"] : "");
$worksheet->getCell('G8')->setValue(isset($request["CUSTOMER_CELL"]) && !empty($request["CUSTOMER_CELL"]) ? $request["CUSTOMER_CELL"] : "");
$worksheet->getCell('H8')->setValue(isset($request["CUSTOMER_WORK_TEL"]) && !empty($request["CUSTOMER_WORK_TEL"]) ? $request["CUSTOMER_WORK_TEL"] : "");
$worksheet->getCell('I8')->setValue(isset($request["CUSTOMER_EMAIL"]) && !empty($request["CUSTOMER_EMAIL"]) ? $request["CUSTOMER_EMAIL"] : "");
		//Customer address
$worksheet->getCell('A10')->setValue(isset($request["CUSTOMER_ADDRESS"]) && !empty($request["CUSTOMER_ADDRESS"]) ? $request["CUSTOMER_ADDRESS"] : "");
$worksheet->getCell('E10')->setValue(isset($request["CUSTOMER_CITY"]) && !empty($request["CUSTOMER_CITY"]) ? $request["CUSTOMER_CITY"] : "");
$worksheet->getCell('G10')->setValue(isset($request["CUSTOMER_PROVINCE"]) && !empty($request["CUSTOMER_PROVINCE"]) ? $request["CUSTOMER_PROVINCE"] : "");
$worksheet->getCell('I10')->setValue(isset($request["CUSTOMER_POSTAL_CODE"]) && !empty($request["CUSTOMER_POSTAL_CODE"]) ? $request["CUSTOMER_POSTAL_CODE"] : "");
		//Building address
$worksheet->getCell('A12')->setValue(isset($request["BUILDING_ADDRESS"]) && !empty($request["BUILDING_ADDRESS"]) ? $request["BUILDING_ADDRESS"] : "");
$worksheet->getCell('E12')->setValue(!empty($buildingCity) ? $buildingCity : "");
$worksheet->getCell('G12')->setValue(!empty($buildingProvince) ? $buildingProvince : "");
$worksheet->getCell('I12')->setValue(isset($request["BUILDING_POSTAL_CODE"]) && !empty($request["BUILDING_POSTAL_CODE"]) ? $request["BUILDING_POSTAL_CODE"] : "");
		//Ship to address
$worksheet->getCell('A15')->setValue(isset($request["BUILDING_ADDRESS"]) && !empty($request["BUILDING_ADDRESS"]) ? $request["BUILDING_ADDRESS"] : "");
$worksheet->getCell('E15')->setValue(!empty($buildingCity) ? $buildingCity : "");
$worksheet->getCell('G15')->setValue(!empty($buildingProvince) ? $buildingProvince : "");
$worksheet->getCell('I15')->setValue(isset($request["BUILDING_POSTAL_CODE"]) && !empty($request["BUILDING_POSTAL_CODE"]) ? $request["BUILDING_POSTAL_CODE"] : "");
		//Exposure
$worksheet->getCell('A18')->setValue(!empty($use) ? $use : "");
$worksheet->getCell('C18')->setValue(!empty($serie) ? $serie : "");
$worksheet->getCell('D18')->setValue(!empty($model) ? $model : "");
$worksheet->getCell('E18')->setValue(!empty($foundationSystem) ? $foundationSystem : "");
$worksheet->getCell('F18')->setValue(isset($request["ANCHORS"]) && !empty($request["ANCHORS"]) ? "ANCHORS" : "");
$worksheet->getCell('G18')->setValue(isset($request["WIDTH"]) && !empty($request["WIDTH"]) ? $request["WIDTH"] : "");
$worksheet->getCell('H18')->setValue(isset($request["LENGTH"]) && !empty($request["LENGTH"]) ? $request["LENGTH"] : "");
$worksheet->getCell('I18')->setValue(isset($request["HEIGHT"]) && !empty($request["HEIGHT"]) ? $request["HEIGHT"] : "");
$worksheet->getCell('J18')->setValue(isset($arResult["GAUGE_INDEX"]) && !empty($arResult["GAUGE_INDEX"]) ? $arResult["GAUGE_INDEX"] : "");
		//Front wall
$worksheet->getCell('C23')->setValue(!empty($frontWall) ? $frontWall : "");
$worksheet->getCell('E23')->setValue(!empty($request["FRONT_WALL_TYPE"]) && $request["FRONT_WALL_TYPE"] == 1? "LARGE C/A" : "INCLUDED");
$worksheet->getCell('C25')->setValue(isset($request["FRONT_WALL_QUANTITY"]) && !empty($request["FRONT_WALL_QUANTITY"]) ? $request["FRONT_WALL_QUANTITY"] : "");
$worksheet->getCell('D25')->setValue(isset($request["FRONT_WALL_WIDTH"]) && !empty($request["FRONT_WALL_WIDTH"]) ? $request["FRONT_WALL_WIDTH"] : "");
$worksheet->getCell('E25')->setValue(isset($request["FRONT_WALL_HEIGHT"]) && !empty($request["FRONT_WALL_HEIGHT"]) ? $request["FRONT_WALL_HEIGHT"] : "");
		//Rear wall
$worksheet->getCell('C26')->setValue(!empty($rearWall) ? $rearWall : "");
$worksheet->getCell('E26')->setValue(!empty($request["REAR_WALL_TYPE"]) && $request["REAR_WALL_TYPE"] == 1? "LARGE C/A" : "INCLUDED");
$worksheet->getCell('C28')->setValue(isset($request["REAR_WALL_QUANTITY"]) && !empty($request["REAR_WALL_QUANTITY"]) ? $request["REAR_WALL_QUANTITY"] : "");
$worksheet->getCell('D28')->setValue(isset($request["REAR_WALL_WIDTH"]) && !empty($request["REAR_WALL_WIDTH"]) ? $request["REAR_WALL_WIDTH"] : "");
$worksheet->getCell('E28')->setValue(isset($request["REAR_WALL_HEIGHT"]) && !empty($request["REAR_WALL_HEIGHT"]) ? $request["REAR_WALL_HEIGHT"] : "");
		//Accessories
$countAccessories = count($arAccessories);
if($countAccessories >= 3 && $countAccessories <= 6) 
{
	$firstRow = 3;
}
elseif($countAccessories > 6)
{
	$addingRow = ceil(($countAccessories-6)/2);
	$firstRow = 3 + $addingRow;
	$worksheet->insertNewRowBefore(34,$addingRow);
}
else
	$firstRow = 3;
$accesoriesName = CHighData::GetList(ACCESSORIES_HIGHLOAD, array("ID" => $accessoriesID), array("UF_ACCESSORIES_TYPE", "ID"));	
$first = 32;
$second = 32;
foreach ($arAccessories as $key => $item)
{
	foreach($accesoriesName as $name)
	{
		if($item['ACCESSORY'] == $name['ID'])
		{
			if($key+1 <= $firstRow)
			{
				$worksheet->getCell('A'.$first)->setValue($name['UF_ACCESSORIES_TYPE']);
				$worksheet->getCell('E'.$first)->setValue($item['ACCESSORIES_QUANTITY']);
				$first++;
			}
			else
			{
				$worksheet->getCell('G'.$second)->setValue($name['UF_ACCESSORIES_TYPE']);
				$worksheet->getCell('J'.$second)->setValue($item['ACCESSORIES_QUANTITY']);
				$second++;
			}

		}
	}
}
$totalNum = $first <= 35 ? 35 : $first;
if(count($arDoors) > 2)
	$worksheet->insertNewRowBefore(($totalNum + 3), count($arDoors)-2);
$doorsName = CHighData::GetList(ACCESSORIES_HIGHLOAD, array("ID" => $doorsID), array("UF_ACCESSORIES_TYPE", "ID"));	
$num = $totalNum + 2;
foreach ($arDoors as $key => $item)
{
	foreach($doorsName as $name)
	{
		if($item['DOOR'] == $name['ID'])
		{
			$worksheet->getCell('A'.$num)->setValue($name['UF_ACCESSORIES_TYPE']);
		}
	}
	$worksheet->getCell('E'.$num)->setValue($item['DOOR_QUANTITY']);
	$worksheet->getCell('F'.$num)->setValue($item['DOOR_WIDTH']);
	$worksheet->getCell('G'.$num)->setValue($item['DOOR_HEIGHT']);
	$num++;
}

$totalNum = $num <= 39 ? 39 : $num;
if(count($arDoors) < 2)
	$totalNum += 1;
// $total = $arResult['COST'] + $arResult['ARCHES_FREIGHT_COST'];
$total = $arResult["ASKING"] + $arResult['ARCHES_FREIGHT_COST'];
$worksheet->getCell('E'.($totalNum + 1))->setValue(!empty($request["INSULATION"]) && $request["INSULATION"] == "Y" ? "Included" : "Not included");
$worksheet->getCell('A'.($totalNum + 7))->setValue(isset($request["NOTES"]) && !empty($request["NOTES"]) ? $request["NOTES"] : "");
// $worksheet->getCell('J'.($totalNum + 11))->setValue(isset($arResult['COST']) && !empty($arResult['COST']) ? $arResult['COST'] : "");
// $worksheet->getCell('J'.($totalNum + 12))->setValue(isset($arResult['ARCHES_FREIGHT_COST']) && !empty($arResult['ARCHES_FREIGHT_COST']) ? $arResult['ARCHES_FREIGHT_COST'] : "");
$worksheet->getCell('J'.($totalNum + 11))->setValue(isset($arResult["ASKING"]) && !empty($arResult["ASKING"]) ? $arResult["ASKING"] : "");
$worksheet->getCell('J'.($totalNum + 12))->setValue(isset($arResult['ARCHES_FREIGHT_COST']) && !empty($arResult['ARCHES_FREIGHT_COST']) ? $arResult['ARCHES_FREIGHT_COST'] : "");
$worksheet->getCell('J'.($totalNum + 13))->setValue($total);

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save($_SERVER['DOCUMENT_ROOT'].'/local/components/custom/quotation.system/quatation_print.xlsx');
exec("libreoffice --headless --convert-to pdf:calc_pdf_Export --outdir /home/bitrix/www/local/components/custom/quotation.system /home/bitrix/www/local/components/custom/quotation.system/quatation_print.xlsx");
$file = CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'].'/local/components/custom/quotation.system/quatation_print.pdf');
$id = isset($request["QUOTATION_ID"]) && !empty($request["QUOTATION_ID"]) ? $request["QUOTATION_ID"] : $res;
$res = CHighData::UpdateRecord(QUOTATION_SYSTEM_HIGHLOAD, $id, array("UF_DOCUMENT_PDF" => $file));
if($weightError == true){
	echo json_encode(array(
		"STATUS" => "success",
		"WEIGHT_ERROR" => "true"
	));
} 
else
	echo json_encode("success");

}
}