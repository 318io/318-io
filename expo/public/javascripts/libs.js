$(function () {

  /*
     Get corrdinates
  */
  function getPosition(element) {
    var xPosition = 0;
    var yPosition = 0;
    while(element) {
      xPosition += (element.offsetLeft - element.scrollLeft + element.clientLeft);
      yPosition += (element.offsetTop - element.scrollTop + element.clientTop);
      element = element.offsetParent;
    }

    return { x: xPosition, y: yPosition };
  }


  function getClickPosition(e) {
    var parentPosition = getPosition(e.currentTarget);

    var xPosition = e.clientX - parentPosition.x;
    var yPosition = e.clientY - parentPosition.y;

    console.log('here');

    alert('X: ' + xPosition + ', ' + 'Y: ' + yPosition);
  }

  //$('.sticker').on('click', getClickPosition);


});
