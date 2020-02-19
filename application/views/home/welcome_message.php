<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Welcome to LinkStream API</title>

        <style type="text/css">

            ::selection { background-color: #E13300; color: white; }
            ::-moz-selection { background-color: #E13300; color: white; }

            body {
                background-color: #fff;
                margin: 40px;
                font: 13px/20px normal Helvetica, Arial, sans-serif;
                color: #4F5155;
            }

            a {
                color: #003399;
                background-color: transparent;
                font-weight: normal;
            }

            h1 {
                color: #444;
                background-color: transparent;
                border-bottom: 1px solid #D0D0D0;
                font-size: 19px;
                font-weight: normal;
                margin: 0 0 14px 0;
                padding: 14px 15px 10px 15px;
            }

            code {
                font-family: Consolas, Monaco, Courier New, Courier, monospace;
                font-size: 12px;
                background-color: #f9f9f9;
                border: 1px solid #D0D0D0;
                color: #002166;
                display: block;
                margin: 14px 0 14px 0;
                padding: 12px 10px 12px 10px;
            }

            #body {
                margin: 0 15px 0 15px;
            }

            p.footer {
                text-align: right;
                font-size: 11px;
                border-top: 1px solid #D0D0D0;
                line-height: 32px;
                padding: 0 10px 0 10px;
                margin: 20px 0 0 0;
            }

            #container {
                margin: 10px;
                border: 1px solid #D0D0D0;
                box-shadow: 0 0 8px #D0D0D0;
            }
        </style>
    </head>
    <body>

        <div id="container">
            <h1>Welcome to LinkStream API!</h1>

            <div id="body">
                <p>The page you are looking at is a guide of the LinkStream API.</p>
                <hr>
                <h3>Login:</h3>
                <code>POST <?= base_url() ?>v1/users/login</code>
                <h3>Parameters:</h3>
                <p>email</p>
                <p>password</p>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "staging",
    "data": {
        "id": "38",
        "user_name": "pa",
        "first_name": null,
        "last_name": null,
        "display_name": "pa",
        "email": "pa@link.stream",
        "plan_id": "1",
        "created_at": "2020-02-18 22:07:56",
        "url": "pa",
        "phone": null,
        "image": null,
        "banner": null,
        "about": null,
        "email_paypal": null,
        "platform": "LinkStream",
        "platform_id": null,
        "platform_token": null,
        "bio": null
    }
}');
                    echo '</pre>';
                    ?>

                </p>
                <hr>
                <h3>Registration:</h3>
                <code>POST <?= base_url() ?>v1/users/registration</code>
                <h3>Parameters:</h3>
                <p>email</p>
                <p>password</p>
                <p>user_name</p>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "staging",
    "data": {
        "url": "pa5",
        "display_name": "pa5",
        "user_name": "pa5",
        "email": "pa5@link.stream",
        "plan_id": "1",
        "platform": "LinkStream",
        "id": 44
    }
}');
                    echo '</pre>';
                    ?>

                </p>


                <hr>
                <h3>Availability:</h3>
                <code>GET <?= base_url() ?>v1/users/availability/{type}/{value}</code>
                <h3>Parameters:</h3>
                <p>Type = 'username or email'</p>
                <p>Value = Example 'a@link.stream or paolofq'</p>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "staging"
}');
                    echo '</pre>';
                    ?>
                </p>
                OR
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "false",
    "env": "staging",
    "error": "Username: paolofq is not available"
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Get User Info:</h3>
                <code>GET <?= base_url() ?>v1/users/{user_id}</code>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": {
        "id": "35",
        "user_name": "pferra1",
        "first_name": null,
        "last_name": null,
        "display_name": "pferra1",
        "email": "paul@link.stream",
        "email_confirmed": "0",
        "password": "968671695f00cd9a7c953a0d13f57a727b5e3468",
        "status_id": "1",
        "plan_id": "1",
        "created_at": "2020-02-17 22:34:38",
        "url": "pferra1",
        "phone": null,
        "image": null,
        "banner": null,
        "about": null,
        "youtube": null,
        "facebook": null,
        "instagram": null,
        "twitter": null,
        "soundcloud": null,
        "email_paypal": null,
        "platform": "LinkStream",
        "platform_id": null,
        "platform_token": null,
        "bio": null
    }
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Instagram Login/Register:</h3>
                <code>POST <?= base_url() ?>v1/users/instagram</code>
                <h3>Parameters:</h3>
                <p>user_id (Instagram ID - Example: 17841400070704000)</p>
                <p>instagram_username</p>
                <p>platform_token (Instagram Token ID - Example: IGQVJXS1o5cktiU2NhTEp1YTJJamVrempkSnVlMkJTb1NoekY0R1ZAWSXdHLVlXUzYxeHpGUmRDdWhfbDNET19rc2k3UXdYSTBpY09Ga)</p>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": {
        "url": "paolo_streamy45",
        "display_name": "paolo_streamy45",
        "user_name": "paolo_streamy45",
        "email": "",
        "plan_id": "1",
        "platform": "IG",
        "platform_id": "1234567891",
        "platform_token": "12345678901234567890",
        "image": "1582081532.png",
        "status_id": "3",
        "id": 43
    }
}');
                    echo '</pre>';
                    ?>
                </p>
                
                 <hr>
                <h3>Google Login/Register:</h3>
                <code>POST <?= base_url() ?>v1/users/google</code>
                <h3>Parameters:</h3>
                <p>platform_token (Google Token ID - Example: eyJhbGciOiJSUzI1NiIsImtpZCI6ImQ4ZWZlYTFmNjZlODdiYjM2YzJlYTA5ZDgzNzMzOGJkZDgxMDM1M2IiLCJ0eXAiOiJKV1QifQ)</p>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": {
        "url": "paolo_streamy45",
        "display_name": "paolo_streamy45",
        "user_name": "paolo_streamy45",
        "email": "",
        "plan_id": "1",
        "platform": "IG",
        "platform_id": "1234567891",
        "platform_token": "12345678901234567890",
        "image": "1582081532.png",
        "status_id": "3",
        "id": 43
    }
}');
                    echo '</pre>';
                    ?>
                </p>




                <!-- EXAMPLE 
                <hr>
                <h3>Name:</h3>
                <code>POST <?= base_url() ?>v1/users/example</code>
                <h3>Parameters:</h3>
                <p>a</p>
                <p>b</p>
                 <h3>Response Example:</h3>
                <p>
                <?php
                echo '<pre>';
                print_r('');
                echo '</pre>';
                ?>
                </p>
                -->

<!--                <p>If you are exploring CodeIgniter for the very first time, you should start by reading the <a href="user_guide/">User Guide</a>.</p>-->
            </div>

            <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds. <?php echo (ENVIRONMENT === 'development') ? 'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '' ?></p>
        </div>

    </body>
</html>