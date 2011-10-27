<?php
error_reporting(E_ALL);
include('include/id.php');
$apiCacheTime = 21600; // Ammount of time in seconds the detailed API info is cached. Currently 6 hours.
$debug = true; // Turns on debug comments in the output.
$uuid = rand(0,9999999);
$bbcodePattern = "/\[{1}eveban\-(.*)\={1}([a-zA-Z0-9 ']{4,24})\](.*)\[{1}\/{1}eveban\]{1}/"; // Regex for the BBCode
$bbcodeMatch = $_POST['input'];

// How long will this take?
$starttime = microtime_float();

function microtime_float() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

// Set up the template
include('template.inc');
$template = new Template('.', 'keep');
$template->set_file('page', 'template/id.html');
$template->set_var('TITLE', 'API Test');

// Extract the header block from the template and output it to the page.
$template->set_block('page', 'header');
$template->pparse('out', 'header');

if(!isset($bbcodeMatch)) {
	echo 'No input specified. Fill out the bbCode in the box below.';
	$template->set_var('BL_INPUT_TEXT','[eveban-char=Some Character]Reason for ban.[/eveban]');
} else {
	// mySQL Connection Information
	$mySQL_username = 'tdf_idTest';
	$mySQL_password = '*******************';
	$mySQL_host = 'localhost';
	$mySQL_database = 'tdf_idTest';
	$TDFbl_table_id = 'id';
	$TDFbl_table_char = 'lookup_character';
	
	// Connect to mySQL
	echo mysqlConnect($mySQL_host,$mySQL_username,$mySQL_password,$mySQL_database);
	
	preg_match($bbcodePattern, $bbcodeMatch, $bbcodeOutput, PREG_OFFSET_CAPTURE);
	$bbcodeType = $bbcodeOutput[1][0];
	$bbcodeName = $bbcodeOutput[2][0];
	$bbcodeReason = $bbcodeOutput[3][0];
	$template->set_var('BL_INPUT_TEXT',$bbcodeMatch);


	// Check if the entity is already known
	$query = mysqlQuery("SELECT * FROM `$TDFbl_table_id` WHERE `value` LIKE '%s'",$bbcodeName);
	if($debug == true) { $debug_log .= "<!--"; $debug_log .= print_r($query,true);	$debug_log .= "-->\n"; }
	if($query != false) {
		$TDFbl_charID = $query[0]['characterID'];
		$TDFbl_charName = $query[0]['value'];
		$TDFbl_type = $query[0]['type'];
		if($debug == true) { $debug_log .= "<!--ID data was retrieved from the database-->\n";	}
	} else {
		$TDFbl_query = urlencode($bbcodeName);
		$TDFbl_url = 'http://api.eve-online.com/eve/CharacterID.xml.aspx?names='.$TDFbl_query;
		$TDFbl_fp = fopen($TDFbl_url, "r");
		$TDFbl_data = fread($TDFbl_fp, 80000); 

		$TDFbl_xml = simplexml_load_string($TDFbl_data);
		$TDFbl_xml2 = $TDFbl_xml->result->rowset;
		$TDFbl_json = json_encode($TDFbl_xml2);
		$TDFbl_array = json_decode($TDFbl_json,TRUE);

		$TDFbl_charID = $TDFbl_array['row']['@attributes']['characterID'];
		$TDFbl_charName = $TDFbl_array['row']['@attributes']['name'];
		$TDFbl_type = '1';

		if($TDFbl_charID > 0) {
			$query = mysqlQuery("INSERT INTO $TDFbl_table_id (characterID,value,type) VALUES ('%s','%s','%s')",$TDFbl_charID,$TDFbl_charName,'1');
			if($query != false && $debug == true) {
				$debug_log .= "<!--ID data was retrieved from the API and added to the database-->\n";
			} else {
				$debug_log .= "<!--ID data was retrieved from the API but a problem prevented it from being added to the database-->\n";
			}
		} else {
			$TDFbl_charName = "Name Invalid";
		}
	}

	// Check if entity detailed information exists
	if($TDFbl_type == '1') {
		$tableType = $TDFbl_table_char;
	}
	$query = mysqlQuery("SELECT * FROM `$tableType` WHERE `characterID` = '%s'",$TDFbl_charID);
	if($debug == true) { $debug_log .= "<!--"; $debug_log .= print_r($query,true); $debug_log .= "-->\n"; }
	if($query != false && time() - $apiCacheTime < $query[0]['date']) {
		$TDFbl_race = $query[0]['race'];
		$TDFbl_bloodline = $query[0]['bloodline'];
		$TDFbl_corp = $query[0]['corp'];
		$TDFbl_corpID = $query[0]['corpID'];
		$TDFbl_alliance = $query[0]['alliance'];
		$TDFbl_allianceID = $query[0]['allianceID'];
		$TDFbl_sec = $query[0]['sec'];
		$TDFbl_cached = $query[0]['date'];
		if($debug == true) {	$debug_log .= "<!--Detailed data was retrieved from the database-->\n"; }
		$cachedText = sprintf("<!--Data cached on: %s. Cache valid for another %s seconds.-->",date('r',$TDFbl_cached),$TDFbl_cached - (time() - $apiCacheTime));
	} else {
		$TDFbl_url_detailed = 'https://api.eveonline.com/eve/CharacterInfo.xml.aspx?characterID='.$TDFbl_charID;
		$TDFbl_fp_detailed = fopen($TDFbl_url_detailed, "r");
		$TDFbl_data_detailed = fread($TDFbl_fp_detailed, 80000); 
	
		$TDFbl_xml_detailed = simplexml_load_string($TDFbl_data_detailed);
		$TDFbl_xml2_detailed = $TDFbl_xml_detailed->result;
		$TDFbl_json_detailed = json_encode($TDFbl_xml2_detailed);
		$TDFbl_array_detailed = json_decode($TDFbl_json_detailed,TRUE);
	
		$TDFbl_race = $TDFbl_array_detailed['race'];
		$TDFbl_bloodline = $TDFbl_array_detailed['bloodline'];
		$TDFbl_corp = $TDFbl_array_detailed['corporation'];
		$TDFbl_corpID = $TDFbl_array_detailed['corporationID'];
		$TDFbl_alliance = $TDFbl_array_detailed['alliance'];
		$TDFbl_allianceID = $TDFbl_array_detailed['allianceID'];
		$TDFbl_sec = $TDFbl_array_detailed['securityStatus'];
		$TDFbl_cached = time();

		if($TDFbl_charID > 0) {
			if($query != false && time() - $apiCacheTime > $query[0]['date']) {
				$query = mysqlQuery("UPDATE $tableType SET race = '%s', bloodline = '%s', corp = '%s', corpID = '%s', alliance = '%s', allianceID = '%s', sec = '%s', date = '%s' WHERE `characterID` = '%s'",$TDFbl_race,$TDFbl_bloodline,$TDFbl_corp,$TDFbl_corpID,$TDFbl_alliance,$TDFbl_allianceID,$TDFbl_sec,time(),$TDFbl_charID);
				if($query != false && $debug == true) {
					$debug_log .= "<!--Database was updated with newer data from the API-->\n";
				} else {
					$debug_log .= "<!--Detailed data was retrieved from the API but a problem prevented the database from being updated-->\n";
				}
			} else {
				$query = mysqlQuery("INSERT INTO $tableType (race,bloodline,corp,corpID,alliance,allianceID,sec,characterID,date) VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s')",$TDFbl_race,$TDFbl_bloodline,$TDFbl_corp,$TDFbl_corpID,$TDFbl_alliance,$TDFbl_allianceID,$TDFbl_sec,$TDFbl_charID,$TDFbl_cached);
				if($query != false && $debug == true) {
					$debug_log .= "<!--Detailed data was retrieved from the API and added to the database-->\n";
				} else {
					$debug_log .= "<!--Detailed data was retrieved from the API but a problem prevented it from being added to the database-->\n";
				}
			}
		}
		$cachedText = sprintf("<!--Data cached on: %s. Cache valid for another %s seconds.-->",date('r',$TDFbl_cached),$apiCacheTime);

	}
	
	if(isset($bbcodeReason)) {
		$reason = $bbcodeReason;
	} else {
		$reason = 'No reason given.';
	}

	if($TDFbl_alliance) {$alliance = '<br />Alliance: '.$TDFbl_alliance;}
	$template->set_var('BL_CHAR_ID',$TDFbl_charID);
	$template->set_var('BL_CHAR_NAME',$TDFbl_charName);
	$template->set_var('BL_CHAR_IMG','imgCache.php?s=128&q='.$TDFbl_charID);
	$template->set_var('BL_CHAR_INFO',$TDFbl_charName.'<br />'.$TDFbl_race.' - '.$TDFbl_bloodline.'<br />Sec Status: '.sprintf("%04.2f", round($TDFbl_sec,2)).'<br />Corp: '.$TDFbl_corp.$alliance);
	$template->set_var('BL_CHAR_REASON',htmlspecialchars($reason));
	$template->set_var('BL_JS_LOAD','loadAccordion(\'BL_Content_'.$TDFbl_charID.'_'.$uuid.'\', \'BL_Toggle_'.$TDFbl_charID.'_'.$uuid.'\', \'[-]\', \'[+]\');');
	$template->set_var('BL_UUID',$uuid);
	
	// Extract the entry block from the template and output it to the page.
	$template->set_block('page', 'bl_entry');
	$template->pparse('out', 'bl_entry');
}

// Extract the footer block from the template and output it to the page.
$endtime = microtime_float(); $totaltime = $endtime - $starttime;
$template->set_var('BL_DEBUG',$debug_log);
$template->set_var('BL_EXE_TIME', sprintf('<!--Page generated in %.3f seconds.-->', $totaltime));
$template->set_var('BL_CACHED',$cachedText);
$template->set_block('page', 'footer');
$template->pparse('out', 'footer');
?>
