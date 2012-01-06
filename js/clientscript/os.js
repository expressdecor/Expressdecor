//Defines an Operating System level controller for the Web OS
//Author: Brian R Miedlar (c) 2006-2007 (miedlar.com)

var OS = Class.create();
OS.PageLoading = true;
OS.PageLoadComplete = function() {
    OS.PageLoading = false;
    $A(OS.BehaviourQueue).each(function(selectors) {
        OS.ApplyBehaviour(selectors);
    });
    OS.BehaviourQueue = [];
};
Event.observe(document, 'dom:loaded', function() {
    OS.PageLoadComplete();
});
OS.BehaviourQueue = [];
OS.RegisterBehaviour = function(selectors) {
    if(!OS.PageLoading) { OS.ApplyBehaviour(selectors); return; }
    OS.BehaviourQueue.push(selectors);    
}
OS.ApplyBehaviour = function(selectors) {
    $H(selectors).each(function(item) {
        var sKey = item.key;
        var iDelay = 0;
        var iToken = sKey.indexOf("!D");
        if (iToken > 0) {
            iDelay = parseFloat(sKey.substring(iToken + 2)) || 0;
            sKey = sKey.substring(0, iToken);
            iDelay = parseInt(iDelay);
        }
        $$(sKey).each(function(element) {
            if (!iDelay) { item.value(element); return; }
            item.value.delay(iDelay, element);
        });
    });
};


