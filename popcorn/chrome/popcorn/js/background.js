//-------------------------------------------------------------------------
//-------------------------------------------------------------------------

function downloadHandler(data, onend, onerror) {
    var id = data.id;
    var url = data.url;
    var src_url = data.src_url;
    var dest_dir = data.dest_dir;
    var index = data.index;

    popcorn.download(url, dest_dir, function(fpath) {
        console_log('download successful: ' + fpath);
        popcorn.extract(fpath, dest_dir, function(fpath) {
            console_log('extract successful: ' + fpath);
            popcorn.getFsUrl(index, function(url) {
                console_log('index: ' + url);
                onend(url);
            }, function(e) {
                console_log('index error: ' + index + ': ' + e.toString());
                onerror(e);
            });
        }, function(e) {
            console_log('extract failed: ' + fpath);
            onerror(e);
        });
    }, function(e) {
        if (parseInt(e, 10) == 405) {
            onerror('Unverified mobile number');
        } else {
            onerror(e);
        }
    });
}

//-------------------------------------------------------------------------

function messageHandler(request, sender, sendResponse) {
    console_log("messageHandler got: ", request);
    
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
