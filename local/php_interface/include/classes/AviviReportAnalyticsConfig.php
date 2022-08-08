<?php

class ReportAnalyticsConfig
{
//    protected const HB_REPORT_ANALYTICS_CONFIG = 29;
    protected static $current_values = [];

    public static function update_values() {
        if (!empty($_POST['values'])) {
            $values = $_POST['values'];
            $hb_values = [];
            foreach ($values as $value) {
                $hb_values[$value['type']][$value['key']][] = $value['value'];
            }
            unset($values);
            if (!empty($hb_values)) {
                self::set_values();
                self::clean_values($hb_values);
                foreach ($hb_values as $type => $keys) {
                    foreach ($keys as $key => $value) {
                        self::save($type, $key, $value);
                    }
                }
            }
        } else {
            self::set_values();
            self::clean_values([]);
        }
    }

    public static function get_values() {
        self::set_values();
        return self::$current_values;
    }

    public static function get_filter($type, $key) {
        $arFilter = [
            'UF_TYPE' => $type,
            'UF_KEY' => $key,
        ];
        $DB_res = CHighData::GetList(HB_REPORT_ANALYTICS_CONFIG, $arFilter);
        $value = [];
        foreach ($DB_res as $value) {
            $value = unserialize($value['UF_VALUE']);
        }
        return $value;
    }

    protected static function set_values() {
        self::$current_values = [];
        $DB_res = CHighData::GetList(HB_REPORT_ANALYTICS_CONFIG);
        foreach ($DB_res as $value) {
            self::$current_values[$value['UF_TYPE']][$value['UF_KEY']] = unserialize($value['UF_VALUE']);
        }
    }

    protected static function clean_values($new_values) {
        foreach (self::$current_values as $type => $keys) {
            foreach ($keys as $key => $value) {
                if (!isset($new_values[$type])
                    || !isset($new_values[$type][$key])
                ) {
                    $arFilter = [
                        'UF_TYPE' => $type,
                        'UF_KEY' => $key,
                    ];
                    $arSelect = [
                        'ID'
                    ];
                    $DB_res = CHighData::GetList(HB_REPORT_ANALYTICS_CONFIG, $arFilter, $arSelect);
                    foreach ($DB_res as $value) {
                        CHighData::DeleteRecord(HB_REPORT_ANALYTICS_CONFIG, $value['ID']);
                    }
                }
            }
        }
    }

    protected static function save($type, $key, $value) {
        $data = [
            'UF_TYPE' => $type,
            'UF_KEY' => $key,
            'UF_VALUE' => serialize($value),
        ];
        if (!empty(self::$current_values)
            && !empty(self::$current_values[$type])
            && !empty(self::$current_values[$type][$key])
        ) {
            $arFilter = [
                'UF_TYPE' => $type,
                'UF_KEY' => $key,
            ];
            $arSelect = [
                'ID'
            ];
            $DB_res = CHighData::GetList(HB_REPORT_ANALYTICS_CONFIG, $arFilter, $arSelect);
            foreach ($DB_res as $value) {
                CHighData::UpdateRecord(HB_REPORT_ANALYTICS_CONFIG, $value['ID'], $data);
            }
        } else {
            CHighData::AddRecord(HB_REPORT_ANALYTICS_CONFIG, $data);
        }
    }
}