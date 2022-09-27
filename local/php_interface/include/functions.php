<?
function findNext($array, $item)
{
    if ($item >= max($array)) {
        $result = max($array);
    } else {
        rsort($array);
        foreach ($array as $ar) {
            if ($ar >= $item)
                $result = $ar;
        }
    }
    return $result;
}

function uniTrim($str)
{
    $text = hex2bin(str_replace('c2a0', '20', bin2hex($str)));
    while (strpos($text, '  ') !== false)
        $text = str_replace("  ", " ", $text);
    $text = trim($text);
    return $text;
}

function fp($array, $filename = "aRes", $append = false)
{
    $trace = debug_backtrace();
    $file = str_replace($_SERVER['DOCUMENT_ROOT'], '/', $trace[0]['file']);
    $line = PHP_EOL . $file . "(" . $trace[0]['line'] . ")" . PHP_EOL . PHP_EOL;

    if (!$append) {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $filename . '.txt', $line);
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $filename . '.txt', print_r($array, true), FILE_APPEND);
    } else {
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $filename . '.txt', $line, FILE_APPEND);
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/' . $filename . '.txt', print_r($array, true), FILE_APPEND);
    }
}

function p($array, $header = "")
{
    $trace = debug_backtrace();
    $file = str_replace($_SERVER['DOCUMENT_ROOT'], '/', $trace[0]['file']);
    echo '<pre style="font-size: 10pt; background-color: #fff; color: #000; margin: 10px; padding: 10px; border: 1px solid red; text-align: left; max-width: 800px; max-height: 600px; overflow: scroll">';
    echo '<div style="font-size: 7pt; color:#aaa; margin-bottom:6px;">' . $file . ' (' . $trace[0]['line'] . ')</div>';
    if ($header)
        echo "<h1>" . $header . "</h1>";
    echo htmlspecialcharsEx(print_r($array, true));
    echo '</pre>';
}

function checkAccessForDelete()
{
    global $USER;
    $currentUserId = $USER->GetId();

    $userArray = array();
    $depArray = array();

    // get from relation table b_crm_role_relation
    $res = CCrmRole::GetRelation();
    while ($item = $res->fetch()) {
        if ($item['ROLE_ID'] == LEAD_MANAGEMENT_ROLE_ID) {
            $relation = str_split($item['RELATION']);

            if ($relation[0] == 'U') {
                $userId = '';

                for ($i = 1; $i < count($relation); $i++) {
                    $userId .= $relation[$i];
                }

                $userArray[] = intval($userId);
            } else if ($relation[0] == 'D' && $relation[1] != 'R') {
                $depId = '';

                for ($i = 1; $i < count($relation); $i++) {
                    $depId .= $relation[$i];
                }

                $depArray[] = intval($depId);
            } else if ($relation[0] == 'D' && $relation[1] == 'R') {
                $depId = '';

                for ($i = 2; $i < count($relation); $i++) {
                    $depId .= $relation[$i];
                }

                $depArray[] = intval($depId);
            }
        }
    }

    $db = \Bitrix\Intranet\Util::GetDepartmentEmployees([
        'DEPARTMENTS' => $depArray,
        'RECURSIVE' => 'Y',
        'ACTIVE' => 'Y',
        'SELECT' => [
            'EMAIL'
        ]
    ]);

    $tempUserArray = array();

    while ($item = $db->fetch()) {
        $tempUserArray[] = $item['ID'];
    }

    // array_unique - for prevent duplicating id after merge array with single user's and user's from departments
    $userIdArray = array_unique(array_merge($userArray, $tempUserArray));

    foreach ($userIdArray as $user) {
        if ($user == $currentUserId) {
            return false;
        }
    }
    return true;
}

function validate_date($date, $format)
{
    $date_obj = DateTime::createFromFormat($format, $date);
    return $date_obj && $date_obj->format($format) == $date;
}

function convert_date($date, $from_format, $to_format)
{
    $date_obj = DateTime::createFromFormat($from_format, $date);
    return $date_obj->format($to_format);
}

function fixEndDate(&$DateTime)
{
    $input_format = 'Y-m-d 23:59:59';
    $output_format = 'Y-m-d H:i:s';
    $DateTime = new Bitrix\Main\Type\DateTime($DateTime->format($input_format), $output_format);
}

function fixAnalyticsFilter(&$control, $GUID)
{
    if (empty($control['filter']['periodType'])) {
        $GUID_parts = explode('_', $GUID);
        $filter_name = 'crm-vc-myreports-crm-' . $GUID_parts[0];
        CModule::IncludeModule('crm');
        global $USER;
        $user_id = $USER->GetID();
        $filters = \CUserOptions::getOption("main.ui.filter", $filter_name, [], $user_id);
        if (!empty($filters)) {
            foreach ($filters['filters'] as $filter_key => $filter) {
                if ($filter_key == 'tmp_filter') {
                    if (!empty($filter['fields']['PERIOD_datesel'])) {
                        $control['filter']['periodType'] = Bitrix\Crm\Widget\FilterPeriodType::convertFromDateType($filter['fields']['PERIOD_datesel']);
                        if (!empty($filter['fields']['PERIOD_year'])) {
                            $control['filter']['year'] = $filter['fields']['PERIOD_year'];
                        }
                        if (!empty($filter['fields']['PERIOD_month'])) {
                            $control['filter']['month'] = $filter['fields']['PERIOD_month'];
                        }
                        if (!empty($filter['fields']['PERIOD_quarter'])) {
                            $control['filter']['quarter'] = $filter['fields']['PERIOD_quarter'];
                        }
                    }
                }
            }
        }
    }
}

function getDataByIP($ip)
{
    $ch = curl_init('http://ip-api.com/json/' . $ip);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

function DMStoDEC($deg, $min, $sec)
{
    return $deg + ((($min * 60) + ($sec)) / 3600);
}

function DECtoDMS($dec)
{
    $vars = explode(".", $dec);
    $deg = $vars[0];
    $tempma = "0." . $vars[1];

    $tempma = $tempma * 3600;
    $min = floor($tempma / 60);
    $sec = $tempma - ($min * 60);

    return array("deg" => $deg, "min" => $min, "sec" => $sec);
}

function getGoogleMapsLink($lat, $lon)
{
    $latArray = DECtoDMS($lat);
    $lonArray = DECtoDMS($lon);

    if ($lat > 0)
        $latDirection = 'N';
    else {
        $latDirection = 'S';
        $latArray['deg'] *= -1;
    }
    if ($lon > 0)
        $lonDirection = 'E';
    else {
        $lonDirection = 'W';
        $lonArray['deg'] *= -1;
    }

    $link = 'https://www.google.com/maps/place/' . $latArray['deg'] . '%C2%B0' . $latArray['min'] . '\'' . $latArray['sec'] . '%22' . $latDirection .
        '+' . $lonArray['deg'] . '%C2%B0' . $lonArray['min'] . '\'' . $lonArray['sec'] . '%22' . $lonDirection;

    return '<a target="_blank" href="' . $link . '">Click</a>';
}

/**
 * @param array $ufCRMTask Ex. D_1234, L_4321
 * @return array
 */
function getTitlesForTaskFilter($ufCRMTask)
{
    $titles = array();
    foreach ($ufCRMTask as $item) {
        $explodedItem = explode('_', $item);

        if ($explodedItem[0] == 'L') {
//            $lead = CCrmLead::GetByID($explodedItem[1]);
            $lead = CCrmLead::GetListEx(array(), array('ID' => $explodedItem[1]), false, false, array('TITLE'))
                ->fetch();
            $title = $lead['TITLE'];

            $titles[] = $title;
        } elseif ($explodedItem[0] == 'D') {
//            $deal = CCrmDeal::GetByID($explodedItem[1]);
            $deal = CCrmDeal::GetListEx(array(), array('ID' => $explodedItem[1]), false, false, array('TITLE'))
                ->fetch();
            $title = $deal['TITLE'];

            $titles[] = $title;
        }
    }

    return $titles;
}