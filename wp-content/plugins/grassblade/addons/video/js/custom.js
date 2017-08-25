    function parseQueryString() 
    {
        var loc, qs, pairs, pair, ii, parsed;
        
        loc = window.location.href.split('?');
        if (loc.length === 2) {
            qs = loc[1];
            pairs = qs.split('&');
            parsed = {};
            for ( ii = 0; ii < pairs.length; ii++) {
                pair = pairs[ii].split('=');
                if (pair.length === 2 && pair[0]) {
                    parsed[pair[0]] = decodeURIComponent(pair[1]);
                }
            }
        }
        
        return parsed;
    }
    