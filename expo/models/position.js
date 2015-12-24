module.exports = (function() {

var position_db = [
  { id: 1, left: 319, top: 10, right: 394, bottom: 86 },    // 1
  { id: 2, left: 295, top: 56, right: 331, bottom: 91 },    // 2
  { id: 3, left: 378, top: 46, right: 414, bottom: 82 },    // 3
  { id: 4, left: 218, top: 79, right: 291, bottom: 116 },   // 4
  { id: 5, left: 295, top: 95, right: 332, bottom: 171 },   // 5
  { id: 6, left: 336, top: 75, right: 372, bottom: 107 },   // 6
  { id: 7, left: 336, top: 110, right: 372, bottom: 148 },  // 7
  { id: 8, left: 377, top: 87, right: 429, bottom: 137 },   // 8
  { id: 9, left: 197, top: 119, right: 235, bottom: 155 },  // 9
  { id: 10, left: 239, top: 120, right: 291, bottom: 171 },  // 10
  { id: 11, left: 336, top: 150, right: 373, bottom: 185 },  // 11
  { id: 12, left: 379, top: 144, right: 429, bottom: 193 },  // 12
  { id: 13, left: 167, top: 156, right: 250, bottom: 250 },  // 13
  { id: 14, left: 258, top: 176, right: 332, bottom: 211 },  // 14
  { id: 15, left: 336, top: 191, right: 373, bottom: 228 },  // 15
  { id: 16, left: 379, top: 197, right: 439, bottom: 233 },  // 16
  { id: 17, left: 130, top: 208, right: 167, bottom: 245 },  // 17
  { id: 18, left: 254, top: 216, right: 292, bottom: 250 },  // 18
  { id: 19, left: 295, top: 216, right: 331, bottom: 252 },  // 19
  { id: 20, left: 335, top: 231, right: 373, bottom: 269 },  // 20
  { id: 21, left: 376, top: 237, right: 413, bottom: 273 },  // 21
  { id: 22, left: 117, top: 249, right: 153, bottom: 286 },  // 22
  { id: 23, left: 158, top: 256, right: 232, bottom: 291 },  // 23
  { id: 24, left: 237, top: 256, right: 276, bottom: 292 },  // 24
  { id: 25, left: 279, top: 256, right: 332, bottom: 308 },  // 25
  { id: 26, left: 335, top: 272, right: 371, bottom: 309 },  // 26
  { id: 27, left: 375, top: 276, right: 417, bottom: 326 },  // 27
  { id: 28, left: 101, top: 290, right: 153, bottom: 340 },  // 28
  { id: 29, left: 158, top: 297, right: 195, bottom: 332 },  // 29
  { id: 30, left: 200, top: 296, right: 274, bottom: 333 },  // 30
  { id: 31, left: 278, top: 311, right: 316, bottom: 348 },  // 31
  { id: 32, left: 320, top: 313, right: 381, bottom: 373 },  // 32
  { id: 33, left: 60, top: 338, right: 99, bottom: 374 },    // 33
  { id: 34, left: 98, top: 345, right: 168, bottom: 412 },   // 34
  { id: 35, left: 160, top: 338, right: 233, bottom: 374 },  // 35
  { id: 36, left: 236, top: 338, right: 274, bottom: 374 },  // 36
  { id: 37, left: 279, top: 352, right: 316, bottom: 389},   // 37
  { id: 38, left: 33, top: 379, right: 96, bottom: 414 },    // 38
  { id: 39, left: 180, top: 378, right: 218, bottom: 469 },  // 39
  { id: 40, left: 221, top: 378, right: 274, bottom: 429 },  // 40
  { id: 41, left: 278, top: 394, right: 318, bottom: 429 },  // 41
  { id: 42, left: 320, top: 377, right: 373, bottom: 429 },  // 42
  { id: 43, left: 22, top: 419, right: 60, bottom: 455 },    // 43
  { id: 44, left: 63, top: 419, right: 100, bottom: 456 },   // 44
  { id: 45, left: 102, top: 418, right: 176, bottom: 455 },  // 45
  { id: 46, left: 222, top: 436, right: 260, bottom: 470 },  // 46
  { id: 47, left: 263, top: 434, right: 301, bottom: 470 },  // 47
  { id: 48, left: 303, top: 433, right: 364, bottom: 470 },  // 48
  { id: 49, left: 36, top: 459, right: 104, bottom: 496 },   // 49
  { id: 50, left: 108, top: 459, right: 144, bottom: 495 },  // 50
  { id: 51, left: 148, top: 459, right: 185, bottom: 494 },  // 51
  { id: 52, left: 193, top: 474, right: 269, bottom: 511 },  // 52
  { id: 53, left: 273, top: 475, right: 310, bottom: 511 },  // 53
  { id: 54, left: 313, top: 475, right: 352, bottom: 511 },  // 54
  { id: 55, left: 25, top: 499, right: 62, bottom: 535 },    // 55
  { id: 56, left: 65, top: 499, right: 101, bottom: 534 },   // 56
  { id: 57, left: 112, top: 500, right: 193, bottom: 590 },  // 57
  { id: 58, left: 196, top: 516, right: 236, bottom: 550 },  // 58
  { id: 59, left: 237, top: 517, right: 275, bottom: 550 },  // 59
  { id: 60, left: 278, top: 517, right: 347, bottom: 551 },  // 60
  { id: 61, left: 24, top: 540, right: 62, bottom: 590 },    // 61
  { id: 62, left: 65, top: 540, right: 118, bottom: 591 },   // 62
  { id: 63, left: 200, top: 555, right: 280, bottom: 590 },  // 63
  { id: 64, left: 283, top: 555, right: 320, bottom: 592 },  // 64
  { id: 65, left: 58, top: 594, right: 96, bottom: 630 },    // 65
  { id: 66, left: 99, top: 595, right: 137, bottom: 630 },   // 66
  { id: 67, left: 138, top: 593, right: 220, bottom: 630 },  // 67
  { id: 68, left: 223, top: 594, right: 260, bottom: 630 },  // 68
  { id: 69, left: 263, top: 595, right: 299, bottom: 631 },  // 69
  { id: 70, left: 86, top: 636, right: 124, bottom: 671 },   // 70
  { id: 71, left: 127, top: 636, right: 164, bottom: 686 },  // 71
  { id: 72, left: 167, top: 636, right: 220, bottom: 686 },  // 72
  { id: 73, left: 223, top: 636, right: 286, bottom: 672 },  // 73
  { id: 74, left: 141, top: 690, right: 180, bottom: 727 },  // 74
  { id: 75, left: 183, top: 690, right: 220, bottom: 725 },  // 75
  { id: 76, left: 223, top: 675, right: 260, bottom: 726 },  // 76
  { id: 77, left: 179, top: 729, right: 243, bottom: 766 },  // 77
  { id: 78, left: 202, top: 770, right: 242, bottom: 803 }   // 78
];

function size() { return position_db.length; }

function copy() { return position_db.slice(); }

function get(idx) { return position_db[idx-1]; } // idx : 1 - 78

function width(idx) {
  var pos = get(idx);
  return pos.right - pos.left;
}

function height(idx) {
  var pos = get(idx);
  return pos.bottom - pos.top;  
}

return {
  'size'  : size,
  'copy'  : copy,
  'get'   : get, 
  'width' : width,
  'height': height
}

})();