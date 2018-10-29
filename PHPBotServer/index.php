<?PHP
	/* Include global variables, helper functions, check access permission, check data passed in and set up defaults.. */
	include_once 'inc\globals.php';

	/* If codex page, display and exit. */
	if ($filename == "codex.php") {
		display_codex();
		exit;
	}

	$result = "";//Used to hold output until echoed in the result JSON.
	switch ($filename) {
	{
		case "senddata.php":
		case "senddata2.php":
		case "sendpostdata.php":
			switch ($dataType) {
				/*
					Possible Errors:
						invalidUser
				*/
				case "conversion"://Depreciated, but kept for backwards compatibility. Mostly used to just indicate when a user changes faction.
				case "log"://Correct usage.
						$success = false;
						if ($user = getUserByDiscordID($userID)) {
							$r2 = logUserAction($userID, $dataType, $dataToSend);//NEED TO TELL THE USER IT LOGGED EVERYTHING OKAY OR NOT
							$success = true;
						} else {
							$error->addError("invalidUser", "Invalid user $userID used in request command $dataType.");
						}

						if ($success) {
							$result .= "Successfully logged user action for user $userID with action $dataType and details $dataToSend";
						}
				break;

				/*
					Possible Errors:
						userAlreadyExists
				*/
				case "newUser":
							$success = false;
							if (createUser($userID) {
								$success = true;
								$r2 = logUserAction($userID, $dataType, "Created new user $userID.");
							}

							if ($success) {
								$result .= "createdUser";
							}
				break;

				/*
					Possible Errors:
						invalidAmountNotAboveZero -> Note that this used to output amountNotAboveZero.
						invalidUser
				*/
				case "checkin":
							$success = false;
							if ($checkin = getUserCheckIn($userID)) {
								if ($checkin["hasCheckedInToday"]) {
										//already checked in today.
										$flastupdated=stripslashes($checkin["lastCheckInTime"]);
										$timeAgoObject = new convertToAgo; // Create an object for the time conversion functions
										// Query your database here and get timestamp
										$ts = $flastupdated;
										$convertedTime = ($timeAgoObject -> convert_datetime($ts)); // Convert Date Time
										$when = ($timeAgoObject -> makeAgo($convertedTime)); // Then convert to ago time
										//date("F j, Y, g:i a", strtotime($flastupdated));
										$success = true;
								} else {
									//Can check in.
									if (addUserWallet($userID, $dataToSend)) {
										$r2 = logUserAction($userID, $dataType, "Checked in for $dataToSend crystals.");
										$success = true;
									}
								}
							}

							if ($success) {
								if ($checkin["hasCheckedInToday"]) {
									$result .= $when;
								} else {
									$result .= "available";//Changed from echo 1; per Ratstail91's request.
								}
							}

							exit;

				break;

				/*
					Possible Errors:
						invalidAmountNotAboveZero -> Note that this used to output amountNotAboveZero
						invalidAmount -> Note that this used to output notEnoughInWallet
						invalidUser
						invalidFaction
				*/
				case "deposit":
							$success = false;
							if (subUserWallet($userID, $dataToSend) {
								if (addFactionAccount($dataToSend2, $dataToSend) {
									$r2 = logUserAction($userID, $dataType, "User $userID deposited $dataToSend crystals to Faction $dataToSend2.");
									$success = true;
								}
							}

							if ($success) {
								$result .= 1;
							}

							exit;

				break;

				/*
					Possible Errors:
						invalidAmountNotAboveZero -> Note that this used to output amountNotAboveZero
						invalidUser
				*/
				case "gambleWon":
							$success = false;
							if (addUserWallet($userID, $dataToSend) {
								$r2 = logUserAction($userID, $dataType, "Gambled and won for $dataToSend crystals.");
							}

							if ($success) {
								echo 1;
							} else {
								$err = $error->getLastError();
								switch ($err["name"]) {
									case "invalidAmountNotAboveZero":
										echo "amountNotAboveZero";//Amount passed in was not above zero.
										break;
									case "invalidUser":
										echo "invalidUser";//Couldn't find user with Discord ID $userID
										break;
								}
							}

							exit;

				break;

				/*
					Possible Errors:
						invalidAmountNotAboveZero -> Note that this used to output amountNotAboveZero
						invalidAmount -> Note that this used to output notEnoughInWallet
				*/
				case "gambleLost":
							$success = false;
							if (subUserWallet($userID, $dataToSend) {
								$r2 = logUserAction($userID, $dataType, "Gambled and lost for $dataToSend crystals.");
								$success = true;
							}

							if ($success) {
								echo 1;
							} else {
								$err = $error->getLastError();
								switch ($err["name"]) {
									case "invalidAmountNotAboveZero":
										echo "amountNotAboveZero";//Amount passed in was not above zero.
										break;
									case "invalidAmount":
										echo "notEnoughInWallet";//User with Discord ID $userID didn't have more than $dataToSend2 crystals in their wallet.
										break;
									case "invalidUser":
										echo "invalidUser";//Couldn't find user with Discord ID $userID
										break;
								}
							}

							exit;

				break;

				/*
					Possible Errors:
						invalidGiftSameID -> Note that this used to output invalidUser
						invalidAmountNotAboveZero -> Note that this used to output amountNotAboveZero
						invalidAmount -> Note that this used to output notEnoughInWallet
						invalidUser -> Note that this used to output two different possible results invalidUserSelf if the invalid user was the giver and invalidUser if the invalid user was the recipient
							Along with the invalidUser error, the result passed back from this request will indicate giver or recipient
				*/
				case "transfer":
							$success = false;
							$userCheck = "giver";
							if ($userID == $dataToSend) {
								$error->addError("invalidGiftSameID", "Giver $userID and recipient $dataToSend must be different.");
							} else {
								$userCheck = "recipient";
								if (subUserWallet($userID, $dataToSend2) {
									if (addUserWallet($dataToSend, $dataToSend2) {
										$r2 = logUserAction($userID, $dataType, "$userID gave $dataToSend2 crystals to $dataToSend.");
										$success = true;
									}
								}
							}

							if ($success) {
								result.= 1;
							} else {
								$err = $error->getLastError();
								if ($err["name"] == "invalidUser") {
									$result .= $userCheck;
								}
							}

							exit;

				break;

				/*
					Possible Errors:
						invalidStatusValue
						invalidStatusName
						invalidStatusParts
						JSONError
						invalidStat
						invalidAmountNotAboveZero
						invalidUser
						invalidHostile
						Note -> This request didn't return errors before, but only output 0 if not successful. If not successful, then result has 0 appended.
				*/
				case "attack":
							$success = false;
							if (performUserAttack($userID, $dataToSend2, $dataToSend)) {
								if ($hostile = getHostileByID($dataToSend2)) {
									$success = true;
								}
							}

							if ($success) {
								$result .= $hostileHealth.",".$hostileMaxHealth;
							} else {
								$result .= 0;
							}

				break;

				/*
					Possible Errors:
						invalidStatusValue
						invalidStatusName
						JSONError
						invalidStatusParts
						invalidStat
						invalidAmountNotAboveZero
						unableToApplyHostileDamage
						invalidUser
						invalidHostile
						Note -> This request didn't return errors before, but only output 0 if not successful. If not successful, then result has 0 appended.

				*/
				case "attackAndCounterAttack":
							$success = false;
							if (performUserAttack($userID, $dataToSend2, $dataToSend) &&  $damageDealt = performHostileAttack($userID, $dataToSend2, $dataToSend)) {
								$success = true;
							}

							if ($success) {
								$result .= $hostileHealth.",".$hostileMaxHealth.",".$damage["hitAmount".",".$damage["criticalHit"];;
							} else {
								$result .= 0;
							}

							exit;

				break;

				/*
					Possible Errors:
						invalidStat
						invalidAmountNotAboveZero
						unableToApplyHostileDamage
						invalidUser
						invalidHostile
						Note -> This request didn't return errors before, but only output 0 if not successful. If not successful, then result has 0 appended.
				*/
				case "hostileAttack":
				case "counterAttack":
				case "hostileAttackBack":
					$success = false;
					if ($damageDealt = performHostileAttack($userID, $dataToSend2, $dataToSend)) {
						$success = true;
					}

					if ($success) {
						$result .= $hostileHealth.",".$hostileMaxHealth.",".$damage["hitAmount".",".$damage["criticalHit"];;
					} else {
						$result .= 0;
					}

				break;

				/*
					Possible Errors:
						JSONError
						invalidStatusName
						invalidStatusValue
						invalidStatusParts
						hostileAlreadyDead
						invalidHostile
						Note -> Doesn't appear to be used currently. Old functionality just got the most recently created ravager by searching the database. New code requires an id passed in $dataToSend.
				*/
				case "hostileFlee":
					$success = false;
					if ($hostile = getHostileByID($dataToSend)) {
						if ($hostile["status"]["alive"] == 1) {
							$statusParts = array("fled" => 1, "alive" => 0);
							if (setHostileStatusParts($dataToSend, $statusParts)) {
								$success = true;
							}
						} else {
							$error->addError("hostileAlreadyDead", "Hostile $dataToSend passed to hostileFlee request is already dead.");
						}
					} else {
						$error->addError("invalidHostile", "Invalid hostile $id passed into hostileFlee request.");
					}

					if ($success) {
						$result .= "fled";
					} else {
						$result .= "alreadyDead";
					}

				break;

				case "newHostile":
						$q = "SELECT id FROM hostiles WHERE alive = 1 LIMIT 1;";
						$r2 = mysqli_query($con,$q);
						if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
								echo "notCreated";
						} else{
									$elvl = $dataToSend;
									$healthBase = 50; $strengthBase = 3; $speedBase = 3; $stashBase = 3;
									$healthMin = ($healthBase * $elvl) / 2; $healthMax = $healthBase * $elvl;
									$strengthMin = ($strengthBase * $elvl) / 2; $strengthMax = $strengthBase * $elvl;
									$speedMin = ($speedBase * $elvl) / 2; $speedMax = $speedBase * $elvl;
									$stashMin = ($stashBase * $elvl) / 2; $stashMax = $stashBase * $elvl;

									$health = floor(rand($healthMin,$healthMax));
									$strength = floor(rand($strengthMin,$strengthMax));
									$speed = floor(rand($speedMin,$speedMax));
									$stash = floor(rand($stashMin,$stashMax));

									$claimID = floor(rand(1000,9999));
									$q = "INSERT INTO hostiles (hostileType, maxHealth, health, strength, speed, stash, alive, claimID)
																			VALUES ('ravager', '$health', '$health', '$strength', '$speed', '$stash', 1, '$claimID');";
									$r2 = mysqli_query($con,$q);
									echo $health.",".$speed.",".$strength.",".$claimID;
						}
				break;

				case "claim":
							$claimAmount = $dataToSend2;


							$q = "SELECT stash FROM hostiles WHERE alive = 0 AND claimID = '$dataToSend' LIMIT 1;";
							$r2 = mysqli_query($con,$q);
							if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
								while ( $a = mysqli_fetch_assoc($r2) ) {
										$stash=stripslashes($a['stash']);
								}
								if($claimAmount <= $stash){
										//take money from the stash
										$q = "UPDATE hostiles SET stash = stash - $claimAmount WHERE claimID = '$dataToSend' LIMIT 1";
										$r2 = mysqli_query($con,$q);
										$q = "UPDATE users SET wallet = wallet + $claimAmount WHERE discordUserID = '$userID' LIMIT 1";
										$r2 = mysqli_query($con,$q);

															$q = "INSERT INTO userLog (discordUserID, actionType, actionData)
															VALUES (" . $userID . ", '" . $dataType . "', '$userID claimed $claimAmount crystals from a Ravager.');";
															$r2 = mysqli_query($con,$q);
										$stash = $stash - $claimAmount;
										if($stash == 0){
												$q = "UPDATE hostiles SET claimID=0 WHERE claimID = '$dataToSend' LIMIT 1";
												$r2 = mysqli_query($con,$q);
										}
										echo $stash;
								}else{
									echo "notEnough";
								}
								exit;
							} else{
								echo "noClaimID";
							}


							exit;

				break;

				case "getHostileData":
							$q = "SELECT stash,claimID FROM hostiles WHERE alive = 0 AND id = '$dataToSend' LIMIT 1;";
							$r2 = mysqli_query($con,$q);
							if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
								while ( $a = mysqli_fetch_assoc($r2) ) {
										$stash=stripslashes($a['stash']);
										$claimID=stripslashes($a['claimID']);
								}
								echo $stash.",".$claimID;
							}
							exit;

				break;



				case "getDamageDistribution":
						//Gets base stats for enemy
						$q = "SELECT stash,maxHealth,fled FROM hostiles WHERE id = '$dataToSend' LIMIT 1;";
						$r2 = mysqli_query($con,$q);
						$a = mysqli_fetch_assoc($r2);
						$stash=stripslashes($a['stash']);
						$maxHealth=stripslashes($a['maxHealth']);
						$fled=stripslashes($a['fled']);
						$totalCrystalsInStash = 0;

						if($fled == 1){
									echo "fled";
						}else{
									//gets all dammage from users
									$damageDistribution = array();
									$q = "SELECT discordUserID,SUM(damage) totalDamage FROM attackLog WHERE hostileID = $dataToSend GROUP BY discordUserID;";
									//$q = "SELECT attackLog.damage,attackLog.discordUserID,hostiles.stash,hostiles.maxHealth FROM attackLog WHERE hostiles.id = attackLog.hostileID AND attackLog.hostileID = '$dataToSend';";
									$r2 = mysqli_query($con,$q);
									if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
										while ( $a = mysqli_fetch_assoc($r2) ) {
												$damage=stripslashes($a['totalDamage']);
												$discordUserID=stripslashes($a['discordUserID']);
												$damagePercent = round(( $damage / $maxHealth ) * 100);
												$percentStashAmount = round($stash * ($damagePercent/100));
												$totalCrystalsInStash += $percentStashAmount;
												// you can add single array values too
												$damageDistribution[] = array('id'=>$discordUserID, 'totalDamage'=>$damage, 'damagePercent'=>$damagePercent, 'crystalsReceived'=>$percentStashAmount);
												if($dataToSend2 == 1){
													//Flag to actually distribute crystals
													$q2 = "UPDATE users SET wallet = wallet + $percentStashAmount WHERE discordUserID = '$discordUserID' LIMIT 1";
													$r3 = mysqli_query($con,$q2);
												}

										}
										echo json_encode($damageDistribution);
									} else{
										echo 0;
									}
									exit;
						}

				break;


				case "updateStamina":
							$q = "UPDATE users SET stamina = stamina + 1 WHERE stamina < maxStamina;";
							$r2 = mysqli_query($con,$q);
							//UPDATE users SET health = min(floor(health + (maxHeath/100)), maxHealth)
							$q = "UPDATE users SET health = least(floor(health + (maxHealth/100)), maxHealth) WHERE health < maxHealth AND health > 0;";
							$r2 = mysqli_query($con,$q);
							exit;
				break;


				case "reviveAll":
							$q = "UPDATE users SET health = 1 WHERE health = 0;";
							$r2 = mysqli_query($con,$q);
							$q = "INSERT INTO userLog (discordUserID, actionType, actionData)
							VALUES (0,'revive','reviveAll');";
							$r2 = mysqli_query($con,$q);
							exit;
				break;

				case "lvlinfo":
						$q = "SELECT xp,lvl FROM users WHERE discordUserID = '$userID';";
						$r2 = mysqli_query($con,$q);
						if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
							while ( $a = mysqli_fetch_assoc($r2) ) {
									$xp=stripslashes($a['xp']);
									$currentlvl=stripslashes($a['lvl']);
									$lvlbase=getLevelBase();
									$lvl=getLevel($xp,$lvlbase);
									$level = $dataToSend2;
									$str = generateStatFromLevel($level,"str");
									$spd = generateStatFromLevel($level,"spd");
									$hp = generateStatFromLevel($level,"hp");
									$stash = generateStatFromLevel($level,"stash");

							}
						}
						//echo "LEVEL: ".getLevel($xp,$lvlbase),"<BR>XP: ".$xp."<BR>CURRENT LEVEL PROGRESS:".getCurrentLevelProgress($xp,$lvl);
						echo "LEVEL: ".getLevel($dataToSend,$lvlbase),"<BR>XP: ".$xp."<BR>CURRENT LEVEL PROGRESS:".getCurrentLevelProgress($xp,$lvl)."<BR><BR>STR: ".$str." SPD: ".$spd." HP: ".$hp." STASH:: ".$stash;
				break;

				case "upgradeStats":
						//Changed it to just upgrade 1 point automatically
						$dataToSend2 = 1;
						$q = "SELECT statPoints FROM users WHERE discordUserID = '$userID';";
						$r2 = mysqli_query($con,$q);
						if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
							while ( $a = mysqli_fetch_assoc($r2) ) {
									$statPoints=stripslashes($a['statPoints']);
							}
							if($dataToSend2 <= $statPoints){
								$tableName = "";
								switch (strtoupper($dataToSend)) {
										case "STR":
											$tableName = "strength = strength + ".$dataToSend2;
										break;
										case "HP":
											$tableName = "maxHealth = maxHealth + 10";
										break;
										case "SPD":
											$tableName = "speed = speed + ".$dataToSend2;
										break;
										case "STAM":
											$tableName = "maxStamina = maxStamina + ".$dataToSend2;
										break;
								}
									$q = "UPDATE users SET statPoints = statPoints - $dataToSend2,$tableName WHERE discordUserID = '$userID' LIMIT 1";
									$r2 = mysqli_query($con,$q);
									echo "success";
							} else{
									echo "notEnoughPoints";
							}
						} else{
							echo "failure";
						}

				break;




				case "heal":
						$q = "SELECT health,maxHealth,wallet FROM users WHERE discordUserID = '$userID';";
						$r2 = mysqli_query($con,$q);
						if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
							while ( $a = mysqli_fetch_assoc($r2) ) {
									$health=stripslashes($a['health']);
									$maxHealth=stripslashes($a['maxHealth']);
									$crystals=stripslashes($a['wallet']);
							}
							$treatmentCost = $dataToSend;
							$treatmentName = $dataToSend2;
							$newHealth = $health;
							if($health == $maxHealth){echo "fullHealth";exit;}

							if($health > 0){
									if($crystals >= $treatmentCost){
												switch ($treatmentName) {
														case "TREAT":
																		$newHealth += 15;
														break;
														case "TREATV2":
																		$newHealth = $maxHealth*0.15;
														break;
														case "PATCH":
																		$newHealth += 50;
														break;
														case "PATCHV2":
																		$newHealth = $maxHealth*0.5;
														break;
														case "REGEN":
																		$newHealth += 100;
														break;
														case "REGENV2":
																		$newHealth = $maxHealth;
														break;
														default:
																echo "cantDoThat";exit;
														break;
												}
													if($newHealth < $health){echo "lessThanYourHealth";exit;}
													if($newHealth>$maxHealth){$newHealth = $maxHealth;};
													$q = "UPDATE users SET health = $newHealth,wallet = wallet - $treatmentCost WHERE discordUserID = '$userID' LIMIT 1";
													$r2 = mysqli_query($con,$q);
													echo "success,".$newHealth."/".$maxHealth;
								} else{
									echo "notEnoughCrystals";
								}
							} else{
											if($crystals >= $treatmentCost){
														switch ($treatmentName) {
																case "REVIVE":
																					$newHealth = 25;
																break;
																case "REVIVEV2":
																					$newHealth = $maxHealth*0.5;
																break;
																case "REVIVEV3":
																					$newHealth = $maxHealth;
																break;
																case "TREAT":
																				echo "youreKnockedOut";exit;
																break;
																case "TREATV2":
																				echo "youreKnockedOut";exit;
																break;
																case "PATCH":
																				echo "youreKnockedOut";exit;
																break;
																case "PATCHV2":
																				echo "youreKnockedOut";exit;
																break;
																case "REGEN":
																				echo "youreKnockedOut";exit;
																break;
																default:
																		echo "cantDoThat";exit;
																break;
														}
														if($newHealth < $health){echo "lessThanYourHealth";exit;}
														if($newHealth>$maxHealth){$newHealth = $maxHealth;};
														$q = "UPDATE users SET health = $newHealth,wallet = wallet - $treatmentCost WHERE discordUserID = '$userID' LIMIT 1";
														$r2 = mysqli_query($con,$q);
														echo "success,".$newHealth."/".$maxHealth;
												}else{
													echo "notEnoughCrystals";
												}
							}
						} else{
							echo "failure";
						}

				break;



				case "addXP":
						addXp($userID,$dataToSend);
				break;



				case "getLevelUp":
					//addXp($userID,$dataToSend);
					$levelCap = 30;$levelCapXP = 625;
					$q = "SELECT xp,lvl,statPoints,chests FROM users WHERE discordUserID = '$userID';";
					$r2 = mysqli_query($con,$q);
					if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
								while ( $a = mysqli_fetch_assoc($r2) ) {
										$xp=stripslashes($a['xp']);
										$lvl=stripslashes($a['lvl']);
										$statPoints=stripslashes($a['statPoints']);
										$chests=stripslashes($a['chests']);
								}
								$lvlbase = getLevelBase();
								$currentLVL = floor(getLevel($xp,$lvlbase));
								if($currentLVL > $lvl){
										if($currentLVL > $levelCap){
												$chests += 1;
												$q = "UPDATE users SET lvl = $levelCap,chests = chests + 1,xp = $levelCapXP WHERE discordUserID = '$userID' LIMIT 1";
												$r2 = mysqli_query($con,$q);
										}else{
												$statPoints += 1;
												$q = "UPDATE users SET lvl = lvl + 1,statPoints = statPoints + 1 WHERE discordUserID = '$userID' LIMIT 1";
												$r2 = mysqli_query($con,$q);
												$lvl = $lvl + 1;
										}
										echo "levelup,".$lvl.",".$statPoints.",".$statPoints;
								} else{
										echo "xpadded,".$currentLVL.",".$statPoints;
								}
					}
				break;


				case "scavenge":
						$random = floor(rand(0,101));
						$ultrarare = 0;$rare = 0; $uncommon = 0; $common = 0; $scrap = 0;
						if($random <= 0.5){
								$ultrarare = 1;
						}
						if($random <= 3 && $random > 0.5){
								$rare = round(rand(1,2));
						}
						if($random <= 10 && $random > 3){
								$uncommon = round(rand(1,3));
						}
						if($random <= 50 && $random > 10){
								$common = round(rand(1,3));
						}
						if($random > 50){
								$scrap = round(rand(1,7));
						}

						$staminaCost = $dataToSend;
						$crystalCost = $dataToSend2;
						$q = "UPDATE users SET stamina = stamina - $staminaCost,wallet = wallet - $crystalCost WHERE discordUserID = '$userID';";
						$r2 = mysqli_query($con,$q);

						$q = "SELECT id FROM artifacts WHERE discordUserID = '$userID';";
						$r2 = mysqli_query($con,$q);
						if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {

								$q = "UPDATE artifacts SET scrap = scrap + $scrap,common = common + $common,uncommon = uncommon + $uncommon,rare = rare + $rare,ultrarare = ultrarare + $ultrarare WHERE discordUserID = '$userID';";
								$r2 = mysqli_query($con,$q);
								echo "success,".$ultrarare.",".$rare.",".$uncommon.",".$common.",".$scrap;
						} else{
								$q = "INSERT INTO artifacts (discordUserID, scrap, common, uncommon, rare, ultrarare)
								VALUES ($userID,$scrap,$common,$uncommon,$rare,$ultrarare);";
								$r2 = mysqli_query($con,$q);
								echo "success,".$ultrarare.",".$rare.",".$uncommon.",".$common.",".$scrap;
						}
				break;



				case "artifactSell":


					$q = "SELECT scrap,common,uncommon,rare,ultrarare FROM artifacts WHERE discordUserID = '$userID';";
					$r2 = mysqli_query($con,$q);
					if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
								$a = mysqli_fetch_assoc($r2);
								$scrapQuantity=stripslashes($a['scrap']);
								$commonQuantity=stripslashes($a['common']);
								$uncommonQuantity=stripslashes($a['uncommon']);
								$rareQuantity=stripslashes($a['rare']);
								$ultrarareQuantity=stripslashes($a['ultrarare']);

								$itemToSell = strtolower ($dataToSend);
								$price = 0;$totalPayout = 0;$itemQuantity = 0;

								$price = 0.1;
								$scrapTotalPayout = round($price * $scrapQuantity);
								$price = 2;
								$commonTotalPayout = $price * $commonQuantity;
								$price = 5;
								$uncommonTotalPayout = $price * $uncommonQuantity;
								$price = 10;
								$rareTotalPayout = $price * $rareQuantity;
								$price = 30;
								$ultrarareTotalPayout = $price * $ultrarareQuantity;

								$itemQuantity = $scrapQuantity + $commonQuantity + $uncommonQuantity + $rareQuantity + $ultrarareQuantity;
								$totalPayout = $scrapTotalPayout + $commonTotalPayout + $uncommonTotalPayout + $rareTotalPayout + $ultrarareTotalPayout;

								if($itemToSell == "all"){
										$q = "UPDATE artifacts SET scrap =0,common = 0,uncommon = 0,rare = 0,ultrarare = 0 WHERE discordUserID = '$userID';";
										$r2 = mysqli_query($con,$q);
										$q = "UPDATE users SET wallet = wallet + $totalPayout  WHERE discordUserID = '$userID';";
										$r2 = mysqli_query($con,$q);
										echo "success,".$itemQuantity.",".$totalPayout;
								}else{

											$crystals = 0;
											switch ($itemToSell) {
													case "scrap":
															$singlePayout = $scrapTotalPayout;
															$itemQuantity = $scrapQuantity;
													break;
													case "common":
															$singlePayout = $commonTotalPayout;
															$itemQuantity = $commonQuantity;
													break;
													case "uncommon":
															$singlePayout = $uncommonTotalPayout;
															$itemQuantity = $uncommonQuantity;
													break;
													case "rare":
															$singlePayout = $rareTotalPayout;
															$itemQuantity = $rareQuantity;
													break;
													case "ultrarare":
															$singlePayout = $ultrarareTotalPayout;
															$itemQuantity = $ultrarareQuantity;
													break;
											}
										$q = "UPDATE artifacts SET $itemToSell = 0 WHERE discordUserID = '$userID';";
										$r2 = mysqli_query($con,$q);
										$q = "UPDATE users SET wallet = wallet + $singlePayout  WHERE discordUserID = '$userID';";
										$r2 = mysqli_query($con,$q);
										echo "success,".$itemQuantity.",".$singlePayout;
								}
							}else{
									echo "failure";
							}
				break;



				case "buyDrink":
						$q = "UPDATE users SET wallet = wallet - $dataToSend  WHERE discordUserID = '$userID';";
						$r2 = mysqli_query($con,$q);
				break;

				case "sendAllAttacks":
						$message = "";
						$playerIDs = explode("|", $dataToSend);
						$hostileType = $dataToSend2;

						if (is_array($playerIDs)){
										foreach($playerIDs as $item) {
												$message .= "discordUserID = '".$item."' OR ";
										}
										$message = substr($message, 0, -4);
										//echo json_encode($playerIDs);
										//Get all user data
										$attackerStats= array();
										$q = "SELECT discordUserID,speed,maxHealth,health,strength FROM users WHERE $message;";
										$r2 = mysqli_query($con,$q);
										if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
											while ( $a = mysqli_fetch_assoc($r2) ) {
													$discordUserID=stripslashes($a['discordUserID']);
													$userHealth=stripslashes($a['health']);
													$userMaxHealth=stripslashes($a['maxHealth']);
													$userSpeed=stripslashes($a['speed']);
													$userStrength=stripslashes($a['strength']);
													$attackerStats[] = array('id'=>$discordUserID, 'maxHealth'=>$userHealth, 'health'=>$userHealth, 'speed'=>$userSpeed, 'strength'=>$userStrength, 'hitback'=>'');
											}
										}
										//Get enemy data
										$q = "SELECT hostiles.health,hostiles.maxHealth,hostiles.speed,hostiles.strength,hostiles.alive,hostiles.fled FROM hostiles WHERE hostileType = '$hostileType' ORDER BY id DESC LIMIT 1;";
										$r2 = mysqli_query($con,$q);
										if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
											while ( $a = mysqli_fetch_assoc($r2) ) {
													$hostileHealth=stripslashes($a['health']);
													$hostileMaxHealth=stripslashes($a['maxHealth']);
													$hostileSpeed=stripslashes($a['speed']);
													$hostileStrength=stripslashes($a['strength']);
													$hostileAlive=stripslashes($a['alive']);
													$hostileFled=stripslashes($a['fled']);
											}
										}

										//do all the damage
										$totalDamage = 0;
										$returnInfo= array();
										$query = "UPDATE users SET health = CASE discordUserID ";
										$queryIDs = "";
										for ($i=0;$i<count($attackerStats);$i++){
											//$message += $attackerStats[$i][0];
											//If bad guy is still alive, carry on.
											if($hostileHealth > $attackerStats[$i]['strength']){
														$totalDamage = $totalDamage + $attackerStats[$i]['strength'];
														$hitAmount = getEnemyDamage($hostileSpeed,$attackerStats[$i]['speed'],$hostileStrength);
														if($hitAmount > 0){
																if ($hitAmount >= $attackerStats[$i]['health']){$hitAmount = $attackerStats[$i]['health'];};
																$attackerStats[$i]['health'] = $attackerStats[$i]['health'] - $hitAmount;
																$attackerStats[$i]['hitback'] = $hitAmount;
																//$q = "UPDATE users SET health = health - $hitAmount WHERE discordUserID = '$userID' LIMIT 1";
																//$r2 = mysqli_query($con,$q);
														}
														$query .= " WHEN ".$attackerStats[$i]['id']." THEN ".$attackerStats[$i]['health'];
														$queryIDs .= $attackerStats[$i]['id'].",";
														$hhealth = $hostileHealth-$totalDamage;
														$returnInfo[] = array('hostileHealth'=>$hhealth.'|'.$hostileMaxHealth, 'atkDamage'=>$attackerStats[$i]['strength'], 'id'=>$attackerStats[$i]['id'], 'hitback'=>$hitAmount, 'userHealth'=>$attackerStats[$i]['health']."|".$attackerStats[$i]['maxHealth']);
											}else{
												//If the bad guy is not alive, finish up.
																	$q = "UPDATE hostiles SET health = 0 WHERE hostileType = '$hostileType' ORDER BY id DESC LIMIT 1";
																	$r2 = mysqli_query($con,$q);
																	$query .= " END
														WHERE discordUserID IN (".substr($queryIDs, 0, -1).");";
																	$r2 = mysqli_query($con,$query);
																	echo json_encode($returnInfo);
																	exit;
											}
										}
										//assemble the end of the query.
										$query .= " END
										WHERE discordUserID IN (".substr($queryIDs, 0, -1).");";
										$r2 = mysqli_query($con,$query);
										$q = "UPDATE hostiles SET health = health - $totalDamage WHERE hostileType = '$hostileType' ORDER BY id DESC LIMIT 1";
										$r2 = mysqli_query($con,$q);
											echo json_encode($returnInfo);
											exit;



					}else{
							echo "notArray";
							exit;
					}

				break;



			}
			break;

		case "getdata.php":
			switch ($dataType) {
				case "isConversionLocked"://Added per Ratstail91
				case "hasConvertedToday":
						$q = "SELECT id FROM userLog WHERE actionTime >= (DATE_SUB(now(), INTERVAL 30 DAY)) AND actionType = 'conversion' AND discordUserID = '$userID';";
						$r2 = mysqli_query($con,$q);
						if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
							echo 1;
							exit;
						} else{
							echo 0;
						}
				break;

				case "account":
						$q = "SELECT wallet FROM users WHERE discordUserID = '$userID';";
						$r2 = mysqli_query($con,$q);
						if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
							while ( $a = mysqli_fetch_assoc($r2) ) {
									$account=stripslashes($a['wallet']);
							}
							echo $account;
							exit;
						} else{
							echo "{ERROR}";
						}
				break;

				case "bank":
						$q = "SELECT account FROM factions WHERE discordRoleID = '$userID';";
						$r2 = mysqli_query($con,$q);
						if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
							while ( $a = mysqli_fetch_assoc($r2) ) {
									$account=stripslashes($a['account']);
							}
							echo $account;
							exit;
						} else{
							echo "{ERROR}";
						}
				break;

				case "victors":
						$q = "SELECT discordRoleName FROM factions WHERE isCurrentVictor = '1';";
						$r2 = mysqli_query($con,$q);
						if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
							while ( $a = mysqli_fetch_assoc($r2) ) {
									$discordRoleName=stripslashes($a['discordRoleName']);
							}
							echo $discordRoleName;
							exit;
						} else{
							echo "0";
						}
				break;
				case "hostileActive":
						$q = "SELECT id FROM hostiles WHERE alive = 1 AND health > 0 AND fled = 0;";
						$r2 = mysqli_query($con,$q);
						if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
							while ( $a = mysqli_fetch_assoc($r2) ) {
									$hostileID=stripslashes($a['id']);
							}
							echo $hostileID;
							exit;
						} else{
							echo "0";
						}
				break;
				case "lastHostileActive":
						$q = "SELECT id FROM hostiles ORDER BY id DESC LIMIT 1;";
						$r2 = mysqli_query($con,$q);
						if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
							while ( $a = mysqli_fetch_assoc($r2) ) {
									$hostileID=stripslashes($a['id']);
							}
							echo $hostileID;
							exit;
						} else{
							echo "0";
						}
				break;
				case "userStats":
						$q = "SELECT strength,speed,stamina,health,maxStamina,maxHealth,wallet,xp,lvl,statPoints,chests FROM users WHERE discordUserID = '$userID';";
						$r2 = mysqli_query($con,$q);
						if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
							while ( $a = mysqli_fetch_assoc($r2) ) {
									$strength=stripslashes($a['strength']);
									$speed=stripslashes($a['speed']);
									$stamina=stripslashes($a['stamina']);
									$health=stripslashes($a['health']);
									$maxHealth=stripslashes($a['maxHealth']);
									$maxStamina=stripslashes($a['maxStamina']);
									$wallet=stripslashes($a['wallet']);
									$xp=stripslashes($a['xp']);
									$recordedLVL=stripslashes($a['lvl']);
									$statPoints=stripslashes($a['statPoints']);
									$chests=stripslashes($a['chests']);
									$lvlbase = getLevelBase();
									$lvl = getLevel($xp,$lvlbase);
									$lvlpercent = getCurrentLevelProgress($xp,$lvl);
							}
							echo $strength.",".$speed.",".$stamina.",".$health.",".$maxStamina.",".$maxHealth.",".$wallet.",".$xp.",".$recordedLVL.",".$lvlpercent.",".$statPoints.",".$chests;
							exit;
						} else{
							echo "0";
						}
				break;

				case "artifactsGet":

						$q = "SELECT scrap,common,uncommon,rare,ultrarare FROM artifacts WHERE discordUserID = '$userID';";
						$r2 = mysqli_query($con,$q);
						if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
									$a = mysqli_fetch_assoc($r2);
									$scrapQuantity=stripslashes($a['scrap']);
									$commonQuantity=stripslashes($a['common']);
									$uncommonQuantity=stripslashes($a['uncommon']);
									$rareQuantity=stripslashes($a['rare']);
									$ultrarareQuantity=stripslashes($a['ultrarare']);
									echo "success,".$ultrarareQuantity.",".$rareQuantity.",".$uncommonQuantity.",".$commonQuantity.",".$scrapQuantity;
						} else{
							echo "failure";
						}

				break;


			}
			break;
	}

	if ($filename == "getdata.php") {
		/*
		if ($dataType == "crystals"){
			echo "343 Crystals in the vault!";
		} else{
			echo "No command found.";
		}


		if($featured==1){
				$sqlterms .= "AND userartistinfo.featured = 1 ";
		}
		if($search !== ''){
				$sqlterms .= "AND (userartistinfo.city LIKE '%".$search."%' OR userartistinfo.state LIKE '%".$search."%' OR userartistinfo.zip LIKE '%".$search."%')";
		}
					$q = "SELECT
					user.id as artistid, user.slug, user.displayname, user.picurl,
					userartistinfo.genre, userartistinfo.views,
					userartistinfo.contactemail, userartistinfo.phone, userartistinfo.address, userartistinfo.city, userartistinfo.state, userartistinfo.zip, userartistinfo.website
					FROM user, userartistinfo
					where user.active = 1
					AND user.id = userartistinfo.id
					AND user.type = 'store'
					".$sqlterms."
					ORDER BY user.created DESC, views DESC
					LIMIT 15";

					$r2 = mysqli_query($con,$q);
					$i=0;
						if ( $r2 !== false && mysqli_num_rows($r2) > 0 ) {
						  while ( $a = mysqli_fetch_assoc($r2) ) {

								$displayname = stripslashes($a['displayname']);
								$picurl = s3url('weedauthority-userimages', stripslashes($a['picurl']) );
								$phone = stripslashes($a['phone']);
								$address = stripslashes($a['address'])."<br />".stripslashes($a['city'])." ".stripslashes($a['state']).", ".stripslashes($a['zip']);
								$addressPlainText = stripslashes($a['address'])." ".stripslashes($a['city'])." ".stripslashes($a['state']).", ".stripslashes($a['zip']);
								$link = $baseurl."dispensary/".stripslashes($a['slug']);
								//$maplink ="https://maps.google.com/maps?q=". urlencode($addressPlainText);
								//$maplink = "https://www.google.com/maps/place/".urlencode($addressPlainText)."/";
								$maplink = stripslashes($a['website']);

								$statushtml = "<div id='build-" . $i . "' class='card'><div class='list-block beta-div'><div class='beta-title'>" . $displayname . "</div><img src='" . $picurl . "' class='beta-icon'>";
								$statushtml .=    "<div class='beta-version'><i class='fa fa-phone'></i> " . $phone . "</div><div class='beta-version'><i class='fa fa-map-marker'></i> " . $address . "</div><div class='beta-button'>";
								$statushtml .=    "<button class='button button-fill launchbutton launchbuttonactive launchbeta' data-url='" . $maplink . "'>View Shop</button>";
								//$statushtml .=    "<a href='geo://0,0?q=".$addressPlainText."' data-rel='external'><button class='button button-fill launchbutton launchbuttonactive launchbeta''>View Shop</button></a>";
								$statushtml .=    "</div><div style='clear:both;'></div><BR></div></div>";


							$array[$i]=array($statushtml );
							//$array[$i]=array($displayname,$picurl,$phone,$address,$link );
							//echo $title;
							$i++;
						  }
						}else{
								$array[0]=array("<BR /><BR /><center><i class='fa fa-search error-icon' aria-hidden='true'></i><BR /><BR />
								Looks like we can't find any shops matching that criteria. <BR />Try searching again!</center>");
								//echo json_encode($array);
						}

						echo json_encode($array);

		//}

		*/
	} else {
		//echo json_encode($array);
	}

	if($debug){
		$result .= "\n"."UID:".$userID;
	}

	mysqli_close($con);

	//Check if we had errors. If so, then status is error, otherwise success.
	$status = "success";
	if ($error->hasErrors()) {
		$status = "error";
	}

	$jsonArray = array("status"=>$status, "result"=>$result);//Set up the output JSON array.

	if ($status != "success") {//If we had errors, we need to add them to the output JSON array.
		array_push($jsonArray, json_encode($error->getAllErrors, 0, 1));//No Flags, One Level
	}

	//Output the JSON encoded data.
	echo json_encode($jsonArray, 0, 1);//No Flags, One Level

/* FROM sendPostData.php */

function generateStatFromLevel($level,$stat){
		$value = 0;
		if(strtolower($stat) === "str"){
					$value = (round((((($level + 1) * log10($level + 1)) / (0.02 * ($level + 1))) + 0.6) * 0.4)) -2;
					$value = round($value + (rand(-$value/10,$value/10)));
					if($level < 15){$value = round($value * 0.9);};
		}elseif(strtolower($stat) === "spd"){
					$value = (round((((($level + 1) * log10($level + 1)) / (0.02 * ($level + 1))) + 0.6) * 0.4)) -2 ; //round(rand(-2,2))
					$value = round($value + (rand(-$value/10,$value/10)));
					if($level < 15){$value = round($value * 0.9);};
		}elseif(strtolower($stat) === "hp"){
					$value = floor(50 + (30 * $level) + pow($level, 1.5));
		}elseif(strtolower($stat) === "stash"){
					$value = (round((((($level + 1) * log10($level + 1)) / (0.02 * ($level + 1))) + 0.6) * 0.1)) ;
					$value = rand(pow($value, 2.2),pow($value, 2.3));
					if($level < 15){$value = round($value * 0.7);};
		}

		return $value;
}

/* END FROM sendPostData.php */

/* SUGGESTED REPLACEMENT FOR generateStatFromLevel */
function generateStatFromLevel($level,$stat){
        $value = 0;
        switch (strtolower($stat) {
            case "str":
            case "spd":
                    $value = roundlog10($level)*21+16);
                    $value = round($value + (rand(-$value/10,$value/10)));
                    if($level < 15){$value = round($value * 0.9);};
        }elseif(strtolower($stat) === "hp"){
                    $value = floor(50 + (30 * $level) + pow($level, 1.5));
        }elseif(strtolower($stat) === "stash"){
                    $value = round(log10($level)*6+2.5 );
                    $value = rand(pow($value, 2.2),pow($value, 2.3));
                    if($level < 15){$value = round($value * 0.7);};
        }

        return $value;
}
/* END SUGGESTED REPLACEMENT */

?>