var generate = require('./generate.js');


//var txt = 'Blogger 的成人內容政策要調整了，未來將不提供成人內容公開傳播了，只能設定成私人網誌。影響會是我得搬家了，還有以後要找資訊會變難找了。我還沒想好要怎麼應對，要搬到哪之類的。讓我想想再跟你們說',
var long_text = 'Blogger 的成人內容政策要調整了，未來將不提供成人內容公開傳播了，只能設定成私人網誌。';
var short_text = '我得搬家了';

generate.sticker('40.png', long_text, 60, 60);
