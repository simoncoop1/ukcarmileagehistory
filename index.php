<?php
// This is the server-side script.

// Set the content type.
header('Content-Type: text/plain');

// Send the data back.
//echo "This is the returned text.";

$xmlstr = <<<XML
<?xml version='1.0' standalone='yes'?>
<movies>
 <movie>
  <title>PHP: Behind the Parser</title>
  <characters>
   <character>
    <name>Ms. Coder</name>
    <actor>Onlivia Actora</actor>
   </character>
   <character>
    <name>Mr. Coder</name>
    <actor>El Act&#211;r</actor>
   </character>
  </characters>
  <plot>
   So, this language. It's like, a programming language. Or is it a
   scripting language? All is revealed in this thrilling horror spoof
   of a documentary.
  </plot>
  <great-lines>
   <line>PHP solves all my web problems</line>
  </great-lines>
  <rating type="thumbs">7</rating>
  <rating type="stars">5</rating>
 </movie>
</movies>
XML;

//$xml = new SimpleXMLElement($xmlstr);
//$result = $xml->xpath("/movies");

//$xml = new SimpleXMLElement($site);
//$result = $xml->xpath("/test");

//for debugging this page on its own
$debug = false;

$theNum = getPostNum();

//print $theNum; //the num used for post data
if(isset($_GET["reg"])){
	$reg = $_GET["reg"];
}
else{
	//error message
	print "reg not set !!\n";
}
if($debug == true){
	//$reg = "y334+tko";
}


//echo json_encode($_GET["reg"] . "ok");
//echo urlencode($theNum . "=" . "y334+tko" . "&submit=Continue") . "\n";
//echo rawurlencode($theNum . "=" . "y334+tko" . "&submit=Continue") . "\n";
//echo $theNum . "=" . "y334+tko" . "&submit=Continue" . "\n";

//useful for debug
$theNum = "47073374952";

//to debug copy in some post valid post date from browser debug
$postData = $theNum . "=" . urlencode($reg) . "&submit=Continue";
$info = curl_download2("https://www.check-mot.service.gov.uk/",$postData);
ParseMOTPage($info);


function ParseMOTPage($data){

	$dom = new DOMDocument;
	libxml_use_internal_errors(true); // deal with error my self
	$dom->loadHTML($data);
	libxml_clear_errors();
	libxml_use_internal_errors(false); // error reporting back to standard
	
	$xpath = new DOMXpath($dom);
	
	//initialise data objects
	$acar = new Car();
	$acar->motRecords = array();	
	
	$carName = $xpath->evaluate("string(/html/body/main/h1/text()[2])");
	$carName= trim($carName);
	
	$acar->name  = $carName;
	
	//get the registration date to represent mileago 0
	//$elements2 = $xpath->evaluate("string(/html/body/main/div/div/div/div[@class=\"column-one-third\"][3]/h2/text())");
	$registrationDate = $xpath->evaluate("string(/html/body/main/div/div/div/div[@class=\"column-one-third\"][3]/h2/text()[2])");
	$registrationDate = trim($registrationDate);
	
	$test = new MOTMileage();
	$test->date = $registrationDate;
	$test->mileage = "0";
	$test->motNum = "n/a";
	array_push( $acar->motRecords , $test );
	
	//print_r($elements2,false);
	//echo "\n";
	//echo $registrationDate;
	//foreach($elements2 as $ok)
		//echo $ . ",";
	//echo "\n";
	
	//$elements = $xpath->query("*/div[@class='grid-row u-space-b15']");	
	//$elements = $xpath->query("//div/@class");	
	//$elements = $xpath->query("//div[@class=\"grid-row u-space-b15\" or @class=\"grid-row u-space-b15 u-pad-b15 u-border-bgrey2\"]");
	//$elements = $xpath->query("/html/body/main/div[@class=\"grid-row u-space-b15\"]/div/h3/text()");
	
	//old
	//$elements = $xpath->query("/html/body/main/div[@class=\"grid-row u-space-b15\"]");
		
	$elements = $xpath->query("/html/body/main/div/div/div/div/div[@class=\"grid-row u-space-b30 u-pad-b15 u-border-bgrey2\"]");
	//print "\n" . count($elements) . "\n"; print number of elements

	
	//print "\n" . count($elements) . "\n"; //print number of elements

	
	$counter1 = 0;
	
	//$xml = new SimpleXMLElement('<xml/>');
	
	if (!is_null($elements)) {
		//print_r($elements,FALSE);
		foreach ($elements as $element) {
			//echo get_class( $element );
			//echo trim($element->nodeValue);
			//echo "<br/>[". $element->nodeName. "]";
			//$nodes = $element->childNodes;
			
			//foreach ($nodes as $node) {
			//	echo $node->nodeValue. "\n";
			//}
			//echo $element->getNodePath();

			//$subelements = $xpath->query("div/h3/text()[2]",$element);
			$subelements1 = $xpath->query("div/h3/text()[2]",$element);
			$subelements2 = $xpath->query("div/div/div/h3/text()[2]",$element);
			
			
			//$atest = $xml->addChild('test');
			
			//debug
			//print_r($subelements);
						
			$test = new MOTMileage();		

			$xpathDate = $subelements1->item(0)->nodeValue;
			$xpathMileage = $subelements2->item(0)->nodeValue;
			$xpathMotNum = $subelements2->item(1)->nodeValue;
			
			$test->date = $xpathDate;
			$test->mileage = $xpathMileage;
			$test->motNum = $xpathMotNum;
			
			array_push( $acar->motRecords , $test );
			
			
			foreach ($subelements1 as $subelement1){
				//if(strlen(trim($subelement->nodeValue)) == 0) //ignore 
				//	continue;
				//$tNodeValue = trim($subelement->nodeValue);
				//$test->addChild('date' , "$tNodeValue");			
				
				
				//echo $subelement->getNodePath() . "\n";
				//echo $subelement->nodeValue . "\n";
			}
			
			//for ($i = 1; $i <= 8; ++$i) {
			//	$track = $xml->addChild('track');
			//	$track->addChild('path', "song$i.mp3");
			//	$track->addChild('title', "Track $i - Track Title");
			//}

			//
			
			
		}
	}
	
	//Header('Content-type: text/xml');
	//print($xml->asXML());
	
	$myJSON = json_encode($acar);
	echo $myJSON;

	//print $data;
}

//get the number you need to post a number plate
function getPostNum(){

	$site = curl_download1("https://www.check-mot.service.gov.uk/");

	$theNum = "0";
	$dom = new DOMDocument;
	libxml_use_internal_errors(true); // deal with error my self
	$dom->loadHTML($site);
	libxml_clear_errors();
	libxml_use_internal_errors(false); // error reporting back to standard
	$inputs = $dom->getElementsByTagName('input');
	//print $inputs->length;
	foreach ($inputs as $input) {
			//$input->setAttribute('src', 'http://example.com/' . $input->getAttribute('src'));
			$nameVal = ($input->getAttribute("name"));
			$match = preg_match("/^[0-9][0-9]+$/",$nameVal);
			if($match == 1){
				//print strlen($nameVal) . ","; //the longest number is the one that seem to work
				if(strlen($nameVal) > strlen($theNum)){
					$theNum = $nameVal;
				}				
				
			}
			//print_r($input,FALSE);
		
	}
	//$site = $dom->saveHTML();

	//print $site;
	
	//quick error check
	if($theNum != -1){
		return $theNum;
	}
	else{
		Print "ERROR getting Num for Poat";
	}
}

//get initial form in order to get post number
function curl_download1($Url){
 
    // is cURL installed yet?
    if (!function_exists('curl_init')){
        die('Sorry cURL is not installed!');
    }
 
    // OK cool - then let's create a new cURL resource handle
    $ch = curl_init();
 
    // Now set some options (most are optional)
 
    // Set URL to download
    curl_setopt($ch, CURLOPT_URL, $Url);
	
	//ignore ca cert for https
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 
    // Set a referer
    //curl_setopt($ch, CURLOPT_REFERER, "http://www.example.org/yay.htm");
 
    // User agent
    //curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
 
    // Include header in result? (0 = yes, 1 = no)
    curl_setopt($ch, CURLOPT_HEADER, 0);	
		
	// Should cURL return or print out the data? (true = return, false = print)
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
    // Timeout in seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
 
    // Download the given URL, and return output
    $output = curl_exec($ch);
 
    // Close the cURL resource, and free system resources
    curl_close($ch);
 
    return $output;
}

//post request for MOT page
function curl_download2($Url,$postData){
 
    // is cURL installed yet?
    if (!function_exists('curl_init')){
        die('Sorry cURL is not installed!');
    }
 
    // OK cool - then let's create a new cURL resource handle
    $ch = curl_init();
 
    // Now set some options (most are optional)
 
    // Set URL to download
    curl_setopt($ch, CURLOPT_URL, $Url);
	
	//ignore ca cert for https
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS,
            $postData);
 
    // Set a referer
    //curl_setopt($ch, CURLOPT_REFERER, "http://www.example.org/yay.htm");
 
    // User agent
    //curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
	
	//set useragent
	//curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36");
	//linux
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.108 Safari/537.36");

 
    // Include header in result? (0 = yes, 1 = no)
    curl_setopt($ch, CURLOPT_HEADER, 0);	
		
	// Should cURL return or print out the data? (true = return, false = print)
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
    // Timeout in seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
 
    // Download the given URL, and return output
    $output = curl_exec($ch);
 
    // Close the cURL resource, and free system resources
    curl_close($ch);
 
	//debug
	//echo $output;
 
    return $output;
}

class Car{

	public $name;
	public $motRecords;
	
	function __construct(){		
	}

}

class MOTMileage
{
    // property declaration
    public $date;
	public $mileage;
	public $motNum;
	
	function __construct(){		
	}

	//new DateTime("2015-11-01 00:00:00"

    // method declaration
    public function displayVar() {
        echo $this->var;
    }
}

?>