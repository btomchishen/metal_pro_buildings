<?php // Avivi #20215 Reports Configuration
namespace Bitrix\Crm\Widget\Data;

use Bitrix\Main;
use Bitrix\Main\Type\Date;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type\Collection;

use Bitrix\Crm\History\HistoryEntryType;
use Bitrix\Crm\History\Entity\LeadStatusHistoryTable;
use Bitrix\Crm\Widget\Filter;
use DateTimeZone;
use Bitrix\Main\Type\DateTime;

\Bitrix\Main\Loader::includeModule('crm');

class NewLeadsCount extends LeadDataSource
{
    const TYPE_NAME = 'NEW_LEADS_COUNT';
    const GROUP_BY_USER = 'USER';
    const GROUP_BY_DATE = 'DATE';
    private static $messagesLoaded = false;
    /**
     * @return string
     */
    public function getTypeName()
    {
        return self::TYPE_NAME;
    }
    /**
     * @return array
     */
    public function getList(array $params)
    {
        /** @var Filter $filter */
        $filter = isset($params['filter']) ? $params['filter'] : null;
        if(!($filter instanceof Filter))
        {
            throw new Main\ObjectNotFoundException("The 'filter' is not found in params.");
        }

        $permissionSql = '';
        if($this->enablePermissionCheck)
        {
            $permissionSql = $this->preparePermissionSql();
            if($permissionSql === false)
            {
                //Access denied;
                return array();
            }
        }

        /** @var array $select */
        $select = isset($params['select']) && is_array($params['select']) ? $params['select'] : array();
        $name = '';
        if(!empty($select))
        {
            $selectItem = $select[0];
            if(isset($selectItem['name']))
            {
                $name = $selectItem['name'];
            }
        }

        if($name === '')
        {
            $name = 'COUNT';
        }

        $group = isset($params['group'])? mb_strtoupper($params['group']) : '';
        if($group !== ''
            && $group !== self::GROUP_BY_USER
            && $group !== self::GROUP_BY_DATE)
        {
            $group = '';
        }

        $period = $filter->getPeriod();
        $periodStartDate = $period['START'];
        $periodEndDate = $period['END'];
//        fp($period, 'aPeriod');

//        if(file_exists($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/CHighData.php"))
//            require_once($_SERVER["DOCUMENT_ROOT"]."/local/php_interface/include/CHighData.php");

        $COUNT = 0;

        $arFilter = [];
        if (!empty($params['custom_status'])) {
//            $query->addFilter('=STATUS_ID', $params['custom_status']['STATUS_ID']);
//            fp($params['custom_status']['NEW_LEADS_COUNT'], 'aType');
            if ($params['custom_status']['NEW_LEADS_COUNT'] == 'THIS_MONTH') {
                $date_create = $this->get_date_time_system(date('Y-m-01 00:00:00'));
                $arFilter = ['>=UF_CREATED' => $date_create];
            } else if ($params['custom_status']['NEW_LEADS_COUNT'] == 'TODAY') {
                $date_create = $this->get_date_time_system(date('Y-m-d 00:00:00'));
                $arFilter = ['>=UF_CREATED' => $date_create];
            }
        }



        $arSelect = ['ID'];
        $hbRes = \CHighData::GetList(HB_NEW_LEADS, $arFilter, $arSelect);
        foreach($hbRes as $lead) {
            $COUNT++;
        }
//        fp($COUNT, 'aThisMonth');
/*
        $query = new Query(LeadStatusHistoryTable::getEntity());
        $query->registerRuntimeField('', new ExpressionField($name, "COUNT(*)"));
        $query->addSelect($name);
//        $query->addFilter('=TYPE_ID', HistoryEntryType::CREATION);
        $query->addFilter('>=CREATED_DATE', $periodStartDate);
        $query->addFilter('<=CREATED_DATE', $periodEndDate);
        if (!empty($params['custom_status'])) {
//            $query->addFilter('=STATUS_ID', $params['custom_status']['STATUS_ID']);
            fp($params['custom_status']['NEW_LEADS_COUNT'], 'aType');
        }

        if($this->enablePermissionCheck && is_string($permissionSql) && $permissionSql !== '')
        {
            $query->addFilter('@OWNER_ID', new SqlExpression($permissionSql));
        }

        $responsibleIDs = $filter->getResponsibleIDs();
        if(is_array($responsibleIDs) && !empty($responsibleIDs))
        {
            $query->addFilter('@RESPONSIBLE_ID', $responsibleIDs);
        }

        $sort = isset($params['sort']) && is_array($params['sort']) && !empty($params['sort']) ? $params['sort'] : null;

        if($group !== '')
        {
            if($group === self::GROUP_BY_USER)
            {
                $query->addSelect('RESPONSIBLE_ID');
                $query->addGroup('RESPONSIBLE_ID');
            }
            elseif($group === self::GROUP_BY_DATE)
            {
                $query->addSelect('CREATED_DATE', 'DATE');
                $query->addGroup('CREATED_DATE');
                if(!$sort)
                {
                    $query->addOrder('CREATED_DATE', 'ASC');
                }
            }
        }

        $results = array();
        $dbResult = $query->exec();
        if($group === self::GROUP_BY_DATE)
        {
            while($ary = $dbResult->fetch())
            {

                $date =  $ary['DATE'];
                $ary['DATE'] = $date->format('Y-m-d');
                $results[] = $ary;
            }
//            fp($results, 'aResults');
        }
        elseif($group === self::GROUP_BY_USER)
        {
            $rawResult = array();
            $userIDs = array();
            while($ary = $dbResult->fetch())
            {
                $userID = $ary['RESPONSIBLE_ID'] = (int)$ary['RESPONSIBLE_ID'];
                if($userID > 0 && !isset($userIDs[$userID]))
                {
                    $userIDs[$userID] = true;
                }
                $rawResult[] = $ary;
            }
            $userNames = self::prepareUserNames(array_keys($userIDs));
            foreach($rawResult as $item)
            {
                $userID = $item['RESPONSIBLE_ID'];
                $item['USER_ID'] = $userID;
                $item['USER'] = isset($userNames[$userID]) ? $userNames[$userID] : "[{$userID}]";
                unset($item['RESPONSIBLE_ID']);

                $results[] = $item;
            }
//            fp($results, 'aResults');
        }
        else
        {
            while($ary = $dbResult->fetch())
            {
                $results[] = $ary;
            }
//            fp($results, 'aResults');

        }

        if($sort)
        {
            foreach($params['sort'] as $sortItem)
            {
                if(isset($sortItem['name']) && $sortItem['name'] === $name)
                {
                    $order = isset($sortItem['order']) && mb_strtolower($sortItem['order']) === 'desc'
                        ? SORT_DESC : SORT_ASC;
                    Collection::sortByColumn($results, array($name => $order));
                    break;
                }
            }
        }*/
        $results = [
            0 => [
                'COUNT' => $COUNT
            ]
        ];
        return $results;
    }
    /**
     * Get current data context
     * @return DataContext
     */
    public function getDataContext()
    {
        return DataContext::ENTITY;
    }
    /**
     * @return array Array of arrays
     */
    public static function getPresets()
    {
        self::includeModuleFile();
        return array(
            array(
                'entity' => \CCrmOwnerType::LeadName,
                'title' => GetMessage('CRM_LEAD_NEW_PRESET_OVERALL_COUNT'),
                'listTitle' => GetMessage('CRM_LEAD_NEW_PRESET_OVERALL_COUNT_SHORT'),
                'name' => self::TYPE_NAME.'::OVERALL_COUNT',
                'source' => self::TYPE_NAME,
                'select' => array('name' => 'COUNT'),
                'context' => DataContext::ENTITY,
                'category' => 'NEW'
            )
        );
    }

    /**
     * @return array Array of arrays
     */
    public static function prepareCategories(array &$categories)
    {
        if(isset($categories['NEW']))
        {
            return;
        }

        self::includeModuleFile();
        $categories['NEW'] = array(
            'entity' => \CCrmOwnerType::LeadName,
            'title' => GetMessage('CRM_LEAD_NEW_CATEGORY'),
            'name' => 'NEW',
            'enableSemantics' => false
        );
    }

    /**
     * @return void
     */
    protected static function includeModuleFile()
    {
        if(self::$messagesLoaded)
        {
            return;
        }

        Main\Localization\Loc::loadMessages(__FILE__);
        self::$messagesLoaded = true;
    }

    /** @return array */
    public function prepareEntityListFilter(array $filterParams)
    {
        $filter = self::internalizeFilter($filterParams);
        $query = new Query(LeadStatusHistoryTable::getEntity());
        $query->addSelect('OWNER_ID');
        $query->addGroup('OWNER_ID');

        $period = $filter->getPeriod();
        $periodStartDate = $period['START'];
        $periodEndDate = $period['END'];

//        $query->addFilter('=TYPE_ID', HistoryEntryType::CREATION);
        $query->addFilter('>=CREATED_DATE', $periodStartDate);
        $query->addFilter('<=CREATED_DATE', $periodEndDate);

        if (!empty($filterParams['custom_status'])) {
//            $query->addFilter('=STATUS_ID', $filterParams['custom_status']['STATUS_ID']);
//            fp($filterParams['custom_status']['NEW_LEADS_COUNT']);
        }

        $responsibleIDs = $filter->getResponsibleIDs();
        if(!empty($responsibleIDs))
        {
            $query->addFilter('@RESPONSIBLE_ID', $responsibleIDs);
        }

        return array(
            '__JOINS' => array(
                array(
                    'TYPE' => 'INNER',
                    'SQL' => 'INNER JOIN('.$query->getQuery().') LN ON LN.OWNER_ID = L.ID'
                )
            )
        );
    }
    protected function get_date_time_system($input_date) {
        $serverZone = \COption::GetOptionString("main", "default_time_zone"); // get time zone America/Toronto
        $location_date_time = new DateTime($input_date, 'Y-m-d H:i:s');
        $location_date_time_zone = new DateTimeZone($serverZone);
        $location_date_time->setTimeZone($location_date_time_zone);
        $date_time_format = \Bitrix\Main\Type\DateTime::getFormat();
        return $location_date_time->format($date_time_format);
    }
}