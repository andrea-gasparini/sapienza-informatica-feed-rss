<?php
include "db_credentials.php";
header("Content-Type: application/rss+xml; charset=UTF-8");

# Use the Curl extension to query the URL and get back a page of results
	$url = "https://www.studiareinformatica.uniroma1.it/avvisi";

# Init the Curl process
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

# store in $HTML array the curl output
	$html = curl_exec($ch);
	curl_close($ch);

# Init MySQL DB
	$connection = @mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)
    or die('Could not connect to database');
    mysql_select_db(DB_NAME)
    or die ('Could not select database');
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
	
# Create a DOM parser object
	$dom = new DOMDocument();

# Parse the HTML from array.
# The @ before the method call suppresses any warnings that
# loadHTML might throw because of invalid HTML in the page.
	@$dom->loadHTML($html);
	
#Init the rssfeed variable
    $rssfeed = '<?xml version="1.0" encoding="UTF-8"?>';
    $rssfeed .= '<rss version="2.0">';
    $rssfeed .= '<channel>';
    $rssfeed .= '<title>Università La Sapienza</title>';
    $rssfeed .= '<link>https://www.gasparini.cloud</link>';
    $rssfeed .= '<description>Bacheca Avvisi - Corso di Laurea in Informatica</description>';
    $rssfeed .= '<language>it-IT</language>';
    $rssfeed .= '<copyright>Copyright (C) 2018 gasparini.cloud</copyright>';
#Init the temp array
	$array = array();
	$count = 0;
# Iterate over all the <span> tags 
	foreach($dom->getElementsByTagName('span') as $link) { 
	$array[$count] = array();
	$array[$count] = $link->nodeValue;
	$count++;
}
#Print all array values
	#print_r(array_values($array));

#Store array value in MySQL
	$count = 0;
	while ($count < count($array)) {
	$description = $array[$count];
	$count++;
	$data_description = $array[$count];
	$count++;
	$new_date = date_create_from_format('d/m/Y', $data_description);
	$data_description = date_format($new_date, 'Y-m-d');
#Query the RSSFEED table if an element is present, if not insert into table
	$query = "SELECT * FROM `RSSFEED` WHERE `DATA`='". mysql_real_escape_string($data_description) . "' AND `DESCRIPTION` = '". mysql_real_escape_string($description) ."'";
	$result = $conn->query($query);
	if ($result->num_rows == 0) {
		$query = "INSERT INTO RSSFEED (DESCRIPTION, DATA) VALUES ( '" . mysql_real_escape_string($description) . "','" . mysql_real_escape_string($data_description) ."')";
		$result = mysql_query($query) or die ("Could not execute query");
		# Iterate over all the <a> tags 
			foreach($dom->getElementsByTagName('a') as $link) {
				$query = "UPDATE `RSSFEED` SET `HYPERLINK`='http://www.studiareinformatica.uniroma1.it". mysql_real_escape_string($link->getAttribute('href')) ."' WHERE `DATA`='". mysql_real_escape_string($data_description) . "' AND `DESCRIPTION` = '". mysql_real_escape_string($link->nodeValue) ."'";
				$result = mysql_query($query) or die ("Could not execute query");
			}
	}
}

    $query = "SELECT * FROM RSSFEED ORDER BY DATA DESC";
    $result = mysql_query($query) or die ("Could not execute query");
 
    while($row = mysql_fetch_array($result)) {
        extract($row);

        $rssfeed .= '<item>';
        $rssfeed .= '<title>' . $DESCRIPTION. '</title>';
        $rssfeed .= '<description>' . $DESCRIPTION. '</description>';
        $rssfeed .= '<link>' . $HYPERLINK . '</link>';
		#$DATA = str_replace('/', '-', $DATA);
        $rssfeed .= '<pubDate>' . date("D, d M Y H:i:s O", strtotime($DATA)). '</pubDate>';
        $rssfeed .= '</item>';
    }
    $rssfeed .= '</channel>';
    $rssfeed .= '</rss>';
    echo $rssfeed;
?>