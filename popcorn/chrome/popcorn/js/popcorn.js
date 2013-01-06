(function(obj) {

    obj.requestFileSystem = obj.webkitRequestFileSystem || obj.mozRequestFileSystem || obj.requestFileSystem;
    obj.resolveLocalFileSystemURL = obj.webkitResolveLocalFileSystemURL || obj.resolveLocalFileSystemURL;
    obj.BlobBuilder = obj.WebKitBlobBuilder || obj.mozBlobBuilder || obj.BlobBuilder;
    obj.URL = obj.webkitURL || obj.mozURL || obj.URL;

    var fs = null;

    //--------------------------------------------------------------------------
    //-------------------------------------------------------------------------

    function strPad(i,l,s) {
        var o = i.toString();
        if (!s) { s = '0'; }
        while (o.length < l) {
            o = s + o;
        }
        return o;
    }

    //--------------------------------------------------------------------------
    //--------------------------------------------------------------------------

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

    //--------------------------------------------------------------------------

    function fsTruncate(file_entry, onend, onerror) {
        file_entry.createWriter(function(file_writer) {
            file_writer.onwriteend = function(e) {
                if (onend) {
                    onend('File ' + file_entry.name + ' truncated.');
                }
            };
            file_writer.onerror = function(e) {
                if (onerror) {
                    onerror('Write failed: ' + e.toString());
                }
            };

            try {
                file_writer.seek(0);
                file_writer.truncate(0);
            } catch (e) {
                if (onerror) {
                    onerror('Write failed: ' + e.toString());
                }
            }
        }, function(e) {
            fsErrorHandler(e, onerror);
        });
    }

    //--------------------------------------------------------------------------

    function fsSave(file_entry, data, onend, onerror) {
        file_entry.createWriter(function(file_writer) {
            file_writer.onwriteend = function(e) {
                if (onend) {
                    //onend(file_entry.name);
                    onend(file_entry.fullPath);
                }
            };
            file_writer.onerror = function(e) {
                if (onerror) {
                    onerror('Write failed: ' + e.toString());
                }
            };

            try {
                var blob = null;
                if (Blob) {
                    blob = new Blob([data]);
                } else {
                    var bb = new obj.BlobBuilder();
                    bb.append(data);
                    blob = bb.getBlob();
                }
                file_writer.write(blob);
            } catch (e) {
                if (onerror) {
                    onerror('Write failed: ' + e.toString());
                }
            }
        }, function(e) {
            fsErrorHandler(e, onerror);
        });
    }

    //--------------------------------------------------------------------------

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
            fsTruncate(file_entry, function(msg) {
                fsSave(file_entry, data);
            }, onerror);
        }
        file_entry.file(function(file) {
            var reader = new FileReader(); 
            reader.onloadend = function(e) {
                _onend(this.result);
            };
            reader.readAsText(file);
        });
    }

    //--------------------------------------------------------------------------
    //--------------------------------------------------------------------------

    function initialize(onend, onerror) {
        zip.workerScriptsPath = "js/zip/";

        //obj.requestFileSystem(obj.TEMPORARY, 5*1024*1024, function(filesystem)
        obj.requestFileSystem(obj.PERSISTENT, 5*1024*1024*1024, function(filesystem)
		{
            fs = filesystem;
            if (onend) onend();
        }, function(e) {
            if (onerror) fsErrorHandler(e, onerror);
        });
    }

    //--------------------------------------------------------------------------

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

    //--------------------------------------------------------------------------

    function deleteFolder(folder, onend, onerror) {
        if (! fs) {
            return onerror ? onerror('File system unavailable!') : false;
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

    //--------------------------------------------------------------------------

    function createFolder(folders, onend, onerror) {
        if (! fs) {
            return onerror ? onerror('File system unavailable!') : false;
        }
        createDir(fs.root, folders, onend, onerror);
    }

    //--------------------------------------------------------------------------

    function deleteFile(fpath, onend, onerror) {
        if (! fs) {
            return onerror ? onerror('File system unavailable!') : false;
        }
        fs.root.getFile(fpath, {create: false}, function(file_entry) {
            file_entry.remove(function() {
                if (onend) {
                    onend('File ' + fpath + ' removed.');
                }
            }, function(e) {
                fsErrorHandler(e, onerror);
            });
        }, function(e) {
            fsErrorHandler(e, onerror);
        });
    }

    //--------------------------------------------------------------------------

    function createFile(fpath, onend, onerror, truncate) {
        var folders = fpath.split('/');
        var file = folders.pop();

        function _createFile(dir_entry, file, onend, onerror) {
            dir_entry.getFile(file, {create: true, exclusive: true}, onend, onerror);
        }
        
        function _deleteFile(dir_entry, file, onend, onerror) {
            dir_entry.getFile(file, {create: false}, function(file_entry) {
                file_entry.remove(onend, onerror);
            }, onerror);
        }
        
        createFolder(folders, function(dir_entry) {
            if (truncate) {
                dir_entry.getFile(file, {create: true}, function(file_entry) {
                    fsTruncate(file_entry, function(msg) {
                        onend(file_entry);
                    }, onerror);
                }, function(e) {
                    fsErrorHandler(e, onerror);
                });
            } else {
                _deleteFile(dir_entry, file, function() {
                    _createFile(dir_entry, file, function(file_entry) {
                        onend(file_entry);
                    }, function(e) {
                        fsErrorHandler(e, onerror);
                    });
                }, function(e) {
                    _createFile(dir_entry, file, function(file_entry) {
                        onend(file_entry);
                    }, function(e) {
                        fsErrorHandler(e, onerror);
                    });
                });
            }
        }, onerror);
    }

    //--------------------------------------------------------------------------

    function saveAs(fpath, data, onend, onerror) {
        createFile(fpath, function(file_entry) {
            fsSave(file_entry, data, onend, onerror);
        }, onerror);
    }

    //--------------------------------------------------------------------------

    function getFile(fpath, onend, onerror) {
        if (! fs) {
            return onerror ? onerror('File system unavailable!') : false;
        }
        fs.root.getFile(fpath, {create: false}, function(file_entry) {
            onend(file_entry);
        }, function(e) {
            fsErrorHandler(e, onerror);
        });
    }

    //--------------------------------------------------------------------------

    function fileExists(fpath, onend) {
        getFile(fpath, function(file_entry) {
            onend(true);
        }, function(e) {
            onend(false);
        });
    }

    //--------------------------------------------------------------------------

    function basename(path) {
        return path.replace(/.*\//, '');
    }

    //--------------------------------------------------------------------------

    function dirname(path) {
        return path.replace(/\/[^\/]*$/, '');
    }

    //--------------------------------------------------------------------------

    function getFilePath(url, folder) {
        var fname = basename(url);
        var fpath = (folder || '.') + '/' + fname;
        return fpath;
    }

    //--------------------------------------------------------------------------

    function download(url, folder, onend, onerror) {
        function _sync() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', url, false); // Note: synchronous
            xhr.responseType = 'arraybuffer';
            xhr.send();

            var fpath = getFilePath(url, folder);
            saveAs(fpath, xhr.response, onend, onerror);
        }

        function _async() {
            var xhr = new XMLHttpRequest();
            xhr.open('GET', url);
            xhr.responseType = 'arraybuffer';
            xhr.onload = function() {
                var fpath = getFilePath(url, folder);
                saveAs(fpath, xhr.response, onend, onerror);
            }
            xhr.send();
        }

        try {
            _async();
        } catch(e) {
            onerror('XHR Error: ' + e.toString());
        }
    }

    //--------------------------------------------------------------------------

    function cached_download(url, folder, onend, onerror) {
        var download_key = $.md5(url + '-' + folder);
        var download_data = JSON.stringify({url: url, folder: folder, status: 'ongoing'});
        localStorage.setItem(download_key, download_data);
        
        function _onend(fpath) {
            download_data = JSON.stringify({url: url, folder: folder, status: 'success'});
            localStorage.setItem(download_key, download_data);
            onend(fpath);
        }
        function _onerror(e) {
            download_data = JSON.stringify({url: url, folder: folder, status: 'error'});
            localStorage.setItem(download_key, download_data);
            onerror(e);
        }
        
        var fpath = getFilePath(url, folder);
        fileExists(fpath, function(exists) {
            if (exists) {
                _onend(fpath);
            } else {
                download(url, folder, _onend, _onerror);
            }
        });
    }

    //--------------------------------------------------------------------------

    function download_status(url, folder) {
        var download_key = $.md5(url + '-' + folder);
        var download_data = localStorage.getItem(download_key);
        var retval = 'not_found';
        
        if (download_data) {
            item = JSON.parse(download_data);
            retval = item.status;
        }

        return retval;
    }
    
    //--------------------------------------------------------------------------

    function extract(fpath, folder, onend, onerror, onprogress) {
        var extract_key = $.md5(fpath + '-' + folder);
        var extract_data = JSON.stringify({fpath: fpath, folder: folder, status: 'ongoing'});
        localStorage.setItem(extract_key, extract_data);
        
        function _onend(fpath) {
            extract_data = JSON.stringify({fpath: fpath, folder: folder, status: 'success'});
            localStorage.setItem(extract_key, extract_data);
            onend(fpath);
        }
        function _onerror(e) {
            extract_data = JSON.stringify({fpath: fpath, folder: folder, status: 'error'});
            localStorage.setItem(extract_key, extract_data);
            onerror(e);
        }

        getFile(fpath, function(file_entry) {
            file_entry.file(function(file) {
                zip.createReader(new zip.BlobReader(file), function(zip_reader) {
                    zip_reader.getEntries(function(entries) {
                        var total_files = entries.length;
                        var file_count = 0;
                        
                        function _getNextEntry() {
                            var idx = file_count++;
                            var retval = false;

                            if (file_count <= total_files) {
                                retval = entries[idx];
                                if (onprogress) onprogress(file_count, total_files);
                            }
                            return retval;
                        }
                        
                        function _extractFiles() {
                            var entry = _getNextEntry();
                            if (entry) {
                                var entry_path = (folder || '.') + '/' + entry.filename;
                                createFile(entry_path, function(file_entry) {
                                    try {
                                        var writer = new zip.FileWriter(file_entry);
                                        entry.getData(writer, function(blob) {
                                            //fsResolveUrls(file_entry);
                                            _extractFiles();
                                        });
                                    } catch (e) {
                                        _extractFiles();
                                    }
                                }, function(e) {
                                    _extractFiles();
                                });
                            } else {
                                _onend(fpath);
                            }
                        }
                        
                        _extractFiles();
                    });
                }, _onerror);
            }, function(e) {
                fsErrorHandler(e, _onerror);
            });
        }, _onerror);
    }

    //--------------------------------------------------------------------------

    function extract_status(fpath, folder) {
        var extract_key = $.md5(fpath + '-' + folder);
        var extract_data = localStorage.getItem(extract_key);
        var retval = 'not_found';
        
        if (extract_data) {
            item = JSON.parse(extract_data);
            retval = item.status;
        }

        return retval;
    }
    
    //--------------------------------------------------------------------------

    function getFsUrl(fpath, onend, onerror) {
        getFile(fpath, function(file_entry) {
            onend(file_entry.toURL());
        }, onerror);
    }

    //--------------------------------------------------------------------------

    function resolveFsUrl(url, onend) {
        obj.resolveLocalFileSystemURL(url, function(file_entry) {
            onend(file_entry);
        });
    }

    //--------------------------------------------------------------------------

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

    //--------------------------------------------------------------------------
    //--------------------------------------------------------------------------

    obj.popcorn = {
        test : function(onend) {
            onend('Hello World!');
        },
        initialize      : initialize,
        download        : cached_download,
        download_status : download_status,
        extract         : extract,
        extract_status  : extract_status,
        basename        : basename,
        dirname         : dirname,
        getFsUrl        : getFsUrl,
        getFilePath     : getFilePath,
        deleteFolder    : deleteFolder,
        deleteFile      : deleteFile,
        readFileAsText  : readFileAsText,
        saveAs          : saveAs,
        strPad          : strPad
    };

})(this);
