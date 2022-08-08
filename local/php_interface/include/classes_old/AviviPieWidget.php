<?php
namespace Bitrix\Crm\Widget;

use Bitrix\Main;
//use Bitrix\Crm\Widget\Data\DataSourceFactory;
use Bitrix\Crm\Widget\Data\AviviDataSourceFactory; // Avivi #20215 Reports Configuration

\Bitrix\Main\Loader::includeModule('crm');

class AviviPieWidget extends Widget // Avivi #20215 Reports Configuration
{
	/** @var array[WidgetConfig] */
	private $configs = null;
	/** @var string */
	private $groupField = '';

	public function __construct(array $settings, Filter $filter, $userID = 0, $enablePermissionCheck = true)
	{
		parent::__construct($settings, $filter, $userID, $enablePermissionCheck);

		$this->configs = array();
		$configs = $this->getSettingArray('configs', array());
		foreach($configs as $config)
		{
			$this->configs[] = new WidgetConfig($config);
		}

		if(isset($settings['group']) && is_string($settings['group']) && $settings['group'] !== '')
		{
			$this->setGroupField($settings['group']);
		}
	}

	/** @return string */
	public function getGroupField()
	{
		return $this->groupField;
	}

	/**
	 * @param string $name Group Field Name
	 * @return void
	 */
	public function setGroupField($name)
	{
		if(!is_string($name))
		{
			throw new Main\ArgumentTypeException('name', 'string');
		}

		$this->groupField = $name;
	}

	/**
	* @return array
	*/
	public function prepareData()
	{
		/** @var WidgetConfig|null $config */
		$config = count($this->configs) > 0 ? $this->configs[0] : null;
		if($config === null)
		{
			return array();
		}

		$this->filter->setExtras($config->getFilterParams());

		$source = null;
		$sourceSettings = $config->getDataSourceSettings();
		if(AviviDataSourceFactory::checkSettings($sourceSettings)) // Avivi #20215 Reports Configuration
		{
			$source = AviviDataSourceFactory::create($sourceSettings, $this->userID, $this->enablePermissionCheck); // Avivi #20215 Reports Configuration
			$source->setFilterContextData($this->getFilterContextData());
		}

		$selectField = $config->getSelectField();
		$aggregate = $config->getAggregate();
		$groupField = $this->groupField !== '' ? $this->groupField : $config->getGroupField();

		if($source !== null)
		{
            $custom_status = [];
            if ($sourceSettings['name'] === 'LEAD_CUSTOM_STATUS'
                || $sourceSettings['name'] === 'DEAL_CUSTOM_STATUS'
            ) {
                $parts = explode('::', $sourceSettings['presetName']);
                $custom_status['STATUS_ID'] = $parts[1];
            }

			$items = $source->getList(
				array(
					'filter' => $this->filter,
					'select' => array(array('name' => $selectField, 'aggregate' => $aggregate)),
					'group' => $groupField,
					'sort' => array(array('name' => $selectField)),
                    'custom_status' => $custom_status, // Avivi #20215 Reports Configuration
				)
			);
		}
		else
		{
			$items = array();
		}

		return array('items' => $items, 'valueField' => $selectField, 'titleField' => $groupField);
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

		/** @var WidgetConfig|null $config */
		$config = count($this->configs) > 0 ? $this->configs[0] : null;
		if($config === null)
		{
			return $data;
		}

		$sourceSettings = $config->getDataSourceSettings();
		$source = AviviDataSourceFactory::checkSettings($sourceSettings)
			? AviviDataSourceFactory::create($sourceSettings, $this->userID, $this->enablePermissionCheck)
			: null;

		if($source === null)
		{
			return $data;
		}

		$groupField = $this->groupField !== '' ? $this->groupField : $config->getGroupField();
		$data = $source->initializeDemoData($data, array('group' => $groupField));
		return $data;
	}
}