(function(obj) {

    obj.requestFileSystem = obj.webkitRequestFileSystem || obj.mozRequestFileSystem || obj.requestFileSystem;
    obj.BlobBuilder = obj.WebKitBlobBuilder || obj.mozBlobBuilder || obj.BlobBuilder;
    obj.URL = obj.webkitURL || obj.mozURL || obj.URL;

    var fs = null;

    function fsEerrorHandler(e, onerror) {
        var error = '';

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

        if (onerror) onerror(error);
    }

    function initialize(onerror) {
        //obj.requestFileSystem(obj.PERSISTENT, 1024*1024*1024, function(filesystem) {
        obj.requestFileSystem(obj.TEMPORARY, 4*1024*1024, function(filesystem) {
            fs = filesystem;
        }, function(e) {
            fsEerrorHandler(e, onerror);
        });
    }

    function createDir(root_dir_entry, folders, onend, onerror) {
        if (folders[0] == '.' || folders[0] == '') {
            folders.shift();
        }

        if (! folders.length) {
            return onend(root_dir_entry);
        }

        var folder = folders.shift();
        root_dir_entry.getDirectory(folder, {create: true}, function(dir_entry) {
            //-- Recursively add the new subfolder...
            createDir(dir_entry, folders, onend, onerror);
        }, function(e) {
            fsEerrorHandler(e, onerror);
        });
    }

    function createFolder(folders, onend, onerror) {
        if (! fs) {
            return onerror('File system unavailable!');
        }
        createDir(fs.root, folders, onend, onerror);
    }

    function createFile(fpath, onend, onerror) {
        var folders = fpath.split('/');
        var file = folders.pop();

        createFolder(folders, function(dir_entry) {
            dir_entry.getFile(file, {create: true}, function(file_entry) {
                onend(file_entry);
            }, function(e) {
                fsEerrorHandler(e, onerror);
            });
        }, onerror);
    }

    function saveAs(fpath, data, onend, onerror) {
        createFile(fpath, function(file_entry) {
            file_entry.createWriter(function(file_writer) {
                file_writer.onwriteend = function(e) {
                    onend(fpath);
                };
                file_writer.onerror = function(e) {
                    onerror('Write failed: ' + e.toString());
                };

                try {
                    var bb = new obj.BlobBuilder();
                    bb.append(data);
                    file_writer.write(bb.getBlob());
                } catch (e) {
                    onerror('Write failed: ' + e.toString());
                }
            }, function(e) {
                fsEerrorHandler(e, onerror);
            });
        }, onerror);
    }

    function getFile(fpath, onend, onerror) {
        fs.root.getFile(fpath, {create: false}, function(file_entry) {
            onend(file_entry);
        }, function(e) {
            fsEerrorHandler(e, onerror);
        });
    }

    function fileExists(fpath, onend) {
        getFile(fpath, function(file_entry) {
            onend(true);
        }, function(e) {
            onend(false);
        });
    }

    function getFilePath(url, folder) {
        var fname = url.replace(/.*\//, '');
        var fpath = (folder || '.') + '/' + fname;
        return fpath;
    }

    function download(url, folder, onend, onerror) {
        try {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', url, false); // Note: synchronous
            xhr.responseType = 'arraybuffer';
            xhr.send();

            var fpath = getFilePath(url, folder);

            saveAs(fpath, xhr.response, onend, onerror);
        } catch(e) {
            onerror('XHR Error: ' + e.toString());
        }
    }

    function cached_download(url, folder, onend, onerror) {
        var fpath = getFilePath(url, folder);
        fileExists(fpath, function(exists) {
            if (exists) {
                onend(fpath);
            } else {
                download(url, folder, onend, onerror);
            }
        });
    }

    function extract(fpath, onend, onerror) {
        getFile(fpath, function(file_entry) {
            file_entry.file(function(file) {
                zip.createReader(new zip.BlobReader(file), function(zip_reader) {
                    zip_reader.getEntries(function(entries) {
                        entries.forEach(function(entry) {
                            createFile(entry.filename, function(file_entry) {
                                var writer = new zip.FileWriter(file_entry);
                                entry.getData(writer, function(blob) {
                                    var blobURL = file_entry.toURL();
                                    onend(blobURL);
                                });
                            });
                        });
                    });
                }, onerror);
            }, function(e) {
                fsEerrorHandler(e, onerror);
            });
        }, onerror);
    }

    obj.popcorn = {
        test : function(onend) {
            onend('Hello World!');
        },
        initialize : initialize,
        download   : cached_download,
        extract    : extract
    };

})(this);
