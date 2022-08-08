<?php
namespace Bitrix\Crm\Widget;
use Bitrix\Crm\Widget\Data\DataSource;
//use Bitrix\Crm\Widget\Data\DataSourceFactory;
use Bitrix\Crm\Widget\Data\AviviDataSourceFactory; // Avivi #20215 Reports Configuration
use Bitrix\Crm\Widget\Data\ExpressionDataSource;

\Bitrix\Main\Loader::includeModule('crm');

class AviviNumericWidget extends Widget // Avivi #20215 Reports Configuration
{
	/** @var array[WidgetConfig] */
	private $configs = null;
	public function __construct(array $settings, Filter $filter, $userID = 0, $enablePermissionCheck = true)
	{
		parent::__construct($settings, $filter, $userID, $enablePermissionCheck);

		$this->configs = array();
		$configs = $this->getSettingArray('configs', array());
		foreach($configs as $config)
		{
			$this->configs[] = new WidgetConfig($config);
		}
	}
	/**
	* @return array
	*/
	public function prepareData()
	{
		$items = array();
		$expressions = array();
		$qty = count($this->configs);
		for($i = 0; $i < $qty; $i++)
		{
			/** @var WidgetConfig $config */
			$config = $this->configs[$i];

			$name = $config->getName();
			if($name === '')
			{
				$name = strval($i + 1);
			}

			$title = $config->getTitle();

			$items[$name] = array('name' => $name, 'title' => $title, 'value' => 0);

			$source = null;
			$sourceSettings = $config->getDataSourceSettings();
			if(AviviDataSourceFactory::checkSettings($sourceSettings)) // Avivi #20215 Reports Configuration
			{
				$source = AviviDataSourceFactory::create($sourceSettings, $this->userID, $this->enablePermissionCheck); // Avivi #20215 Reports Configuration
				$source->setFilterContextData($this->getFilterContextData());
			}

			$params = array('name' => $name, 'config' => $config, 'source' => $source);
			//Skip expressions. They will be processed at the end of this function.
			if($source instanceof ExpressionDataSource)
			{
				$expressions[] = $params;
				continue;
			}
			$this->prepareItem($params, $items);
		}

		foreach($expressions as $params)
		{
			$this->prepareItem($params, $items);
		}

		return array('items' => array_values($items));
	}
	/**
	* @return void
	*/
	protected function prepareItem(array $params, array &$result)
	{
		/** @var string $name */
		$name = $params['name'];
		/** @var WidgetConfig $config */
		$config = $params['config'];
		/** @var DataSource $source */
		$source = $params['source'];

		if($source === null)
		{
			$result[$name] = array();
			return;
		}

		$selectField = $config->getSelectField();
		if($selectField === '')
		{
			$selectField = $name;
		}
		$this->filter->setExtras($config->getFilterParams());

        $custom_status = [];
        $sourceSettings = $config->getDataSourceSettings();
        if (!empty($sourceSettings)) {
            if ($sourceSettings['name'] === 'LEAD_CUSTOM_STATUS'
                || $sourceSettings['name'] === 'DEAL_CUSTOM_STATUS'
            ) {
                $parts = explode('::', $sourceSettings['presetName']);
                $custom_status['STATUS_ID'] = $parts[1];
            } else if ($sourceSettings['name'] === 'NEW_LEADS_COUNT') {
                $parts = explode('::', $sourceSettings['presetName']);
                $custom_status['NEW_LEADS_COUNT'] = $parts[1];
            }
        }

		$value = (double)$source->getFirstValue(
			array(
				'filter' => $this->filter,
				'select' => array(array('name' => $selectField, 'aggregate' => $config->getAggregate())),
				'result' => $result,
                'custom_status' => $custom_status, // Avivi #20215 Reports Configuration
			),
			$selectField,
			0.0
		);


		if(!isset($result[$name]))
		{
			$result[$name] = array();
		}

		$format = $config->getFomatParams();
		if(empty($format))
		{
			$result[$name]['value'] = $value;
		}
		else
		{
			$result[$name]['format'] = $format;
			if(isset($format['enableDecimals']) && $format['enableDecimals'] == 'N')
			{
				$value = round($value, 2);
			}

			$result[$name]['value'] = $value;

			if(isset($format['isCurrency']) && $format['isCurrency'] === 'Y')
			{
				//hack fom Currency module issue.
				//$result[$name]['html'] = \CCrmCurrency::MoneyToString(strval($value), \CCrmCurrency::GetAccountCurrencyID());
				$html = \CCrmCurrency::MoneyToString(strval($value), \CCrmCurrency::GetAccountCurrencyID());
				$html = preg_replace('/(&#8381;)/', '<span style="font-family:Helvetica Neue;">${1}</span>', $html);
				$result[$name]['html']  = $html;

			}
			elseif(isset($format['isPercent']) && $format['isPercent'] === 'Y')
			{
				$result[$name]['html'] = "{$value}%";
			}
		}

		$detailsPageUrl = $source->getDetailsPageUrl(array('filter' => $this->filter, 'field' => $selectField));
		if($detailsPageUrl !== '')
		{
			$result[$name]['url'] = $detailsPageUrl;
		}

		$display = $config->getDisplayParams();
		if(!empty($display))
		{
			$result[$name]['display'] = $display;
		}
	}
	/**
	* @return WidgetConfig|null
	*/
	protected function findConfigByName($name)
	{
		if($name === '')
		{
			return null;
		}

		$qty = count($this->configs);
		for($i = 0; $i < $qty; $i++)
		{
			/** @var WidgetConfig $config */
			$config = $this->configs[$i];
			if($config->getName() === $name)
			{
				return $config;
			}
		}
		return null;
	}
	/**
	* @return array
	*/
	public function initializeDemoData(array $data)
	{
		if(!(isset($data['items']) && is_array($data['items'])))
		{
			return $data;
		}

		foreach($data['items'] as &$item)
		{
			$config = $this->findConfigByName(isset($item['name']) ? $item['name'] : '');
			if(!$config)
			{
				continue;
			}

			$item['title'] = $config->getTitle();
			$value = isset($item['value']) ? (double)$item['value'] : 0.0;
			$format = $config->getFomatParams();
			if(isset($format['enableDecimals']) && $format['enableDecimals'] == 'N')
			{
				$value = round($value, 0);
			}
			$item['value'] = $value;
			if(isset($format['isCurrency']) && $format['isCurrency'] === 'Y')
			{
				//hack fom Currency module issue.
				//$item['html'] = \CCrmCurrency::MoneyToString($value, \CCrmCurrency::GetAccountCurrencyID());
				$html = \CCrmCurrency::MoneyToString(strval($value), \CCrmCurrency::GetAccountCurrencyID());
				$html = preg_replace('/(&#8381;)/', '<span style="font-family:Helvetica Neue;">${1}</span>', $html);
				$item['html']  = $html;
			}
			elseif(isset($format['isPercent']) && $format['isPercent'] === 'Y')
			{
				$item['html'] = "{$value}%";
			}

			$display = $config->getDisplayParams();
			if(!empty($display))
			{
				$item['display'] = $display;
			}
		}
		unset($item);
		return $data;
	}
}