#***NOTICE***
This folder is under heavy changes at the moment. It is not guaranteed to work. It has not been tested. DO NOT PULL from it UNTIL I SAY OTHERWISE!!!
More documentation will follow soon. In the mean time, some documentation is available in the commit messages for each file. Sorry. So much still to do.
Full API Documentation will be available eventually. Below is the documentation for the current state of the Users, Hostiles, and Factions Modules. More changes to come!
#***NOTICE***

#***Users Module*** Filename: (/inc/modules/users.php)
##Functions List
	/*	
		Input:
			$userID		A string representing the Discord ID for the User.
	 	Returns:
	 		Returns user information as an associative array if found, otherwise false.
	 	Possible Errors:
	 		mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			None
	 	Uses:
	 		mysqli
	 			mysqli_query
	 			$result->fetch_assoc
	 	DB Queries:
	 		"SELECT * FROM users WHERE discordUserID = '$userID' LIMIT 1"
		Notes:
			None
	*/
	function getUserByDiscordID($userID)

	/*
		Input:
			$userID		A string representing the Discord ID for the User.
		Returns:
			Creates a new user with Discord User ID $userID unless that user already exists. Returns true on success, false on error. If false, adds error to $error object.
		Possible Errors:
			userAlreadyExists	The user being created already exists in the database.
			mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			None
		Uses:
			mysqli
				mysqli_query
				$result->fetch_assoc
			/inc/modules/users.php
				getUserByDiscordID
			/inc/error.php
				$error->addError
		DB Queries:
			"INSERT INTO users (discordUserID,wallet) VALUES ('$userID', 0);"			
	 		getUserByDiscordID
	 			"SELECT * FROM users WHERE discordUserID = '$userID' LIMIT 1"
	*/
		Notes:
			None
	function createUser($userID)

	/*
		Input:
			$userID		A string representing the Discord ID for the User.
			$amount		A numeric amount to subtract from the wallet. Must be greater than zero and less than or equal to the available balance in the User's wallet. Forces integer using floor.
		Returns:
			Subtracts a given amount from a particular user's wallet if there is enough in the wallet. Returns true on success, false otherwise. If false, adds error to $error object.
		Possible Errors:
			invalidAmount			The amount passed in is more than what is available in the User's wallet.
			invalidUser			The user ID passed in could not be found in the database.
			invalidAmountNotAboveZero	The amount passed in was not above zero.
	 		mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			None
		Uses:
			floor
			mysqli
				mysqli_query
				$result->fetch_assoc
			/inc/modules/users.php
				getUserByDiscordID
			/inc/error.php
				$error->addError
		DB Queries:
			"UPDATE users SET wallet = wallet - $amount WHERE discordUserID = '$userID' LIMIT 1"
	 		getUserByDiscordID
	 			"SELECT * FROM users WHERE discordUserID = '$userID' LIMIT 1"
		Notes:
			None
	*/
	function subUserWallet($userID, $amount)

	/*
		Input:
			$userID		A string representing the Discord ID for the User.
			$amount		A numeric amount to add to the wallet. Must be greater than zero. Forces integer using floor.
		Returns:
			Adds a given amount to a particular user's wallet. Returns true on success, false otherwise. If false, adds error to $error object.
		Possible Errors:
			invalidUser			The user ID passed in could not be found in the database.
			invalidAmountNotAboveZero	The amount passed in was not above zero.
	 		mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			None
		Uses:
			floor
			mysqli
				mysqli_query
				$result->fetch_assoc
			/inc/modules/users.php
				getUserByDiscordID
			/inc/error.php
				$error->addError
		DB Queries:
			"UPDATE users SET wallet = wallet + $amount WHERE discordUserID = '$userID' LIMIT 1"
	 		getUserByDiscordID
	 			"SELECT * FROM users WHERE discordUserID = '$userID' LIMIT 1"
		Notes:
			None
	*/
	function addUserWallet($userID, $amount)

	/*
		Input:
			$stat		A stat name to be checked against an array of valid stat names. (Eventually will be from a database table.)
		Returns:
			Returns true if valid stat for user, otherwise false.
		Possible Errors:
			None
		Possible Warnings:
			None
		Uses:
			in_array
		DB Queries:
			None
		Notes:
			None
	*/
	function isValidUserStat($stat)

	/*
		Input:
			$stat		A stat name to retrieve the current stat cap from a switch statement. (Eventually will be from a database table.)
		Returns:
			Returns stat cap for a given stat if valid stat, otherwise returns zero.
		Possible Errors:
			None
		Possible Warnings:
			None
		Uses:
			None
		DB Queries:
			None
		Notes:
			None
	*/
	function getUserStatCap($stat)

	/*
		Input:
			$userID		A string representing the Discord ID for the User.
			$stat		A stat name to subtract a specified amount from.
			$amount		The amount to subtract from the specified stat. Must be greater than zero. Forces integer using floor.
		Returns:
			Subtracts an amount from a user's stat provided that $stat is a valid stat and $amount is greater than zero. If stat is less than $amount, sets to zero and logs action.
			Returns true on success, false otherwise. If false, adds error to $error object.
		Possible Errors:
			invalidUser			The user ID passed in could not be found in the database.
			invalidStat			The stat name passed in is not a valid user stat name.
			invalidAmountNotAboveZero	The amount passed in was not above zero.
			mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			AmountGreaterThanStat		The user didn't have enough in the stat to subtract the total amount, so stat just set to zero.
		Uses:
			floor
			mysqli
				mysqli_query
				$result->fetch_assoc
			/inc/modules/users.php
				isValidUserStat
				getUserByDiscordID
				logUserAction
			/inc/error.php
				$error->addError
		DB Queries:
			"UPDATE users SET $stat = " . $user[$stat] . " - $amount WHERE discordUserID = '$userID' LIMIT 1" or "UPDATE users SET $stat = 0 WHERE discordUserID = '$userID' LIMIT 1"
			logUserAction
				"INSERT INTO userLog (discordUserID, actionType, actionData) VALUES (" . $userID . ", '" . $actionType . "', '" . $actionData . "');"
	 		getUserByDiscordID
	 			"SELECT * FROM users WHERE discordUserID = '$userID' LIMIT 1"
		Notes:
			None
	*/
	function subUserStat($userID, $stat, $amount)

	/*
		Input:
			$userID		A string representing the Discord ID for the User.
			$stat		A stat name to add a specified amount to.
			$amount		The amount to add to the specified stat. Must be greater than zero. Forces integer using floor.
		Returns:
			Adds an amount to a user's stat provided that $stat is a valid stat and $amount is greater than zero.
			If amount plus current stat is greater than stat cap, then sets to stat cap and logs action.
			Returns true on success, false otherwise. If false, adds error to $error object.
		Possible Errors:
			invalidUser			The user ID passed in could not be found in the database.
			invalidStat			The stat name passed in is not a valid user stat name.
			invalidAmountNotAboveZero	The amount passed in was not above zero.
			mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			StatPlusAmountGreaterThanStatCap	Adding the amount specified to the stat's current value would exceed the stat's current specified stat cap.
		Uses:
			floor
			mysqli
				mysqli_query
				$result->fetch_assoc
			/inc/modules/users.php
				isValidUserStat
				getUserByDiscordID
				getUserStatCap
				logUserAction
			/inc/error.php
				$error->addError
		DB Queries:
			"UPDATE users SET $stat = $statCap + $amount WHERE discordUserID = '$userID' LIMIT 1" or "UPDATE users SET $stat = " . $user[$stat] . " + $amount WHERE discordUserID = '$userID' LIMIT 1"
			logUserAction
				"INSERT INTO userLog (discordUserID, actionType, actionData) VALUES (" . $userID . ", '" . $actionType . "', '" . $actionData . "');"
	 		getUserByDiscordID
	 			"SELECT * FROM users WHERE discordUserID = '$userID' LIMIT 1"
		Notes:
			None
	*/
	function addUserStat($userID, $stat, $amount)


	/*
		Input:
			$userID		A string representing the Discord ID for the User.
		Returns:
			Gets the timestamp for the user's last checkin time and a boolean if the user has checked in today as hasCheckedInToday -- case sensitive.
		Possible Errors:
			invalidUser	The user ID passed in could not be found in the database.
			mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			None
		Uses:
			mysqli
				mysqli_query
				$result->fetch_assoc
			/inc/error.php
				$error->addError
		DB Queries:
			"SELECT lastCheckInTime, (actionTime >= CURDATE()) AS hasCheckedInToday FROM users WHERE discordUserID = '$userID' LIMIT 1;"
		Notes:
			Previously checked userLog table for actionType with value "checkin". Updated to check field added to users table.
	*/
	function getUserCheckIn($userID)

	/*
		Input:
			$userID		A string representing the Discord ID for the User.
			$hostileID	The hostile's ID as an integer
			$damage		An amount of damage to be taken from the hostile's health stat.
		Returns:
			Performs an user attack on a hostile. Updates hostile and user stats in the process and logs attack if successful.
			Returns true on success, false otherwise. If false, logs error to $error object.
			Subtracts $damage from hostile health stat.
			Subtracts 1 from user stamina.
			If hostile is killed (hostile health - damage <= 0), then updates hostile status JSON part "alive" to be 0 for dead.
			Logs the attack in attackLog table.
		Possible Errors:
			invalidUser			The user ID passed in could not be found in the database.
			invalidHostile			The hostile ID passed in could not be found in the database.
			invalidStat			The stat name passed in is not a valid user stat name.
			invalidStat			The stat name passed in is not a valid hostile stat name.
			invalidAmountNotAboveZero	The amount passed in was not above zero.
			invalidStatusValue	The value passed in is not numeric or is less than zero.
			invalidStatusName	The status name passed in is not a known status name.
			JSONError		The function json_encode encountered an error trying to encode the status array.
			invalidHostile		The hostile ID passed in could not be found in the database.
			invalidStatusParts	The statusParts argument was either empty or not an array.
			mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			AmountGreaterThanStat		The user didn't have enough in the stat to subtract the total amount, so stat just set to zero.
			AmountGreaterThanStat		The hostile didn't have enough in the stat to subtract the total amount, so stat just set to zero.
		Uses:
			mysqli
				mysqli_query
				$result->fetch_assoc
			/inc/modules/users.php
				getUserByDiscordID
				subUserStat
				logUserAttack
			/inc/modules/hostiles.php
				getHostileByID
				subHostileStat
				setHostileStatusParts
		DB Queries:
			subHostileStat
				"UPDATE hostiles SET $stat = " . $hostile[$stat] . " - $amount WHERE id = '$hostileID' LIMIT 1" or "UPDATE hostiles SET $stat = 0 WHERE id = '$hostileID' LIMIT 1"
			logHostileAction
				"INSERT INTO hostileLog (hostileID, actionType, actionData) VALUES (" . $hostileID . ", '" . $actionType . "', '" . $actionData . "');"
			subUserStat
				"UPDATE users SET $stat = $statCap + $amount WHERE discordUserID = '$userID' LIMIT 1" or "UPDATE users SET $stat = " . $user[$stat] . " + $amount WHERE discordUserID = '$userID' LIMIT 1"
			logUserAction
				"INSERT INTO userLog (discordUserID, actionType, actionData) VALUES (" . $userID . ", '" . $actionType . "', '" . $actionData . "');"
			logUserAttack
				"INSERT INTO attackLog (discordUserID, hostileID, damage) VALUES (" . $userID . ", '" . $hostileID . "', '" . $damage . "');"
			setHostileStatusParts
				"SELECT status FROM hostiles WHERE id = '$hostileID' LIMIT 1"
				"UPDATE hostiles SET status = '$statusJSON' WHERE id = '$hostileID' LIMIT 1"
			getUserByDiscordID
				"SELECT * FROM users WHERE discordUserID = '$userID' LIMIT 1"			
			getHostileByID
				"SELECT * FROM hostiles WHERE id = '$hostileID' LIMIT 1"
		Notes:
			Code was used in both attack and hostileAttackBack. Now separated out to just handle the user attacking portion of that functionality.
			Also added aliases, separated, and combined attack commands that use this function.
	*/
	function performUserAttack($userID, $hostileID, $damage) {

	/*
		Input:
			$userID		A string representing the Discord ID for the User.
			$actionType	A string representing the action type that took place.
			$actionData	A string with details on the action that took place.
		Returns:
			Logs action into userLog DB Table Returns true on success, else false.
		Possible Errors:
			mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			None
		Uses:
			mysqli
				mysqli_query
		DB Queries:
			"INSERT INTO userLog (discordUserID, actionType, actionData) VALUES (" . $userID . ", '" . $actionType . "', '" . $actionData . "');"
		Notes:
			Should add at least a check to see if the insert worked and if not report logging error.
	*/
	function logUserAction($userID, $actionType, $actionData)

	/*
		Input:
			$userID		A string representing the Discord ID for the User.
			$hostileID	The hostile's ID as an integer
			$damage		An amount of damage that was taken from the hostile's health stat.
		Returns:
			Logs user attack action into attackLog DB Table Returns true on success, else false.
		Possible Errors:
			mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			None
		Uses:
			mysqli
				mysqli_query
		DB Queries:
			"INSERT INTO attackLog (discordUserID, hostileID, damage) VALUES (" . $userID . ", '" . $hostileID . "', '" . $damage . "');"
		Notes:
			Should add at least a check to see if the insert worked and if not report logging error.
	*/
	function logUserAtack($userID, $hostileID, $damage)

#***Hostiles Module*** Filename: (/inc/modules/hostiles.php)
##Functions List
	/*
		Input:
			$hostileID	The hostile's ID as an integer
		Returns:
			Returns hostile information as an associative array if found, otherwise false. 
		Possible Errors:
			mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			None
		Uses:
			mysqli
				mysqli_query
				$result->fetch_assoc
			json_decode
		DB Queries:
			"SELECT * FROM hostiles WHERE id = '$hostileID' LIMIT 1"
		Notes:
			This function decodes the JSON status field for you as an associative array stored back in the same result field.
	*/
	function getHostileByID($hostileID)

	/*
		Input:
			$stat		A stat name to be checked against an array of valid stat names. (Eventually will be from a database table.)
		Returns:
			Returns true if valid stat for hostile, otherwise false.
		Possible Errors:
			None
		Possible Warnings:
			None
		Uses:
			in_array
		DB Queries:
			None
		Notes:
			None
	*/
	function isValidHostileStat($stat)

	/*
		Input:
			$stat		A stat name to retrieve the current stat cap from a switch statement. (Eventually will be from a database table.)
		Returns:
			Returns stat cap for a given stat if valid stat, otherwise returns zero.
		Possible Errors:
			None
		Possible Warnings:
			None
		Uses:
			None
		DB Queries:
			None
		Notes:
			None
	*/
	function getHostileStatCap($stat)

	/*
		Input:
			$status		A status name to be checked against an array of valid status names. (Eventually will be from a database table.)
		Returns:
			Returns true if valid status for hostile, otherwise false.
		Possible Errors:
			None
		Possible Warnings:
			None
		Uses:
			in_array
		DB Queries:
			None
		Notes:
			None
	*/
	function isValidHostileStatus($status)

	/*
		Input:
			$hostileID	The hostile's ID as an integer
			$stat		A stat name to add a specified amount to.
			$amount		The amount to add to the specified stat. Must be greater than zero. Forces integer using floor.
		Returns:
			Subtracts an amount from a hostile's stat provided that $stat is a valid stat and $amount is greater than zero. If stat is less than $amount, sets to zero and logs action.
			Returns true on success, false otherwise. If false, adds error to $error object.
		Possible Errors:
			invalidHostile			The hostile ID passed in could not be found in the database.
			invalidStat			The stat name passed in is not a valid hostile stat name.
			invalidAmountNotAboveZero	The amount passed in was not above zero.
			mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			AmountGreaterThanStat		The hostile didn't have enough in the stat to subtract the total amount, so stat just set to zero.
		Uses:
			floor
			mysqli
				mysqli_query
				$result->fetch_assoc
			/inc/modules/hostiles.php
				isValidHostileStat
				getHostileByID
				logHostileAction
			/inc/error.php
				$error->addError
		DB Queries:
			"UPDATE hostiles SET $stat = " . $hostile[$stat] . " - $amount WHERE id = '$hostileID' LIMIT 1" or "UPDATE hostiles SET $stat = 0 WHERE id = '$hostileID' LIMIT 1"
			logHostileAction
				"INSERT INTO hostileLog (hostileID, actionType, actionData) VALUES (" . $hostileID . ", '" . $actionType . "', '" . $actionData . "');"
	 		getHostileByID
	 			"SELECT * FROM hostiles WHERE id = '$hostileID' LIMIT 1"
		Notes:
			None
	*/
	function subHostileStat($hostileID, $stat, $amount)

	/*
		Input:
			$hostileID	The hostile's ID as an integer
			$stat		A stat name to add a specified amount to.
			$amount		The amount to add to the specified stat. Must be greater than zero. Forces integer using floor.
		Returns:
			Adds an amount to a hostile's stat provided that $stat is a valid stat and $amount is greater than zero.
			If amount plus current stat is greater than stat cap, then sets to stat cap and logs action.
			Returns true on success, false otherwise. If false, adds error to $error object.
		Possible Errors:
			invalidHostile			The hostile ID passed in could not be found in the database.
			invalidStat			The stat name passed in is not a valid hostile stat name.
			invalidAmountNotAboveZero	The amount passed in was not above zero.
			mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			StatPlusAmountGreaterThanStatCap	Adding the amount specified to the stat's current value would exceed the stat's current specified stat cap.
		Uses:
			floor
			mysqli
				mysqli_query
				$result->fetch_assoc
			/inc/modules/hostiles.php
				isValidHostileStat
				getHostileByID
				getHostileStatCap
				logHostileAction
			/inc/error.php
				$error->addError
		DB Queries:
			"UPDATE hostiles SET $stat = $statCap + $amount WHERE id = '$hostileID' LIMIT 1" or "UPDATE hostiles SET $stat = " . $hostile[$stat] . " + $amount WHERE id = '$hostileID' LIMIT 1"
			logHostileAction
				"INSERT INTO hostileLog (hostileID, actionType, actionData) VALUES (" . $hostileID . ", '" . $actionType . "', '" . $actionData . "');"
	 		getHostileByID
	 			"SELECT * FROM hostiles WHERE id = '$hostileID' LIMIT 1"
		Notes:
			None	*/
	function addHostileStat($hostileID, $stat, $amount)

	/*
		Input:
			$hostileID	The hostile's ID as an integer
			$statusParts	An associative array of status names and status values to update or add.
		Returns:
			Pulls the current status JSON from the DB for hostile $hostileID and replaces any $statusParts found and adds any not found. Returns true on success, otherwise false.
			$statusParts is an associative array with the keys being status names. If an invalid status name is found, then no change is made and the function returns false.
			If false, adds error to $error object. Also, status values forced to be integer through floor function and must be greater than or equal to zero.
		Possible Errors:
			invalidStatusValue	The value passed in is not numeric or is less than zero.
			invalidStatusName	The status name passed in is not a known status name.
			JSONError		The function json_encode encountered an error trying to encode the status array.
			invalidHostile		The hostile ID passed in could not be found in the database.
			invalidStatusParts	The statusParts argument was either empty or not an array.
			mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			None
		Uses:
			is_array
			count
			mysqli
				mysqli_query
			json_decode
			json_encode
			is_numeric
			floor
			/inc/modules/hostiles.php
				isValidHostileStatus
			/inc/error.php
				$error->addError
		DB Queries:
			"SELECT status FROM hostiles WHERE id = '$hostileID' LIMIT 1"
			"UPDATE hostiles SET status = '$statusJSON' WHERE id = '$hostileID' LIMIT 1"
		Notes:
			Currently there is no function to remove status parts. That function needs added later.
	*/
	function setHostileStatusParts($hostileID, $statusParts)

	/*
		Input:
			$userID			A string representing the Discord ID for the User.
			$hostileID		The hostile's ID as an integer
			$userCausedDamage	The amount of damage the user caused when attacking the hostile.
		Returns:
			Performs a hostile attack on a user aka (counter attack). Updates user stats in the process and logs attack if successful. Returns damage array on success, false otherwise.
			Subtracts $hitAmount calculated by getHostileDamage from user health stat.
			Logs the attack in hostileAttackLog table.
			Returns an array containing the keys "criticalHit" and "hitAmount" if successful, otherwise false. If false, logs error to $error object.
		Possible Errors:
			unableToApplyHostileDamage	Was unable to apply hostile attack damage to user.
			invalidUser			The user ID passed in could not be found in the database.
			invalidHostile			The hostile ID passed in could not be found in the database.
			invalidStat			The stat name passed in is not a valid user stat name.
			invalidAmountNotAboveZero	The amount passed in was not above zero.
			mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			AmountGreaterThanStat		The hostile didn't have enough in the stat to subtract the total amount, so stat just set to zero.
		Uses:
			/inc/modules/users.php
				getUserByDiscordID
				subUserStat
			/inc/modules/hostiles.php
				getHostileByID
				getHostileDamage
				logHostileAttack
			/inc/error.php
				$error->addError
		DB Queries:
	 		getHostileByID
	 			"SELECT * FROM hostiles WHERE id = '$hostileID' LIMIT 1"
	 		logHostileAttack
	 			"INSERT INTO hostileAttackLog (discordUserID, hostileID, criticalHit, hitAmount) VALUES (" . $userID . ", '" . $hostileID . "', '" . $criticalHit . "', '" . $hitAmount . "');"
	 		subUserStat
				"UPDATE users SET $stat = $statCap + $amount WHERE discordUserID = '$userID' LIMIT 1" or "UPDATE users SET $stat = " . $user[$stat] . " + $amount WHERE discordUserID = '$userID' LIMIT 1"
			logUserAction
				"INSERT INTO userLog (discordUserID, actionType, actionData) VALUES (" . $userID . ", '" . $actionType . "', '" . $actionData . "');"
	 		getUserByDiscordID
	 			"SELECT * FROM users WHERE discordUserID = '$userID' LIMIT 1"
		Notes:
	*/
	function performHostileAttack($userID, $hostileID, $userCausedDamage)

	/*
		Input:
			$hostileSpeed		The hostile's speed.
			$userSpeed		The user's speed.
			$hostileStrength	The hostile's strength.
		Returns:
			Calculates the damage the hostile will deliver to the user. Returns damage (hit) amount.
		Possible Errors:
			None
		Possible Warnings:
			None
		Uses:
			floor
			rand
		DB Queries:
			None
		Notes:
			Should probably check that the data passed in is numeric and proper.
	*/
	function getHostileDamage($hostileSpeed,$userSpeed,$hostileStrength)


	/*
		Input:
			$hostileID	The hostile's ID as an integer
			$actionType	A string representing the action type that took place.
			$actionData	A string with details on the action that took place.
		Returns:
			Logs action into hostileLog DB Table Returns true on success, else false.
		Possible Errors:
			mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			None
		Uses:
			mysqli
				mysqli_query
		DB Queries:
			"INSERT INTO hostileLog (hostileID, actionType, actionData) VALUES (" . $hostileID . ", '" . $actionType . "', '" . $actionData . "');"
		Notes:
			Should add at least a check to see if the insert worked and if not report logging error.
	*/
	function logHostileAction($hostileID, $actionType, $actionData)

	/*
		Input:
			$userID		A string representing the Discord ID for the User.
			$hostileID	The hostile's ID as an integer
			$criticalHit	Boolean indicating if this a critical hit to (killing) the player.
			$hitAmount	An amount of damage that was taken from the user's health stat.
		Returns:
			Logs hostile attack action into hostileAttackLog DB Table Returns true on success, else false.
		Possible Errors:
			mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			None
		Uses:
			mysqli
				mysqli_query
		DB Queries:
			"INSERT INTO hostileAttackLog (discordUserID, hostileID, criticalHit, hitAmount) VALUES (" . $userID . ", '" . $hostileID . "', '" . $criticalHit . "', '" . $hitAmount . "');"
		Notes:
			Should add at least a check to see if the insert worked and if not report logging error.
	*/
	function logHostileAtack($userID, $hostileID, $criticalHit, $hitAmount)

#***Factions Module*** Filename: (/inc/modules/factions.php)
##Functions List

	/*
		Input:
			$roleID		The string representation of the Discord Role ID assigned to the faction leader.
		Returns:
			Returns a factions information as an associative array if found, otherwise false.
		Possible Errors:
			mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			None
		Uses:
			mysqli
				mysqli_query
				$result->fetch_assoc
		DB Queries:
			"SELECT * FROM factions WHERE discordRoleID = '$roleID' LIMIT 1"
		Notes:
	*/
	function getFactionByDiscordRoleID($roleID)

	/*
		Input:
			$roleID		The string representation of the Discord Role ID assigned to the faction leader.
			$maount		An amount to subtract from the factions shared account. Forces amount to be integer using floor function.
		Returns:
			Subtracts a given amount from a particular faction's account if there is enough in the account.Returns true on success, false otherwise. If false, adds error to $error object.
		Possible Errors:
			invalidAmount			The amount passed in is more than what is available in the faction's account.
			invalidFaction			The faction ID passed in could not be found in the database.
			invalidAmountNotAboveZero	The amount passed in was not above zero.
			mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			None
		Uses:
			floor
			mysqli
				mysqli_query
				$result->fetch_assoc
			/inc/modules/factions.php
				getFactionByDiscordRoleID
			/inc/error.php
				$error->addError
		DB Queries:
			"UPDATE factions SET account = account - $amount WHERE discordRoleID = '$roleID' LIMIT 1"
			getFactionByDiscordRoleID
				"SELECT * FROM factions WHERE discordRoleID = '$roleID' LIMIT 1"
		Notes:
			None
	*/
	function subFactionAccount($roleID, $amount)

	/*
		Input:
			$roleID		The string representation of the Discord Role ID assigned to the faction leader.
			$maount		An amount to add to the factions shared account. Forces amount to be integer using floor function.
		Returns:
			Adds a given amount to a particular faction's account. Returns true on success, false otherwise. If false, adds error to $error object.
		Possible Errors:
			invalidFaction			The faction ID passed in could not be found in the database.
			invalidAmountNotAboveZero	The amount passed in was not above zero.
			mysqli error codes -- Not handled by the system yet.
		Possible Warnings:
			None
		Uses:
			floor
			mysqli
				mysqli_query
				$result->fetch_assoc
			/inc/modules/factions.php
				getFactionByDiscordRoleID
			/inc/error.php
				$error->addError
		DB Queries:
			"UPDATE factions SET account = account + $amount WHERE discordRoleID = '$roleID' LIMIT 1"
			getFactionByDiscordRoleID
				"SELECT * FROM factions WHERE discordRoleID = '$roleID' LIMIT 1"
		Notes:
			None
	*/
	function addFactionAccount($roleID, $amount)

More Documentation Coming Soon!