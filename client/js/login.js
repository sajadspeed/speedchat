const login = async ()=>{
	try{
		$("#login-button").html("...");
		const params = {
			username: $("#username").val(),
			password: $("#password").val(),
		}
		const url = getFileRouteServer('login');
		const response = await axios.post(url, params);
		console.log(response.data);
		$("#login-button").html("ورود");
		if(action(response.data, false)){
			localStorage.token = response.data.token;
			document.location = "main.html";
		}
	}
	catch(error){
		console.log(error)
	}
}