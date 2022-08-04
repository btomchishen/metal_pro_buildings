<?
header('Content-type: application/json');
$json = json_decode(file_get_contents("arrays.json"), true);
echo json_encode( $json );
?>