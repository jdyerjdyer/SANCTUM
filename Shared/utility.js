//initialize the exports
exports = module.exports = {};

//GetFooterCommands - Gets footer commands for botspam channel commands
//commandArray - the array of possible commands to use
//excludeCommand (optional) - the command to filter out
exports.GetFooterCommands = function(commandArray, excludeCommand = null) {
	let filteredCommandList = commandArray.filter(command => command !== excludeCommand);

	let returnText = "";
	filteredCommandList.forEach(command => {
		if (returnText.length !== 0) { //if this isn't the first command, prepend the separator to this command
			returnText += " | ";
		}
		returnText += command;
	});

	return returnText;
}

//IsAdmin
//client - discord.js client
//user - discord.js user OR username
exports.IsAdmin = function(client, user) {
	//handle user strings
	if (typeof(user) === "string") {
		user = client.users.find(item => item.username === user);
	}

	let guild = client.guilds.get(process.env.SANCTUM_ID);

	return guild.members.get(user.id).roles.find(role => role.name === process.env.ADMIN_ROLE) != null;
}

//SplitArray
//arr - 1 dimensional array to split into chunks
//chunkSize - the size of the chunks in the resulting array
exports.SplitArray = function(arr, chunkSize) {
	// http://www.frontcoded.com/splitting-javascript-array-into-chunks.html
	let groups = [];
	for (let i = 0; i < arr.length; i += chunkSize) {
		groups.push(arr.slice(i, i + chunkSize));
	}
	return groups;
}

//CheckValidDisplay
//client - discord.js client
//member - discord.js member OR username
//channel - discord.js channel OR channel name
//checkRole - check the member's role or not
exports.CheckValidDisplay = function(client, member, channel, checkRole) { //See if the bot should display its message
	//handle member strings
	if (typeof(member) === "string") {
		//get the member
		//NOTE: I think the bot needs to run inside a single server only, otherwise I don't know what will happen here due to the [0] part
		member = client.guilds.map(guild => guild.members.filter(mbr => mbr.user.username === member))[0].first();
	}

	//handle channel strings
	if (typeof(channel) === "string") {
		channel = client.channels.find(item => item.name === channel);
	}

	switch(client.user.username) {
		//NOTE: some copy/paste here that could be fixed
		case process.env.GROUP_A_LEADER_NAME:
			if (checkRole) {
				return channel.id == process.env.GROUP_A_CHANNEL_ID && member.roles.has(process.env.GROUP_A_ROLE);
			} else {
				return channel.id == process.env.GROUP_A_CHANNEL_ID;
			}

		case process.env.GROUP_B_LEADER_NAME:
			if (checkRole) {
				return channel.id == process.env.GROUP_B_CHANNEL_ID && member.roles.has(process.env.GROUP_B_ROLE);
			} else {
				return channel.id == process.env.GROUP_B_CHANNEL_ID;
			}

		case process.env.GROUP_C_LEADER_NAME:
			if (checkRole) {
				return channel.id == process.env.GROUP_C_CHANNEL_ID && member.roles.has(process.env.GROUP_C_ROLE);
			} else {
				return channel.id == process.env.GROUP_C_CHANNEL_ID;
			}

		case process.env.GHOST_NAME: {
			// JSON
			let rooms = require('../TextAdv/rooms.json'); //TODO: should this be here?
			let roomExists = false;

			// Loops for all rooms
			rooms.rooms.forEach(room => {
				if (channel.id === rooms[room].channel) {
					roomExists = true;
				}
			});

			//if the given room exists
			if (roomExists) {
				return true;
			}

			//DEBUGGING: test channel
			if (channel.id == process.env.TEST_CHANNEL_ID) {
				return true;
			}

			//otherwise
			return false;
		}
		default:
			//default value
			return false;
	}
}
