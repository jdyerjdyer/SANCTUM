<?PHP
	//Returns the private key for accessing a resource.
	function privateKey() {
		global $filename;

		if ($filename == "codex.php") {//WHY IS THIS DIFFERENT FOR CODEX???
			return "123";
		} else {
			return "INSERT PRIVATE KEY";
		}
	}

	//Returns an error message to be logged or displayed to the user based on $errorName.
	function throwError($errorName){
			switch ($errorName) {
		case "invalidPrivateKey":
			return "Sorry, this didnt work. Admins used an invalid key. Bug them about it!";
			break;
		case 1:
			echo "i equals 1";//WHAT IS THIS ERROR???
			break;
		case 2:
			echo "i equals 2";//WHAT IS THIS ERROR???
			break;
			}
	}

	/* Establishes a connection to the database and if an error occurs, then exits gracefully (or at least it will!) */
	function mysqlConnect() {
			$dbhost = 'INSERT DB HOST';
			$dbusername = 'INSERT DB USERNAME';
			$dbpassword = 'INSERT DB PASSWORD';
			$dbtable = 'INSERT DB TABLE';

			/* NEED TO DO BETTER ERROR HANDLING HERE FOR V2 */
			$con = mysqli_connect($dbhost,$dbusername,$dbpassword,$dbtable);
			if (!$con) { die('Could not connect: ' . mysqli_error());}
			mysqli_select_db($con, "$dbtable")or die("cannot select DB");
			return $con;
	}

	/* NOT EVALUATED YET */
	function display_codex() {
?>
<HTML>
	<HEAD>
		<TITLE>THE CODEX</TITLE>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">

<script
  src="https://code.jquery.com/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
</HEAD>

<BODY>

	<table id="table_id" class="display">
	    <thead>
	        <tr>
	            <th>ID</th>
	            <th>Level</th>
	            <th>Crystals</th>
	            <th>SPD</th>
	            <th>STR</th>
	            <th>STAM</th>
	            <th>HP</th>
	        </tr>
	    </thead>
	    <tbody>

<?php
$q = "SELECT discordUserID,wallet,speed,health,maxHealth,strength,maxStamina,stamina,lvl FROM users LIMIT 500;";
$r2 = mysqli_query($con,$q);
if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
	while ( $a = mysqli_fetch_assoc($r2) ) {
			$discordUserID=stripslashes($a['discordUserID']);
			$wallet=stripslashes($a['wallet']);
			$speed=stripslashes($a['speed']);
			$health=stripslashes($a['health']);
			$maxHealth=stripslashes($a['maxHealth']);
			$strength=stripslashes($a['strength']);
			$maxStamina=stripslashes($a['maxStamina']);
			$stamina=stripslashes($a['stamina']);
			$lvl=stripslashes($a['lvl']);
			?>

			        <tr>
			            <td><?php echo $discordUserID; ?></td>
			            <td><?php echo $lvl; ?></td>
			            <td><?php echo $wallet; ?></td>
			            <td><?php echo $speed; ?></td>
			            <td><?php echo $strength; ?></td>
			            <td><?php echo $stamina."/".$maxStamina; ?></td>
			            <td><?php echo $health."/".$maxHealth; ?></td>
			        </tr>
			<?php
	}
}
 ?>
</tbody>
</table>
<script>
$(document).ready( function () {
    $('#table_id').DataTable();
} );
</script>
</BODY>
</HTML>
<?PHP
	}

	//Returns the value available in the $method array or $defaultValue if not present.
	function defaultVar($method, $varName, $defaultValue) {
		if ($method == "GET") {
			if ( isset($_GET[$varName]) ) {
				return $_GET[$varName];
			}
		} else if ($method == "POST") {
			if ( isset($_POST[$varName]) ) {
				return $_POST[$varName];
			}
		} else if ($method == "COOKIE") {
			if ( isset($_COOKIE[$varName]) ) {
				return $_COOKIE[$varName];
			}
		}
		return $defaultValue;
	}



function constants($tag){

		//Base Domain Name
		if ($tag == 'baseurl'){ return "http://gdu.io/"; }

		//Base URL to the members section
		if ($tag == 'ubaseurl'){ return "http://gdu.io/"; }

		//Turns on PHP errors
		if ($tag == 'debugmode'){ return true; }

}



function getLevel($xp,$lvlbase){
	if($xp >= 1){
		$lvl = $lvlbase * sqrt($xp);;
	} else{
		$lvl = 0;
	}

	//Returns level based total # of xp
	return $lvl;
}
/*
function getLevel($xp,$lvlbase){
	if($xp >= 1){
		$lvl = log($xp,$lvlbase);
	} else{
		$lvl = 0;
	}

	//Returns level based total # of xp
	return $lvl;
}
*/


function getCurrentLevelProgress($xp,$lvl){

	$baseLevel = floor($lvl);
	$levelDecimal = $lvl - $baseLevel;

	$percentage = $levelDecimal*100;
	$fullNumber = 100;

	$actualPercentage = floor(($percentage / 100) * $fullNumber);

	//Returns progress in percentage
	return $actualPercentage;
}
/*


function getXp($lvl,$lvlbase){

	$xp = floor(pow($lvlbase,ceil($lvl)) - pow($lvlbase,floor($lvl)));

	//Returns xp needed from start to finish to complete current level
	return $xp;
}

function getAllXp($lvl,$lvlbase){


	$xp = pow($lvlbase,floor($lvl));
	//Returns # of xp required to reach current level
	return $xp;
}
function getCurrentLevelProgress($xp,$lvl,$lvlbase){

	$xpInCurrentLevel = getXp($lvl,$lvlbase);
	$totalXpRequiredForCurrentLevel = getAllXp($lvl,$lvlbase);
	$xpTowardNextLevel = $xp - $totalXpRequiredForCurrentLevel;

	if($xpInCurrentLevel > 0){
			$xpProgress = round(($xpTowardNextLevel / $xpInCurrentLevel)*100,1);
	}else{
			$xpProgress = 1;
	}

	//Returns progress in percentage
	return $xpProgress;
}
*/


function addXp($uid,$xpamount){
		$con = mysqlConnect();
		$q = "UPDATE users SET xp=(xp + $xpamount) WHERE discordUserID = '$uid'";
		$r = mysqli_query($con,$q);
}
function getLevelBase(){
		return 1.2;
}

function full_url()
{
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
    $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
    return $protocol . "://" . $_SERVER['SERVER_NAME'] . $port . $_SERVER['REQUEST_URI'];
}
// CLASS FOR CONVERTING TIME TO AGO
class convertToAgo {

    function convert_datetime($str) {

   		list($date, $time) = explode(' ', $str);
    	list($year, $month, $day) = explode('-', $date);
    	list($hour, $minute, $second) = explode(':', $time);
    	$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
    	return $timestamp;
    }

    function makeAgo($timestamp){

   		$difference = time() - $timestamp;
   		$periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
   		$lengths = array("60","60","24","7","4.35","12","10");
   		for($j = 0; $difference >= $lengths[$j]; $j++)
   			$difference /= $lengths[$j];
   			$difference = round($difference);
   		if($difference != 1) $periods[$j].= "s";
			if ( (($periods[$j] == "minute") or ($periods[$j] == "minutes") && ($difference < 3))
			or (($periods[$j] == "second") or ($periods[$j] == "seconds")) ){
   				$text = "Just now";
			} else {
   				$text = "$difference $periods[$j] ago";
			}
   			return $text;
    }

} // END CLASS


function checkArrayFor($array, $key, $val) {
    foreach ($array as $item)
        if (isset($item[$key]) && $item[$key] == $val)
            return true;
    return false;
}



function addhttp($url) {
	if ($url != ''){
		if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
			$url = "http://" . $url;
		}
	}
  return $url;
}


?>