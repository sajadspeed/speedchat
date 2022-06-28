
const socket = io(
	socketServer, 
	{
		extraHeaders: {
			token: localStorage.token
		}
	}
);

// socket.on("connect_error", (err) => {
// 	alert(`connect_error due to ${err.message}`);
//   });

let selectedContactID = 0;
let onlineUsers = [];

const peerServer = new Peer(undefined, {
	host: serverIP,
	port: '3001'
});

let peerID = '';
let peerIDTarget = '';

let callStatus = '';
let callUserIDTarget = 0;

let currentStream = false;
let currentCall = false;

peerServer.on("open", id => {
	peerID = id;
	console.log("My call ID:", peerID)
})

$(document).ready(()=>{
	loadContacts();
	getInfo();
	checkOnlineFirstTime();
})

document.addEventListener("keyup", event => {
    if (event.keyCode === 13) {
        sendMessage();
    }
});

function optionsMenuToggle() {
	$("#options-menu").slideToggle();
}

const getInfo = async () => {
	try {
		const url = getFileRouteServer('profile_get');
		const response = await axios.get(url);
		if(action(response.data)){
			document.getElementById("image-profile").src = response.data.info.image;
			$("#username").html(response.data.info.username);
		}
	} catch (error) {
		
	}
}

const checkOnlineFirstTime = async () => {
	try {
		const url = getFileRouteServer('online_get');
		const response = await axios.get(url);
		if(response.data.status == 1){
			const contacts = contacts_get();
			for (const contact of contacts) {
				const index = response.data.online_list.findIndex(item => item.user_id == contact.id);
				if(index > -1)
					setOnline(contact.id);
			}
		}
	} catch (error) {
		
	}
}

const loadContacts = () => {
	let html = "";
	let online = false;
	let active = false;
	
	const contacts = contacts_get();
	for (const contact of contacts) {
		if(onlineUsers.indexOf(contact.id) > -1)
			online = true;
		else
			online = false;
		
		if(selectedContactID == contact.id)
			active = true;
		else
			active = false;
			
		html += view_user(contact, false, true, online, true, active);
	}		
	$("#contacts").html(html);
}

const userAddSearch = async () => {
	try {
		const url = getFileRouteServer('user_get');
		const username = $("#input-username-search").val();
		if(username.length <= 0){
			$("#search-users-list").html("");
			return 1;
		}
		const response = await axios.get(url, {params: {username: username}});
		if(response.data.status > 0){
			let usersHtml = '';
			for (const user of response.data.users) {
				usersHtml += view_user(user, true, false);
			}		
			$("#search-users-list").html(usersHtml);
		}
		else{
			$("#search-users-list").html("");
		}
	} catch (error) {
		console.log(error)
	}
}

const addContact = async (userId)=>{
	try {
		const url = getFileRouteServer('user_get');
		const response = await axios.get(url, {params: {user_id: userId}});
		if(action(response.data, false)){
			if(response.data.info.online == 1)
				setOnline(userId);
			contacts_add({
				id: userId,
				username: response.data.info.username,
				image: response.data.info.image
			});
			loadContacts();
			anchor("#");
			return true;
		}
		else
			return false;
	} catch (error) {
		return false;
	}
}

const selectContact = (id) => {
	
	contacts_update(id, {unread: 0});
	loadContacts();
	
	if(selectedContactID > 0)
		$("#user_id_"+selectedContactID).removeClass("active");
		
	$("#user_id_"+id).addClass("active");
	
	selectedContactID = id;
	const contact = contacts_get(id);
	
	$("#username-text").html(contact.username);
	
	$("#start-chat").css("display", "none");
	$("main").css("display", "flex");
	
	// LOAD CHATS
	
	const messages = messages_get();
	for (const message of messages) {
		if(message.from == selectedContactID && message.ack != 2){
			let messageAck = {
				from: 'me',
				to: message.from,
				type: 'ack',
				ack: 2,
				time: message.time
			};
			messages_update(message.time, {ack: 2});
			socket.send(messageAck);
		}			
	}
	
	loadMessages();
}

const loadMessages = () => {
	let html = "";
	
	const messages = messages_get();
	for (const message of messages) {
		if(message.from == selectedContactID)
			html += view_message(messageAddTime(message));
		else if(message.to == selectedContactID)
			html += view_message(messageAddTime(message), true);
	}		
	$("#chats").html(html);
	chatsScrollEnd();
}


function openFileInput(id){
    var input = document.getElementById(id);
    input.click();
    input.addEventListener('change', function(event){
        if(typeof event.target.files[0] !== 'undefined'){
            let formData = new FormData(); 
			formData.append("image", event.target.files[0]);
			axios.post(getFileRouteServer("user_image_upload"), formData, {
				headers: {
                    'Content-Type': 'multipart/form-data'
                }
			}).then(function(response){
				if(response.data.status == 1){
					document.getElementById("image-profile").src = response.data.url;
				}
			})
			.catch(()=>{
				
			});
        }
    });
}

function selectFileToUpload(){
    var input = document.getElementById('file-upload');
    input.click();
    input.addEventListener('change', function(event){
        if(typeof event.target.files[0] !== 'undefined'){
            sendFile(event.target.files[0]);
        }
    });
}

function clearMessages() {
	$("#options-menu").slideUp();
	const messages = messages_get();
	let finalMessages = [];
	for (const message of messages) {
		if(message.from == selectedContactID || message.to == selectedContactID)
			continue;
		finalMessages.push(message);
	}
	messages_set(finalMessages);
	loadMessages();
}

function clearContact() {
	clearMessages();
	$("#options-menu").slideUp();
	const contacts = contacts_get();
	let finalContact = [];
	for (const contact of contacts) {
		if(contact.id == selectedContactID)
			continue;
		finalContact.push(contact);
	}
	
	contacts_set(finalContact);
	loadContacts();
	
	$("#start-chat").css("display", "flex");
	$("main").css("display", "none");
}

/// Socket

const sendMessage = () => {
	const message = document.getElementById("message").value;
	if(message.length > 0){
		
		const messageObject = {
			from: 'me',
			to: selectedContactID,
			type: 'string',
			value: message,
			time: new Date().getTime()
		};
		
		socket.send(messageObject);
		messages_add(messageObject);
		
		view_messages_append(view_message(messageAddTime(messageObject), true));
		
		document.getElementById("message").value = "";
	}
}

async function sendFile(file) {
	try{
		const fileBase64 = await toBase64(file);
		
		const messageObject = {
			from: 'me',
			to: selectedContactID,
			type: 'file',
			file_name: file.name,
			value: fileBase64,
			time: new Date().getTime(),
			ack: 0
		};
		
		socket.send(messageObject);
		messages_add(messageObject);
		
		view_messages_append(view_message(messageAddTime(messageObject), true));
	}
	catch(error){
		console.log(error);
	}
}

socket.on("online", user => {
	setOnline(user.user_id);
})

socket.on("offline", user => {
	if (callUserIDTarget == user.user_id){
		stopCall();
	}
	setOffline(user.user_id);
})

socket.on("message", async (message) => {
	
	if(message.type == 'ack'){
		if(message.from == selectedContactID){
			const messageElement = document.getElementById("message_ack_"+message.time);
			if(message.ack == 1){
				messageElement.className = "ri-check-double-line";
			}
			else if(message.ack == 2){
				messageElement.className = "ri-check-double-line";
				messageElement.style.color = '#3b38ff';
			}
		}
		messages_update(message.time, {ack: message.ack});
	}
	
	else{
		
		let messageAck = {
			from: 'me',
			to: message.from,
			type: 'ack',
			ack: 1,
			time: message.time
		};
		
		messages_add(message);
		
		if(message.from == selectedContactID){
			view_messages_append(view_message(messageAddTime(message)));
			
			messageAck.ack = 2;
		}
		else{
			const contacts = contacts_get();
			const contactIndex = contacts.findIndex(item => item.id == message.from);
			if(contactIndex < 0){ // Contact not exist
				await addContact(message.from);
				contacts_update(message.from, {unread: 1});
				loadContacts();
			}
			else{
				contacts_update(message.from, {unread: contacts[contactIndex].unread > 0 ? contacts[contactIndex].unread + 1 : 1});
				loadContacts();
			}
		}
		
		socket.send(messageAck);
	}
})

///

function setOffline(userId) {
	const index = onlineUsers.indexOf(parseInt(userId));
	//console.log("Offline", userId, "Index:", index);
	if (index > -1) {
		onlineUsers.splice(index, 1);
	}
	//console.log(onlineUsers)
	loadContacts();
}

function setOnline(userId) {
	const index = onlineUsers.indexOf(parseInt(userId));
	//console.log("Online", userId, "Index:", index);
	if (index < 0) {
		onlineUsers.push(parseInt(userId));
	}
	//console.log(onlineUsers)
	loadContacts();
}

function saveBase64(base64File, fileName){
	const linkSource = `${base64File}`;
	const downloadLink = document.createElement("a");
	downloadLink.href = linkSource;
	downloadLink.download = fileName;
	downloadLink.click();
}

function view_user(userObject, addIcon = false, userId = true, online = false, selectUserClick = false, active = false) {
	return '<div class="user-cart '+(active? 'active' : '')+' '+(online? 'online' : '')+'" '+(userId ? 'id="user_id_'+(userObject.id ? userObject.id : userObject.user_id)+'"' : '')+' '+(selectUserClick ? 'onclick="selectContact('+(userObject.id ? userObject.id : userObject.user_id)+')"' : '')+'>'+
				'<div class="online-badge"></div>'+
				'<img src="'+(userObject.image ? userObject.image : userObject['user.image'])+'">'+
				'<span class="username">'+(userObject.username ? userObject.username : userObject['user.username'])+'</span>'+
				'<span class="count-badge" '+(userId ? 'id="user_id_'+(userObject.id ? userObject.id : userObject.user_id)+'_count"' : '')+' style="display: '+(userObject.unread > 0 ? 'block' : 'none')+';">'+(userObject.unread ? userObject.unread : '')+'</span>'+
				(addIcon ? '<i class="ri-add-fill icon-edit" onclick="addContact('+(userObject.id ? userObject.id : userObject.user_id)+')"></i>' : '')+
			'</div>';
}

function view_message(messageObject, me = false) {
	let ackIcon = '<i class="ri-check-line" id="message_ack_'+messageObject.timestamp+'"></i>';
	if(messageObject.ack == 1)
		ackIcon = '<i class="ri-check-double-line" id="message_ack_'+messageObject.timestamp+'"></i>';
	else if (messageObject.ack == 2)
		ackIcon = '<i class="ri-check-double-line" style="color: #3b38ff" id="message_ack_'+messageObject.timestamp+'"></i>';
	
	if(messageObject.type == 'string')
		return '<div class="chat-card '+(me?'me':'')+'"><span class="message">'+messageObject.value+'</span><div class="footer"><span class="date">'+messageObject.time+'</span>'+(me?ackIcon:'')+'</div><span class="date-date">'+messageObject.date+'</span></div>';
	else if(messageObject.type == 'file')
		return  '<div class="chat-card-file '+(me?'me':'')+'">'+
					'<div class="container">'+
					'	<i class="ri-file-line file-icon"></i>'+
					'	<span class="file-name">'+messageObject.file_name+'</span>'+
					'	<i class="ri-download-line download-icon" onclick="saveBase64(\''+messageObject.value+'\', \''+messageObject.file_name+'\')"></i>'+
					'</div>'+
					'<span class="date">'+messageObject.time+'</span>'+
				'</div> '
}

function view_messages_append(messageHtml) {
	$("#chats").html($("#chats").html() + messageHtml);
	chatsScrollEnd();
}

function chatsScrollEnd() {
	$('#chats').scrollTop(parseInt($('#chats')[0].scrollHeight));
}

function messageAddTime(message) {
	let messageTmp = message;
	const time = get_date(parseInt(message.time)).time;
	const date = get_date(parseInt(message.time)).date;
	
	messageTmp.timestamp = message.time;
	messageTmp.time = time;
	messageTmp.date = date;
	return messageTmp;
}

function contacts_get(id = 0) {
	const contacts = localStorage.contacts ? JSON.parse(localStorage.contacts) : [];
	if(id > 0)
		return contacts.find(item => item.id == id);
	return contacts;
}

function contacts_set(contacts) {
	localStorage.contacts = JSON.stringify(contacts);
}

function contacts_add(contact) {
	if(!isset(contacts_get(contact.id))){
		let contacts = contacts_get();
		contacts.push(contact);
		contacts_set(contacts);
	}
}

function contacts_update(id, params) {
	let contacts = contacts_get();
	let contact = contacts_get(id);
	const contactIndex = contacts.findIndex(item => item.id == id);
	
	for (const key of Object.keys(params)) {
		contact[key] = params[key];
	}
	
	contacts[contactIndex] = contact;
	contacts_set(contacts);
	
}

function messages_get(id = 0) {
	const messages = localStorage.messages ? JSON.parse(localStorage.messages) : [];
	if(id > 0)
		return messages.find(item => item.time == id);
	return messages;
}

function messages_set(messages) {
	localStorage.messages = JSON.stringify(messages);
}

function messages_add(message) {
	let messages = messages_get();
	messages.push(message);
	messages_set(messages);
}

function messages_update(id, params) {
	let contacts = messages_get();
	let contact = messages_get(id);
	const contactIndex = contacts.findIndex(item => item.time == id);
	
	for (const key of Object.keys(params)) {
		contact[key] = params[key];
	}
	
	contacts[contactIndex] = contact;
	messages_set(contacts);
	
}

// Call

function videoCallRequest() {
	const userId = selectedContactID;
	if(onlineUsers.indexOf(userId) < 0){
		snackbar("کاربر مورد نظر آنلاین نیست و امکان برقراری تماس وجود ندارد.");
		return false;
	}
	const user = contacts_get(userId);
	$("#call-request-image").attr("src", user.image);
	$("#call-request-username").html(user.username);
	document.getElementById('call-request').style.display = "flex";
	
	callStatus = 'caller';
	
	socket.emit('call-request', userId, peerID);
}

socket.on('call-request-ask', async (userIdParam, peerIdParam) => {
	const url = getFileRouteServer('user_get');
	const response = await axios.get(url, {params: {user_id: userIdParam}});
	
	callStatus = 'responser';
	
	peerIDTarget = peerIdParam;
	callUserIDTarget = userIdParam;
	
	if(action(response.data, false)){
		$("#call-answer-image").attr("src", response.data.info.image);
		$("#call-answer-username").html(response.data.info.username);
		document.getElementById('call-answer').style.display = "flex"
	}
})

function callRequestReject(me = false) {
	const userId = me ? selectedContactID : callUserIDTarget;
	socket.emit('call-request-reject', userId);
}

function callAnswer() {
	socket.emit('call-answer', callUserIDTarget, peerID);
}

socket.on('call-answer', (userIdParam, peerIdParam) => {
	$("#call").slideDown();
	$("#call-request").css("display", 'none');
	$("#call-answer").css("display", 'none');
	startCall(peerIdParam);
})

socket.on('call-request-reject', () => {
	document.getElementById('call-answer').style.display = "none";
	document.getElementById('call-request').style.display = "none";
})

peerServer.on('close', stopCall);
peerServer.on('error', stopCall);
peerServer.on('disconnected', stopCall);

function startCall(peerIdParam) {
	navigator.getUserMedia({video: {width: $(document).width(), height: $(document).height()}, audio: true}, stream => {
		
		document.getElementById('video-me').srcObject = stream;
		currentStream = stream;
		
		const call = peerServer.call(peerIdParam, stream);
		currentCall = call;
		
		call.on('stream', remoteStream => {
			document.getElementById('video-remote').srcObject = remoteStream;	
		});
		
		call.on('close', stopCall);
		call.on('error', stopCall);
		
		// peerServer.on('close', stopCall);
		// peerServer.on('error', stopCall);
		// peerServer.on('disconnected', stopCall);
		
	  }, function(err) {
		  alert(err);
		console.log('Failed to get local stream' ,err);
	});
}

peerServer.on('call', call => {
	
	$("#call").slideDown();
	$("#call-request").css("display", 'none');
	$("#call-answer").css("display", 'none');
	navigator.getUserMedia({video: {width: $(document).width(), height: $(document).height()}, audio: true}, stream => {
	
	document.getElementById('video-me').srcObject = stream;
	currentStream = stream;
	currentCall = call;
	
	call.answer(stream); 
	call.on('stream', remoteStream => {
		document.getElementById('video-remote').srcObject = remoteStream;	
	});
	
	call.on('close', stopCall);
	call.on('error', stopCall);
	
	// peerServer.on('close', stopCall);
	// peerServer.on('error', stopCall);
	// peerServer.on('disconnected', stopCall);
	
	}, function(err) {
		alert(err);
		console.log('Failed to get local stream' ,err);
	});
});

function callFinish() {
	const userId = callStatus == 'caller' ? selectedContactID : callUserIDTarget;
	socket.emit('call-disconnect', userId);
}

socket.on('call-disconnect', stopCall)

function callMicToggle(_this) {
	currentStream.getTracks().forEach(track => {
        if (track.kind == 'audio'){
			if(track.enabled == true){
				track.enabled = false;
				_this.style.backgroundColor = '#7f8c8d';
				document.getElementById('mic-icon').className = 'ri-mic-off-line';
			}
			else{
				track.enabled = true;	
				_this.style.backgroundColor = '#2980b9';
				document.getElementById('mic-icon').className = 'ri-mic-line';
			}
		}
    });
}

function callWebcamToggle(_this) {
	currentStream.getTracks().forEach(track => {
        if (track.kind == 'video'){
			if(track.enabled == true){
				track.enabled = false;
				_this.style.backgroundColor = '#7f8c8d';
				document.getElementById('webcam-icon').className = 'ri-camera-off-line';
			}
			else{
				track.enabled = true;	
				_this.style.backgroundColor = '#2980b9';
				document.getElementById('webcam-icon').className = 'ri-camera-line';
			}
		}
    });
}

function stopCall() {
	callStatus = '';
	$("#call-minimize-button").slideUp();
	$("#call").slideUp();
	currentStream.getTracks().forEach(track => {
        if (track.readyState == 'live') {
            track.stop();
        }
    });
}

function callMinimize(status) {
	if(status){
		$("#call").animate({bottom: "-100%", right: "-100%"}, 500);
		setTimeout(() => {
			$("#call").css("display", "none");
		}, 500);
		$("#call-minimize-button").css("display", "flex");
	}
	else{
		$("#call").css("display", "block")
		$("#call").animate({bottom: 0, right: 0}, 500);
		$("#call-minimize-button").css("display", "none");
	}
}