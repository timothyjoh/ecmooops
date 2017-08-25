propel-auth0
================

WordPress API Plugin to work with a custom database connection from the Auth0 Dashboard

It exposes two new JSON endpoints:

* GET wp-json/scitent/v1/profile/{encodedEmail} - where encodedEmail is a base64 encoded email address. If a user is found, return the auth0 profile for the user as JSON.

* POST wp-json/scitent/v1/auth - payload is a JSON object with email and password params. If valid, return the minimal Auth0 data for the user as JSON.

The auth0 dashboard requires two scripts to call these endpoints. The are added to a custom database that has the 'Import Users to Auth0' flag set. 

An example login script:

    function login (email, password, callback) {
      request.post({
       headers: {'content-type' : 'application/json'},
       url: "https://{WP_URL_BASE}/wp-json/scitent/v1/auth",
       body: "{\"email\":\""+email+"\", \"password\":\""+password+"\"}"
    }, function (err, response, body) {
        if (err) return callback(new Error(err));
        if (response.statusCode === 401) {
            return callback( new WrongUsernameOrPasswordError(email, "") );
        }
        var user = JSON.parse(body);
        callback(null,   {
            user_id: user.user_id.toString(),
            name:    user.name,
            email:   user.email,
            email_verified:true,
            app_metadata: { role: user.role }
        });
    });
    }
    
An example Get User script:

    function getByEmail (email, callback) {
        var out = new Buffer(email).toString('base64');
        request.get({
            url: "https://{WP_URL}/wp-json/scitent/v1/profile/"+out,
        }, function (err, response, body) {
        if (err) return callback( new Error(err) ;
        if (response.statusCode === 401 || response.statusCode === 404) {
            return callback();
        }
        var user = JSON.parse(body);
        callback(null,   {
            user_id: user.user_id.toString(),
            name:    user.name,
            email:   user.email
        });
    });
    }
    
    
    


