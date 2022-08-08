<?php
namespace Bitrix\Crm\Widget;
use Bitrix\Main;

abstract class AviviWidgetFactory // Avivi #20215 Reports Configuration
{
    const FUNNEL = 'FUNNEL';
    const GRAPH = 'GRAPH';
    const BAR = 'BAR';
    const NUMBER = 'NUMBER';
    const RATING = 'RATING';
    const PIE = 'PIE';
    const CUSTOM = 'CUSTOM';

    /**
     * @return Widget
     */
    public static function create(array $settings, Filter $filter, array $options = null)
    {
        if(!is_array($options))
        {
            $options = array();
        }

        $typeName = isset($settings['typeName'])? mb_strtoupper($settings['typeName']) : '';
        if($typeName === self::FUNNEL)
        {
            return new FunnelWidget($settings, $filter);
        }
        elseif($typeName === self::GRAPH || $typeName === self::BAR)
        {
            if(isset($options['maxGraphCount']))
            {
                $settings['maxGraphCount'] = $options['maxGraphCount'];
            }
            return new AviviGraphWidget($settings, $filter); // Avivi #20215 Reports Configuration
        }
        elseif($typeName === self::NUMBER)
        {
            return new AviviNumericWidget($settings, $filter); // Avivi #20215 Reports Configuration
        }
        elseif($typeName === self::RATING)
        {
            return new AviviRatingWidget($settings, $filter); // Avivi #20215 Reports Configuration
        }
        elseif($typeName === self::PIE)
        {
            return new AviviPieWidget($settings, $filter); // Avivi #20215 Reports Configuration
        }
        elseif($typeName === self::CUSTOM)
        {
            return new AviviCustomWidget($settings, $filter); // Avivi #20215 Reports Configuration
        }

        throw new Main\NotSupportedException("The widget type '{$typeName}' is not supported in current context.");
    }
}