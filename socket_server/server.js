const { Server } = require("socket.io");
const axios = require("axios");
const Functions = require('./Function');

const fg = new Functions();

const io = new Server({cors: { origin: '*' } });

io.on("connection", async (socket) => {
	
	console.log("A client connected: "+socket.id);
	  
	const token = socket.handshake.headers.token;
	
	const response = await axios.get(fg.getFileRouteServer("socket_get_user"), {params: {user_token: token}});
	const userInfo = response.data.info;
	
	let messagesBuffer = await axios.get(fg.getFileRouteServer("socket_message_get"), {params: {user_id: userInfo.id}});
	
	if(messagesBuffer.data.status > 0){
		for (let message of messagesBuffer.data.messages) {
			message = JSON.parse(message.message);
			io.sockets.sockets.get(socket.id).emit("message", message);
		}
	}
	
	socket.on("message", async (message) => {
		let messageFinal = message;
		
		messageFinal.from = userInfo.id;
		//messageFinal.time = new Date().getTime()
		
		const responseTarget = await axios.get(fg.getFileRouteServer("socket_online_get"), {params: {user_id: messageFinal.to}});
		
		if(responseTarget.data.online == 1){
			const userInfoTarget = responseTarget.data.info;
			
			io.sockets.sockets.get(userInfoTarget.socket_id).emit("message", messageFinal);
		}
		else{ // Offline messages
			axios.get(fg.getFileRouteServer("socket_message_add"), {params: messageFinal});
		}
	})
	
	/// Call handling
	
	socket.on('call-request', async (user_id_target, peerId) => {
		const userTarget = await axios.get(fg.getFileRouteServer("socket_online_get"), {params: {user_id: user_id_target}});
		if(userTarget.data.online == 1){
			io.sockets.sockets.get(userTarget.data.info.socket_id).emit("call-request-ask", userInfo.id, peerId);
		}
	})
	
	socket.on('call-request-reject', async (user_id_target) => {
		const userTarget = await axios.get(fg.getFileRouteServer("socket_online_get"), {params: {user_id: user_id_target}});
		if(userTarget.data.online == 1){
			io.sockets.sockets.forEach(socketItem => {
				if(socketItem.id == userTarget.data.info.socket_id || socketItem.id == socket.id)
					socketItem.emit("call-request-reject", userInfo.id);
			})
			
			//(userTarget.data.info.socket_id).emit("call-request-reject", userInfo.id);
		}
	})
	
	
	socket.on('call-disconnect', async (user_id_target) => {
		const userTarget = await axios.get(fg.getFileRouteServer("socket_online_get"), {params: {user_id: user_id_target}});
		if(userTarget.data.online == 1){
			io.sockets.sockets.forEach(socketItem => {
				if(socketItem.id == userTarget.data.info.socket_id || socketItem.id == socket.id)
					socketItem.emit("call-disconnect");
			})
			
			//(userTarget.data.info.socket_id).emit("call-request-reject", userInfo.id);
		}
	})
	
	socket.on('call-answer', async (user_id_target, peerId) => {
		const userTarget = await axios.get(fg.getFileRouteServer("socket_online_get"), {params: {user_id: user_id_target}});
		if(userTarget.data.online == 1){
			io.sockets.sockets.get(userTarget.data.info.socket_id).emit("call-answer", userInfo.id, peerId);
		}
	})
	
	///
	
	/// Online handle

	axios.get(fg.getFileRouteServer("socket_online_add"), {params: {
		user_id: userInfo.id,
		socket_id: socket.id
	}});
	
	io.emit("online", {user_id: userInfo.id});
	///
	
	/// Offline handle
	socket.on("disconnect", () => {
		console.log("A client disconnected: "+socket.id);
		
		axios.get(fg.getFileRouteServer("socket_online_delete"), {params: {
			socket_id: socket.id
		}});
		
		io.emit("offline", {user_id: userInfo.id})
	});	
	///
});

console.log("Server start at port: 3000");

io.listen(3000);
