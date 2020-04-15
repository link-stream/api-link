<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Welcome to LinkStream API</title>
        <link rel="icon" href="<?= HTTP_ASSETS ?>images/favicon/streamy_favicon_color.png" type="image/png">
        <link rel="apple-touch-icon" href="<?= HTTP_ASSETS ?>images/favicon/streamy_favicon_color.png">
        <link rel="shortcut icon" href="<?= HTTP_ASSETS ?>images/favicon/streamy_favicon_color_BkH_icon.ico" type="image/x-icon">
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
                <h3>Endpoints</h3>
                <p><?= base_url() ?>v1/</p>                
                <h3>Authentication</h3>
                <p>The LinkStream API uses Basic HTTP authentication. Use your username and password provide by LinkStream.</p>   
                <p>The LinkStream API requires that X-API-KEY be sent in the header. Use your X-API-KEY provide by LinkStream.</p>  
                <p>The LinkStream API requires that Content-Type: application/x-www-form-urlencoded be sent in the header.</p>  
                <hr>
                <h3>Login:</h3>
                <code>POST <?= base_url() ?>v1/users/login</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>email</li>
                    <li>password</li>
                </ul>
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
                <h3>Logout:</h3>
                <code>POST <?= base_url() ?>v1/users/logout</code>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "staging",
}');
                    echo '</pre>';
                    ?>

                </p>
                <hr>
                <h3>Registration:</h3>
                <code>POST <?= base_url() ?>v1/users/registration</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>email</li>
                    <li>password</li>
                    <li>user_name</li>
                </ul>
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
                <code>GET <?= base_url() ?>v1/users/availability/{type}/{value}/{user_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>Type = 'username or email or url'</li>
                    <li>Value = Example 'a@link.stream or paolofq or pferra'</li>
                    <li>user_id (Only required if Type = "url")</li>
                </ul>
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
        "bio": null,
        "data_image":"",
        "data_banner":""
    }
}');
                    echo '</pre>';
                    echo '* data_image and data_banner are base64_encode.'
                    ?>
                </p>


                <hr>
                <h3>Instagram Login/Register:</h3>
                <code>POST <?= base_url() ?>v1/users/instagram</code>
                <h3>Parameters:</h3>
                <ul>
                    <!--                    <li>user_id (Instagram ID - Example: 17841400070704000)</li>
                                        <li>instagram_username</li>-->
                    <li>code (Instagram Code)</li>
                    <li>redirect_url</li>
                </ul>
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
                <ul>
                    <li>platform_token (Google Token ID - Example: eyJhbGciOiJSUzI1NiIsImtpZCI6ImQ4ZWZlYTFmNjZlODdiYjM2YzJlYTA5ZDgzNzMzOGJkZDgxMDM1M2IiLCJ0eXAiOiJKV1QifQ)</li>
                </ul>
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
                <h3>Update User Info:</h3>
                <code>PUT <?= base_url() ?>v1/users/{user_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_name</li>
                    <li>first_name</li>
                    <li>last_name</li>
                    <li>display_name</li>
                    <li>email</li>
                    <li>email_confirmed</li>
                    <li>password</li>
                    <li>status_id</li>
                    <li>plan_id</li>
                    <li>url</li>
                    <li>phone</li>
                    <li>image(base64_encode)</li>
                    <li>banner(base64_encode)</li>
                    <li>about</li>
                    <li>email_paypal</li>
                    <li>bio</li>
                    <li>city</li>
                    <li>country</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "staging",
    "message": "The user info has been updated successfully.",
    "data": {
        "id": "36",
        "user_name": "AAAA",
        "first_name": "",
        "last_name": "",
        "display_name": "",
        "email": "paoerterto@gmail.com",
        "email_confirmed": "1",
        "password": "",
        "status_id": "3",
        "plan_id": "1",
        "created_at": "2020-02-18 16:35:04",
        "url": "",
        "phone": "",
        "image": "AAA.png",
        "banner": "c.jpeg",
        "about": "",
        "youtube": null,
        "facebook": null,
        "instagram": null,
        "twitter": null,
        "soundcloud": null,
        "email_paypal": "",
        "platform": "LinkStream",
        "platform_id": "",
        "platform_token": "",
        "city": null,
        "country": null,
        "bio": "",
        "data_image":"",
        "data_banner":""
    }
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Get plan information:</h3>
                <code>GET <?= base_url() ?>v1/users/plan</code>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": [
        {
            "id": "1",
            "plan": "Free Client",
            "price": "0.00"
        },
        {
            "id": "2",
            "plan": "Pro Client",
            "price": "4.95"
        },
        {
            "id": "3",
            "plan": "Pro Client - Free",
            "price": "0.00"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Get status information:</h3>
                <code>GET <?= base_url() ?>v1/users/status</code>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": [
        {
            "id": "1",
            "status": "ACTIVE"
        },
        {
            "id": "2",
            "status": "INACTIVE"
        },
        {
            "id": "3",
            "status": "PENDING"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Email Confirm:</h3>
                <code>POST <?= base_url() ?>v1/users/email_confirm</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>param_1</li>
                    <li>param_2</li>
                </ul>
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
    "error": "Email already confirmed previously"
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Resend Email Confirm:</h3>
                <code>POST <?= base_url() ?>v1/users/resend_email_confirm</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev"
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Forgot Password:</h3>
                <code>POST <?= base_url() ?>v1/users/forgot_password</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>email</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev"
}');
                    echo '</pre>';
                    ?>
                    OR
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "false",
    "env": "dev",
    "error": "User Not Found."
}');
                    echo '</pre>';
                    ?>
                </p>




                <hr>
                <h3>Password Reset:</h3>
                <code>POST <?= base_url() ?>v1/users/password_reset</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>param_1</li>
                    <li>param_2</li>
                    <li>new_password</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev"
}');
                    echo '</pre>';
                    ?>
                    OR
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "false",
    "env": "dev",
    "error": "User Not Found."
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Genre:</h3>
                <code>GET <?= base_url() ?>v1/common/genres</code>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": [
        {
            "id": "1",
            "genre": "None"
        },
        {
            "id": "2",
            "genre": "Custom"
        },
        {
            "id": "3",
            "genre": "Hip-hop &amp; Rap"
        },
        {
            "id": "4",
            "genre": "Alternative Rock"
        },
        {
            "id": "5",
            "genre": "Ambient"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Visibility:</h3>
                <code>GET <?= base_url() ?>v1/common/visibility/{user_id}</code>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": {
        "1": "Public",
        "2": "Private"
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>TimeZones:</h3>
                <code>GET <?= base_url() ?>v1/common/timezones/{ip}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>* {ip optional}</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": [
        {
            "id": "4",
            "zone": "America/Los_Angeles (-7)"
        },
        {
            "id": "19",
            "timezone": "America/New_York (-4)"
        }
]
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Related Track:</h3>
                <code>GET <?= base_url() ?>v1/audios/related_track/{user_id}</code>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": [
        {
            "id": "35",
            "title": "Go Hard Prod Silo1"
        },
        {
            "id": "39",
            "title": "Beat It"
        },
        {
            "id": "135",
            "title": "Streamy"
        },
        {
            "id": "136",
            "title": "2pac feat Dr.Dre - California Love HD"
        },
        {
            "id": "140",
            "title": "The Box"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>




                <hr>
                <h3>Get Videos by User:</h3>
                <code>GET <?= base_url() ?>v1/videos/{user_id}/{video_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>video_id (optional)</li>
                    <li>?page={page}&page_size={page_size}</li>
                </ul>
                <h3>Example:</h3>
                <ul>
                    <li><?= base_url() ?>v1/videos/15?page=1&page_size=20</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": [
        {
            "id": "151",
            "created_at": "2020-03-16 15:35:27",
            "user_id": "35",
            "status_id": "1",
            "title": "TESTING API 01",
            "url": "https://www.youtube.com/watch?v=2EbI4inaHwM",
            "public": "1",
            "sort": "3",
            "genre_id": "1",
            "related_track": "",
            "scheduled": false,
            "date": "0000-00-00",
            "time": "00:00:00"
        },
        {
            "id": "169",
            "created_at": "2020-04-07 02:22:36",
            "user_id": "35",
            "status_id": "1",
            "title": "TESTING API 02",
            "url": "https://www.youtube.com/watch?v=2EbI4inaHwM",
            "public": "1",
            "sort": "4",
            "genre_id": "2",
            "related_track": "",
            "scheduled": true,
            "date": "2020-04-10",
            "time": "16:00:00"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Insert Video:</h3>
                <code>POST <?= base_url() ?>v1/videos</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>title</li>
                    <li>url</li>
                    <!--<li>coverart</li>-->
                    <li>public</li>
                    <!--<li>date</li>-->
                    <!--<li>time</li>-->
                    <!--<li>timezone</li>-->
                    <!--<li>sort</li>-->
                    <li>genre_id</li>
                    <li>related_track</li>
                    <!--<li>explicit_content</li>-->
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The video has been created successfully.",
    "id":"1",
    "data": {
        "id": "169",
        "created_at": "2020-04-07 02:22:36",
        "user_id": "35",
        "status_id": "1",
        "title": "TESTING API",
        "url": "https://www.youtube.com/watch?v=2EbI4inaHwM",
        "public": "1",
        "sort": "4",
        "genre_id": "2",
        "related_track": "",
        "date": "2020-04-10",
        "time": "12:00:00",
        "scheduled": true
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Update Video:</h3>
                <code>PUT <?= base_url() ?>v1/videos/{video_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <!--<li>status_id</li>-->
                    <li>title</li>
                    <li>url</li>
                    <!--<li>coverart</li>-->
                    <li>public</li>
                    <li>scheduled(1-Yes, 0-No)</li>
                    <li>date</li>
                    <li>time</li>
                    <!--<li>timezone</li>-->
                    <!--<li>sort</li>-->
                    <li>genre_id</li>
                    <li>related_track</li>
                    <!--<li>explicit_content</li>-->
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The Video info has been updated successfully.",
    "data": {
        "id": "169",
        "created_at": "2020-04-07 02:22:36",
        "user_id": "35",
        "status_id": "1",
        "title": "TESTING API",
        "url": "https://www.youtube.com/watch?v=2EbI4inaHwM",
        "public": "1",
        "sort": "4",
        "genre_id": "2",
        "related_track": "",
        "date": "2020-04-10",
        "time": "12:00:00",
        "scheduled": true
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Delete Video:</h3>
                <code>DELETE <?= base_url() ?>v1/videos/{video_id}</code>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The Video has been deleted successfully."
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Sort Videos:</h3>
                <code>POST <?= base_url() ?>v1/videos/sort_videos</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>list (JSON Array. Example: [{"id":"10","sort":"1"},{"id":"1","sort":"2"}])</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The information of the videos has been updated correctly"
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Track Type:</h3>
                <code>GET <?= base_url() ?>v1/audios/track_type</code>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": [
        {
            "id": "1",
            "track_type": "Song"
        },
        {
            "id": "2",
            "track_type": "Beat"
        },
        {
            "id": "3",
            "track_type": "Podcast"
        },
        {
            "id": "4",
            "track_type": "Audiobook"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Get Links by User:</h3>
                <code>GET <?= base_url() ?>v1/links/{user_id}/{link_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>link_id  (optional)</li>
                    <li>?page={page}&page_size={page_size}</li>
                </ul>
                <h3>Example:</h3>
                <ul>
                    <li><?= base_url() ?>v1/links/15?page=1&page_size=20</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": [
        {
            "id": "147",
            "created_at": "2020-04-07 18:33:21",
            "user_id": "35",
            "status_id": "1",
            "title": "TESTING API LINK",
            "url": "https://www.youtube.com/watch?v=2EbI4inaHwM",
            "coverart": "download.jpeg",
            "public": "1",
            "sort": "1",
            "scheduled": true,
            "date": "2020-04-10",
            "time": "16:00:00",
            "data_image": "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"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Insert Link:</h3>
                <code>POST <?= base_url() ?>v1/links</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <!--<li>status_id</li>-->
                    <li>title</li>
                    <li>url</li>
                    <li>public</li>
                    <li>scheduled</li>
                    <li>date</li>
                    <li>time</li>
                    <!--<li>timezone</li>-->
                    <li>image(base64_encode)</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The link has been created successfully.",
    "id": 152,
    "data": {
        "id": "152",
        "created_at": "2020-04-15 00:56:24",
        "user_id": "35",
        "status_id": "1",
        "title": "TESTING API LINK WITH IMAGE",
        "url": "https://dev-link-vue.link.stream/login",
        "coverart": null,
        "public": "3",
        "sort": "4",
        "scheduled": false,
        "date": "",
        "time": "",
        "data_image": ""
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Update Link:</h3>
                <code>PUT <?= base_url() ?>v1/links/{link_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <!--<li>user_id</li>-->
                    <li>status_id</li>
                    <li>title</li>
                    <li>url</li>
                    <li>public</li>
                    <li>date</li>
                    <li>time</li>
                    <li>timezone</li>
                    <li>sort</li>
                    <li>image(base64_encode)</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The Link info has been updated successfully.",
    "data": {
        "id": "147",
        "created_at": "2020-04-07 18:33:21",
        "user_id": "35",
        "status_id": "1",
        "title": "TESTING API LINK",
        "url": "https://www.youtube.com/watch?v=2EbI4inaHwM",
        "coverart": null,
        "public": "3",
        "publish_at": "2020-04-10 16:00:00",
        "timezone": "19",
        "sort": "1",
        "data_image": "",
        "date": "2020-04-10",
        "time": "12:00:00"
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Sort Links:</h3>
                <code>POST <?= base_url() ?>v1/links/sort_links</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>list (JSON Array. Example: [{"id":"10","sort":"1"},{"id":"1","sort":"2"}])</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The information of the links has been updated correctly"
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Get Audios by User:</h3>
                <code>GET <?= base_url() ?>v1/audios/{user_id}/{audio_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>audio_id (optional)</li>
                    <li>?page={page}&page_size={page_size}</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('');
                    echo '</pre>';
                    ?>
                </p>


                <!-- EXAMPLE 
                <hr>
                <h3>Name:</h3>
                <code>POST <?= base_url() ?>v1/users/example</code>
                <h3>Parameters:</h3>
                 <ul>
                    <li></li>
                    <li></li>
                </ul>
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