

/////////////////////////////////////////////////////////////////////// MAIN
// const API = 'http://localhost/speedchat/public/api/';

// const serverIP = '127.0.0.1';

// const socketServer = 'http://'+serverIP+':3000';

const API = 'http://'+document.location.host+'/speedchat/public/api/';

const serverIP = document.location.host == 'localhost' ? '127.0.0.1' : document.location.host;

const socketServer = 'http://'+serverIP+':3000';

function getFileRouteServer(fileName){
	const token = localStorage.token;
	if(token && token != null)
		return API + fileName + ".php?token=" + token;
	else
		return API + fileName + ".php";
}

function isset(...variables){
	for (const variable of variables) {
		if(variable === 0)
			continue;
		if(typeof variable == 'undefined' || variable == null || variable == "")
			return false;
	}
	return true;
}

function snackbar(message) {
	var x = document.getElementById("snackbar");
	x.className = "show";
	x.innerHTML = message;
	setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
}

const toBase64 = file => new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = error => reject(error);
});

function action(status, successMessage = false, error = "خطا در عملیات لطفا دوباره تلاش کنید."){
	//console.log(status, status.status, typeof status == 'object')
	const statusTmp = typeof status == 'object' ? status.status : status;
	const errorTmp = typeof status == 'object' && isset(status.error) ? status.error : error;
	if(statusTmp == 1){
		if(successMessage != false){
			snackbar(typeof successMessage == 'string' ? successMessage : "موفقیت‌آمیز بود.");
		}
		return true;
	}
	else{
		snackbar(errorTmp);
	}
	return false;
}

function anchor(anchorLink){
    document.location = location.pathname + "#" + anchorLink;
}

function get_date(timestamp){
	const date = new Date(timestamp);
	
	const g_y=date.getFullYear();
	const g_m=date.getMonth()+1;
	const g_d=date.getDate();

	const dateConverted = gregorian_to_jalali(g_y,g_m,g_d);
	
	return {
		time: date.getHours()+":"+('0' + date.getMinutes()).slice(-2)+"",
		date: dateConverted[0]+"/"+dateConverted[1]+"/"+dateConverted[2]
	}
}

function gregorian_to_jalali(gy, gm, gd) {
	let g_d_m, jy, jm, jd, gy2, days;
	g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
	gy2 = (gm > 2) ? (gy + 1) : gy;
	days = 355666 + (365 * gy) + ~~((gy2 + 3) / 4) - ~~((gy2 + 99) / 100) + ~~((gy2 + 399) / 400) + gd + g_d_m[gm - 1];
	jy = -1595 + (33 * ~~(days / 12053));
	days %= 12053;
	jy += 4 * ~~(days / 1461);
	days %= 1461;
	if (days > 365) {
	  jy += ~~((days - 1) / 365);
	  days = (days - 1) % 365;
	}
	if (days < 186) {
	  jm = 1 + ~~(days / 31);
	  jd = 1 + (days % 31);
	} else {
	  jm = 7 + ~~((days - 186) / 30);
	  jd = 1 + ((days - 186) % 30);
	}
	return [jy, jm, jd];
}
