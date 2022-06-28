class Functions {
	
	constructor() {
	}
	
	routes = {
		api: 'http://localhost/speedchat/public/api/',
	}
	
	serverDBToken = 'a53a18eb1fc7e82cece263b0e4265922c3e34511';
	
	getFileRouteServer(fileName, server='api'){
		try{
			const token = this.serverDBToken;
			if(token && token != null)
				return this.routes[server] + fileName + ".php?token=" + token;
			else
				return this.routes[server] + fileName + ".php";
		}
		catch(error) {
			console.log(error);
		}
	}
	
	cleanObject(obj){
		Object.keys(obj).forEach(key => (obj[key] === undefined || obj[key] === null || obj[key] === "") && delete obj[key])
		return obj;
	}
	
	isset(...variables){
		for (const variable of variables) {
			if(variable === 0)
				continue;
			if(typeof variable == 'undefined' || variable == null || variable == "")
				return false;
		}
		return true;
	}
}

module.exports = Functions;
