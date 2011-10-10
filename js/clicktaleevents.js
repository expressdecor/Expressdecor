


var ClickTaleTagBuffer = [];
function BufferedClickTaleTag(tag) {
  if(typeof ClickTaleTag == "function") {
     ClickTaleTag(tag);
  } else {
     ClickTaleTagBuffer.push(tag);
  }
}
setTimeout(function() {
  if(typeof ClickTaleTag == "function") {
    for(var i = 0; i < ClickTaleTagBuffer.length; i++) {
       ClickTaleTag(ClickTaleTagBuffer[i]);
    }
  } else {
     setTimeout(arguments.callee, 100);
  }
}, 100);

