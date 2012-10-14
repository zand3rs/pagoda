//-------------------------------------------------------------------------
//-------------------------------------------------------------------------

if (!chrome.cookies) {
    chrome.cookies = chrome.experimental.cookies;
}

var CONFIG = {
    host       : 'http://localhost/~zander/popcorn',
    clean_root : false,
    debug      : false
}

var POPCORN_PATH = {
    root          : '/popcorn',
    login         : '/users/login',
    user          : '/user',
    bookmarks     : '/bookmarks',
    bookmarks_cs  : '/bookmarks_cs'
}

var COOKIES = {
    user : 'popcorn[user]'
}

var activeUser = null;

//-------------------------------------------------------------------------
//-------------------------------------------------------------------------

function setFlash(msg, append) {
    var message = msg + '<br>';
    if (append) {
        $("#flash").append(message);
    } else {
        $("#flash").html(message);
    }
}

//-------------------------------------------------------------------------

function onError(msg) {
    setFlash('ERROR: ' + msg);
}

//-------------------------------------------------------------------------

function console_log(msg) {
    if (CONFIG.debug) {
        console.log(msg);
    }
}

//-------------------------------------------------------------------------
//-------------------------------------------------------------------------

function getStoragePath(fpath) {
    var storage_path = POPCORN_PATH.root;

    if (fpath != POPCORN_PATH.user) {
        if (activeUser && activeUser.id) {
            var user_dir = '/' + popcorn.strPad(activeUser.id, 10);
            storage_path += user_dir;
        }
    }
    storage_path += fpath;

    return storage_path;
}

//-------------------------------------------------------------------------

function cleanRootFolder(onend) {
    popcorn.deleteFolder(POPCORN_PATH.root, function(msg) {
        console_log('cleanRootFolder: ' + msg);
        if (onend) {
            onend(msg);
        }
    }, function(e) {
        console_log('cleanRootFolder error: ' + e);
        if (onend) {
            onend(e);
        }
    });
}

//-------------------------------------------------------------------------

function setActiveUser(user, onend) {
    var user_path = getStoragePath(POPCORN_PATH.user);
    console_log('setActiveUser: user_path: ' + user_path);

    var data = JSON.stringify(user);

    console_log('setActiveUser: data: ' + data);
    popcorn.saveAs(user_path, data, function(fpath) {
        if (onend) onend();
    });
}

//-------------------------------------------------------------------------

function getActiveUser(onend) {
    console_log('getActiveUser: user cookie: ' + COOKIES.user);

    chrome.cookies.get({url: CONFIG.host, name: COOKIES.user}, function(cookie) {
        console_log(cookie);
        if (cookie) {
            var data = decodeURIComponent(cookie.value);
            var user = JSON.parse(data);
            onend(user);
        } else {
            onend(null);
        }
    });
}

//-------------------------------------------------------------------------

function getBookmarksChecksum(onend) {
    var fpath = getStoragePath(POPCORN_PATH.bookmarks_cs);
    console_log('getBookmarksChecksum: fpath: ' + fpath);

    popcorn.readFileAsText(fpath, function(data) {
        var bookmarks_cs = data;
        onend(bookmarks_cs);
    }, function(msg) {
        console_log('getBookmarksChecksum: ' + msg);
        onend('')
    });
}

//-------------------------------------------------------------------------

function getBookmarks(onend) {
    var bookmarks_path = getStoragePath(POPCORN_PATH.bookmarks);
    var bookmarks_cs_path = getStoragePath(POPCORN_PATH.bookmarks_cs);

    function _getBookmarks() {
        popcorn_api.getBookmarks(function(bookmarks) {
            if (bookmarks.length > 0) {
                var data = JSON.stringify(bookmarks);
                var data_cs = $.md5(data);

                console_log('popcorn_api.getBookmarks: data: ' + data);
                popcorn.saveAs(bookmarks_path, data);
                popcorn.saveAs(bookmarks_cs_path, data_cs);
            }
            onend(bookmarks);
        });
    }

    popcorn.readFileAsText(bookmarks_path, function(data) {
        var bookmarks = JSON.parse(data);
        onend(bookmarks);
    }, function() {
        _getBookmarks();
    });
}

//-------------------------------------------------------------------------

function verifyBookmarksChecksum() {
    var bookmarks_path = getStoragePath(POPCORN_PATH.bookmarks);
    var bookmarks_cs_path = getStoragePath(POPCORN_PATH.bookmarks_cs);

    function _verifyChecksum(bookmarks) {
        var data = JSON.stringify(bookmarks);
        var data_cs = $.md5(data);

        getBookmarksChecksum(function(cs) {
            console_log('checksum: ' + cs);
            if (cs != data_cs) {
                console_log('data_checksum:' + data_cs);
                popcorn.deleteFile(bookmarks_path, function() {
                    console_log('file deleted: ' + bookmarks_path);
                }, function(e) {
                    console_log('delete error: ' + bookmarks_path + ': ' + e);
                });

                popcorn.deleteFile(bookmarks_cs_path, function() {
                    console_log('file deleted: ' + bookmarks_cs_path);
                }, function(e) {
                    console_log('delete error: ' + bookmarks_cs_path + ': ' + e);
                });
            }
        });
    }

    popcorn_api.getBookmarks(function(bookmarks) {
        if (bookmarks.length > 0) {
            _verifyChecksum(bookmarks);
        } else {
            console_log('verifyBookmarksChecksum: bookmarks empty ');
        }
    }, function(e) {
        console_log('verifyBookmarksChecksum error: ' + e);
    });
}

//-------------------------------------------------------------------------

function downloadBookmark(bookmark) {
    var url = popcorn_api.getResourceURL(bookmark.archive);
    var dest_dir = popcorn.dirname(bookmark.archive);
    var index = bookmark.local_path;

    setFlash('Please wait while ' + bookmark.title + ' is being downloaded to your hard drive.');

    popcorn.download(url, dest_dir, function(fpath) {
        console_log('download successful: ' + fpath);
        popcorn.extract(fpath, dest_dir, function(fpath) {
            console_log('extract successful: ' + fpath);
            popcorn.getFsUrl(index, function(url) {
                showBookmarks();
                setFlash(bookmark.title + ' was successfully downloaded to your hard drive.');
                console_log('index: ' + url);
            });
        }, function(e) {
            onError(e);
            console_log('extract failed: ' + fpath);
        });
    }, onError);
}

//-------------------------------------------------------------------------

function confirmBookmarkDownload(bookmark_id) {
    getBookmarks(function(bookmarks) {
        for (var i in bookmarks) {
            var bookmark = bookmarks[i];
            if (bookmark_id == bookmark.id) {
                if (bookmark.archive) {
                    var msg = "Download " + bookmark.title + " to your hard drive?";
                    if (confirm(msg)) {
                        downloadBookmark(bookmark);
                    }
                } else {
                    setFlash(bookmark.title + ' is not yet available. Please try again in a while.');
                }
            }
        }
    });
}

//-------------------------------------------------------------------------

function showBookmarks() {
    function _clear() {
        $('#bookmarks').html('');
    }

    function _show(bookmark, url) {
        var html = "<div><a id='" + bookmark.id + "' href='" + url + "'>" + bookmark.title + "</a></div>";
        var callback = function() {
            if (url == '#') {
                confirmBookmarkDownload(bookmark.id);
            } else {
                //chrome.tabs.create({"url": url});
            }
        }

        $('#bookmarks').append(html);
        $('#bookmarks').find('#' + bookmark.id).click(callback);
        if (url != '#') {
            $('#bookmarks').find('#' + bookmark.id).prop('target', '_blank');
        }
    }

    getBookmarks(function(bookmarks) {
        _clear();
        $.each(bookmarks, function(key, val) {
            var bookmark = val;
            popcorn.getFsUrl(bookmark.local_path, function(url) {
                _show(bookmark, url);
            }, function(e) {
                _show(bookmark, '#');
            });
        });
    });
}

//-------------------------------------------------------------------------

function addBookmark(title, url) {
    if (!activeUser) {
        return console_log("addBookmark: Login required!");
    }

    popcorn_api.addBookmark(title, url, function() {
        if (! CONFIG.debug) {
            window.close();
        }
    });
}

//-------------------------------------------------------------------------

function doLogin(email, onend) {
    popcorn_api.login(email, function(user) {
        if (user) {
            setActiveUser(user, function() {
                activeUser = user;
                popcorn_api.setAccessToken(activeUser.access_token);
                onend();
            });
        }
    });
}

//-------------------------------------------------------------------------

function initHandlers() {
    $("#add-bookmark").click(function() {
        chrome.tabs.getSelected(null, function(tab) {
            addBookmark(tab.title, tab.url);
        });
    });

    $("#login-link").click(function() {
        chrome.tabs.create({"url": CONFIG.host + POPCORN_PATH.login});
    });
}

//-------------------------------------------------------------------------

function initDisplay() {
    $("#login-link").hide();
}

//-------------------------------------------------------------------------

function doBackground() {
    verifyBookmarksChecksum();
}

//-------------------------------------------------------------------------

function initialize(onend) {
    function _onend() {
        getActiveUser(function(user) {
            console_log('initialize: user: ' + JSON.stringify(user));
            if (user) {
                activeUser = user;
                popcorn_api.setAccessToken(activeUser.access_token);
                onend();
            } else {
                $("#add-bookmark").hide();
                $("#login-link").show();
            }
        });
    }

    initDisplay();
    initHandlers();

    if (CONFIG.clean_root) {
        cleanRootFolder(_onend);
    } else {
        _onend();
    }
}

//-------------------------------------------------------------------------
//-------------------------------------------------------------------------

$(document).ready(function() {
    popcorn_api.initialize({"host": CONFIG.host});
    popcorn.initialize(function() {
        initialize(function() {
            showBookmarks();
            doBackground();
        });
    }, onError);
});

//-------------------------------------------------------------------------
//-------------------------------------------------------------------------
