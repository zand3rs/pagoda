(function(obj) {

    var _host = "";
    var _access_token = null;

    var _resources = {
        login          : '/api/login',
        user           : '/api/get_user',
        bookmarks      : '/api/get_bookmarks',
        add_bookmark   : '/api/add_bookmark'
    }

    //--------------------------------------------------------------------------
    //--------------------------------------------------------------------------

    function initialize(params) {
        if (params) {
            _host = params.host || "";
        }
    }

    //--------------------------------------------------------------------------

    function setAccessToken(access_token) {
        _access_token = access_token;
    }

    //--------------------------------------------------------------------------

    function getResourceURL(resource, param) {
        var url = _host + resource;
        if (param) {
            url += '/' + param;
        }
        return url;
    }

    //--------------------------------------------------------------------------

    function login(email, onend) {
        var req_url = getResourceURL(_resources.login);

        var dataObj = {
            email: email
        };
        var payload = JSON.stringify(dataObj);

        $.post(req_url, payload, function(data) {
            onend(data);
        }, 'json')
        .error(function(jqXHR, textStatus, errorThrown) {
            onend(null);
        });
    }

    //--------------------------------------------------------------------------

    function getActiveUser(onend) {
        var req_url = getResourceURL(_resources.user, _access_token);

        $.get(req_url, function(data) {
            onend(data);
        }, 'json')
        .error(function(jqXHR, textStatus, errorThrown) {
            onend(null);
        });
    }

    //--------------------------------------------------------------------------

    function getBookmarks(onend) {
        var req_url = getResourceURL(_resources.bookmarks, _access_token);

        function parseData(data) {
            var bookmarks = new Array();
            if (data) {
                $.each(data, function(key, val) {
                    var bookmark = val.Bookmark;
                    bookmarks.push(bookmark);
                });
            }
            onend(bookmarks);
        }

        $.get(req_url, function(data) {
            parseData(data);
        }, 'json');
    }

    //--------------------------------------------------------------------------

    function addBookmark(title, url, onend) {
        var req_url = getResourceURL(_resources.add_bookmark, _access_token);

        var dataObj = {
             Bookmark: {
                 title: title,
                 url: url
             }
        };
        var payload = JSON.stringify(dataObj);

        $.ajax({
            type: 'POST',
            contentType: 'application/json',
            processData: false,
            dataType: 'json',
            timeout: 60000,
            url: req_url,
            data: payload
        })
        .success(function(data, textStatus, jqXHR) {
        })
        .error(function(jqXHR, textStatus, errorThrown) {
        })
        .complete(function(jqXHR, textStatus) {
            if (onend) {
                onend();
            }
        });
    }

    //--------------------------------------------------------------------------
    //--------------------------------------------------------------------------

    obj.popcorn_api = {
        initialize     : initialize,
        setAccessToken : setAccessToken,
        getResourceURL : getResourceURL,
        login          : login,
        getActiveUser  : getActiveUser,
        getBookmarks   : getBookmarks,
        addBookmark    : addBookmark
    };

})(this);
