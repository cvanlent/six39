<?php

require_once "../lib/header.php";
require_once "header.php";
use Goutte\Client;

line_out("Grading SI339 and SI 539 Assignments 4/7");
$url = getUrlSIx39('http://csevumich.byethost18.com/howdy.php');
$grade = 0;

error_log("ASSN04 ".$url);
line_out("Retrieving ".htmlent_utf8($url)."...");
flush();

// http://symfony.com/doc/current/components/dom_crawler.html
$client = new Client();

$crawler = $client->request('GET', $url);
$html = $crawler->html();
line_out("Retrieved ".strlen($html)." characters.");
togglePre("Show retrieved page",$html);


line_out("Grade is ".$grade);
line_out("Searching for <title> tag...");

try {
    $title = $crawler->filter('title')->text();
    line_out("Found title tag...");
} catch(Exception $ex) {
    error_out("Did not find title tag");
    $title = "";
}

line_out("Searching for external style sheet...");

try {
    $link = $crawler->filterXPath('//link')->attr('href');
    $dir = dirname($url);

    $crawler2 = $client->request('GET', htmlent_utf8($dir)."/".$link);

    $file_headers = @get_headers(htmlent_utf8($dir)."/".$link);
    if (URLIsValid(htmlent_utf8($dir)."/".$link))
        line_out($link." was  a valid  file.");
    else
        error_out($link."  was not  a valid  file.");
} catch(Exception $ex) {
    error_out("Did not find link..");
    $link = "";
}


line_out("Searching for <body> tag...");

try {
    $body = $crawler->filter('body')->text();
    line_out("Found body tag...");
} catch(Exception $ex) {
    error_out("Did not find body tag");
    $body = "";
}

line_out("Searching for <header> tag...");

try {
	$header = $crawler->filter('header')->text();
    line_out("Found header tag...");
} catch(Exception $ex) {
    error_out("Did not find header tag");
	$header = "";
}

line_out("Searching for <nav> tag...");

try {
    $nav = $crawler->filter('nav')->text();
    line_out("Found nav tag...");
} catch(Exception $ex) {
    error_out("Did not find nav tag");
    $nav = "";
}

line_out("Checking the links...");
$dom = new DOMDocument;
@$dom->loadHTML($html);
foreach ($dom->getElementsByTagName('a') as $node)
{
  $u = $node->getAttribute("href");
  if (URLIsValid(htmlent_utf8($dir)."/".$u) || URLIsValid($u))
    line_out($u." was  a valid  file.");
  else
    error_out($u."  was not  a valid  file.");
}


line_out("Searching for lists in the nav...there shouldn't be any");

try {
    $navlinks = $crawler->filter('nav > ul')->text(); 
    error_out("Found lists in the navigation");
} catch(Exception $ex) {
    line_out("Did not find links in the navigation");
    $navlinks = "";
}

line_out("Searching for <footer> tag...");

try {
	$footer = $crawler->filter('footer')->text();
    line_out("Found footer tag...");
} catch(Exception $ex) {
    error_out("Did not find footer tag");
	$footer = "";
}


/*NOW LOOK FOR THINGS THAT SHOULD BE THERE */

line_out("Searching for header, navbar, and footer ids...they shouldn't be here");

try {
    $headerID = $crawler->filterXPath('//div')->attr('id');
    if ($headerID == "header")
        error_out("Found div tag with header id!!...");
    if ($headerID == "navbar")
        error_out("Found div tag with navbar id!!...");
    if ($headerID == "footer")
        error_out("Found div tag with footer id!!...");
} catch(Exception $ex) {
    $headerID = "";
}

line_out("Searching for <table> tag...(it shouldn't be here)");

try {
	$table = $crawler->filter('table')->text();
	error_out("Found a  table tag");
} catch(Exception $ex) {
    line_out("Did not find table tag");
	$table = "";
}

//COLLEEN - WHERE DID DISPLAY NAME COME FROM??
if ( $displayname && strpos($h1,$displayname) !== false ) {
	success_out("Found ($displayname) in the h1 tag");
} else if ( $displayname ) {
	line_out("Warning: Unable to find $displayname in the h1 tag");
}

//COLLEEN Can I search for something *between* elements


$success = "";
$failure = "";
$grade = 0.0;

//COLLEEN -- help me here...
/*if ( strpos($nav, "<li>") !== false ) {
    $failure = "You should not have list items in your nav";
} else if ( strpos($nav, "<li>") == false ) {
    $success = "Did not find list items in nav - assignment correct!";
	$grade = 1.0;
} else {
    $failure = "Did not find 'Hello' in the h1 tag - assignment not complete!";
}
*/
if ( strlen($success) > 0 ) {
    success_out($success);
    error_log($success);
} else if ( strlen($failure) > 0 ) {
    error_out($failure);
    error_log($failure);
    exit();
} else {
    error_log("No status");
    exit();
}

// Send grade
//COLLEEN --DID Penalty come from CTools?
if ( $penalty !== false ) $grade = $grade * (1.0 - $penalty);
if ( $grade > 0.0 ) testPassed($grade);



function URLIsValid($URL)
{
    $exists = true;
    $file_headers = @get_headers($URL);
    $InvalidHeaders = array('404', '403', '500');
    foreach($InvalidHeaders as $HeaderVal)
    {
            if(strstr($file_headers[0], $HeaderVal))
            {
                    $exists = false;
                    break;
            }
    }
    return $exists;
}
