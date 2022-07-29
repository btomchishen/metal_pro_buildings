<?
function findNext($array, $item)
{
    if($item >= max($array))
    {
        $result = max($array);
    }
    else
    {
        rsort($array);
        foreach($array as $ar)
        {
            if($ar >=  $item)
                $result = $ar;
        }
    }
    return $result;
}

function uniTrim($str)
{
    $text = hex2bin(str_replace('c2a0', '20', bin2hex($str)));
    while( strpos($text,'  ') !== false)
        $text = str_replace("  ", " ", $text);
    $text = trim($text);
    return $text;
}

function fp($array, $filename = "aRes", $append = false)
{
    $trace = debug_backtrace();
    $file = str_replace($_SERVER['DOCUMENT_ROOT'], '/', $trace[0]['file']);
    $line = PHP_EOL.$file."(".$trace[0]['line'].")".PHP_EOL.PHP_EOL;

    if(!$append)
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/'.$filename.'.txt', $line);
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/'.$filename.'.txt', print_r($array, true), FILE_APPEND);
    }
    else
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/'.$filename.'.txt', $line, FILE_APPEND);
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/'.$filename.'.txt', print_r($array, true), FILE_APPEND);
    }
}

function p($array, $header = "")
{
    $trace = debug_backtrace();
    $file = str_replace($_SERVER['DOCUMENT_ROOT'], '/', $trace[0]['file']);
    echo '<pre style="font-size: 10pt; background-color: #fff; color: #000; margin: 10px; padding: 10px; border: 1px solid red; text-align: left; max-width: 800px; max-height: 600px; overflow: scroll">';
    echo '<div style="font-size: 7pt; color:#aaa; margin-bottom:6px;">' . $file . ' (' . $trace[0]['line'] . ')</div>';
    if($header)
        echo "<h1>".$header."</h1>";
    echo htmlspecialcharsEx(print_r($array, true));
    echo '</pre>';
}