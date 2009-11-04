<?php

/****************************/
/* constants */
/****************************/

class AnonymousObject {}		


/****************************/
/* global utility functions */
/****************************/
function hasZeroLength($str) {
	return (strlen($str) == 0);
}

function isValidEmail($email) {
	$pattern = "/^[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i";
	return preg_match($pattern, $email);
}

function getExtension($filename)
{
    $path_info = pathinfo($filename);
    return $path_info['extension'];
}

function resizeImage($filename,$newfilename,$max_width='',$max_height='',$withSampling=true)
{
    // if($newfilename=="")
    //     $newfilename=$filename;
    // Get new sizes
    list($width, $height) = getimagesize($filename);

	if ($max_width == '' && $max_height == '')
	{
		exit("You must set a max height or width");
	}

	if ($max_width != '')
    	$percent = $max_width/$width;
	else if ($max_height != '')
    	$percent = $max_height/$height;
   
    //-- dont resize if the width of the image is smaller or equal than the new size.
    // if($width<=$max_width)
    //     $max_width=$width;
       
    // $percent = $max_width/$width;
   
    $newwidth = $width * $percent;
    $newheight = $height * $percent;
    // if($max_height=='') {
    // } else
    // $newheight = $max_height;
   
    // Load
    $thumb = imagecreatetruecolor($newwidth, $newheight);
    $ext = strtolower(getExtension($filename));

	// print $ext;
	// exit();
   
    if ($ext=='jpg' || $ext=='jpeg') {
        $source = imagecreatefromjpeg($filename);
	} else if ($ext=='gif')	{
        $source = imagecreatefromgif($filename);
	} else if ($ext=='png') {
        $source = imagecreatefrompng($filename);
	}
   
    // Resize
    if($withSampling)
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
    else   
        imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
 
	// imagedestroy($thumb);
   
    // Output
    if($ext=='jpg' || $ext=='jpeg')
        return imagejpeg($thumb,$newfilename);
    if($ext=='gif')
        return imagegif($thumb,$newfilename);
    if($ext=='png')
        return imagepng($thumb,$newfilename);

}

function createGUID(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                .chr(125);// "}"
        return $uuid;
    }
}

function isGUID($value)
{
	$value = trim($value);
	return (substr($value,-1,1)=='}')
			&& (substr($value,0,1)=='{')
			&& (strlen($value)==38);
}


class Helper {

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	var $hostname = CONSTANT_HOSTNAME;     // the adress of the MySQL server
	var $username = CONSTANT_USERNAME;    // your username
	var $password = CONSTANT_PASSWORD;     //your password
	var $database = CONSTANT_DATABASE;   // name of the database
	
	private $link;
	static $MAX_AGE_OF_CACHE_IN_MINUTES = 0;
	static $bad_filenames = array(
		'.htaccess',
		'.DS_Store',
		'.',
		'..',
		'index.json',
		);


	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function __construct() {
		// phpinfo();
		// exit();
		// print $_SERVER['HTTP_HOST'];
		
		$this->ROOTURL = 'http://' . $_SERVER['HTTP_HOST'];
		
		switch($_SERVER['HTTP_HOST']):
			case 'example.com':
				$this->hostname = CONSTANT_HOSTNAME; // the address of the MySQL server
				$this->username = CONSTANT_USERNAME; // your username
				$this->password = CONSTANT_PASSWORD; // your password
				$this->database = CONSTANT_DATABASE; // name of the database
			break;
			case 'localhost':
				$this->hostname = CONSTANT_HOSTNAME; // the address of the MySQL server
				$this->username = CONSTANT_USERNAME; // your username
				$this->password = CONSTANT_PASSWORD; // your password
				$this->database = CONSTANT_DATABASE; // name of the database
			break;
			default:
				$this->hostname = CONSTANT_HOSTNAME; // the address of the MySQL server
				$this->username = CONSTANT_USERNAME; // your username
				$this->password = CONSTANT_PASSWORD; // your password
				$this->database = CONSTANT_DATABASE; // name of the database
		endswitch;
		
		$this->connect();

	} // END __construct
	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function getCache() {
		// print "<!--cache {$_SERVER['REQUEST_URI']}-->";
		$cachefile = $this->getCacheFilePath();
		// exec("touch {$cachefile}");
		print "<!--cache $cachefile -->";
	}
	
	function getGoogleAnalytics() {
		
		return <<< ENDTEXT
<script language="JavaScript" type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("");
pageTracker._initData();
pageTracker._trackPageview();
</script>
ENDTEXT
;
		
		
	}

	static function hasQueryString($string) {
		// does it have a question mark?
		return (strpos($string, '?') ===false);
	}
	
	static function getExtension($filename) {
		$pos = strrpos($filename, '.');
		if($pos===false) {
	    	return false;
		} else {
			return strtolower(substr($filename, $pos+1));
		}
	}
	function filename_extension($filename) {
	   $pos = strrpos($filename, '.');
	   if($pos===false) {
	       return false;
	   } else {
	       return strtolower(substr($filename, $pos+1));
	   }
	}
	
	function doNotCache() {
		$cachepath = $this->getPageContentFilePath();
		return true;
	}
		
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	function getCacheFileContent() {
		$cachepath = $this->getPageContentFilePath();
		if(!$this->isCacheToolOld($cachepath)) {
			return false;
		}


	}

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	function isCacheToolOld($filepath) {

		$cachefile = $this->getCacheFilePath();
		$epoch_now = strtotime('now');
		
		if (file_exists($cachefile))
			$epoch_cache = filemtime($cachefile);
		else {
			$epoch_cache = 0;
		}

		/* time mishagosh */
		$seconds_old = ($epoch_now-$epoch_cache);
		$minutes_old =  $seconds_old / 60;
		$hours_old =  $minutes_old / 60;
		$days_old =  $hours_old / 24;
		/* if the age in miniutes is greater than MAX_AGE_OF_CACHE_IN_MINUTES, then cache is too old */

		return ($minutes_old > self::$MAX_AGE_OF_CACHE_IN_MINUTES);		
	}
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function getPageContentFilePath() {
		return $this->getCacheFilePath();
	}
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	function saveCacheFile($intext, $forceRefresh = false){
		// http://us.php.net/function.filemtime
		$cachefile = $this->getCacheFilePath();

		if ($this->isCacheToolOld($cachefile)) {
			$fp = fopen($this->getCacheFilePath(),'w+');
			$timestamp = $this->timeStampComment();
			fwrite($fp,$intext . $timestamp);
			fclose($fp);
			return;
		} else {
			return;
		}
	}
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	function getTimeStamp() {
		return date('c',strtotime('now'));
	}
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	function timeStampComment(){
		return "\n<!-- cached: " . $this->getTimeStamp() . " -->";
	}
	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	static function compressXmlWhiteSpace($xmlstring) {
		$xmlstring = preg_replace("/\n/",'',$xmlstring);
		$xmlstring = preg_replace("/\t/",'',$xmlstring);
		return $xmlstring;
	}

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function getCacheFilePath() {
		return $_SERVER['DOCUMENT_ROOT'] . '/cache/' . $this->makeCacheFileName($_SERVER['REQUEST_URI']);
	}
	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	function makeCacheFileName($request_uri) {
		return 'CACHE' . str_replace('/','~',$request_uri);
	}

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function connect() {
		$this->link = mysql_connect($this->hostname, $this->username, $this->password);
		if ($this->link && mysql_select_db($this->database))
			return ($this->link);
		else
			die(mysql_error());
		return (FALSE);
	} // END connect

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function query($sql) {
		$this->connect();
		mysql_select_db($this->database) or die(mysql_error());
		
		//$link = $this->connect();
		$result = mysql_query($sql) or die ("Cannot make query. ".mysql_error());
		$outarray = array();
		while ($row = mysql_fetch_array($result,MYSQL_ASSOC)) {
			$outarray[] = $row;
		}
		return $outarray;
	} // END query
	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function execute($sql) {
		$this->connect();
		mysql_select_db($this->database) or die(mysql_error());
		
		//$link = $this->connect();
		mysql_query($sql) or die ("Cannot make query. ".mysql_error() . $sql);
		
		return true;
	} // END execute

		/*
		 *
		 */
		// takes an array with these required keys: 

		/*
		//USAGE:
		$configarray = array(
			"table_headers"=>array(),
			"enclosing_div_id"=>"string",
			"table_id"=>"string",
			"query"=>"SQL QUERY",
		);
		$HELPER = new Helper();
		$HELPER->printYUIDataTable($configarray);
		*/
		
		function printjQueryDataTableAjax($configarray) {

			$required = array(
				"table_headers"
				,"enclosing_div_id"
				,"table_id"
				,"query"
				,"extraConfig"
				);
			foreach($required as $requirement) {
				if(isset($configarray[$requirement]))
					$$requirement = $configarray[$requirement];
				else {
					print "Error. Required field $requirement missing in configuration.";
					return false;
				}

			}

			$Helper = new Helper();
			$outObj = new AnonymousObject();
			$results = $Helper->query($query);
			foreach($results as $row) {
				$temp = array();
				foreach($row as $columnName => $columnValue) {
					$temp[] = $columnValue;
				}
				$outObj->aaData[] = $temp;
			}
			// header("Content-Type: text/plain");
			print json_encode($outObj);
		}
		

		
		
		/***************************************************************
		**                                                            **
		** name ........                                              **
		** parameters ..                                              **
		** returns .....                                              **
		**                                                            **
		***************************************************************/
		function getJQueryDataTable($configarray) {
			$required = array(
				"table_headers"
				,"enclosing_div_id"
				,"table_id"
				,"query"
				,"extraConfig"
				);
			foreach($required as $requirement) {
				if(isset($configarray[$requirement]))
					$$requirement = $configarray[$requirement];
				else {
					print "Error. Required field $requirement missing in configuration.";
					return false;
				}

			}

			$out = "";
			$out .= "\n<div id=\"{$enclosing_div_id}\" class=\"enclose-data-table\">\n";
			$out .= "	<table id=\"{$table_id}\" class=\"a-data-table\">\n";
			$out .= "		<thead>\n";
			$out .= "			<th>";
			$out .= implode("</th><th>",$table_headers);
			$out .= "</th>\n";
			$out .= "		</thead>\n";
			$out .= "		<tbody>\n";
			$result = $this->query($query);
			foreach($result as $row) {
				$extra = "";
				// if($row['isActive'] == 0) {
				// 	$extra = " class=inactive-row";
				// } else {
				// 	$extra = "";
				// }
				$out .= "\t\t\t<tr {$extra}><td>" . implode("</td><td>",$row). "</td></tr>\n"; 
				$keys = array_keys($result[0]);
			}
			$out .= "		</tbody>\n";
			// $out .= "		<tfoot>\n";
			// $out .= "			<th>";
			// $out .= implode("</th><th>",$table_headers);
			// $out .= "</th>\n";
			// $out .= "		</tfoot>\n";
			$out .= "	</table><!--#{$table_id}-->\n";
			$out .= "</div><!--#{$enclosing_div_id}-->\n";




			// We need to construct a json config object that defines the parameters of this item
	/*		example config object:
			{
				"enclosing_div_id":"string"
				, "table_id":"string"
				"columnDefs" : [
			    	{key:"media_count",label:"Media Count",sortable:true},
			        {key:"company",label:"Company",sortable:true},
					{key:"is_active",label:"Active",sortable:true}
			    ]
				, "responseSchema":{
			        fields: [
							{key:"media_count",parser:"number"},
							{key:"company"},
							{key:"is_active",parser:"number"}
			        ] // END fields
			    }

				, "extraConfig" : {
					//caption:"Example: Progressively Enhanced Table from Markup",
			        sortedBy:{
							key:"company",dir:"asc"
						}
					//formatRow: myRowFormatter
					}
			}
			*/
			for ($i=0;$i<count($table_headers);$i++) {
				$config['columnDefs'][] = 
					array(
						// 'key' => $keys[$i],
						'label' => $table_headers[$i],
						'sortable' => TRUE,
						);
				// $config['responseSchema']['fields'][] = 
				// 			array( 
				// 				'key'=> $keys[$i],
				// 				'parser' => 'numeric'
				// 			);
			}
			$config['enclosing_div_id'] = $enclosing_div_id;
			$config['table_id'] = $table_id;
			$config['extraConfig'] = $extraConfig;




			// $out .= "<script type=\"text/javascript\">";
			// $out .= "ARTLUNGADMIN.STATICDATATABLE.push(".json_encode($config).")";
			// $out .= "</script>";





			return $out;

		} // END getJQueryDataTable






	/*
	 *
	 */
	// takes an array with these required keys: 
		
	/*
	//USAGE:
	$configarray = array(
		"table_headers"=>array(),
		"enclosing_div_id"=>"string",
		"table_id"=>"string",
		"query"=>"SQL QUERY",
	);
	$HELPER = new Helper();
	$HELPER->printYUIDataTable($configarray);
	*/
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function getYUIDataTable($configarray) {
		$required = array(
			"table_headers"
			,"enclosing_div_id"
			,"table_id"
			,"query"
			,"extraConfig"
			);
		foreach($required as $requirement) {
			if(isset($configarray[$requirement]))
				$$requirement = $configarray[$requirement];
			else {
				print "Error. Required field $requirement missing in configuration.";
				return false;
			}
			
		}
		
		$out = "";
		$out .= "\n<div id=\"{$enclosing_div_id}\">\n";
		$out .= "	<table id=\"{$table_id}\">\n";
		$out .= "		<thead>\n";
		$out .= "			<th>";
		$out .= implode("</th><th>",$table_headers);
		$out .= "</th>\n";
		$out .= "		</thead>\n";
		$out .= "		<tbody>\n";
		$result = $this->query($query);
		foreach($result as $row) {
			$out .= "<tr><td>" . implode("</td><td>",$row). "</td></tr>\n"; 
			$keys = array_keys($result[0]);
		}
		$out .= "		</tbody>\n";
		$out .= "	</table><!--#{$table_id}-->\n";
		$out .= "</div><!--#{$enclosing_div_id}-->\n";
		


		
		// We need to construct a json config object that defines the parameters of this item
/*		example config object:
		{
			"enclosing_div_id":"string"
			, "table_id":"string"
			"columnDefs" : [
		    	{key:"media_count",label:"Media Count",sortable:true},
		        {key:"company",label:"Company",sortable:true},
				{key:"is_active",label:"Active",sortable:true}
		    ]
			, "responseSchema":{
		        fields: [
						{key:"media_count",parser:"number"},
						{key:"company"},
						{key:"is_active",parser:"number"}
		        ] // END fields
		    }

			, "extraConfig" : {
				//caption:"Example: Progressively Enhanced Table from Markup",
		        sortedBy:{
						key:"company",dir:"asc"
					}
				//formatRow: myRowFormatter
				}
		}
		*/
		for ($i=0;$i<count($table_headers);$i++) {
			$config['columnDefs'][] = 
				array(
					'key' => $keys[$i],
					'label' => $table_headers[$i],
					'sortable' => TRUE,
					);
			$config['responseSchema']['fields'][] = 
						array( 
							'key'=> $keys[$i],
							'parser' => 'numeric'
						);
		}
		$config['enclosing_div_id'] = $enclosing_div_id;
		$config['table_id'] = $table_id;
		$config['extraConfig'] = $extraConfig;
		



		$out .= "<script type=\"text/javascript\">";
		$out .= "ARTLUNGADMIN.STATICDATATABLE.push(".json_encode($config).")";
		$out .= "</script>";



		
		
		return $out;

	} // END getYUIDataTable
	

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function printJQueryDataTable($array) {
		print $this->getJQueryDataTable($array);
	} // END printJQueryDataTable

	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function printYUIDataTable($array) {
		print $this->getYUIDataTable($array);
	} // END printYUIDataTable
	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	function initializeStatsVar() {

		$stats = array(
			'items_total' => 0,
			'items_active_total' => 0,
			'items_inactive_total' => 0,
			'clients_total' => 0,
			'clients_active_total' => 0,
			'clients_inactive_total' => 0,
			'products_total' => 0,
			
			);

		$query = "SELECT isActive AS is_active
					, count(*) AS my_count from media group by isActive";
		$result = $this->query($query);


		foreach($result as $row)
		{
			$stats['items_total'] += $row['my_count'];
			switch ($row['is_active']) {
				case 0:
					$stats['items_inactive_total'] = $row['my_count'];
					break;
				case 1:
					$stats['items_active_total'] = $row['my_count'];
					break;
				
				
			}
		}
		
		$query = "SELECT isActive AS is_active, count(*) as my_count from clients group by isActive";
		$result = $this->query($query);


		foreach($result as $row)
		{
			$stats['clients_total'] += $row['my_count'];
			switch ($row['is_active']) {
				case 0:
					$stats['clients_inactive_total'] = $row['my_count'];
					break;
				case 1:
					$stats['clients_active_total'] = $row['my_count'];
					break;
			}
		}			

		$query = "SELECT count(*) as my_count from products";
		$result = $this->query($query);			
		$stats['products_total'] = $result[0]['my_count'];
		
		return $stats;
		
		
	} // END initializeStatsVar

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function radioButtons($fieldname,$fieldvalue,$sql_or_array)
	{

		if(is_array($sql_or_array))
			$results = $sql_or_array;
		else
			$results = $this->query($sql_or_array);
		$radioButtons = "";
		foreach($results as $row)
		{
			$value = $row['value'];
			$display = $row['display'];
			$id = strtolower(str_replace(' ','-',$row['display'])) . '-' . $row['value'];
			$selected = ($fieldvalue==$value)?"checked=\"checked\"":'';
			$radioButtons .= "<label for=\"{$id}\" title=\"{$display}\"><input value=\"$value\" type=\"radio\" id=\"{$id}\" name=\"{$fieldname}\" $selected /><span>{$display}</span></label><br />";
		}
		
		return "<div class=\"checkboxDiv\">{$radioButtons}</div>";
		
	} // END select


	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function select($fieldname,$fieldvalue,$sql_or_array,$first_value_and_display=FALSE)
	{
		if(is_array($sql_or_array))
			$results = $sql_or_array;
		else
			$results = $this->query($sql_or_array);
		$options = "";
		if($first_value_and_display!=FALSE) {
			$options .= "<option value=\"{$first_value_and_display['value']}\">{$first_value_and_display['display']}</option>";
		}
		foreach($results as $row)
		{
			$value = $row['value'];
			$display = $row['display'];
			$selected = ($fieldvalue==$value)?"selected=\"selected\"":'';
			$options .= "<option value=\"$value\" $selected>{$display}</option>";
		}
		
		return "<select name=\"{$fieldname}\">" . $options . "</select>";
		
	} // END select
	
	
	function selectExpando($fieldname,$fieldvalue,$sql_or_array,$first_value_and_display=FALSE)
	{
		$out = '';
		if(is_array($sql_or_array))
			$results = $sql_or_array;
		else
			$results = $this->query($sql_or_array);
		
		// value might be blank, or an array, or comma delimited, or just number
		if(is_array($fieldvalue)) {
			foreach($fieldvalue as $value) {
				$out .= $this->selectExpando($fieldname,$value,$sql_or_array,$first_value_and_display);
			}
			return $out;
		} else if(strpos($fieldvalue,',')!==false) {
			$values = explode(',',$fieldvalue);
			$out .= $this->selectExpando($fieldname,$values,$sql_or_array,$first_value_and_display);
			return $out;
		}
		
		$options = "";
		if($first_value_and_display!=FALSE) {
			$options .= "<option value=\"{$first_value_and_display['value']}\">{$first_value_and_display['display']}</option>";
		}
		foreach($results as $row)
		{
			$value = $row['value'];
			$display = $row['display'];
			$selected = ($fieldvalue==$value)?"selected=\"selected\"":'';
			$options .= "<option value=\"$value\" $selected>{$display}</option>";
		}
		if(strpos($fieldname,'[]')===false) {
			$fieldname .= '[]';
		}
		
		
		$out .= "<select name=\"{$fieldname}\" class=\"selectExpando\">" . $options . "</select>";
		return $out;		
	}
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	function selectIsAdmin($fieldname,$fieldvalue){
		$arr = array(
			0 => array('value'=>'0','display'=>'Not Admin'),
			1 => array('value'=>'1','display'=>'Admin'),
		);
		return $this->select($fieldname,$fieldvalue,$arr);
	} // END selectIsAdmin

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	function selectIsSuperAdmin($fieldname,$fieldvalue){
		$arr = array(
			0 => array('value'=>'0','display'=>'Not SuperAdmin'),
			1 => array('value'=>'1','display'=>'SuperAdmin'),
		);
		return $this->select($fieldname,$fieldvalue,$arr);
	} // END selectIsSuperAdmin

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	function selectBannerType($fieldname,$fieldvalue){
		$arr = array(
			0 => array('value'=>'swf','display'=>'Flash'),
			// 1 => array('value'=>'1','display'=>'Active'),
		);
		return $this->select($fieldname,$fieldvalue,$arr);
	} // END selectIsActive
	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	function selectIsActive($fieldname,$fieldvalue){
		$arr = array(
			0 => array('value'=>'0','display'=>'Not Active'),
			1 => array('value'=>'1','display'=>'Active'),
		);
		return $this->select($fieldname,$fieldvalue,$arr);
	} // END selectIsActive
	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function selectProducts($fieldname,$fieldvalue){
		return $this->select($fieldname,$fieldvalue,"SELECT id as value,name as display from products order by sort_name asc",array('value'=>'','display'=>'Choose an existing project...'));
	} // END selectProducts

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function selectProductsWithGames($fieldname,$fieldvalue){
		return $this->select($fieldname,$fieldvalue,"SELECT distinct media.id as value,
			concat(products.name, ': ',media.title) as display from
			products
			, media
			, menuItems
			where 1=1
			AND products.id = media.product_id
			AND media.menuItem_id = menuItems.id
			AND menuItems.hasGameFiles = 1
			AND media.isActive = 1
			order by sort_name asc",array('value'=>'','display'=>'Choose an existing project...'));
	} // END selectProducts

	function generateMenuItemsJson () {
		$query = "SELECT
			CONCAT('m' , menuItems.id) as mi_id
			, CONCAT(menus.header,' / ',menuItems.label) as display
		FROM menuItems, menus
		WHERE menuItems.menu_id = menus.id
		ORDER BY
			menus.displayOrder, menuItems.displayOrder";
		$results = $this->query($query);
		$object = array();
		foreach ($results as $row) {
			$object[$row['mi_id']] = $row['display'];
			// print_r($row);
		}
		return json_encode($object);
	}
	function generateCompanyJson() {
		$query = "SELECT
			CONCAT('c' , clients.id) as c_id
			, clients.name as display
		FROM clients
		ORDER BY
			clients.id ASC";
		$results = $this->query($query);
		$object = array();
		foreach ($results as $row) {
			$object[$row['c_id']] = $row['display'];
			// print_r($row);
		}
		return json_encode($object);
	}


	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function selectMenuItems($fieldname,$fieldvalue){
		return $this->select($fieldname,$fieldvalue,"SELECT
		
		menuItems.id as value
		, concat(menus.header,'/',menuItems.label) as display
		FROM menuItems, menus
		WHERE menuItems.menu_id = menus.id
		ORDER BY
			menus.displayOrder, menuItems.displayOrder");
		
		
		
	} // END selectMenuItems

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function radioButtonMenuItems($fieldname,$fieldvalue){
		return $this->radioButtons($fieldname,$fieldvalue,"SELECT id as value,label as display from menuItems");
	} // END selectMenuItems

	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function selectMenuItemsWithAll($fieldname,$fieldvalue){
		return $this->select($fieldname,$fieldvalue,"SELECT id as value,label as display from menuItems",array('display'=>'ALL','value'=>''));
	} // END selectMenuItemsWithAll


	function getPermissionsArray($user_id) {
		
		$permissionsQuery = "SELECT
								GROUP_CONCAT( permissions.name SEPARATOR ',') as perms
							FROM
								users_permissions
							LEFT JOIN
								permissions
							ON users_permissions.permission_id = permissions.id
							WHERE
								users_permissions.user_id = {$user_id}
							GROUP BY
								users_permissions.user_id ";
		$permissionsResults = $this->query($permissionsQuery);
		if (count($permissionsResults) > 0) {
			$permissionsArray = explode(',',$permissionsResults[0]['perms']);
		} else {
			$permissionsArray = array();
		}
		return $permissionsArray;
	}


	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function checkBoxes($fieldname,$fieldvalue=array(),$sql_or_array) {
		
		$collapsed_values = array();
		foreach($fieldvalue as $item) {
			foreach ($item as $foo => $bar) {
				$collapsed_values[] = $bar;
			}
		}

		if(is_array($sql_or_array))
			$results = $sql_or_array;
		else
			$results = $this->query($sql_or_array);
		$checkBoxes = "";
		foreach($results as $row)
		{
			$value = $row['value'];
			$display = $row['display'];
			$id = strtolower(str_replace(' ','-',$row['display'])) . '-' . $row['value'];
			if( in_array($value,$collapsed_values) ) {
				$selected = 'checked';
			} else {
				$selected = '';
			}
			$checkBoxes .= "<label for=\"{$id}\" title=\"{$display}\"><input value=\"$value\" type=\"checkbox\" id=\"{$id}\" name=\"{$fieldname}\" $selected /><span>{$display}</span></label><br />";
		}
		
		return "<div class=\"checkboxDiv\" id=\"id{$fieldname}Div\">{$checkBoxes}</div>";
	} // END checkBoxes
	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	function selectGenreCheckboxes($fieldname,$fieldvalue=array()){
		return $this->checkBoxes($fieldname,$fieldvalue,"SELECT id as value,name as display from genres order by name");
	} // END selectGenreCheckboxes
	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function selectClientCheckboxes($fieldname,$fieldvalue=array()){
		return $this->checkBoxes($fieldname,$fieldvalue,"SELECT id as value,name as display from clients order by name");
	} // END selectClientCheckboxes
	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function selectMonth($fieldname,$fieldvalue){
		$months = array(
			0 => array('value'=>'1','display'=>'Jan'),
			1 => array('value'=>'2','display'=>'Feb'),
			2 => array('value'=>'3','display'=>'Mar'),
			3 => array('value'=>'4','display'=>'Apr'),
			4 => array('value'=>'5','display'=>'May'),
			5 => array('value'=>'6','display'=>'Jun'),
			6 => array('value'=>'7','display'=>'Jul'),
			7 => array('value'=>'8','display'=>'Aug'),
			8 => array('value'=>'9','display'=>'Sep'),
			9 => array('value'=>'10','display'=>'Oct'),
			10 => array('value'=>'11','display'=>'Nov'),
			11 => array('value'=>'12','display'=>'Dec'),
		);
		return $this->select($fieldname
				,$fieldvalue
				,$months,
				array('display'=>'Month','value'=>'')
				);
	} // END selectMonth
	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function selectYear($fieldname,$fieldvalue) {
		return $this->select($fieldname,$fieldvalue,"SELECT DISTINCT
				year( launch_date ) as value
				, year( launch_date ) as display
		FROM media
		ORDER BY year( launch_date ) ASC",array('display'=>'Year','value'=>''));
	} // END selectYear
	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function selectClient($fieldname,$fieldvalue) {
		return $this->select($fieldname,$fieldvalue,"SELECT id as value,name as display from clients order by name",array('display'=>'ALL','value'=>''));
	} // END selectClient

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function selectMenuId($fieldname,$fieldvalue) {
		return $this->select($fieldname,$fieldvalue,"SELECT id as value,header as display from menus order by displayOrder",array('display'=>'ALL','value'=>''));
	} // END selectClient

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function selectMediaId($fieldname,$fieldvalue) {
		return $this->select($fieldname,$fieldvalue,"SELECT
				media.id AS value
				,concat(name,' / ',title) as display
 				FROM
					media
					, products
				where
					products.id = media.product_id
				
				",array('display'=>'ALL','value'=>''));
	} // END selectClient

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function selectBannerMediaId($fieldname,$fieldvalue) {
		return $this->select($fieldname,$fieldvalue,"SELECT
				media.id AS value
				,concat(name,' / ',title) as display
 				FROM
					media
					, products
				where
					products.id = media.product_id
				AND
					media.menuItem_id = 9
					order by products.sort_name
				
				",array('display'=>'--','value'=>''));
	} // END selectClient


	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function selectClientExpando($fieldname,$fieldvalue) {
		return $this->selectExpando($fieldname,$fieldvalue,"SELECT id as value,name as display from clients order by name",array('display'=>'','value'=>''),TRUE);
	} // END selectClient


	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function selectPermissionExpando($fieldname,$fieldvalue) {
		// return $this->selectExpando($fieldname,$fieldvalue,"SELECT id as value,name as display from permissions order by name",array('display'=>'','value'=>''),TRUE);
		return $this->checkBoxes($fieldname,$fieldvalue,"SELECT id as value,name as display from permissions order by name",array('display'=>'','value'=>''),TRUE);

	} // END selectClient

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function getDirectoryFiles($directory) {
		$directory_array = array();

		if ($handle = opendir($directory)) {
		    while (false !== ($file = readdir($handle))) {
				if(in_array($file,self::$bad_filenames)) {
				} else if(is_file($directory . '/' . $file)) {
					$directory_array[] = array(
							'value' => $file,
							'display' => $file,
						);
				} else if(is_dir($directory . '/' . $file)) {
					$directory_array = array_merge($directory_array, $this->getDirectoryFiles($directory . '/' . $file));
				}
		    }
		    closedir($handle);
		}
		return $directory_array;

	} // END selectClient
	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function selectGenre($fieldname,$fieldvalue) {
		return $this->select($fieldname,$fieldvalue,"SELECT id as value,name as display from genres order by name",array('display'=>'','value'=>''));
	} // END selectGenre

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function selectBannerFilename($fieldname,$fieldvalue) {
		$banner_files_array = $this->getDirectoryFiles('../banners');
		return $this->select($fieldname,$fieldvalue,$banner_files_array,array('display'=> '--','value' => ''));
	} // END selectBannerFilename

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function selectGroup($fieldname,$fieldvalue) {
		return $this->select($fieldname,$fieldvalue,"SELECT id as value,name as display from groups order by name",array('display'=>'','value'=>''));
	} // END selectGenre

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function selectGenreExpando($fieldname,$fieldvalue) {
		return $this->selectExpando($fieldname,$fieldvalue,"SELECT id as value,name as display from genres order by name",array('display'=>'','value'=>''),TRUE);
	} // END selectGenre
	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function hidden($fieldname,$fieldvalue) {
		return $this->input($fieldname,$fieldvalue,'hidden');
	} // END hidden
	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	function input($fieldname,$fieldvalue,$type="") {

		$extra = "";

		switch($fieldname) {
			case 'media_id':
				// return $this->selectMediaId($fieldname,$fieldvalue);
				return $this->selectBannerMediaId($fieldname,$fieldvalue);
				break;
			case 'product_id':
				return $this->selectProducts($fieldname,$fieldvalue);
				break;
			case 'menuItem_id':
				return $this->selectMenuItems($fieldname,$fieldvalue);
				break;
			case 'group_id':
				return $this->selectGroup($fieldname,$fieldvalue);
				break;
			case 'genre_id':	
				return $this->selectGenre($fieldname,$fieldvalue);
				break;
			case 'client_id':
				return $this->selectMenuItems($fieldname,$fieldvalue);
				break;
			case 'genre_id[]':
				return $this->selectGenreExpando($fieldname,$fieldvalue);
				break;
			case 'menu_id':
				return $this->selectMenuId($fieldname,$fieldvalue);
				break;
			case 'permission_id[]':
				return $this->selectPermissionExpando($fieldname,$fieldvalue);
				break;
			case 'client_id[]':
				return $this->selectClientExpando($fieldname,$fieldvalue);
				break;
			case 'isActive':
			case 'is_active':
				return $this->selectIsActive($fieldname,$fieldvalue);
				break;
			case 'banner_filename':
				return $this->selectBannerFilename($fieldname,$fieldvalue);
				break;
			case 'banner_type':
				return $this->selectBannerType($fieldname,$fieldvalue);
				break;
			case 'is_admin':
			case 'isAdmin':
				return $this->selectIsAdmin($fieldname,$fieldvalue);
				break;
			case 'is_superadmin':
			case 'isSuperAdmin':
				return $this->selectIsSuperAdmin($fieldname,$fieldvalue);
				break;
			case 'id':
				$extra = " readonly=\"readonly\"";
				break;
			default:
				// no switching;
		}
		if($type=="") {
			$type="text";
			$extra .= " size=\"40\"";
		}
		
		
		return "<input type=\"{$type}\" value=\"".htmlentities($fieldvalue)."\" name=\"{$fieldname}\" $extra />";
	} // END input
	
	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function editor($item_type,$fields,$id,$options=array())
	{
		$flash = '';
		if(isset($_GET['msg'])) {
			switch($_GET['msg']) {
				case 'updated':
					$flash = "Your entry was updated.";
					break;
				case 'added':
					$flash = "Your entry was added.";
					break;
			}
		}
		
		$show_chrome = false;

		if(count($options) > 0) {
			if(array_key_exists('show_chrome',$options)) {
				$show_chrome = $options['show_chrome'];
			}
		}

		
		if(!isset($id)) {
			$id = -1;
			$title = "Add {$item_type}";
			$action = "add";
		}
		$show_image_under = false;
		switch ($item_type) {
			case 'products':
			case 'product':
				$sql = "select ".implode(',',$fields)." from products where id={$id}";
				$table = "products";
				break;
			case 'media':
				$goodFields = array();
				$complexFields = array();
				foreach($fields as $field) {
					if(strpos($field,'[]') === false) {
						// print $field;
						$goodFields[] = $field;
					}
				}
				$sql = "select ".implode(',',$goodFields)." from media where id={$id}";
				// select * from 
				$table = "media";
				$show_image_under = true;
				break;
			case 'menuItems':
				$sql = "select ".implode(',',$fields)." from menuItems where id={$id}";
				$table = "menuItems";
				break;			
			case 'users':
				$goodFields = array();
				$complexFields = array();
				foreach($fields as $field) {
					if(strpos($field,'[]') === false) {
						// print $field;
						$goodFields[] = $field;
					}
				}
				$sql = "select ".implode(',',$goodFields)." from users where id={$id}";
				$table = "users";
				break;
			case 'clients':
			case 'company':
			case 'client':
				$sql = "select ".implode(',',$fields)." from clients where id={$id}";
				$table = "clients";
				break;
			case 'genres':
				$sql = "select ".implode(',',$fields)." from genres where id={$id}";
				$table = "genres";
				break;
			case 'banners':
				$sql = "select ".implode(',',$fields)." from banners where id={$id}";
				$table = "banners";
				break;
			default:
				exit("Unknown edit type {$item_type}. Edit inc.functions.php to add.");
		}
		$results = $this->query($sql);
		if(count($results)>0) {
			$row = $results[0];
			$title = "Edit {$item_type}";
			$action = "update";
		}
		else {
			foreach($fields as $field)
				$row[$field] = '';
			$title="Add {$item_type}";
			$action = "add";
		}
		
		if($table == 'media') {
			$media_id = $id;
		}
		if($table == 'users') {
			$user_id = $id;
		}
		
		$html_fields = array();
		
		// print_r($fields);
		// exit();
		foreach($fields as $fieldname) {
			if($fieldname=='genre_id[]') {
				$subquery = "SELECT distinct genre_id from media_genres where media_id = $media_id order by genre_id asc";
				$subresults = $this->query($subquery);
				$html_fields[] = "<tr class=\"x-yui-dt-even \"><td>" . $fieldname ."</td><td>" . $this->selectGenreExpando($fieldname,$subresults) . " <a href='javascript://' class='add-genre-expando'>[+]</a></td></tr>";
			} else if($fieldname=='permission_id[]') {
				$subquery = "SELECT distinct permission_id from users_permissions where user_id = $user_id order by permission_id asc";
				$subresults = $this->query($subquery);
				$html_fields[] = "<tr class=\"x-yui-dt-even \"><td>" . $fieldname ."</td><td>" . $this->selectPermissionExpando($fieldname,$subresults) . "<a href='javascript://' class='add-permission-expando'>[+]</a></td></tr>";
			} else if($fieldname=='client_id[]') {
				$subquery = "SELECT distinct client_id from media_clients where media_id = $media_id order by client_id asc";
				$subresults = $this->query($subquery);
				$html_fields[] = "<tr class=\"x-yui-dt-even \"><td>" . $fieldname ."</td><td>" . $this->selectClientExpando($fieldname,$subresults) . "<a href='javascript://' class='add-client-expando'>[+]</a></td></tr>";
			} else {
				$html_fields[] = "<tr class=\"x-yui-dt-even \"><td>" . $fieldname ."</td><td>" . $this->input($fieldname,$row[$fieldname]) . "</td></tr>";
			}
		}
		
		// foreach($complexFields as $field) {
		// 	//
		// }
		
		$html_fields[] = "<tr><td></td><td><input type=\"submit\" value=\"{$action}\" class=\"ui-state-default ui-corner-all ui-icon-plusthick blt-button blt-button-icon-left\" /></td></tr>";
		
		$out = "";
		if($show_chrome) {
			$th = "<thead class=\"x-yui-dt\">\n<tr><th colspan=\"2\">" . $title . "</th></tr>\n</thead>";
		} else {
			$th = "";
		}
		$out .= "<div class=\"x-yui-dt\"><form method=\"post\" action=\"action-process.php\">";
		$out .= $this->hidden('id',$id,'hidden');
		$out .= $this->hidden('action',$action,'hidden');
		$out .= $this->hidden('table',$table,'hidden');
		$out .= $this->hidden('fields',implode(',',$fields),'hidden');
		
		$out .= "<table cellspacing=\"0\" cellpadding=\"3\" border=\"0\">\n<tbody class=\"x-yui-dt\">" . $th . implode("\n",$html_fields) . "\n</tbody>\n</table>";
		$out .= "</form></div>";
		if($show_image_under) {
			$out .= "<img src=/media/{$id}.jpg>";
		}

		$out = $this->makePage($out,$title,$flash);

		print $out;
		return $out;
	} // END editor


	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function makePage($html,$title,$flash='') {
		$safe_name = 'page-' . strtolower(str_replace(' ','-',$title));
		$out = "<html>\n<head>\n"
			."<title>{$title}</title>";
$out .= <<< ALL_SCRIPTS

<!--
	<script src="/admin/jquery/jquery-1.3.js" type="text/javascript"></script>
	<script src='/admin/jquery/jquery.bgiframe.min.js' type='text/javascript'></script>
	<script src='/admin/jquery/jquery.ajaxQueue.js' type='text/javascript'></script>
	<script src="/admin/jquery/jquery.autocomplete.min.js" type="text/javascript"></script>
	<script src="/admin/jquery/jquery.imgareaselect-0.6.2.min.js" type="text/javascript"></script>
	<script src="/admin/jquery/jquery.ocupload-1.1.2.packed.js" type="text/javascript"></script>
	<script src="/admin/jquery/jquery.datePicker.js" type="text/javascript"></script>
	<script src="/admin/jquery/extended.date.js" type="text/javascript"></script>
	<script src="/admin/jquery/jquery-ui-themeroller/jquery.ui.all.js" type="text/javascript" charset="utf-8"></script>
	<script src="/admin/javascript/ARTLUNGEDITOR.editor.js" type="text/javascript"></script>
-->
<script type="text/javascript" src="/min/b=admin&amp;f=jquery/jquery-1.3.js,jquery/jquery.bgiframe.min.js,jquery/jquery.ajaxQueue.js,jquery/jquery.autocomplete.min.js,jquery/jquery.imgareaselect-0.6.2.min.js,jquery/jquery.ocupload-1.1.2.packed.js,jquery/jquery.datePicker.js,jquery/extended.date.js,jquery/jquery-ui-themeroller/jquery.ui.all.js,javascript/ARTLUNGEDITOR.editor.js"></script>

	<link href="/admin/jquery/jquery.datePicker.css" type="text/css" rel="stylesheet">
	<link href="/admin/jquery/jquery.datePicker.custom.css" type="text/css" rel="stylesheet">
	<link href="/admin/jquery/jquery.autocomplete.css" type="text/css" rel="stylesheet">
	<link href="/admin/javascript/jquery-ui-themeroller/ui.all.css" type="text/css" rel="stylesheet" />
	<link href="/admin/javascript/dataTables/media/css/demos.css" type="text/css" rel="stylesheet" />
	<link href="/admin/edit-item.css" type="text/css" rel="stylesheet">

ALL_SCRIPTS
;
$out .= "</head>"
			
			."<body class=\"x-yui-skin-sam x-yui-content\" id=\"$safe_name\">" 
			. $html
			
			. (($flash!='')?"<div class='flashMessage'>{$flash}</div>":'')
			. "</body></html>";
		return $out;
		
	} // END makePage


	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function errorPage($title,$html) {
		$safe_name = 'page-' . strtolower(str_replace(' ','-',$title));
		$out = '';
		$out .= "<html>\n";
		$out .= "<head>";
		$out .= "<title>";
		$out .= htmlentities($title);
		$out .= "</title>\n";
		$out .= '<script type="text/javascript" src="/admin/jquery/jquery-1.3.js"></script>';
		// $out .= '<script type="text/javascript" src="/admin/jquery/jquery.imgareaselect-0.6.2.min.js"></script>';
		// $out .= '<script type="text/javascript" src="/admin/jquery/jquery.ocupload-1.1.2.packed.js"></script>';
		// $out .= '<script type="text/javascript" src="/admin/jquery/jquery.datePicker.js"></script>';
		// $out .= '<script type="text/javascript" src="/admin/jquery/extended.date.js"></script>';
		// $out .= '<script type="text/javascript" src="/admin/javascript/ARTLUNGEDITOR.editor.js"></script>';
		// $out .= '<link rel="stylesheet" type="text/css" href="/admin/jquery/jquery.datePicker.css" />';
		// $out .= '<link rel="stylesheet" type="text/css" href="/admin/jquery/jquery.datePicker.custom.css" />';
		// $out .= '<link rel="stylesheet" type="text/css" href="/admin/edit-item.css" type="text/css" />';
		$out .= "</head>";
		$out .= "<body id=\"$safe_name\">" ;
		$out .= "<div class=\"error\">";
		$out .= "<h1>{$title}</h1>";
		$out .= $html;
		$out .= "</div>";
		$out .= "</body></html>";
		// return $out;
		print $out;
		
	} // END errorPage

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	function error404($errorPageText='') {
		header("HTTP/1.0 404 Not Found");
		exit($errorPageText);
	} // END error404

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	function redirect($url) {
		// print 'redirect';
		// print $url;
		header("Location: {$url}");
	} // END redirect

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	function makeSlugPage($title,$html) {
		$out = "";
		$out .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
		$out .= "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n";
		$out .= "<head>\n";
		$out .= "	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
		$out .= "	<title>{$title}</title>\n";
		$out .= "</head>\n";
		$out .= "<body>\n";
		$out .= "\n";
		$out .= "{$html}\n";
		$out .= "\n";
		$out .= "</body>\n";
		$out .= "</html>\n";
		return $out;
	} // END redirect

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/	
	function log($user_id,$comment,$table_modified='',$key_id=0) {
		$query = "INSERT into log set
			user_id = {$user_id} 
			, comment = '{$comment}' ";
			
		if($table_modified!='') {
			$query .= ", table_modified = '{$table_modified}'";
		}
		if($key_id!=0) {
			$query .= ", key_id = $key_id ";
		}
		$this->execute($query);
	} // END redirect

	// $HELPER->log($user_id,$table_modified,$key_id,$comment);


	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function getSortableMenuItemsArray() {
		$query = "SELECT
		 			menuItems.id as value
					, concat(menus.header,' / ',menuItems.label) as display
				FROM
					menus
					, menuItems
				WHERE 1
				AND menuItems.menu_id = menus.id
				AND number_across <> 0
				AND number_down <> 0
				AND menuItems.isActive = 1
				ORDER BY 
					menus.displayOrder
					, menuItems.displayOrder
				";
		$results = $this->query($query);
		return $results;
	} // END getSortableMenuItemsArray

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function isMenuItemSortable($menuItem_id) {
		$query = "SELECT
					id
				FROM
					menuItems
				WHERE 1
				AND id = $menuItem_id
				AND number_across <> 0
				AND number_down <> 0
				";
		// print $query;
		$results = $this->query($query);
		return (count($results)>0)?true:false;

	} // END isMenuItemSortable





	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function getSortableMenuItemsSelect($menuItem_id) {
		$results = $this->getSortableMenuItemsArray();
		return $this->select('SortableMenuItemsSelect',$menuItem_id, $results,array('display'=>'Choose One','value'=>''));
	} // END getSortableMenuItemsSelect


	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function getRowsAcross($menuItem_id) {
		$query = "SELECT number_across from menuItems where id = $menuItem_id";
		$results = $this->query($query);
		return $results[0]['number_across'];
		
	} // END getRowsAcross

	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function getRowsDown($menuItem_id) {
		$query = "SELECT number_down from menuItems where id = $menuItem_id";
		$results = $this->query($query);
		return $results[0]['number_down'];
		
	} // END getRowsAcross




	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/
	function getMenuTitle($menuItem_id) {
		$query = "SELECT 
					concat(menus.header,' / ',menuItems.label) as menuTitle
				FROM
					menus
					, menuItems
				WHERE
					menuItems.menu_id = menus.id
				AND menuItems.id = {$menuItem_id}";
				// print $query;
		$results = $this->query($query);
		if(count($results)>0)
			return $results[0]['menuTitle'];
		else
			return '';
		
	} // END getMenuTitle	

	/***************************************************************
	**                                                            **
	**                                                            **
	** STATIC FUNCTIONS                                           **
	**                                                            **
	**                                                            **
	***************************************************************/


	/***************************************************************
	**                                                            **
	** name ........                                              **
	** parameters ..                                              **
	** returns .....                                              **
	**                                                            **
	***************************************************************/

	public static function getImageSize($id) {
		$img_src = "{$_SERVER['DOCUMENT_ROOT']}/media/{$id}.jpg";
		// print $img_src;
		$returnValue = false;
		if(is_file($img_src)) {
			// print "image exists";
			if($imgdata = getimagesize($img_src)) {
				$returnValue = array(
					'width' => $imgdata[0],
					'height' => $imgdata[1],
				);
			}
		} else {
			// print "image does not exist";
		}
		return $returnValue;
		// /* width  */ 0
		// /* height */ and 1
		// // return print_r($foo,FALSE);
	} // END getImageSummary



	
} // END Helper class

/******************************************************
**                                                    *
**                                                    *
**                                                    *
******************************************************/


class Page {


	/******************************************************
	**                                                    *
	**                                                    *
	**                                                    *
	******************************************************/
	public $url;
	public $URLS;
	public $sidebar;
	public $THUMBLINKS;
	public $versionFolder;
	public $DEFAULT_CONTENT_DIRECTORY;


	function parseArchiveUrl($url) {
		$parts = explode('/',$this->url);
	}

	/******************************************************
	**                                                    *
	**                                                    *
	**                                                    *
	******************************************************/
	function __construct() {
		global $CONFIG;
		$this->url = $_GET['url'];

		// this is insurance in case someone misconfigures the
		// $CONFIG['RELEASE_DATE_TO_CONTENT_DIRECTORY']
		// variable
		$this->DEFAULT_CONTENT_DIRECTORY =  $CONFIG['DEFAULT_CONTENT_DIRECTORY'];

		$currentDate = (int)date('Ymd');
		// iterate through the valid releases and set the highest valid release by date
		foreach($CONFIG['RELEASE_DATE_TO_CONTENT_DIRECTORY'] as $releaseDate => $contentFolder) {
			if( $currentDate >= ((int)$releaseDate) ) {
				$this->DEFAULT_CONTENT_DIRECTORY = $contentFolder;
			}
		}
		// if the word "archive" is in the url, then we go looking to see if 

		if (stristr($this->url,'archive')) {
			$this->urlparts = explode('/',$this->url);
			
			for($i=0;$i<count($this->urlparts);$i++) {
				//print $i;
				if ($this->urlparts[$i] == 'archive') {
					$versionIndex = $i+1;
					//print $versionIndex;
					if (is_numeric($this->urlparts[$versionIndex])) {
						$this->versionFolder = sprintf("content_v%03s",$this->urlparts[$versionIndex]);
					}
				}
			}
		}
		if (!is_dir($this->versionFolder)) {
			$this->versionFolder = $this->DEFAULT_CONTENT_DIRECTORY;
		}


		//print $this->versionFolder;
		if(is_file("{$this->versionFolder}/inc.config.php")) {
			include("{$this->versionFolder}/inc.config.php");
		}

		$this->URLS = $CONFIG['URLS'];
		$this->THUMBLINKS = $CONFIG['THUMBLINKS'];
		

		if($this->url=='/')
			$this->url = '/home';
		$this->sidebar = TRUE;

		//$this->query_string = $_GET['query_string'];
	} // END __construct


	/******************************************************
	**                                                    *
	**                                                    *
	**                                                    *
	******************************************************/
	function getPageSlug() {
		$url_parts = $this->url;
		$url_array = explode('/', $this->url);
		$returned_url = '';
		$slug = array_pop($url_array);
		if($slug=='') {
			$slug='home';
		}
		return $slug;
	} // END getPageSlug


	/******************************************************
	**                                                    *
	**                                                    *
	**                                                    *
	******************************************************/
	private function pageExists() {
		// print_r($this->URLS);
		return array_key_exists($this->getPageSlug(),$this->URLS);
		
	} // END pageExists


	/******************************************************
	**                                                    *
	**                                                    *
	**                                                    *
	******************************************************/
	private function generateWideThumbsHTML($prefix,$lightbox,$per_row,$sidebar=FALSE) {
		$files = $this->getAssetList($prefix);
		$this->sidebar = $sidebar;
		$content = '';
		$iterator = 0;
		$linkopen = "";
		$linkclose = "";
		
		foreach($files as $file) {
			$row_class = $iterator % $per_row;
			$safefilename = rawurlencode($file);
			if($lightbox == TRUE) {
				$linkopen = "<a href=\"/{$this->versionFolder}/assets/pop_{$safefilename}\" rel=\"lightbox-{$prefix}\" >";
				$linkclose = "</a><!--lightbox-->";
			} else if (array_key_exists($prefix,$this->THUMBLINKS)){
				$get_number = str_replace('.jpg','',str_replace("{$prefix}_",'',$file));
				// print $get_number;
				// print_r($this->THUMBLINKS[$prefix]);
				$link = '';
				if(array_key_exists($get_number,$this->THUMBLINKS[$prefix]))
				{
					$link = $this->THUMBLINKS[$prefix][$get_number]['url'];
					$linkopen = "<a href=\"{$link}\" class=\"gallery-link\" target=\"_blank\">";
					$linkclose = "</a><!--data from CONFIG['THUMBLINKS']['{$prefix}']-->";
					
					if($link=='') {
						$link ='';
						$linkopen = '';
						$linkclose = '';
					}
				}
			}
			$content .= "\n\t{$linkopen}<img src=\"/{$this->versionFolder}/assets/{$safefilename}\" class=\"asset-{$per_row} asset-{$per_row}-{$row_class}\" />{$linkclose} ";
			$iterator++;
		}
		
		return $content;
	} // END generateWideThumbsHTML


	/******************************************************
	**                                                    *
	**                                                    *
	**                                                    *
	******************************************************/
	private function generateWideHeader($text)
	{
		return "<div class=\"wide-header\">".htmlentities($text)."</div>";
	} // END generateWideHeader
	
		


	/******************************************************
	**                                                    *
	**                                                    *
	**                                                    *
	******************************************************/
	function getPageContent($slug) {
		$filename = "{$this->versionFolder}/content." . $slug . ".html";
		if(file_exists($filename))
		{
			$content = "\n\n\n\n\n\n" . file_get_contents($filename) . "\n\n\n\n\n\n";
			
			return "<div id=\"maincontent\">{$content}</div><!--#maincontent-->\n\n";

		}
		else  if (file_exists("content.{$slug}.html")) {
			$content = file_get_contents("content.{$slug}.html");
			return "<div id=\"maincontent\">{$content}</div><!--#maincontent-->\n\n";
		} else {
			$content = "";

			switch($slug) {
				case 'other-multiple':
					$prefix = 'web';
					$lightbox = FALSE;
					$per_row = 3;
					$content .= $this->generateWideHeader('Misc: Websites');
					$content .= $this->generateWideThumbsHTML($prefix,$lightbox,$per_row);
					$prefix = 'banners';
					$lightbox = FALSE;
					$per_row = 3;
					$content .= $this->generateWideHeader('Misc: Banners');
					$content .= $this->generateWideThumbsHTML($prefix,$lightbox,$per_row);
					$prefix = 'other_web';
					$lightbox = false;
					$per_row = 3;
					$here_content = $this->generateWideThumbsHTML($prefix,$lightbox,$per_row);
					if ($here_content != '') {
						$content .= $this->generateWideHeader('Misc: Other');
						$content .= $here_content;
					}
					$prefix = 'games';
					$lightbox = false;
					$per_row = 3;
					$here_content = $this->generateWideThumbsHTML($prefix,$lightbox,$per_row);
					if ($here_content != '') {
						$content .= $this->generateWideHeader('Misc: Games');
						$content .= $here_content;
					}

					$prefix = 'widget';
					$lightbox = false;
					$per_row = 3;
					$here_content = $this->generateWideThumbsHTML($prefix,$lightbox,$per_row);
					if ($here_content != '') {
						$content .= $this->generateWideHeader('Misc: Widgets');
						$content .= $here_content;
					}

					break;
				case 'something-else':
					$prefix = 'else';
					$lightbox = FALSE;
					$per_row = 3;
					$content .= $this->generateWideHeader('Something Else');
					$content .= $this->generateWideThumbsHTML($prefix,$lightbox,$per_row);
					break;
				default: 
					$this->sidebar = TRUE;
					$content = "<h1>$slug</h1><br /><br /><p>file <b>{$filename}</b> does not exist.<br /><br />Is there content?</p>";
					return "<div id=\"maincontent\">{$content}</div>";
				
			}

			return "<div id=\"widemaincontent\">{$content}</div>";


		}
		
	} // END getPageContent




	/******************************************************
	**                                                    *
	**                                                    *
	**                                                    *
	******************************************************/
	function getPageName($slug) {
		if(isset($this->URLS[$slug]))
			return $this->URLS[$slug];
		else 
			return strtoupper($slug);
	} // END getPageName


	/******************************************************
	**                                                    *
	**                                                    *
	**                                                    *
	******************************************************/
	function globalNav() {

		include("{$this->versionFolder}/inc.globalnav.php");
		
	} // END globalNav



	/******************************************************
	**                                                    *
	**                                                    *
	**                                                    *
	******************************************************/
	function printNav() {
		print "<ul>\n";
		foreach($this->URLS as $slug => $title) {
			
			print "\t<li><a href=\"{$slug}\">" . htmlentities($title) ."</a></li>\n";
			
		}
		print "</ul>\n";
	} // END printNav



	/******************************************************
	**                                                    *
	**                                                    *
	**                                                    *
	******************************************************/
	function header() {

		

		?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head profile="http://gmpg.org/xfn/11">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<!-- <link rel="shortcut icon" href="/favicon.ico" />
	<link rel="apple-touch-icon" href="/apple-touch-icon.png" /> -->
	<title>Page &raquo; <?php print $this->getPageName($this->getPageSlug()); ?></title>
	<!-- <link rel="stylesheet" href="/css/main.css" type="text/css" /> -->
	<!-- <link rel="stylesheet" href="/javascript/jquery-lightbox-0.5/css/jquery.lightbox-0.5.css" type="text/css" /> -->
	<!-- <?=$this->additionalCss()?> -->
	<!--[if lte IE 7]>
	<link type="text/css" rel="stylesheet" href="/css/ie.css" />
	<![endif]-->
	<!-- <script type="text/javascript" src="/min/?b=javascript&amp;f=jquery-1.2.6.min.js,jquery-lightbox-0.5/js/jquery.lightbox-0.5.js,jquery.embedquicktime.js"></script> -->
	<!-- <script src="/javascript/main.js" type="text/javascript"></script> -->
</head>
<body id="page-<?php print $this->getPageSlug() ?>">
<div id="wrapper">
	<div id="header">
		<h1>Page</h1>
	</div><!--#header-->
<?php
		
$this->globalNav();
		
?>
	<div id="content">

		<?php
		
		
	} // END header


	/******************************************************
	**                                                    *
	**                                                    *
	**                                                    *
	******************************************************/
	function sidebar() {

		include("{$this->versionFolder}/inc.sidebar.php");
		
	} // end sidebar
	
	/******************************************************
	**                                                    *
	**                                                    *
	**                                                    *
	******************************************************/	
	function additionalCss() {
		if(file_exists("{$this->versionFolder}/additional.css")) {
			return "<link rel=\"stylesheet\" href=\"/{$this->versionFolder}/additional.css\" type=\"text/css\" />\n";
		} else {
			return "";
		}
	}

	/******************************************************
	**                                                    *
	**                                                    *
	**                                                    *
	******************************************************/
	function getGalleryNav() {
		
		include("{$this->versionFolder}/inc.highlights-nav.php");
		
	} // END getGalleryNav


	/******************************************************
	**                                                    *
	**                                                    *
	**                                                    *
	******************************************************/
	function footer() {
		print ($this->sidebar)?"\n</div><!--#content-->":"\n</div><!--#widemaincontent-->";

?>

<div id="footer"></div><!--#footer-->
</div><!--#wrapper-->
</body>
</html><?php
	} // END footer

	/******************************************************
	**                                                    *
	**                                                    *
	**                                                    *
	******************************************************/
	function printPage()
	{
		
		$slug = $this->getPageSlug();

		$this->header();

		if($this->pageExists($slug))
		{
			print $this->getPageContent($slug);
			if($this->sidebar === true) {
				print $this->sidebar();
			}
		} else {
			print '<div id=\"maincontent\">';
			print "<h1>ERROR</h1><br /><div class=\"major-section\"><p>";
			print "I don't have a handler for \"";
			print htmlentities($slug);
			print ".\" Maybe you should go <a href=\"/home\">home</a></p>";
			print '</div><!--.major-section--></div><!--#maincontent-->';

		}

		$this->footer();

		
	} // END printPage


	/******************************************************
	**                                                    *
	**                                                    *
	**                                                    *
	******************************************************/
	function getAssetList($prefix)
	{
		$arr = array();
		$directory = "{$this->versionFolder}/assets/";
		if ($handle = opendir($directory)) {
		    /* This is the correct way to loop over the directory. */
		    while (false !== ($file = readdir($handle))) {
			//	if(stristr($prefix,$file))
 				if(preg_match  ( "/^$prefix/", $file ))
					array_push($arr,$file);
		    }
		    closedir($handle);
		}
		sort($arr);
		return $arr;
	} // END getAssetList


	
	
}




?>