var Canvas = require('canvas')
  , Image = Canvas.Image
  , canvas = new Canvas(400,400)
  , ctx = canvas.getContext('2d')
  , fs = require('fs');

//var text = '中文!\n另一行';

ctx.font = '80px Impact';
//ctx.rotate(.1);


ctx.fillStyle = '#FEF778';
ctx.fillRect(0,0, 400, 400);

ctx.fillStyle = '#000000';
//ctx.fillText(text, 15, 30);

//var ctx = demo.getContext('2d'),
var txt = 'Blogger 的成人內容政策要調整了，未來將不提供成人內容公開傳播了，只能設定成私人網誌。影響會是我得搬家了，還有以後要找資訊會變難找了。我還沒想好要怎麼應對，要搬到哪之類的。讓我想想再跟你們說',
    w = canvas.width,
    i = 0,
    x = 0,
    y = 0,
    lw,
    line = '';

var txt = '中文測試';

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
    y += 80;
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