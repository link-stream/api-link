(function ($, gapi, base_url) {
    'use strict';

    gapi.load('auth2', function () {
        var auth2 = gapi.auth2.init({
            client_id: '508620332071-blu7sd9t3osgc56sg1hnq9m8mu9a6tda.apps.googleusercontent.com',
            fetch_basic_profile: true,
            scope: 'profile'
        });

        // Bind login button
        var $login_btn = $('.js-g-login');
        $login_btn.length && auth2.attachClickHandler($login_btn[0], {}, function (user) {
            var id_token = user.getAuthResponse().id_token;
            $.post(base_url + 'app/google_oauth', {id_token: id_token}).done(function (response) {
                if (response.success) {
                    window.location = base_url + 'app';
                } else {
                    auth2.disconnect();
                    auth2.signOut().then(function () {
                        alert('Unable to sign-in with Google. ' + response.error + '.');
                    });
                }
            });
        }, function (response) {
            alert('Unable to sign-in with Google. Make sure your ad blocker is turned off. Error: ' + response.error);
        });

        // Bind logout button
        $(document.body).on('click', '.js-logout', function (e) {
            e.preventDefault();
            var url = $(this).attr('href');
            var logout = function () {
                window.location = url
            };
            try {
                auth2.disconnect();
                auth2.signOut().then(logout, logout);
            } catch (ex) {
                logout();
            }
        });
    });
})($, gapi, window.urlBase);