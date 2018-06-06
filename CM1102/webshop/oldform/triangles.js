"use strict";

var c = document.getElementById("triangles");
var ctx = c.getContext("2d");

var vertices, originalVertices, triangles, aspect;

var mousePos = {x:0.5,y:0.5};

//ctx.globalCompositeOperation = 'destination-atop';

function getRGBColor(fraction) {
	var r = fraction;
	var g = 0.6;
	var b = 0.7;
	return "rgb(" + Math.round(r*255) + ", " + Math.round(g*255) + ", " + Math.round(b*255) + ")";
}

function getMousePos(canvas, e) {
	var rect = c.getBoundingClientRect();
	return {
		x: (e.clientX - rect.left)/c.width,
		y: (e.clientY - rect.top)/c.height
	};
}

function renderVertices() {
	vertices.forEach( function(vertex) {
		ctx.fillRect(vertex[0]*c.width, vertex[1]*c.height, 4, 4);
	});
}

function renderTriangles() {
	triangles.forEach( function(triangle) {
		var v0 = vertices[triangle[0]];
		ctx.beginPath();
		ctx.moveTo(v0[0]*c.width, v0[1]*c.height)
		var average = 0;
		for (var i=triangle.length-1; i >= 0; i--) {
			var v = vertices[triangle[i]];
			ctx.lineTo(v[0]*c.width, v[1]*c.height);
			average += v[1];
		}
		ctx.fillStyle = getRGBColor(average/triangle.length);
		ctx.closePath();
		ctx.fill();
		ctx.strokeStyle = "rgba(255, 255, 255, 0.5)";
		ctx.stroke();
	});
}

function render() {
	ctx.clearRect(0, 0, c.width, c.height);
	renderTriangles();
}

function update() {
	for (var i=0; i< vertices.length; i++) {
		var dx = mousePos.x - originalVertices[i][0];
		var dy = mousePos.y - originalVertices[i][1];
		dy /= aspect;
		var dist = 1/Math.sqrt(dx*dx+dy*dy);
		vertices[i][0] = originalVertices[i][0] - dx*dist*0.05;
		vertices[i][1] = originalVertices[i][1] - dy*dist*0.05;
	}
}

function loop() {
	update();
	render();
	window.requestAnimationFrame(loop);
}

function init() {
	c.width = window.innerWidth;
	c.height = window.innerHeight;
	
	vertices = [];
	triangles = [];

	aspect = (c.width/c.height);

	var w = 20;
	var h = Math.round(w / (aspect * 0.86602540378));

	for (var y = 0; y < h; y += 1) {
		for (var x = 0; x < w; x += 1) {
			var x1 = x/(w-1.5);
			var y1 = y/(h-1);
			var offset = (y % 2)/(2*w);
			if (x < w-1 && y < h-1) {
				if (offset != 0) {
					triangles.push([y*w+x, y*w+x+1, y*w+x+w]);
					triangles.push([y*w+x+w+1, y*w+x+1, y*w+x+w]);
				} else {
					triangles.push([y*w+x+w+1, y*w+x+0, y*w+x+w]);
					triangles.push([y*w+x+w+1, y*w+x+0, y*w+x+1]);
				}
			}
			vertices.push( [x1-offset, y1] );
		}
	}

	originalVertices = [];
	for (var i = 0; i < vertices.length; i++) originalVertices.push( vertices[i].slice(0) );
}

window.addEventListener("mousemove", function(e) {
	mousePos = getMousePos(c, e);
}, false);

window.addEventListener("resize", init);

init();
loop();
