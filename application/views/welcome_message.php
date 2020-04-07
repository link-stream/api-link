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
        "2": "Private",
        "3": "Scheduled"
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
            "id": "153",
            "created_at": "2020-03-19 15:43:54",
            "user_id": "35",
            "status_id": "1",
            "title": "Disney OOoo",
            "url": "https://www.youtube.com/watch?v=XOnHtStmbCI",
            "coverart": null,
            "public": "1",
            "publish_at": "0000-00-00 00:00:00",
            "timezone": null,
            "sort": "1",
            "genre_id": "2",
            "related_track": "0",
            "explicit_content": "0",
            "date": "",
            "time": ""
        },
        {
            "id": "169",
            "created_at": "2020-04-07 02:22:36",
            "user_id": "35",
            "status_id": "1",
            "title": "TESTING API",
            "url": "https://www.youtube.com/watch?v=2EbI4inaHwM",
            "coverart": null,
            "public": "3",
            "publish_at": "2020-04-10 16:00:00",
            "timezone": "19",
            "sort": "2",
            "genre_id": "2",
            "related_track": "0",
            "explicit_content": "0",
            "date": "2020-04-10",
            "time": "12:00:00"
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
                    <li>date</li>
                    <li>time</li>
                    <li>timezone</li>
                    <li>sort</li>
                    <li>genre_id</li>
                    <li>related_track</li>
                    <li>explicit_content</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The video has been created successfully.",
    "id":"1"
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Update Video:</h3>
                <code>PUT <?= base_url() ?>v1/videos/{video_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>status_id</li>
                    <li>title</li>
                    <!--<li>url</li>-->
                    <!--<li>coverart</li>-->
                    <li>public</li>
                    <li>date</li>
                    <li>time</li>
                    <li>timezone</li>
                    <li>sort</li>
                    <li>genre_id</li>
                    <li>related_track</li>
                    <li>explicit_content</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The Video info has been updated successfully.",
    "data": []
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
                <code>POST <?= base_url() ?>v1/links/{user_id}/{link_id}</code>
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
            "title": "TESTING API",
            "url": "https://www.youtube.com/watch?v=2EbI4inaHwM",
            "coverart": null,
            "public": "3",
            "publish_at": "2020-04-10 16:00:00",
            "timezone": "19",
            "sort": "1",
            "date": "2020-04-10",
            "time": "12:00:00",
            "data_image": ""
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
                    <li>status_id</li>
                    <li>title</li>
                    <li>url</li>
                    <li>public</li>
                    <li>date</li>
                    <li>time</li>
                    <li>timezone</li>
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
    "id":"1"
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