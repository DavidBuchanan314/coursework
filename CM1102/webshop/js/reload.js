function basketadd(e) {
	e.childNodes[1].disabled = true; // janky AF
	e.childNodes[1].value = "Pending...";
	var xhr = new XMLHttpRequest();
	xhr.open("POST", e.getAttribute("action"));
	xhr.onload = function() {
		if (this.status === 200) {
			document.body.innerHTML = (this.responseXML.body.innerHTML); // janky AF
			init(); // restart canvas animation
		}
	};
	var data = new FormData(e);
	xhr.responseType = "document";
	xhr.send(data);
	return false;
}
