function formhash(form, password, element_name) {
	if (element_name === undefined) {
		element_name = "p";
	}
	// Crea un elemento di input che verrà usato come campo di output per la password criptata.
	var p = document.createElement("input");
	// Aggiungi un nuovo elemento al tuo form.
	form.appendChild(p);
	p.name = element_name;
	p.type = "hidden"
	p.value = hex_sha512(password.value);
	// Assicurati che la password non venga inviata in chiaro.
	password.value = "";
	// Come ultimo passaggio, esegui il 'submit' del form.
	form.submit();
}

function gAlert(gTitle, gText, gImage, gUrl, gTimeout, gClose, gReturnField, gReturnValue) {
	gTimeout = gTimeout || 3000;
	var unique_id = $.gritter.add({
		// (string | mandatory) the heading of the notification
		title: gTitle,
		// (string | mandatory) the text inside the notification
		text: gText,
		// (string | optional) the image to display on the left
		image: gImage,
		// (bool | optional) if you want it to fade out on its own or just sit there
		sticky: true,
		// (int | optional) the time you want it to be alive for before fading out
		time: '',
		// (string | optional) the class name you want to apply to that specific message
		class_name: 'my-sticky-class'
	});
	setTimeout(function(){
		$.gritter.remove(unique_id, {
			fade: true,
			speed: 'slow'
		});
		if (gUrl) {
			$(location).attr('href',gUrl);
		}
		if (gClose == 1) {
			if (gReturnValue) {
				window.opener.$('#' + gReturnField).val(gReturnValue);
				window.opener.$('#' + gReturnField).trigger('change');
				window.opener.document.getElementById(gReturnField).value = gReturnValue;
			} else {
				window.opener.location.reload(true);
			}
			close();
		}
	}, gTimeout)
	return false;
};

// Read a page's GET URL variables and return them as an associative array.
function getUrlVars()
{
	var vars = [], hash;
	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	for(var i = 0; i < hashes.length; i++) {
		hash = hashes[i].split('=');
		vars.push(hash[0]);
		vars[hash[0]] = hash[1];
	}
	return vars;
}

function windowpop(url, width, height, name, newtab) {
	var leftPosition, topPosition;
	//Allow for borders.
	leftPosition = (window.screen.width / 2) - ((width / 2) + 10);
	//Allow for title and status bars.
	topPosition = (window.screen.height / 2) - ((height / 2) + 50);
	//Open the window.
	if(!name){
		name = "popupwindow";
	}
	if(newtab){
		window.open(url, name);		
	} else {
		window.open(url, name, "status=no,height=" + height + ",width=" + width + ",resizable=yes,left=" + leftPosition + ",top=" + topPosition + ",screenX=" + leftPosition + ",screenY=" + topPosition + ",toolbar=no,menubar=no,scrollbars=yes,location=no,directories=no");
	}
}