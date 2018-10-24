<?PHP
	/* Returns a factions information as an associative array if found, otherwise false. */
	function getFactionByDiscordRoleID($roleID) {
		$q = "SELECT * FROM factions WHERE discordRoleID = '$roleID' LIMIT 1";
		if ($r2 = mysqli_query($con, $q)) {
			return $r2->fetch_assoc();//Found Faction so return it as an associative array.
		}
		return false;//Didn't find the faction with that discord role id.
	}

	/* Subtracts a given amount from a particular faction's account if there is enough in the account. Returns true on success, false otherwise. If false, adds error to $error object. */
	function subFactionAccount($roleID, $amount) {
		$amount = floor($amount);//Integer Only
		if ($amount > 0) {
			if ($faction = getFactionByDiscordRoleID($roleID)) {
				if ($faction["account"] >= $amount) {
					$q = "UPDATE factions SET account = account - $amount WHERE discordRoleID = '$roleID' LIMIT 1";
					$r2 = mysqli_query($con,$q);
					return true;
				} else {
					$error->addError("invalidAmount", "Faction $roleID only has " . $faction["account"] . " crystals so unable to remove $amount."
				}
			} else {
				$error->addError("invalidFaction", "Invalid faction $roleID passed into subFactionAccount.");
			}
		} else {
			$error->addError("invalidAmountNotAboveZero", "Amount passed to subFactionAccount must be greater than zero.");
		}
		return false;
	}

	/* Adds a given amount to a particular faction's account. Returns true on success, false otherwise. If false, adds error to $error object. */
	function addFactionAccount($roleID, $amount) {
		$amount = floor($amount);//Integer Only
		if ($amount > 0) {
			if ($user = getFactionByDiscordRoleID($roleID)) {
				$q = "UPDATE factions SET account = account + $amount WHERE discordRoleID = '$roleID' LIMIT 1";
				$r2 = mysqli_query($con,$q);
				return true;
			} else {
				$error->addError("invalidFaction", "Invalid faction $roleID passed into addFactionAccount.");
			}
		} else {
			$error->addError("invalidAmountNotAboveZero", "Amount passed to addFactionAccount must be greater than zero.");
		}
		return false;
	}
?>