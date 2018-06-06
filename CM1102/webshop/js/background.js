"use strict";

var c = document.getElementById("bgcanvas");
var ctx = c.getContext("2d");

function init() {
	c = document.getElementById("bgcanvas");
	ctx = c.getContext("2d");
	//ctx.globalCompositeOperation = "destination-atop";
	c.width = c.clientWidth;
	c.height = c.clientHeight;
}

function loop() {
	ctx.clearRect(0, 0, c.width, c.height);
	ctx.strokeStyle = "rgba(0, 0, 255, 0.2)";
	ctx.lineWidth = 3;
	
	var speed = 1000;
	var offset = (Date.now() % speed) / speed;
	
	var near = 0.1;
	var far = 10;
	
	for (var redraws = 0; redraws < 2; redraws++) {
		ctx.beginPath();
		var horizontals = 10;
		for (var i=offset; i < horizontals; i++) { // horizontal lines
			var dist = far - (i/horizontals) * (far - near);
			var height = (Math.atan(far/1.5)-Math.atan(dist/1.5)) / (Math.PI/2 - Math.atan(near/1.5)) * c.height;
			if (height > c.height) continue;
			var farWidth = c.width/far;
			var nearWidth = c.width/near;
			var realDist = 0.5 * ( (height/c.height) * (nearWidth - farWidth) + farWidth ); // maths is hard...
			ctx.moveTo((c.width/2) - realDist, height);
			ctx.lineTo(realDist + (c.width/2), height);
		}
		
		ctx.stroke();
		ctx.beginPath();
		
		var verticals = 40;
		for (var i=0; i <= verticals; i++) { // vertical lines
			ctx.moveTo((i/verticals - 0.5) * (c.width/far) + (c.width/2), 0);
			ctx.lineTo((i/verticals - 0.5) * (c.width/near) + (c.width/2), c.height);
		}
		
		ctx.stroke();
		
		ctx.lineWidth = 1;
	}
	
	window.requestAnimationFrame(loop);
}

window.addEventListener("resize", init);
init();
loop();
