(function(obj) {

    var _host = "";

    var _resources = {
        login          : '/users/login',
        logout         : '/users/logout',
        user           : '/users/getActiveUser',
        bookmarks      : '/bookmarks/get_all',
        add_bookmark   : '/bookmarks/save'
    }

    //--------------------------------------------------------------------------
    //--------------------------------------------------------------------------

    function initialize(params) {
        if (params) {
            _host = params.host || "";
        }
    }

    //--------------------------------------------------------------------------

    function getResourceURL(resource) {
        var url = _host + resource;
        return url;
    }

    //--------------------------------------------------------------------------

    function getLoginURL() {
        var url = _host + _resources.login;
        return url;
    }

    //--------------------------------------------------------------------------

    function getActiveUser(onend) {
        var req_url = getResourceURL(_resources.user);

        $.get(req_url, function(data) {
            onend(data);
        }, 'json')
        .error(function(jqXHR, textStatus, errorThrown) {
            onend(null);
        });
    }

    //--------------------------------------------------------------------------

    function getBookmarks(onend) {
        var req_url = getResourceURL(_resources.bookmarks);

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
        var req_url = getResourceURL(_resources.add_bookmark);

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
        getResourceURL : getResourceURL,
        getLoginURL    : getLoginURL,
        getActiveUser  : getActiveUser,
        getBookmarks   : getBookmarks,
        addBookmark    : addBookmark
    };

})(this);
