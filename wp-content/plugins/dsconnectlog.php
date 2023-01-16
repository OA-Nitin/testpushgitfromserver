<?php
$path = preg_replace( '/wp-content.*$/', '', __DIR__ );
require_once( $path . 'wp-load.php' );

$filename = "docusingLog.txt";
$data = file_get_contents("php://input");
$events = json_decode($data, true);

file_put_contents($filename,"status: ".print_r($events['event'],true).PHP_EOL,FILE_APPEND);
file_put_contents($filename,"generatedDateTime: ".print_r($events['generatedDateTime'],true).PHP_EOL,FILE_APPEND);
file_put_contents($filename,"accountId: ".print_r($events['data']['accountId'],true).PHP_EOL,FILE_APPEND);
file_put_contents($filename,"userId: ".print_r($events['data']['userId'],true).PHP_EOL,FILE_APPEND);
file_put_contents($filename,"envelopeId: ".print_r($events['data']['envelopeId'],true).PHP_EOL,FILE_APPEND);
file_put_contents($filename,"recipientId: ".print_r($events['data']['recipientId'],true).'<br>'.PHP_EOL,FILE_APPEND);

$insert_data = array(
			'status' => $events['event'],
			'generatedDateTime' => $events['generatedDateTime'],
			'accountId' =>$events['data']['accountId'],
			'userId' => $events['data']['userId'],
			'envelopeId' => $events['data']['envelopeId'],
			'recipientId' =>$events['data']['recipientId'],
		);
		
$columns = implode(", ",array_keys($insert_data));
$val = implode("', '",array_values($insert_data));
$tble_name = $wpdb->prefix .'docusignconnect_log_tbl';
$sql = "INSERT INTO `$tble_name` ($columns) VALUES ('$val')";

//echo $sql;
global $wpdb;
if(isset($events['event'])){
	if($wpdb->query($sql) == true){
		$last_insert_id = $wpdb->insert_id;
	}else{
		$last_insert_id = '';
	}
	echo 'Data logged id:'.$last_insert_id.'<br>';
}




/******** Downloading singed documents after eveloped completed *******/
if($events['event']=="envelope-completed"){
	echo 'completed';
	$envelopeid = $events['data']['envelopeId'];
	$endpoint = get_site_url()."/wp-json/v1/getsigned_documents_by_envid";
	$envelopeid_data = json_encode(array(
		"envelopeid"  => $envelopeid,
	)); 
	//print_r($envelopeid_data);
	$ch_env = curl_init();
	curl_setopt($ch_env, CURLOPT_URL, $endpoint);
	curl_setopt($ch_env, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch_env, CURLOPT_POST, 1);
	curl_setopt($ch_env, CURLOPT_POSTFIELDS, $envelopeid_data);
	$result_env = curl_exec($ch_env);
	//print_r($result_env);
	if (curl_errno($ch_env)) {
		file_put_contents($filename,"envelopeId: ".print_r($events['data']['envelopeId'],true)."- Error:".print_r(curl_error($ch_env)).PHP_EOL,FILE_APPEND);
		echo 'Error:' . curl_error($ch_env);
	}
	
	$env_json_result = json_decode($result_env);
	
	//print_r($_POST);
	print_r($env_json_result);
	file_put_contents($filename,"envelopeId: ".print_r($events['data']['envelopeId'],true)."- Exiting download function:".print_r($env_json_result).PHP_EOL,FILE_APPEND);
	
}


$results2 = $wpdb->get_results("SELECT * FROM $tble_name" );
//print_r($results2);
echo '<table>';
foreach($results2 as $result1){
	echo '<tr>';
	foreach($result1 as $resultKey1 => $resultValue1){
		echo '<th style="padding: 7px;text-transform: capitalize;">'.$resultKey1.'</th>';
		//echo '<td>'.$resultValue.'</td>';
	}
	echo '</tr>';
	break;
}
foreach($results2 as $result1){
	echo '<tr style="background-color:#e7e7e7;">';
	foreach($result1 as $resultKey1 => $resultValue1){
		//echo '<th>'.$resultKey.'</th>';
		echo '<td>'.$resultValue1.'</td>';
	}
	echo '</tr>';
}
echo '</table>';
?>