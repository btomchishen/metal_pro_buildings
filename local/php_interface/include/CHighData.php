<?

use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Highloadblock as HL;

class CHighData
{
	function GetList($hlblock_id, $arFilter = array(), $arSelect = array("*"), $sort = array(), $keyID = false, $count = 0)
	{	
		$arResult = array();
		if(CModule::IncludeModule("highloadblock"))
		{
			$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlblock_id)->fetch();
			$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
			$entity_data_class = $entity->getDataClass();
			$entity_table_name = $hlblock['TABLE_NAME'];
			$sTableID = 'tbl_'.$entity_table_name;

			if($keyID && !in_array("*", $arSelect) && !in_array("ID", $arSelect)) {
				$arSelect[] = "ID";
			}

			$params = array(
                "select" => $arSelect,
                "filter" => $arFilter,
                "order" => $sort
            );

			if($count != 0){
                $params["limit"] = $count;
			}
			$rsData = $entity_data_class::getList($params);
			$rsData = new CDBResult($rsData, $sTableID);

			if($count == 1) {
				$arResult = $rsData->Fetch();
			}
			else {
				if($keyID) {
					while($arRes = $rsData->Fetch()) {
						$arResult[$arRes["ID"]] = $arRes;
					}
				} 
				else{
					while($arRes = $rsData->Fetch()){
						$arResult[] = $arRes;
					}
				}
			}
		}
		return $arResult;
	}

	function IsRecordExist($hlblock_id, $arFil = array(), $getAllFields = false)
	{
		$isExist = false;
		if(CModule::IncludeModule("highloadblock"))
		{
			$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlblock_id)->fetch();
			$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
			$entity_data_class = $entity->getDataClass();
			$entity_table_name = $hlblock['TABLE_NAME'];
			$sTableID = 'tbl_'.$entity_table_name;
			$rsData = $entity_data_class::getList(array(
				"select" => array('*'),
				"filter" => $arFil,
				"order" => array("ID"=>"ASC")
			));
			$rsData = new CDBResult($rsData, $sTableID);
			if($arRes = $rsData->Fetch()) {
				if($getAllFields) {
					$isExist = $arRes;
				} else {
					$isExist = $arRes["ID"];
				}
			}
		}
		return $isExist;
	}

	function AddRecord($hlblock_id, $data = array())
	{
		if(CModule::IncludeModule("highloadblock") && $data)
		{
			$ID = false;
			$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlblock_id)->fetch();
			$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
			$entity_data_class = $entity->getDataClass();
			$result = $entity_data_class::add($data);
			$ID = $result->getId();
			if($result->isSuccess())
				return($ID);
		}
	}

	function UpdateRecord($hlblock_id, $recordId, $arFieldToUpdate = array())
	{
		if(CModule::IncludeModule("highloadblock") && $arFieldToUpdate)
		{
			$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlblock_id)->fetch();
			$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
			$entity_data_class = $entity->getDataClass();
			$result = $entity_data_class::update($recordId, $arFieldToUpdate); 
    		if ($result->isSuccess()) 
    			return true;
    		else
    			return false;
    	}
	}

	function DeleteRecord($hlblock_id, $recordId)
	{
	 	if(CModule::IncludeModule("highloadblock") && $recordId)
	  	{
		   $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlblock_id)->fetch();
		   $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
		   $entity_data_class = $entity->getDataClass();
		   $res = $entity_data_class::delete($recordId);
		   return $res;
	  	}
	}

    function GetListGrid($hlblock_id, $arFilter = array(), $arSelect = array("*"), $by = "ID", $order = "ASC", $keyID = false, $count = 0, $offset = 0)
    {
        $arResult = array();
        if(CModule::IncludeModule("highloadblock"))
        {
            $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlblock_id)->fetch();
            // p($hlblock);

            $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();
            $entity_table_name = $hlblock['TABLE_NAME'];
            $sTableID = 'tbl_'.$entity_table_name;

            if($keyID && !in_array("*", $arSelect) && !in_array("ID", $arSelect)) {
                $arSelect[] = "ID";
            }

            $params = array(
                "select" => $arSelect,
                "filter" => $arFilter,
                "order" => array($by => $order)
            );

            if($count != 0){
                $params["limit"] = $count;
            }

            if($offset != 0){
                $params["offset"] = $offset;
            }

            $rsData = $entity_data_class::getList($params);
            $rsData = new CDBResult($rsData, $sTableID);

            if($count == 1) {
                $arResult = $rsData->Fetch();
            }
            else {
                if($keyID) {
                    while($arRes = $rsData->Fetch()) {
                        $arResult[$arRes["ID"]] = $arRes;
                    }
                }
                else{
                    while($arRes = $rsData->Fetch()){
                        $arResult[] = $arRes;
                    }
                }
            }
        }
        return $arResult;
    }
	
    function GetFields($hlblock_id) {
        $arUserFields = [];
        if(CModule::IncludeModule("highloadblock")) {
            $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlblock_id)->fetch();
            if (!empty($hlblock)) {
                $ufEntityId = 'HLBLOCK_' . $hlblock['ID'];
                global $USER_FIELD_MANAGER;
                $lang = Bitrix\Main\Application::getInstance()->getContext()->getLanguage();
                $arUserFields = $USER_FIELD_MANAGER->GetUserFields($ufEntityId, 0, $lang);
            }
        }
        return $arUserFields;
    }

    function GetTotalCount($hlblock_id, $arFilter = array()) {
        $totalCount = 0;
        if(CModule::IncludeModule("highloadblock")) {
            $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlblock_id)->fetch();
            if (!empty($hlblock)) {
                $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
                $entity_data_class = $entity->getDataClass();
                $countQuery = new Query($entity_data_class::getEntity());
                $countQuery->addSelect(new ExpressionField('CNT', 'COUNT(1)'));
                $countQuery->setFilter($arFilter);
                $totalCount = $countQuery->setLimit(null)->setOffset(null)->exec()->fetch();
                unset($countQuery);
                $totalCount = (int)$totalCount['CNT'];
            }
        }

        return $totalCount;
    }

}