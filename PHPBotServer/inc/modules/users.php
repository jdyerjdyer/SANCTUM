<?PHP
	/* Returns a user information as an associative array if found, otherwise false. */
	function getUserByDiscordID($userID) {
		$q = "SELECT * FROM users WHERE discordUserID = '$userID' LIMIT 1";
		if ($r2 = mysqli_query($con, $q)) {
			return $r2->fetch_assoc();//Found User so return it as an associative array.
		}
		return false;//Didn't find the user with that discord id.
	}

	/* Creates a new user with Discord User ID $userID unless that user already exists. Returns true on success, false on error. If false, adds error to $error object. */
	function createUser($userID) {
		if (!getUserByDiscordID($userID)) {
			$q = "INSERT INTO users (discordUserID,wallet) VALUES ('$userID', 0);";
			$r2 = mysqli_query($con,$q);
			return true;
		}

		$error->addError("userAlreadyExists", "User with Discord ID $userID already exists.");
		return false;
	}

	/* Subtracts a given amount from a particular user's wallet if there is enough in the wallet. Returns true on success, false otherwise. If false, adds error to $error object. */
	function subUserWallet($userID, $amount) {
		$amount = floor($amount);//Integer Only
		if ($amount > 0) {
			if ($user = getUserByDiscordID($userID)) {
				if ($user["wallet"] >= $amount) {
					$q = "UPDATE users SET wallet = wallet - $amount WHERE discordUserID = '$userID' LIMIT 1";
					$r2 = mysqli_query($con,$q);
					return true;
				} else {
					$error->addError("invalidAmount", "User $userID only has " . $user["wallet"] . " crystals so unable to remove $amount."
				}
			} else {
				$error->addError("invalidUser", "Invalid user $userID passed into subUserWallet.");
			}
		} else {
			$error->addError("invalidAmountNotAboveZero", "Amount passed to subUserWallet must be greater than zero.");
		}
		return false;
	}

	/* Adds a given amount to a particular user's wallet. Returns true on success, false otherwise. If false, adds error to $error object. */
	function addUserWallet($userID, $amount) {
		$amount = floor($amount);//Integer Only
		if ($amount > 0) {
			if ($user = getUserByDiscordID($userID)) {
				$q = "UPDATE users SET wallet = wallet + $amount WHERE discordUserID = '$userID' LIMIT 1";
				$r2 = mysqli_query($con,$q);
				return true;
			} else {
				$error->addError("invalidUser", "Invalid user $userID passed into addUserWallet.");
			}
		} else {
			$error->addError("invalidAmountNotAboveZero", "Amount passed to addUserWallet must be greater than zero.");
		}
		return false;
	}

	/* Returns true if valid stat for user, otherwise false. */
	function isValidUserStat($stat) {
		return in_array($stat, ["speed","health","maxHealth","strength","stamina","maxStamina","xp","lvl","statPoints"]);
	}

	/* Returns stat cap for a given stat if valid stat, otherwise returns zero. */
	function getUserStatCap($stat) {
		switch ($stat) {
			case "speed":
				return xx;
			case "health":
				return xx;
			case "maxHealth":
				return xx;
			case "strength":
				return xx;
			case "stamina":
				return xx;
			case "maxStamina":
				return xx;
			case "xp":
				return xx;
			case "lvl":
				return xx;
			case "statPoints":
				return xx;
			default:
				return 0;
		}
	}

	/* Subtracts an amount from a user's stat provided that $stat is a valid stat and $amount is greater than zero. If stat is less than $amount, sets to zero and logs action. Returns true on success, false otherwise. If false, adds error to $error object. */
	function subUserStat($userID, $stat, $amount) {
		$amount = floor($amount);//Integer Only
		if ($amount > 0) {
			if (isValidUserStat($stat)) {
				if ($user = getUserByDiscordID($userID)) {
					if ($user[$stat] >= $amount) {
						$q = "UPDATE users SET $stat = " . $user[$stat] . " - $amount WHERE discordUserID = '$userID' LIMIT 1";
						$r2 = mysqli_query($con,$q);
					} else {
						$q = "UPDATE users SET $stat = 0 WHERE discordUserID = '$userID' LIMIT 1";
						$r2 = mysqli_query($con,$q);
						$r2 = logUserAction($userID, "AmountGreaterThanStat", "User $userID only has " . $user[$stat] . " $stat so unable to remove $amount. Stat set to zero instead.");
					}
					return true;
				} else {
					$error->addError("invalidUser", "Invalid user $userID passed into subUserStat.");
				}
			} else {
				$error->addError("invalidStat", "Stat $stat passed to subUserStat is not a valid stat.");
			}
		} else {
			$error->addError("invalidAmountNotAboveZero", "Amount passed to subUserStat must be greater than zero.");
		}
		return false;
	}

	/* Adds an amount to a user's stat provided that $stat is a valid stat and $amount is greater than zero. If amount plus current stat is greater than stat cap, then sets to stat cap and logs action. Returns true on success, false otherwise. If false, adds error to $error object. */
	function addUserStat($userID, $stat, $amount) {
		$amount = floor($amount);//Integer Only
		if ($amount > 0) {
			if (isValidUserStat($stat)) {
				if ($user = getUserByDiscordID($userID)) {
					$statCap = getUserStatCap($stat);
					if ($user[$stat] + $amount > $statCap) {
						$q = "UPDATE users SET $stat = $statCap + $amount WHERE discordUserID = '$userID' LIMIT 1";
						$r2 = mysqli_query($con,$q);
						$r2 = logUserAction($userID, "StatPlusAmountGreaterThanStatCap", "Adding $amount to user $userID stat $stat currently at " . $user[$stat] . " would exceed $statCap so unable to add $amount. Stat set to $statCap instead.");
					} else {
						$q = "UPDATE users SET $stat = " . $user[$stat] . " + $amount WHERE discordUserID = '$userID' LIMIT 1";
						$r2 = mysqli_query($con,$q);
					}
					return true;
				} else {
					$error->addError("invalidUser", "Invalid user $userID passed into addUserStat.");
				}
			} else {
				$error->addError("invalidStat", "Stat $stat passed to addUserStat is not a valid stat.");
			}
		} else {
			$error->addError("invalidAmountNotAboveZero", "Amount passed to addUserStat must be greater than zero.");
		}
		return false;
	}


	/* Gets the timestamp for the user's last checkin time and a boolean if the user has checked in today as hasCheckedInToday -- case sensitive. */
	function getUserCheckIn($userID) {
		$q = "SELECT lastCheckInTime, (actionTime >= CURDATE()) AS hasCheckedInToday FROM users WHERE discordUserID = '$userID' LIMIT 1;";
		if ($r2 = mysqli_query($con,$q)) {
			return $r2->fetch_assoc();//Found User so return the check in data as an associative array.
		}
		$error->addError("invalidUser", "Invalid user $userID passed into getUserCheckIn.");
		return false;
	}

	/*
		Performs an user attack on a hostile. Updates hostile and user stats in the process and logs attack if successful. Returns true on success, false otherwise. If false, logs error to $error object.
		Subtracts $damage from hostile health stat.
		Subtracts 1 from user stamina.
		If hostile is killed (hostile health - damage <= 0), then updates hostile status JSON part "alive" to be 0 for dead.
		Logs the attack in attackLog table.
	*/
	function performUserAttack($userID, $hostileID, $damage) {
		$user = getUserByDiscordID($userID);
		$hostile = getHostileByID($hostileID);
		if ($user && $hostile) {
			subHostileStat($hostileID, "health", $damage);//Deal Hostile Damage From User.
			subUserStat($userID, "stamina", 1);//Decrease User Stamina by 1.

			logUserAttack($userID, $hostileID, $damage);//Log User Attacking Hostile

			if ($hostile["health"] - $damage <= 0) {//Did we kill it?
				setHostileStatusParts($hostileID, array("alive"=>0));//Mark it as no longer alive.
			}

			return true;
		} else {
			if (!$user) {
				$error->addError("invalidUser", "Invalid user $userID passed into performUserAttack on hostile $hostileID with damage $damage.");
			} else {
				$error->addError("invalidHostile", "Invalid hostile $hostileID passed into performUserAttack attacking with user $userID and damage $damage.");
			}
			return false;
		}
	}

	/* Logs action into userLog DB Table Returns true on success, else false. */
	function logUserAction($userID, $actionType, $actionData) {
		$q = "INSERT INTO userLog (discordUserID, actionType, actionData)
				VALUES (" . $userID . ", '" . $actionType . "', '" . $actionData . "');";
		return mysqli_query($con,$q);
	}

	/* Logs user attack action into attackLog DB Table Returns true on success, else false. */
	function logUserAtack($userID, $hostileID, $damage) {
		$q = "INSERT INTO attackLog (discordUserID, hostileID, damage)
			VALUES (" . $userID . ", '" . $hostileID . "', '" . $damage . "');";
		return mysqli_query($con,$q);
	}

?>