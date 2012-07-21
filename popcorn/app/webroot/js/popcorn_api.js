(function(obj) {

    var _host = "";

    //--------------------------------------------------------------------------
    //--------------------------------------------------------------------------

    function initialize(params) {
        if (params) {
            _host = params.host || "";
        }
    }

    //--------------------------------------------------------------------------

    function getActiveUser(onend) {
        var req_url = _host + '/users/getActiveUser';

        $.get(req_url, function(data) {
            if (data && data.id) {
                onend(data);
            }
        }, 'json');
    }

    //--------------------------------------------------------------------------

    function getBookmarks(onend) {
        var req_url = _host + '/bookmarks/get_all';

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
        var req_url = _host + '/bookmarks/save';
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
        initialize    : initialize,
        getActiveUser : getActiveUser,
        getBookmarks  : getBookmarks,
        addBookmark   : addBookmark
    };

})(this);
