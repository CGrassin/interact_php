function interactphpSubmit(maxNameLen=30, maxCommentLen=2000) {
	document.getElementById('interactphp-alert').classList.add('hidden');
	var valid = true;
	var commentForm = document.getElementById("commentForm");

	if (commentForm.elements["name"].value.length <=0 || commentForm.elements["name"].value.length > maxNameLen) {
		valid = false; commentForm.elements["name"].classList.add("has-error");
	}
	else commentForm.elements["name"].classList.remove("has-error");

	if (commentForm.elements["message"].value.length <=0 || commentForm.elements["message"].value.length > maxCommentLen) {
		valid = false; commentForm.elements["message"].classList.add("has-error");
	}
	else commentForm.elements["message"].classList.remove("has-error");

	if (valid) {
		var elem   = commentForm.elements;
		var url    = commentForm.action;    
		var params = "";
		var value;

		for (var i = 0; i < elem.length; i++) {
			if (elem[i].tagName == "SELECT") {
				value = elem[i].options[elem[i].selectedIndex].value;
			} else {
				value = elem[i].value;                
			}
			params += elem[i].name + "=" + encodeURIComponent(value) + "&";
		}

		if (window.XMLHttpRequest) {
			xmlhttp=new XMLHttpRequest();
		} else { 
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}

		xmlhttp.open("POST",url);
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				if(xmlhttp.responseText.includes('ok')) {
					// Empty form and reload page
					commentForm.elements["name"].value = ""
					commentForm.elements["message"].value = ""
					location.reload();
				} else {
					document.getElementById('interactphp-alert').innerHTML = xmlhttp.responseText;
					document.getElementById('interactphp-alert').classList.remove('hidden');
				}
			}
		}; 
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xmlhttp.send(params);
	}
	return valid;
}

function recaptchaDisplay() {
	if (document.getElementById('google-recaptcha') != null)
		document.getElementById('google-recaptcha').classList.add('show');
}
