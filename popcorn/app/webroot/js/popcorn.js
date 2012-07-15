(function(obj) {

    obj.requestFileSystem = obj.webkitRequestFileSystem || obj.mozRequestFileSystem || obj.requestFileSystem;
    obj.resolveLocalFileSystemURL = obj.webkitResolveLocalFileSystemURL || obj.resolveLocalFileSystemURL;
    obj.BlobBuilder = obj.WebKitBlobBuilder || obj.mozBlobBuilder || obj.BlobBuilder;
    obj.URL = obj.webkitURL || obj.mozURL || obj.URL;

    var fs = null;

    function fsErrorHandler(e, onerror) {
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

    function fsSave(file_entry, data, onend, onerror) {
        file_entry.createWriter(function(file_writer) {
            file_writer.onwriteend = function(e) {
                if (onend) {
                    onend(file_entry.name);
                }
            };
            file_writer.onerror = function(e) {
                if (onerror) {
                    onerror('Write failed: ' + e.toString());
                }
            };

            try {
                var bb = new obj.BlobBuilder();
                bb.append(data);
                file_writer.write(bb.getBlob());
            } catch (e) {
                if (onerror) {
                    onerror('Write failed: ' + e.toString());
                }
            }
        }, function(e) {
            fsErrorHandler(e, onerror);
        });
    }

    function fsResolveUrls(file_entry) {
        var fname = file_entry.name;
        var file_supported = fname.match(/\.(htm|css)/);
        if (!file_supported) {
            return;
        }

        function _onend(data) {
            var blobURL = file_entry.toURL();
            var baseURL = dirname(blobURL).replace(/\/$/, '') + '/';
            var regex = /(href|src)( *= *)(['"])(?!https?:\/\/|\/\/)([^'"]*)(['"])/g;

            data = data.replace(regex, "$1$2$3" + baseURL + "$4$5");
            fsSave(file_entry, data);
        }
        file_entry.file(function(file) {
            var reader = new FileReader(); 
            reader.onloadend = function(e) {
                _onend(this.result);
            };
            reader.readAsText(file);
        });
    }

    //==========================================================================

    function initialize(onerror) {
        zip.workerScriptsPath = "js/zip/";

        //obj.requestFileSystem(obj.PERSISTENT, 1024*1024*1024, function(filesystem) {
        obj.requestFileSystem(obj.TEMPORARY, 4*1024*1024, function(filesystem) {
            fs = filesystem;
        }, function(e) {
            fsErrorHandler(e, onerror);
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
            fsErrorHandler(e, onerror);
        });
    }

    function deleteFolder(folder, onend, onerror) {
        if (! fs) {
            if (onerror) {
                onerror('File system unavailable!');
            }
            return;
        }
        fs.root.getDirectory(folder, {}, function(dir_entry) {
            dir_entry.removeRecursively(function() {
                if (onend) {
                    onend('Directory ' + folder + ' removed.');
                }
            }, function(e) {
                fsErrorHandler(e, onerror);
            });
        }, function(e) {
            fsErrorHandler(e, onerror);
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
                fsErrorHandler(e, onerror);
            });
        }, onerror);
    }

    function saveAs(fpath, data, onend, onerror) {
        createFile(fpath, function(file_entry) {
            fsSave(file_entry, data, onend, onerror);
        }, onerror);
    }

    function getFile(fpath, onend, onerror) {
        fs.root.getFile(fpath, {create: false}, function(file_entry) {
            onend(file_entry);
        }, function(e) {
            fsErrorHandler(e, onerror);
        });
    }

    function fileExists(fpath, onend) {
        getFile(fpath, function(file_entry) {
            onend(true);
        }, function(e) {
            onend(false);
        });
    }

    function basename(path) {
        return path.replace(/.*\//, '');
    }

    function dirname(path) {
        return path.replace(/\/[^\/]*$/, '');
    }

    function getFilePath(url, folder) {
        var fname = basename(url);
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

    function extract(fpath, folder, onend, onerror, onprogress) {
        getFile(fpath, function(file_entry) {
            file_entry.file(function(file) {
                zip.createReader(new zip.BlobReader(file), function(zip_reader) {
                    zip_reader.getEntries(function(entries) {
                        var total_files = entries.length;
                        var file_count = 0;
                        function _onprogress(file_entry) {
                            fsResolveUrls(file_entry);

                            file_count++;
                            if (onprogress) {
                                onprogress(file_count, total_files);
                            }
                            if (file_count >= total_files) {
                                onend(fpath);
                            }
                        }
                        entries.forEach(function(entry) {
                            var entry_path = (folder || '.') + '/' + entry.filename;
                            createFile(entry_path, function(file_entry) {
                                var writer = new zip.FileWriter(file_entry);
                                entry.getData(writer, function(blob) {
                                    //var blobURL = file_entry.toURL();
                                    //onend(blobURL);
                                    _onprogress(file_entry);
                                });
                            });
                        });
                    });
                }, onerror);
            }, function(e) {
                fsErrorHandler(e, onerror);
            });
        }, onerror);
    }

    function getFsUrl(fpath, onend) {
        getFile(fpath, function(file_entry) {
            onend(file_entry.toURL());
        });
    }

    function resolveFsUrl(url, onend) {
        obj.resolveLocalFileSystemURL(url, function(file_entry) {
            onend(file_entry);
        });
    }

    function readFileAsText(fpath, onend, onerror) {
        getFile(fpath, function(file_entry) {
            file_entry.file(function(file) {
                var reader = new FileReader(); 
                reader.onloadend = function(e) {
                    onend(this.result);
                };
                reader.readAsText(file);
            }, function(e) {
                fsErrorHandler(e, onerror);
            });
        }, onerror);
    }

    obj.popcorn = {
        test : function(onend) {
            onend('Hello World!');
        },
        initialize     : initialize,
        download       : cached_download,
        extract        : extract,
        basename       : basename,
        dirname        : dirname,
        getFsUrl       : getFsUrl,
        deleteFolder   : deleteFolder,
        readFileAsText : readFileAsText
    };

})(this);
