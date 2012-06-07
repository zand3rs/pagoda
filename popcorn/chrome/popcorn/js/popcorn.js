(function(obj) {

    obj.requestFileSystem = obj.webkitRequestFileSystem || obj.mozRequestFileSystem || obj.requestFileSystem;
    obj.BlobBuilder = obj.webkitBlobBuilder || obj.mozBlobBuilder || obj.BlobBuilder;
    obj.URL = obj.webkitURL || obj.mozURL || obj.URL;

    var fs = null;
    var error = '';

    function getLastError() {
        return error;
    }

    function fsEerrorHandler(e) {

        switch (e.code) {
            case FileError.QUOTA_EXCEEDED_ERR:
                error = 'QUOTA_EXCEEDED_ERR';
                break;
            case FileError.NOT_FOUND_ERR:
                error = 'NOT_FOUND_ERR';
                break;
            case FileError.SECURITY_ERR:
                error = 'SECURITY_ERR';
                break;
            case FileError.INVALID_MODIFICATION_ERR:
                error = 'INVALID_MODIFICATION_ERR';
                break;
            case FileError.INVALID_STATE_ERR:
                error = 'INVALID_STATE_ERR';
                break;
            default:
                error = 'Unknown Error';
                break;
        }
    }

    function initialize() {
        obj.requestFileSystem(obj.PERSISTENT, 1024*1024*1024, function(filesystem) {
            fs = filesystem;
        }, fsEerrorHandler);
    }

    function createFolder(folders, onend, onerror) {
        if (! fs) {
            return onerror('File system unavailable!');
        }

        if (folders[0] == '.' || folders[0] == '') {
            folders.shift();
        }

        if (! folders.length) {
            return onend();
        }
    }

    function createFile(fpath, onend, onerror) {
        onend(file_entry);
    }

    function saveAs(fpath, data, onend, onerror) {
        onend(fpath);
    }

    function download(url, folder, onend, onerror) {
        try {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', url, false); // Note: synchronous
            xhr.responseType = 'arraybuffer';
            xhr.send();

            var fname = url.replace(/.*\//, '');
            var fpath = folder + '/' + fname;

            writeToFile(file_path, xhr.response, function(file_path) {
                onend(file_path);
            });
        } catch(e) {
            onerror('XHR Error: ' + e.toString());
        }
    }

    obj.popcorn = {
        test : function(onend) {
            onend('Hello World!');
        },
        download : download
    };

})(this);
