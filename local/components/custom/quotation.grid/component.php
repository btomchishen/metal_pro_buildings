<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Highloadblock\HighloadBlockTable as HLBT;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;

$requiredModules = array('highloadblock');
foreach ($requiredModules as $requiredModule)
{
	if (!CModule::IncludeModule($requiredModule))
	{
		ShowError(GetMessage("F_NO_MODULE"));
		return false;
	}
}
$models = CHighData::GetList(MODEL_HIGHLOAD);
$cities = CHighData::GetList(CITIES_HIGHLOAD);
$arResult["MODELS"] = array();
$arResult["CITIES"] = array();
global $USER;
$arResult["USERS"] = array();
$rsUsers = CUser::GetList(($by = "NAME"), ($order = "desc"), array());
while ($arUser = $rsUsers->Fetch())
	$arResult["USERS"][$arUser["ID"]] = $arUser["NAME"] . " " . $arUser["LAST_NAME"];
foreach($models as $model)
   $arResult["MODELS"][$model["ID"]] = $model["UF_MODEL"];
foreach($cities as $city)
	$arResult["CITIES"][$city["ID"]] = $city["UF_CITY"];

$grid_options = new Bitrix\Main\Grid\Options('quotation_list');
$sort = $grid_options->GetSorting(
	array('sort' => array("UF_QUOTATION_MODIFIED" => "DESC"), 'vars' => array('by' => 'by', 'order' => 'order'))
);

$nav_params = $grid_options->GetNavParams();
$entity = HLBT::compileEntity(QUOTATION_SYSTEM_HIGHLOAD);
$entity_data_class = $entity->getDataClass();
$arResult["NAV"] = new \Bitrix\Main\UI\PageNavigation('quotation_list');
$arResult["NAV"]->allowAllRecords(true)
	->setPageSize(20)
	->initFromUri();
$filterOption = new Bitrix\Main\UI\Filter\Options('quotation_list');
$filterData = $filterOption->getFilter();

$filter = array();
if(isset($arParams["ENTITY_TYPE"]) && !empty($arParams["ENTITY_TYPE"]))
{
	$filter["UF_QUOTATION_CUSTOMER_ID"] = $arParams["ENTITY_TYPE"]."_".$arParams["QUATATION_ENTITY_ID"];
}	
foreach ($filterData as $field => $data) {
	if($field == "ID")
		$filter[$field] = $data;
	if($field == "DATE_datesel")
	{
		if(!empty($filterData["DATE_from"]))
			$filter[">=UF_QUOTATION_MODIFIED"] = ConvertDateTime($filterData["DATE_from"], FORMAT_DATETIME);
		if(!empty($filterData["DATE_to"]))
			$filter["<=UF_QUOTATION_MODIFIED"] = ConvertDateTime($filterData["DATE_to"], FORMAT_DATETIME);
	}
	

	if($field == "OWNER")
		$filter["UF_QUOATION_OWNER"] = $data;
	if($field == "ASKING_numsel")
	{
		if(!empty($filterData["ASKING_from"]))
			$filter[">=UF_ASKING"] = floatval($filterData["ASKING_from"]);
		if(!empty($filterData["ASKING_to"]))
			$filter["<=UF_ASKING"] = floatval($filterData["ASKING_to"]);
	}
	if($field == "COST_numsel")
	{
		if(!empty($filterData["COST_from"]))
			$filter[">=UF_COST"] = floatval($filterData["COST_from"]);
		if(!empty($filterData["COST_to"]))
			$filter["<=UF_COST"] = floatval($filterData["COST_to"]);
	}
	if($field == "MODEL")
		$filter["UF_SELECTED_MODEL"] = $data;
	if($field == "CITY")
		$filter["UF_SELECTED_CITY"] = $data;
	if($field == "FIND" && !empty($filterData["FIND"]))
	{
		$filter = array(
			array(
			   "LOGIC"=>"OR",
			   array(
				  "ID"=>$data
			   ),
			   array(
				"UF_QUOATION_OWNER"=> "%".$data."%"
			   ),
			   array(
				"UF_ASKING"=> floatval($data)
			   ),
			   array(
				"UF_COST"=> floatval($data)
			   ),
			)
		 );
	}
}
$rsData = $entity_data_class::getList(array(
	"select" => array('*'),
	'filter' => $filter,
	"count_total" => true, 
	'order' => $sort["sort"],
	"offset" => $arResult["NAV"]->getOffset(), 
	"limit" => $arResult["NAV"]->getLimit(), 
));
$arResult["ALL_ROWS"] = $rsData->getCount();
$arResult["NAV"]->setRecordCount($arResult["ALL_ROWS"]);

$quotations = array();
$arResult["LIST"] = array();
$arResult["QUOTATION_DATA"] = array();

while($elements = $rsData->fetch())
{
	$quotations[] = $elements;
}

foreach($quotations as $quotation)
{
	$city = array_shift(CHighData::GetList(CITIES_HIGHLOAD, array("ID" => unserialize($quotation["UF_QUOATION_BUILDING_DATA"])["BUILDING_CITY"])));
	$model = array_shift(CHighData::GetList(MODEL_HIGHLOAD, array("ID" => unserialize($quotation["UF_QUOATION_EXPOSURE_DATA"])["MODEL"])));
	$rsUser = CUser::GetByID($quotation["UF_QUOATION_OWNER"]);
	$arUser = $rsUser->Fetch();
	$arResult["LIST"][] = array(
		'data' => array( 
			"ID" => $quotation["ID"],
			// "QUOTATION_NUMBER" => isset($arParams["FROM_ENTITY"]) && $arParams["FROM_ENTITY"] == "Y" ? "Quote №.".$quotation["ID"] : "<a href='/quotation_system/".$quotation["ID"]."/'>Quote №.".$quotation["ID"]."</a>",
			"QUOTATION_NUMBER" => isset($arParams["FROM_ENTITY"]) && $arParams["FROM_ENTITY"] == "Y" ? "<a href='#' onclick='showQuotation(".$quotation['ID'].");'>Quote №.".$quotation["ID"]."</a>" : "<a href='/quotation_system/".$quotation["ID"]."/'>Quote №.".$quotation["ID"]."</a>",
			// "QUOTATION_NUMBER" =>"<a href='/quotation_system/".$quotation["ID"]."/'>Quote №.".$quotation["ID"]."</a>",
			"MODEL" => $model["UF_MODEL"],
			"OWNER" => $arUser["NAME"] . " " . $arUser["LAST_NAME"],
			"DATE" => (!empty($quotation["UF_QUOTATION_MODIFIED"]))	? ConvertDateTime($quotation["UF_QUOTATION_MODIFIED"], "DD.MM.YYYY HH:MI:SS") : '',
			'ASKING' => !empty(unserialize($quotation["UF_QUOTATION_OTHER"])['ASKING']) ? '$'.number_format(round(unserialize($quotation["UF_QUOTATION_OTHER"])['ASKING'], 2), 2, '.', ',') : '',
			'COST' => !empty(unserialize($quotation["UF_CALCULATION"])["COST"]) ? '$'.number_format(round(unserialize($quotation["UF_CALCULATION"])["COST"], 2), 2, '.', ',') : '',
			'CITY' => $city["UF_CITY"],
		),
		'actions' => array(
			array(
				"text" => "Show",
				"onclick" => "showQuotation(".$quotation['ID'].");"
			),
			array(
				"text" => "Delete",
				"onclick" => "deleteQuotation(".$quotation['ID'].");"
			),
            array(
                "text" => "Test_Avivi",
                "onclick" => "showQuotation_test_avivi(".$quotation['ID'].");"
            ),
		)
	);
}

if(isset($arParams["FROM_ENTITY"]) && $arParams["FROM_ENTITY"] == "Y")
{
	$arResult["ENTITY_DATA"] = $arParams;
	$arResult["ENTITY_DATA"]["ACTION"] = "NEW";
}
?>

<script>	
function deleteQuotation(id)
{
	$.ajax({
        type: 'POST',
        url: "/local/components/custom/quotation.grid/ajax.php", 
        data: {"DELETE_DATA": "Y", "QUOTATION_ID": id},
        success: function(data)
        {
			BX.UI.Notification.Center.notify({
				content: "Quotation deleted successfully!"  
			});
			$("tr[data-id='"+id+"']").remove();
		}
    })
}
function showQuotation(id)
{
	BX.SidePanel.Instance.open('/quotation_system/show.php',
	{
		requestMethod: "post",
		cacheable: false,
		requestParams: 
		{ 
			ACTION: "SHOW",
			QUATATION_ID: id,
		}
	});
}
//Avivi: test_avivi
function showQuotation_test_avivi(id)
{
    BX.SidePanel.Instance.open('/quotation_system/test_show.php',
        {
            requestMethod: "post",
            cacheable: false,
            requestParams:
                {
                    ACTION: "SHOW",
                    QUATATION_ID: id,
                }
        });
}
//Avivi: END_test_avivi
</script>

<?$this->IncludeComponentTemplate();
