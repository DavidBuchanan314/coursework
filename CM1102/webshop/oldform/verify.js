"use strict";

var CLASS_INVALID = "invalid";

var ccname = document.getElementById("ccname");
var ccnum  = document.getElementById("ccnum");
var ccexpm  = document.getElementById("ccexpm");
var ccexpy  = document.getElementById("ccexpy");
var cvc  = document.getElementById("cvc");

var helpP = document.getElementById("help");
var errDiv = document.getElementById("errors");

function nameErrors(name) {
	var err = [];
	if (/^\s*$/.test(name)) err.push("Cardholder name must not be empty!");
	return err;
}

function ccnumErrors(number) {
	var err = [];
	if (! /^[0-9]+$/.test(number)) err.push("Card number must be a number!");
	if (number.length < 16) err.push("Card number is too short!");
	return err;
}

function ccexpmErrors(month) {
	var err = [];
	if (! /^[0-9]+$/.test(month)) err.push("Expiry month must be a number!");
	month = parseInt(month);
	if (month < 1 || month > 12) err.push("Expiry month must be between 1 and 12!");
	return err;
}

function ccexpyErrors(year) {
	var err = [];
	if (! /^[0-9]+$/.test(year)) err.push("Expiry year must be a number!");
	year = parseInt(year);
	if (year < 2017) err.push("Expiry year must be in the future!");
	return err;
}

function cvcErrors(cvc) {
	var err = [];
	if (! /^[0-9]{3}$/.test(cvc)) err.push("Security code must be a 3 digit number!");
	return err;
}

var help = [
	[ccname, "Please enter the Cardholder's name"],
	[ccnum, "Card number must be 16 decimal digits"],
	[ccexpm, "Expiry month must be a number between 1 and 12"],
	[ccexpy, "Expiry year must be greater than or equal to 2017"],
	[cvc, "Security code must be a 3 digit integer"]
];

help.forEach(function(item){
	item[0].addEventListener("focus", function(){
		helpP.innerHTML = item[1];
	})
});

document.getElementById("ccform").addEventListener("submit", function(e) {
	var errors = [];
	errors = errors.concat(nameErrors(ccname.value));
	errors = errors.concat(ccnumErrors(ccnum.value));
	errors = errors.concat(ccexpmErrors(ccexpm.value));
	errors = errors.concat(ccexpyErrors(ccexpy.value));
	errors = errors.concat(cvcErrors(cvc.value));
	
	if (errors.length != 0) {
		while (errDiv.hasChildNodes()) {
			errDiv.removeChild(errDiv.lastChild);
		}
		errors.forEach(function(error){
			var p = document.createElement("p");
			p.innerHTML = error;
			errDiv.appendChild(p);
		});
		errDiv.classList.remove("hidden");
	} else {
		errDiv.classList.add("hidden");
		alert("Card details harvested successfully!");
	}
	e.preventDefault();
});
