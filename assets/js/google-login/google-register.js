$(document).ready(function () {
    var googleUser = {};
    var $error = $('#error');
    var startApp = function () {
        gapi.load('auth2', function () {
            // Retrieve the singleton for the GoogleAuth library and set up the client.
            auth2 = gapi.auth2.init({
                client_id: google_id,
                cookiepolicy: 'single_host_origin',
                // Request scopes in addition to 'profile' and 'email'
                //scope: 'additional_scope'
            });
            attachSignin(document.getElementById('customBtn'));
        });
    };

    function attachSignin(element) {
        //console.log(element.id);
        auth2.attachClickHandler(element, {},
                function (googleUser) {
                    var id_token = googleUser.getAuthResponse().id_token;
                    var profile = googleUser.getBasicProfile();
                    //console.log('ID: ' + profile.getId());
                    $.ajax({
                        type: 'post',
                        data: {id_token: id_token},
                        url: urlBase + "app/google_oauth",
                        success: function (r)
                        {
                            //console.log(r);
                            if (r.success) {
                                //console.log(r.sub);
                                //console.log(r.token);
                                window.location = urlBase + 'app';
                            } else {
                                $error.html('<h5>Google Sign In Failed</h5>' + r.error).show();
                            }
                        }
                    });
//                            console.log(id_token);
//                            var profile = googleUser.getBasicProfile();
//                            console.log('ID: ' + profile.getId());
//                            console.log('Full Name: ' + profile.getName());
//                            console.log('Given Name: ' + profile.getGivenName());
//                            console.log('Family Name: ' + profile.getFamilyName());
//                            console.log('Image URL: ' + profile.getImageUrl());
//                            console.log('Email: ' + profile.getEmail());
//                            console.log(googleUser.getBasicProfile().getName());
//                            document.getElementById('name').innerText = "Signed in: " + googleUser.getBasicProfile().getName();
                }, function (error) {
            //alert(JSON.stringify(error, undefined, 2));
            //$error.html(JSON.stringify(error, undefined, 2));
            $error.html('<h5>' + JSON.stringify(error, undefined, 2) + '</h5>')
        });
    }

    startApp();
});
