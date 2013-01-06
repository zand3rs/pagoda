//-------------------------------------------------------------------------
//-------------------------------------------------------------------------

function downloadHandler(data, onend, onerror) {
    var url = data.url;
    var dest_dir = data.dest_dir;
    var index = data.index;

    popcorn.download(url, dest_dir, function(fpath) {
        console.log('download successful: ' + fpath);
        popcorn.extract(fpath, dest_dir, function(fpath) {
            console.log('extract successful: ' + fpath);
            popcorn.getFsUrl(index, function(url) {
                console.log('index: ' + url);
                onend(url);
            }, function(e) {
                console.log('index error: ' + index + ': ' + e.toString());
                onerror(e);
            });
        }, function(e) {
            console.log('extract failed: ' + fpath);
            onerror(e);
        });
    }, onerror);
}

//-------------------------------------------------------------------------

function messageHandler(request, sender, sendResponse) {
    console.log("messageHandler got: ", request);
    
    var cmd = request.cmd;
    var data = request.data;

    switch (cmd) {
        case 'download':
            downloadHandler(data, function(url) {
                sendResponse({cmd: cmd, status: 'success', data: url});
            }, function(e) {
                sendResponse({cmd: cmd, status: 'error', data: e});
            });
            break;
        case 'test':
            sendResponse('test');
            break;
        default:
            sendResponse({cmd: cmd, status: 'error', data: 'Unknown command!'});
    }
    
    return true;
}

//-------------------------------------------------------------------------
//-------------------------------------------------------------------------

popcorn.initialize(function() {
}, function(e) {
});

chrome.extension.onMessage.addListener(messageHandler);
  
//-------------------------------------------------------------------------
//-------------------------------------------------------------------------
