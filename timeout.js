var serverSide, serverWarning, sync, serverTimeout, secLeft, now;

$(function(){
	if (localStorage.loggedOut) {
		localStorage.clear("loggedOut");
	}
	if(sessionStorage.lastPage){
		window.location.href = sessionStorage.lastPage;
		sessionStorage.clear("lastPage");
	}
	check();
});

function check(){
	var cookieValue = document.cookie.replace(/PHPSESSID=/g, '');
	$.ajax({type: "POST", data: {cookie: document.cookie}, url: "refresh.php", success: function(data){
		var timeStamp = JSON.parse(data);
		timeStamp = timeStamp * 1000; 															//adjust timestamp difference between file timestamp and javascript date
		serverSide = new Date(timeStamp);
		clientSide = new Date();
		serverWarning = new Date(timeStamp+1200000);
		serverTimeout = new Date(timeStamp+1440000);
		sync = (serverSide - clientSide)+1000;													//get the time difference between client and server
		localStorage.setItem("warning", JSON.stringify(serverWarning.valueOf()));				//for other tabs to check against. sessionStorage is not accessible across tabs http://stackoverflow.com/questions/20325763/browser-sessionstorage-share-between-tabs
		localStorage.setItem("timeout", JSON.stringify(serverTimeout.valueOf()));
	}});	
	var timeoutCheck = setInterval(function(){
		if(localStorage.loggedOut){
			timedOut();
		}
	}, 1000);
	var timeoutInterval = setInterval(function(){
		//console.log(localStorage);
		now = new Date();
		now = new Date(now.valueOf()+sync);
		if(localStorage.loggedOut){																//needs to check for log out first, the next if loop will error and break
			timedOut();
		}else if(now >= JSON.parse(localStorage.warning)){
			$("#timeoutModal").modal();
			function pad(val) {
					return val > 9 ? val : "0" + val;
			}
			if(serverWarning.valueOf() !== localStorage.warning){
				serverWarning = JSON.parse(localStorage.warning);
				secLeft = Math.floor((JSON.parse(localStorage.timeout) - now)/1000);
			}else{
				secLeft = Math.floor((JSON.parse(localStorage.timeout) - serverWarning)/1000);
			}
			var timeLeft = setInterval(function () {
				now = new Date();
				now = new Date(now.valueOf()+sync);
				timeout = new Date(JSON.parse(localStorage.timeout));
				//console.log(localStorage);
				//console.log(timeout+" - "+ now + " = "+(timeout - now)/1000);
				if(now < localStorage.warning){													//check for other tabs refreshing session, if so ditch the warning
					$("#timeoutModal").modal("hide");
				}
				if(secLeft !== Math.floor((JSON.parse(localStorage.timeout) - now)/1000)){
					secLeft = Math.floor((JSON.parse(localStorage.timeout) - now)/1000);
				}
				document.getElementById("remaining-time-sec").innerHTML = pad(--secLeft % 60);
				document.getElementById("remaining-time-min").innerHTML = pad(parseInt((secLeft / 60), 10) % 60);
				if(now >= timeout){
					timedOut();
				}else if(localStorage.loggedOut){
					location.reload();
				}
			}, 1000);
		}
	}, 60000);
}

function timedOut(){
	$.ajax({type: "POST", url: "logout.php", success: function(){
		localStorage.clear("warning");
		localStorage.clear("timeout");
		localStorage.setItem("loggedOut", "true");
		sessionStorage.setItem("lastPage", window.location.href);
		location.reload();
	}});		
}

function refresh(){
	$("#timeoutModal").modal("hide");
	$.ajax({
		type: "POST", 
		url: "refresh.php", 
		success: function(data){
			//console.log(data);
			check();
			if(!data.match(/session refreshed/g)){
				sessionStorage.setItem("lastPage", window.location.href);
				localStorage.setItem("loggedOut", "true");
				localStorage.clear("warning");
				localStorage.clear("timeout");
				location.reload();
			}
		}
	});
}
