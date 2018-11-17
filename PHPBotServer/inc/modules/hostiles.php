<?PHP

	/* Returns hostile information as an associative array if found, otherwise false. Note -- This function decodes the JSON status field for you as an associative array stored back in the same result field. */
	function getHostileByID($hostileID) {
		$q = "SELECT * FROM hostiles WHERE id = '$hostileID' LIMIT 1";
		if ($r2 = mysqli_query($con, $q)) {
			$r2->fetch_assoc();//Found hostile so retrieve it as an associative array.
			$r2["status"] = json_decode($r2["status"], true);//Decode the JSON status field and return it as an associative array.
			return $r2;//Return the result.
		}
		return false;//Didn't find the hostile with that discord id.
	}

	/* Returns true if valid stat for hostile, otherwise false. */
	function isValidHostileStat($stat) {
		return in_array($stat, ["health","maxHealth","strength","speed","stash"]);
	}

	/* Returns stat cap for a given stat if valid stat, otherwise returns zero. */
	function getHostileStatCap($stat) {
		switch ($stat) {
			case "health":
				return xx;
			case "maxHealth":
				return xx;
			case "strength":
				return xx;
			case "speed":
				return xx;
			case "stash":
				return xx;
			default:
				return 0;
		}
	}

	/* Returns true if valid status for hostile, otherwise false. */
	function isValidHostileStatus($status) {
		return in_array($status, ["alive","fled", "removed"]);
	}

	/* Subtracts an amount from a hostile's stat provided that $stat is a valid stat and $amount is greater than zero. If stat is less than $amount, sets to zero and logs action. Returns true on success, false otherwise. If false, adds error to $error object. */
	function subHostileStat($hostileID, $stat, $amount) {
		$amount = floor($amount);//Integer Only
		if ($amount > 0) {
			if (isValidHostileStat($stat)) {
				if ($hostile = getHostileByID($hostileID)) {
					if ($hostile[$stat] >= $amount) {
						$q = "UPDATE hostiles SET $stat = " . $hostile[$stat] . " - $amount WHERE id = '$hostileID' LIMIT 1";
						$r2 = mysqli_query($con,$q);
						return true;
					} else {
						$q = "UPDATE hostiles SET $stat = 0 WHERE id = '$hostileID' LIMIT 1";
						$r2 = mysqli_query($con,$q);
						$r2 = logHostileAction($hostileID, "amountGreaterThanStat", "Hostile $hostileID only has " . $hostile[$stat] . " $stat so unable to remove $amount. Stat set to zero instead.");
						$warning->addWarning("amountGreaterThanStat", "Hostile $hostileID only has " . $hostile[$stat] . " $stat so unable to remove $amount. Stat set to zero instead.");
					}
				} else {
					$error->addError("invalidHostile", "Invalid hostile $hostileID passed into subHostileStat.");
				}
			} else {
				$error->addError("invalidStat", "Stat $stat passed to subHostileStat is not a valid stat.");
			}
		} else {
			$error->addError("invalidAmountNotAboveZero", "Amount passed to subHostileStat must be greater than zero.");
		}
		return false;
	}

	/* Adds an amount to a hostile's stat provided that $stat is a valid stat and $amount is greater than zero. If amount plus current stat is greater than stat cap, then sets to stat cap and logs action. Returns true on success, false otherwise. If false, adds error to $error object. */
	function addHostileStat($hostileID, $stat, $amount) {
		$amount = floor($amount);//Integer Only
		if ($amount > 0) {
			if (isValidHostileStat($stat)) {
				if ($hostile = getHostileByID($hostileID)) {
					$statCap = getHostileStatCap($stat);
					if ($hostile[$stat] + $amount > $statCap) {
						$q = "UPDATE hostiles SET $stat = $statCap + $amount WHERE id = '$hostileID' LIMIT 1";
						$r2 = mysqli_query($con,$q);
						$r2 = logHostileAction($hostileID, "statPlusAmountGreaterThanStatCap", "Adding $amount to hostile $hostileID stat $stat currently at " . $hostile[$stat] . " would exceed $statCap so unable to add $amount. Stat set to $statCap instead.");
						$warning->addWarning("statPlusAmountGreaterThanStatCap", "Adding $amount to hostile $hostileID stat $stat currently at " . $hostile[$stat] . " would exceed $statCap so unable to add $amount. Stat set to $statCap instead.");
					} else {
						$q = "UPDATE hostiles SET $stat = " . $hostile[$stat] . " + $amount WHERE id = '$hostileID' LIMIT 1";
						$r2 = mysqli_query($con,$q);
					}
					return true;
				} else {
					$error->addError("invalidHostile", "Invalid hostile $hostileID passed into addHostileStat.");
				}
			} else {
				$error->addError("invalidStat", "Stat $stat passed to addHostileStat is not a valid stat.");
			}
		} else {
			$error->addError("invalidAmountNotAboveZero", "Amount passed to addHostileStat must be greater than zero.");
		}
		return false;
	}

	/*	Pulls the current status JSON from the DB for hostile $hostileID and replaces any $statusParts found and adds any not found. Returns true on success, otherwise false.
		$statusParts is an associative array with the keys being status names. If an invalid status name is found, then no change is made and the function returns false.
		If false adds error to $error object. Also, status values forced to be integer through floor function and must be greater than or equal to zero.
	*/
	function setHostileStatusParts($hostileID, $statusParts) {
		if (is_array($statusParts) && count($statusParts) {
			$q = "SELECT status FROM hostiles WHERE id = '$hostileID' LIMIT 1";
			if ($r2 = mysqli_query($con, $q)) {
				$status = json_decode($r2["status"], true);//Decode the JSON status field and return it as an associative array.

				foreach ($statusParts AS $statusName => $statusValue) {
					if (isValidHostileStatus($statusName)) {
						if (is_numeric($statusValue) && $statusValue >= 0) {
							$statusValue = floor($statusValue);//Integer Only
							$status[$statusName] = $statusValue;//Replace (or Add) the value to the status array.
						} else {
							$error->addError("invalidStatusValue", "The status value $value passed in to setHostileStatusPart for $statusName is not valid. Status values must be numeric integers 0 or larger.");
							return false;//Must not fall out and accidentally return true.
						}
					} else {
						$error->addError("invalidStatusName", "The status name $statusName passed into setHostileStatusPart is not valid.");
						return false;//Must not fall out and accidentally return true.
					}
				}

				if ($statusJSON = json_encode($status, JSON_NUMERIC_CHECK)) {
					$q = "UPDATE hostiles SET status = '$statusJSON' WHERE id = '$hostileID' LIMIT 1";
					return mysqli_query($con,$q);//Perform the actual status update in the database and return the result.
				} else {
					$error->addError("JSONError", "The function json_encode encountered an error trying to encode the status array. JSON Error: " . json_last_error_msg());
				}
			} else {
				$error->addError("invalidHostile", "Invalid hostile $hostileID passed into setHostileStatusPart.");
			}
		} else {
			$error->addError("invalidStatusParts", "Invalid argument statusParts passed into setHostileStatusPart. statusParts must be a non-empty associative array of status names and value pairs.");
		}

		return false;
	}

	/*
		Performs a hostile attack on a user aka (counter attack). Updates user stats in the process and logs attack if successful. Returns damage array on success, false otherwise.
		Subtracts $hitAmount calculated by getHostileDamage from user health stat.
		Logs the attack in hostileAttackLog table.
		Returns an array containing the keys "criticalHit" and "hitAmount" if successful, otherwise false. If false, logs error to $error object.
	*/
	function performHostileAttack($userID, $hostileID, $userCausedDamage) {
		$user = getUserByDiscordID($userID);
		$hostile = getHostileByID($hostileID);
		if ($user && $hostile) {
			$criticalHit = 0;
			$hitAmount = getHostileDamage($hostile["speed"],$user["speed"],$hostile["strength"]);
			if($hitAmount > 0){
				if ($hitAmount >= $user["health"]) {
					$hitAmount = $user["health"];
					$criticalHit = 1;//OVER KILL!!!!!!!!! USER BE DEAD!!!!!
					logHostileAttack($userId, $hostileID, $criticalHit, $hitAmount);//Log the attack.
				}
				if (subUserStat($userID, "health", $hitAmount)) {
					return array("criticalHit" => $criticalHit, "hitAmount" => $hitAmount);//Success, so return user damage information.
				} else {
					$criticalMsg = "";
					if ($criticalHit == 1) {
						$criticalMsg = "critical hit ";
					}
					$error->addError("unableToApplyHostileDamage", "The system was unable to apply " . $criticalMsg . "damage $hitAmount from hostile $hostileID to user $userID in performHostileAttack.");
				}
			}
		} else {
			if (!$user) {
				$error->addError("invalidUser", "Invalid user $userID passed into performHostileAttack attacking with hostile $hostileID and user caused damage $userCausedDamage.");
			} else {
				$error->addError("invalidHostile", "Invalid hostile $hostileID passed into performHostileAttack on user $userID with user caused damage $userCausedDamage.");
			}
		}
		return false;
	}

	/* Calculates the damage the hostile will deliver to the user. Returns damage (hit) amount. */
	function getHostileDamage($hostileSpeed,$userSpeed,$hostileStrength){
		$hitAmount = 0;
		$percentage = floor(rand(0,101));
		if($hostileSpeed > $userSpeed){
			if($percentage <= 80 ){
				//80% chance to hit you back.
				$hitAmount = $hostileStrength + rand(-($hostileStrength/4),$hostileStrength/4);
			}
		} else{
			if($percentage <= 30){
				//30% chance to hit you back.
				$hitAmount = $hostileStrength + rand(-($hostileStrength/4),$hostileStrength/4);
			}
		}
		/*
			if($hostileSpeed > $userSpeed){
				if(20 > rand(0,100)){
					$hitAmount = $hostileStrength + rand(-($hostileStrength/4),$hostileStrength/4);
				}
			} elseif($hostileSpeed == $userSpeed){
				if(50 >= rand(0,100)){
					$hitAmount = $hostileStrength + rand(-($hostileStrength/4),$hostileStrength/4);
				}
			} else{
				if(70 >= rand(0,100)){
					$hitAmount = $hostileStrength + rand(-($hostileStrength/4),$hostileStrength/4);
				}
			}
		*/
		return $hitAmount;
	}


	/* Logs action into hostileLog DB Table Returns true on success, else false. */
	function logHostileAction($hostileID, $actionType, $actionData) {
		$q = "INSERT INTO hostileLog (hostileID, actionType, actionData)
				VALUES (" . $hostileID . ", '" . $actionType . "', '" . $actionData . "');";
		return mysqli_query($con,$q);
	}

	/* Logs hostile attack action into hostileAttackLog DB Table Returns true on success, else false. */
	function logHostileAtack($userID, $hostileID, $criticalHit, $hitAmount) {
		$q = "INSERT INTO hostileAttackLog (discordUserID, hostileID, criticalHit, hitAmount)
			VALUES (" . $userID . ", '" . $hostileID . "', '" . $criticalHit . "', '" . $hitAmount . "');";
		return mysqli_query($con,$q);
	}

?>