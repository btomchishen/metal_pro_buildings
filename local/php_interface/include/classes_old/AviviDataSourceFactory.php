<?php
namespace Bitrix\Crm\Widget\Data;
use Bitrix\Main;
use Bitrix\Crm;

abstract class AviviDataSourceFactory // Avivi #20215 Reports Configuration
{
    public static function checkSettings(array $settings)
    {
        return !empty($settings) && isset($settings['name']) && $settings['name'] !== '';
    }
    public static function create(array $settings, $userID = 0, $enablePermissionCheck = true)
    {
        $name = isset($settings['name'])? mb_strtoupper($settings['name']) : '';
//        fp($name, 'aSourceName', true);
        if($name === AviviDealSumStatistics::TYPE_NAME)
        {
            return new AviviDealSumStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === AviviLeadSumStatistics::TYPE_NAME)
        {
            return new AviviLeadSumStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === DealInvoiceStatistics::TYPE_NAME)
        {
            return new DealInvoiceStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === DealActivityStatistics::TYPE_NAME)
        {
            return new DealActivityStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === AviviLeadActivityStatistics::TYPE_NAME)
        { // Avivi #20683 Leads Report
            return new AviviLeadActivityStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === DealStageHistory::TYPE_NAME)
        {
            return new DealStageHistory($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === LeadStatusHistory::TYPE_NAME)
        {
            return new LeadStatusHistory($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === DealInWork::TYPE_NAME)
        {
            return new DealInWork($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === AviviLeadInWork::TYPE_NAME)
        { // Avivi #20683 Leads Report
            return new AviviLeadInWork($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === DealIdle::TYPE_NAME)
        {
            return new DealIdle($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === AviviLeadIdle::TYPE_NAME)
        { // Avivi #20683 Leads Report
            return new AviviLeadIdle($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === AviviLeadNew::TYPE_NAME)
        {
            return new AviviLeadNew($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === AviviLeadConversionStatistics::TYPE_NAME)
        { // Avivi #20683 Leads Report
            return new AviviLeadConversionStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === LeadConversionRate::TYPE_NAME)
        {
            return new LeadConversionRate($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === AviviLeadJunk::TYPE_NAME)
        { // Avivi #20683 Leads Report
            return new AviviLeadJunk($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === LeadChannelStatistics::TYPE_NAME)
        {
            return new LeadChannelStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === DealChannelStatistics::TYPE_NAME)
        {
            return new DealChannelStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === Activity\ChannelStatistics::TYPE_NAME)
        {
            return new Activity\ChannelStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === InvoiceInWork::TYPE_NAME)
        {
            return new InvoiceInWork($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === InvoiceSumStatistics::TYPE_NAME)
        {
            return new InvoiceSumStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === InvoiceOverdue::TYPE_NAME)
        {
            return new InvoiceOverdue($settings, $userID, $enablePermissionCheck);
        }
        elseif($name === ExpressionDataSource::TYPE_NAME)
        {
            return new ExpressionDataSource($settings, $userID);
        }
        elseif($name === ActivityProviderStatus::TYPE_NAME)
        {
            return new ActivityProviderStatus($settings, $userID);
        }
        elseif ($name === Company\ActivityStatistics::TYPE_NAME)
        {
            return new Company\ActivityStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Company\GrowthStatistics::TYPE_NAME)
        {
            return new Company\GrowthStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Company\ActivityStreamStatistics::TYPE_NAME)
        {
            return new Company\ActivityStreamStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Company\ActivityMarkStatistics::TYPE_NAME)
        {
            return new Company\ActivityMarkStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Company\ActivitySumStatistics::TYPE_NAME)
        {
            return new Company\ActivitySumStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Company\ActivityStatusStatistics::TYPE_NAME)
        {
            return new Company\ActivityStatusStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Company\DealSumStatistics::TYPE_NAME)
        {
            return new Company\DealSumStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Company\InvoiceSumStatistics::TYPE_NAME)
        {
            return new Company\InvoiceSumStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Company\DealConversionRate::TYPE_NAME)
        {
            return new Company\DealConversionRate($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Contact\ActivityStatistics::TYPE_NAME)
        {
            return new Contact\ActivityStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Contact\ActivityStreamStatistics::TYPE_NAME)
        {
            return new Contact\ActivityStreamStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Contact\ActivityMarkStatistics::TYPE_NAME)
        {
            return new Contact\ActivityMarkStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Contact\ActivitySumStatistics::TYPE_NAME)
        {
            return new Contact\ActivitySumStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Contact\ActivityStatusStatistics::TYPE_NAME)
        {
            return new Contact\ActivityStatusStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Contact\GrowthStatistics::TYPE_NAME)
        {
            return new Contact\GrowthStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Contact\DealSumStatistics::TYPE_NAME)
        {
            return new Contact\DealSumStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Contact\InvoiceSumStatistics::TYPE_NAME)
        {
            return new Contact\InvoiceSumStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Contact\DealConversionRate::TYPE_NAME)
        {
            return new Contact\DealConversionRate($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Activity\Statistics::TYPE_NAME)
        {
            return new Activity\Statistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Activity\StreamStatistics::TYPE_NAME)
        {
            return new Activity\StreamStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Activity\MarkStatistics::TYPE_NAME)
        {
            return new Activity\MarkStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Activity\SumStatistics::TYPE_NAME)
        {
            return new Activity\SumStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Activity\StatusStatistics::TYPE_NAME)
        {
            return new Activity\StatusStatistics($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Activity\ActivityDynamic::TYPE_NAME)
        {
            return new Activity\ActivityDynamic($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === Activity\ManagerCounters::TYPE_NAME)
        {
//            return new Activity\ManagerCounters($settings, $userID, $enablePermissionCheck);
            return new Activity\AviviManagerCounters($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === DealSaleTarget::TYPE_NAME)
        {
            return new DealSaleTarget($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === LeadCustomStatus::TYPE_NAME) // Avivi #20215 Reports Configuration
        {
            return new LeadCustomStatus($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === NewLeadsCount::TYPE_NAME) // Avivi #20215 Reports Configuration
        {
            return new NewLeadsCount($settings, $userID, $enablePermissionCheck);
        }
        elseif ($name === DealCustomStatus::TYPE_NAME) // Avivi #20215 Reports Configuration
        {
            return new DealCustomStatus($settings, $userID, $enablePermissionCheck);
        }
        else
        {
            throw new Main\NotSupportedException("The data source '{$name}' is not supported in current context.");
        }
    }

    public static function getPresets() {
        $presets = Crm\Widget\Data\DataSourceFactory::getPresets();

        $status_list = self::getLeadStatusList(); // Avivi #20215 Reports Configuration
        foreach ($status_list as $status) {
            $presets[] = [
                'entity' => 'LEAD',
                'title' => $status['NAME'],
                'name' => 'LEAD_CUSTOM_STATUS::'.$status['STATUS_ID'],
                'source' => 'LEAD_CUSTOM_STATUS',
                'select' => [
                    'name' => 'COUNT',
                ],
                'context' => 'E',
                'category' => 'CUSTOM_STATUS'
            ];
        }
        $presets[] = [
            'entity' => 'LEAD',
            'title' => 'New leads this month',
            'name' => 'NEW_LEADS_COUNT::THIS_MONTH',
            'source' => 'NEW_LEADS_COUNT',
            'select' => [
                'name' => 'COUNT',
            ],
            'context' => 'E',
            'category' => 'NEW_LEADS_COUNT'
        ];
        $presets[] = [
            'entity' => 'LEAD',
            'title' => 'New leads today',
            'name' => 'NEW_LEADS_COUNT::TODAY',
            'source' => 'NEW_LEADS_COUNT',
            'select' => [
                'name' => 'COUNT',
            ],
            'context' => 'E',
            'category' => 'NEW_LEADS_COUNT'
        ];

        $status_list = self::getDealStatusList(); // Avivi #20215 Reports Configuration
//        fp($status_list, 'aStatusList');
        foreach ($status_list as $status) {
            $presets[] = [
                'entity' => 'DEAL',
                'title' => $status['NAME'],
                'name' => 'DEAL_CUSTOM_STATUS::'.$status['STATUS_ID'],
                'source' => 'DEAL_CUSTOM_STATUS',
                'select' => [
                    'name' => 'COUNT',
                ],
                'context' => 'E',
                'category' => 'CUSTOM_STATUS'
            ];
        }

        return $presets;
    }

    public static function getCategiries() {
        $categories = Crm\Widget\Data\DataSourceFactory::getCategiries();
        $categories[] = [
            'entity' => 'LEAD',
            'title' => 'Custom status',
            'name' => 'CUSTOM_STATUS',
            'enableSemantics' => false,
        ]; // Avivi #20215 Reports Configuration
        $categories[] = [
            'entity' => 'LEAD',
            'title' => 'New leads count',
            'name' => 'NEW_LEADS_COUNT',
            'enableSemantics' => false,
        ]; // Avivi #20215 Reports Configuration
        $categories[] = [
            'entity' => 'DEAL',
            'title' => 'Custom status',
            'name' => 'CUSTOM_STATUS',
            'enableSemantics' => false,
        ]; // Avivi #20215 Reports Configuration
        return $categories;
    }

    protected static function getLeadStatusList() {
        $list = [];
        \Bitrix\Main\Loader::includeModule('crm');
        $statuses = \CCrmLead::GetStatuses();
        $StartStatusID = \CCrmLead::GetStartStatusID();
        foreach ($statuses as $status) {
            if ($status['STATUS_ID'] != $StartStatusID
                && $status['SEMANTICS'] == null) {
                $list[] = $status;
            }
        }
        return $list;
    }

    protected static function getDealStatusList() {
        $list = [];
        \Bitrix\Main\Loader::includeModule('crm');
        $statuses = \CCrmDeal::GetStages();
        $StartStatusID = \CCrmDeal::GetStartStageID();
        foreach ($statuses as $status) {
            if ($status['STATUS_ID'] != $StartStatusID
                && $status['SEMANTICS'] == null) {
                $list[] = $status;
            }
        }
        return $list;
    }

}