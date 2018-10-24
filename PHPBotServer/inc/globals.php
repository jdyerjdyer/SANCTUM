<?PHP
	$programName = "Discord Bot Server DB API";
	$productVersion = "2.0";


	/* Determine which version of the API we are using. */
		$version = defaultVar("GET", "version", '1.0');

	/* Prepare and send headers. */
		//header('Access-Control-Allow-Origin: *');
		//header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');

	/* Determine what resource was requested. */
		if ($_SERVER["HTTPS"] && strtolower(trim($_SERVER["HTTPS"])) != "off") {
			$https = TRUE;
			$protostr = "https://";
		} else {
			$https = FALSE;
			$protostr = "http://";
		}

		$filename = basename($_SERVER["PHP_SELF"]);
		if ($version == "1.0") {//Version 1.0 filenames could have been case insensitive so we assume that possibility. Version 2.0 and higher assumes case sensitive rest endpoints.
			$filename = strtolower($filename);
		}


	/* Include helper functions. */
		include_once 'functions.php';

	/* Include modules. */
		include_once 'modules\users.php';
		include_once 'modules\factions.php';
		include_once 'modules\hostiles.php';

	/* Include Error Handler. */
	include_once "error.php";

	/* Check URL and POST data and set up defaults. */
		/* Send Data Parameters */
		$dataType = defaultVar("GET", "dataType", '');
		$dataToSend = defaultVar("GET", "dataToSend", '');
		$dataToSend2 = defaultVar("GET", "dataToSend2", '');

		/* Shared Get/Send Data Parameters */
		$userID = defaultVar("GET", "userid", '');

		/* Overload $dataType variable. */
		if ($filename == "getdata.php") {
			$dataType = defaultVar("GET", "dataToLoad", '');
		}

	/* Check for private key. */
		$privateKey = defaultVar("GET", "pk", '');
		if($privateKey != privateKey()){
				echo throwError("invalidPrivateKey");
				exit;
		}

	/* Define global variables. */
		$sqlterms = '';//Found in getData.php and codex.php only.

	/* Determine if we are in debug mode. */
		$debug = (defaultVar("GET","debug",'') == "on") ? true : false;

	/* Set up mysql database connection. */
		$con = mysqlConnect();
?>