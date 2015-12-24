var Canvas = require('canvas')
  , Image = Canvas.Image
  , canvas = new Canvas(400,400)
  , ctx = canvas.getContext('2d')
  , fs = require('fs');

ctx.font = '50px Impact';
//ctx.rotate(.1);

ctx.textBaseline = 'top';
ctx.fillStyle = '#F1F170';
ctx.fillRect(0,0, 400, 400);

ctx.fillStyle = '#000000';
//ctx.fillText(text, 15, 150);

//var ctx = demo.getContext('2d'),
var txt = '我知道你一定沒問題的。',
    w = canvas.width,
    i = 0,
    x = 0,
    y = 50,
    lw,
    line = '';

ctx.textBaseline = 'top'; /// for simplicity in this demo
//ctx.font = '30px sans-serif';

for(; i < txt.length; i++) {
  lw = ctx.measureText(line).width;
  if (lw < w - ctx.measureText(txt[i]).width) {
    line += txt[i];
  } else {
    ctx.fillText(line, x, y);
    line = txt[i];
    x = 0;
    y += 50;
  }
}
if (line.length > 0) ctx.fillText(line, x, y);




/*
var te = ctx.measureText('Awesome!');
ctx.strokeStyle = 'rgba(0,0,0,0.5)';
ctx.beginPath();
ctx.lineTo(50, 102);
ctx.lineTo(50 + te.width, 102);
ctx.stroke();
*/

var out = fs.createWriteStream(__dirname + '/image.png');
var stream = canvas.pngStream();

stream.on('data', function(chunk) {
  out.write(chunk);
});

//console.log('<img src="' + canvas.toDataURL('image/png') + '" />');
