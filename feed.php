<?php
	header("Content-Type: application/rss+xml; charset=UTF-8");

	# Use the Curl extension to query the URL and get back a page of results
	$url = "https://www.studiareinformatica.uniroma1.it";

	# Init the Curl process
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url . "/avvisi");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

	# store in $HTML array the curl output
	$html = curl_exec($ch);
	curl_close($ch);


	# Create a DOM parser object
	$dom = new DOMDocument();

	# Parse the HTML from array.
	# The @ before the method call suppresses any warnings that
	# loadHTML might throw because of invalid HTML in the page.
	@$dom->loadHTML($html);

	#Init the rssfeed variable
	$rssfeed = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	$rssfeed .= '<rss version="2.0">' . "\n";
	$rssfeed .= '<channel>' . "\n";
	$rssfeed .= "\t" . '<title>Università La Sapienza</title>' . "\n";
	$rssfeed .= "\t" . '<link>https://www.gasparini.cloud/sapienza-feed</link>' . "\n";
	$rssfeed .= "\t" . '<description>Bacheca Avvisi - Corso di Laurea in Informatica</description>' . "\n";
	$rssfeed .= "\t" . '<language>it-IT</language>' . "\n";
	$rssfeed .= "\t" . '<copyright>Copyright (C) 2018 gasparini.cloud</copyright>' . "\n";
	$rssfeed .= "\t" . '<lastBuildDate>' . date("D, d M Y H:i:s O") . '</lastBuildDate>' . "\n";

	#Init the temp array
	$array = array();
	$count = 0;

	# Iterate over all the <span> tags 
	# the even spans are title entries, while the odd ones are date entries
	# e.g. array[0] = "Avviso numero 1"; array[1] = "16/03/2021";
	# 	   array[2] = "Avviso numero 2"; array[3] = "17/03/2021";
	foreach($dom->getElementsByTagName('span') as $link)
	{
		$array[$count] = array();
		$array[$count] = $link->nodeValue;
		$count++;
	}

	$count = 0;
	while ($count < count($array))
	{
		$description = $array[$count];
		$count++;
		$data_description = $array[$count];
		$count++;
		$new_date = date_create_from_format('d/m/Y', $data_description);
		$data_description = date_format($new_date, 'Y-m-d');

		$hyperlink = "";

		foreach($dom->getElementsByTagName('a') as $anchor)
		{
			if(strcmp($anchor->nodeValue, $description) == 0)
			{
				$hyperlink = $anchor->getAttribute('href');
				break;
			}
		}

		$rssfeed .= "\n";
		$rssfeed .= "\t" . '<item>' . "\n";
		$rssfeed .= "\t" . '<title>' . $description. '</title>' . "\n";
		$rssfeed .= "\t" . '<description>' . $description. '</description>' . "\n";
		$rssfeed .= "\t" . '<link>' . $url . $hyperlink. '</link>' . "\n";
		#$DATA = str_replace('/', '-', $DATA);
		$rssfeed .= "\t" . '<pubDate>' . date("D, d M Y H:i:s O", strtotime($data_description)). '</pubDate>' . "\n";
		$rssfeed .= "\t" . '</item>' . "\n";
	}

	$rssfeed .= '</channel>' . "\n";
	$rssfeed .= '</rss>' . "\n";

	echo $rssfeed;
?>