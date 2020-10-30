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
            @import url('https://fonts.googleapis.com/css2?family=Montserrat&display=swap');
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
                /*font-family: 'Montserrat', sans-serif;*/
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
            <h1>Welcome to LinkStream API V1! </h1>

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
    "env": "dev",
    "data": {
        "id": "35",
        "user_name": "AAAA1234",
        "first_name": "Paolo",
        "last_name": "LinkStream",
        "display_name": "Paolo_LinkStream",
        "email": "paul@link.stream",
        "plan_id": "1",
        "created_at": "2020-02-17 22:34:38",
        "url": "paolo_linkstream",
        "phone": null,
        "image": "54ec6e5b610a3c082d8d6641f59b94f9.jpeg",
        "banner": "b3c8e56a73665ff17156353b17e27888.png",
        "about": null,
        "email_paypal": null,
        "platform": "LinkStream",
        "platform_id": null,
        "platform_token": null,
        "city": "Fort Lauderdale",
        "country": "US",
        "bio": "Making Progress...",
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX2lkI"
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
                    <li>type (optional)</li>
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
        "user_name": "AAAA1234",
        "first_name": "Paolo",
        "last_name": "LinkStream",
        "display_name": "Paolo_LinkStream",
        "email": "paul@link.stream",
        "plan_id": "2",
        "created_at": "2020-02-17 22:34:38",
        "url": "paolo_linkstream",
        "phone": null,
        "image": "54ec6e5b610a3c082d8d6641f59b94f9.jpeg",
        "banner": "b3c8e56a73665ff17156353b17e27888.png",
        "about": null,
        "email_paypal": null,
        "city": "Fort Lauderdale",
        "country": "US",
        "bio": "Making Progress...",
        "data_image": "/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDA",
        "data_banner": "iVBORw0KGgoAAAANSUhEUgAABAAAAAEE"
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
                    <li>current_password</li>
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
                    <li>timezone</li>
                    <li>facebook</li>
                    <li>twitter</li>
                    <li>instagram</li>
                    <li>soundcloud</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The user info has been updated successfully.",
    "data": {
        "id": "35",
        "user_name": "AAAA1234",
        "first_name": "Paolo",
        "last_name": "LinkStream",
        "display_name": "Paolo_LinkStream",
        "email": "paul@link.stream",
        "plan_id": "2",
        "created_at": "2020-02-17 22:34:38",
        "url": "paolo_linkstream",
        "phone": null,
        "image": "54ec6e5b610a3c082d8d6641f59b94f9.jpeg",
        "banner": "b3c8e56a73665ff17156353b17e27888.png",
        "about": null,
        "email_paypal": null,
        "city": "Fort Lauderdale",
        "country": "US",
        "bio": "Making Progress...",
        "data_image": "/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDA",
        "data_banner": "iVBORw0KGgoAAAANSUhEUgAABAAAAAEE"
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
            "id": "216",
            "created_at": "2020-04-13 20:39:13",
            "user_id": "35",
            "status_id": "1",
            "title": "DANIEL TIGERS NEIGHBORHOOD",
            "url": "https://www.youtube.com/watch?v=lY0jVccFGGc",
            "public": "1",
            "sort": "1",
            "genre_id": "2",
            "related_track": "",
            "scheduled": false,
            "date": "",
            "time": ""
        },
        {
            "id": "227",
            "created_at": "2020-04-14 14:20:07",
            "user_id": "35",
            "status_id": "1",
            "title": "Alyssa Video",
            "url": "https://www.youtube.com/watch?v=Eg08rJGKjtA",
            "public": "1",
            "sort": "2",
            "genre_id": "2",
            "related_track": "",
            "scheduled": false,
            "date": "",
            "time": ""
        },
        {
            "id": "215",
            "created_at": "2020-04-13 20:38:29",
            "user_id": "35",
            "status_id": "1",
            "title": "Super Why - Baby Dinos Big Discovery",
            "url": "https://www.youtube.com/watch?v=ESxTJu2piUw",
            "public": "1",
            "sort": "3",
            "genre_id": "2",
            "related_track": "",
            "scheduled": true,
            "date": "2020-04-23",
            "time": "12:15:00"
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
            "end_date": "2020-04-20",
            "end_time": "18:00:00",
            "data_image": "/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGB"
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
                    <li>end_date</li>
                    <li>end_time</li>
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
        "end_date": "",
        "end_time": "",
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
                    <!--<li>status_id</li>-->
                    <li>title</li>
                    <li>url</li>
                    <li>public</li>
                    <li>scheduled</li>
                    <li>date</li>
                    <li>time</li>
                    <li>end_date</li>
                    <li>end_time</li>
                    <!--<li>timezone</li>-->
                    <!--<li>sort</li>-->
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
        "coverart": "",
        "public": "1",
        "sort": "1",
        "data_image": "",
        "scheduled": true,
        "date": "2020-04-10",
        "time": "12:00:00",
        "end_date": "2020-04-20",
        "end_time": "18:00:00"
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Delete Link:</h3>
                <code>DELETE <?= base_url() ?>v1/links/{link_id}</code>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The Link has been deleted successfully."
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
            "track_type": "Song",
            "active":"1"
        },
        {
            "id": "2",
            "track_type": "Beat",
            "active":"1"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Audio Key:</h3>
                <code>GET <?= base_url() ?>v1/audios/audio_key</code>
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
            "name": "A-flat minor"
        },
        {
            "id": "2",
            "name": "A-flat major"
        }...
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Related Album:</h3>
                <code>GET <?= base_url() ?>v1/albums/related_album/{user_id}/{track_type}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>track_type (optional)</li>
                    <li>?title={title}</li>

                </ul>
                <h3>Example:</h3>
                <ul>
                    <li><?= base_url() ?>v1/albums/related_album/35?title=alb</li>
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
            "id": "1",
            "title": "ALBUM"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Collaborator:</h3>
                <code>GET <?= base_url() ?>v1/users/collaborator/{user_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>?search={search}</li>

                </ul>
                <h3>Example:</h3>
                <ul>
                    <li><?= base_url() ?>v1/users/collaborator/35?search=user_test</li>
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
            "id": "26",
            "user_name": "user_test",
            "email": "user_test@link.stream"
        },
        {
            "id": "27",
            "user_name": "user_test_2",
            "email": "user_test_2@link.stream",
            "image": "54ec6e5b610a3c082d8d6641f59b94f9.jpeg",
            "data_image": "data: image/jpeg;base64,.."
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Invite Collaborator:</h3>
                <code>POST <?= base_url() ?>v1/users/invite_collaborator/{user_id}/{email}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>{user_id}</li>
                    <li>{email}</li>
                </ul>
                <h3>Example:</h3>
                <ul>
                    <li><?= base_url() ?>v1/users/invite_collaborator/35/pepe.cabeza@linkstream.com</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": {
        "id": 105,
        "user_name": "pepe.cabeza",
        "email": "pepe.cabeza@linkstream.com",
        "image": "",
        "data_image": ""
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Get Licenses by User:</h3>
                <code>GET <?= base_url() ?>v1/licenses/{user_id}/{license_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>license_id (optional)</li>
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
            "id": "1",
            "user_id": "35",
            "status_id": "1",
            "title": "Standard License",
            "descripcion": "Untagged MP3",
            "price": "30.00",
            "mp3": "1",
            "wav": "0",
            "trackout_stems": "0",
            "distribution_copies": "0",
            "free_download": "0",
            "audio_streams": "0",
            "music_videos": "0",
            "video_streams": "0",
            "broadcasting_rights": "0",
            "radio_station": "0",
            "paid_performances": "0",
            "non_profit_performances": "Unlimited",
            "license_available": true
        },
        {
            "id": "2",
            "user_id": "35",
            "status_id": "1",
            "title": "Premium License ",
            "descripcion": "Untagged MP3 - WAV",
            "price": "50.00",
            "mp3": "1",
            "wav": "1",
            "trackout_stems": "0",
            "distribution_copies": "0",
            "free_download": "0",
            "audio_streams": "0",
            "music_videos": "0",
            "video_streams": "0",
            "broadcasting_rights": "0",
            "radio_station": "0",
            "paid_performances": "0",
            "non_profit_performances": "Unlimited",
            "license_available": true
        },
        {
            "id": "3",
            "user_id": "35",
            "status_id": "1",
            "title": "Unlimited License",
            "descripcion": "Untagged MP3 - WAV",
            "price": "100.00",
            "mp3": "1",
            "wav": "1",
            "trackout_stems": "0",
            "distribution_copies": "0",
            "free_download": "0",
            "audio_streams": "0",
            "music_videos": "0",
            "video_streams": "0",
            "broadcasting_rights": "0",
            "radio_station": "0",
            "paid_performances": "0",
            "non_profit_performances": "Unlimited",
            "license_available": true
        },
        {
            "id": "4",
            "user_id": "35",
            "status_id": "1",
            "title": "Unlimited With Trackouts License",
            "descripcion": "Untagged MP3 - WAV - .ZIP/.RAR of track stems",
            "price": "200.00",
            "mp3": "1",
            "wav": "1",
            "trackout_stems": "1",
            "distribution_copies": "0",
            "free_download": "0",
            "audio_streams": "0",
            "music_videos": "0",
            "video_streams": "0",
            "broadcasting_rights": "0",
            "radio_station": "0",
            "paid_performances": "0",
            "non_profit_performances": "Unlimited",
            "license_available": true
        },
        {
            "id": "5",
            "user_id": "35",
            "status_id": "1",
            "title": "Exclusive License",
            "descripcion": "Untagged MP3 - WAV - .ZIP/.RAR of track stems",
            "price": "300.00",
            "mp3": "1",
            "wav": "1",
            "trackout_stems": "1",
            "distribution_copies": "0",
            "free_download": "0",
            "audio_streams": "0",
            "music_videos": "0",
            "video_streams": "0",
            "broadcasting_rights": "0",
            "radio_station": "0",
            "paid_performances": "0",
            "non_profit_performances": "Unlimited",
            "license_available": true
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Update License:</h3>
                <code>PUT <?= base_url() ?>v1/licenses/{license_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>status_id</li>
                    <li>price</li>
                    <li>mp3</li>
                    <li>wav</li>
                    <li>trackout_stems</li>
                    <li>distribution_copies</li>
                    <li>free_download</li>
                    <li>audio_streams</li>
                    <li>music_videos</li>
                    <li>video_streams</li>
                    <li>broadcasting_rights</li>
                    <li>radio_station</li>
                    <li>paid_performances</li>
                    <li>non_profit_performances</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The License info has been updated successfully.",
    "data": {
        "id": "1",
        "user_id": "35",
        "status_id": "1",
        "title": "Standard License",
        "descripcion": "Untagged MP3",
        "price": "30.00",
        "mp3": "1",
        "wav": "0",
        "trackout_stems": "0",
        "distribution_copies": "0",
        "free_download": "0",
        "audio_streams": "0",
        "music_videos": "0",
        "video_streams": "0",
        "broadcasting_rights": "0",
        "radio_station": "0",
        "paid_performances": "0",
        "non_profit_performances": "Unlimited",
        "license_available": true
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Get Audios by User:</h3>
                <code>GET <?= base_url() ?>v1/audios/{user_id}/{track_type}/{audio_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>audio_id (optional)</li>
                    <li>track_type (optional)</li>
                    <li>?page={page}&page_size={page_size}&tag={title}</li>
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
            "id": "173",
            "created_at": "2020-06-03 13:44:31",
            "user_id": "35",
            "status_id": "1",
            "title": "Test",
            "bpm": "1024",
            "key_id": "0",
            "coverart": "0858dd0360424f6384b086ca4d6cb9a4.jpeg",
            "public": "1",
            "publish_at": "0000-00-00 00:00:00",
            "sort": "19",
            "genre_id": "2",
            "track_type": "2",
            "tags": "test",
            "untagged_mp3": "ee92d7354fd40189f1b31a2be78f66f1.mp3",
            "untagged_wav": "ee92d7354fd40189f1b31a2be78f66f1.wav",
            "track_stems": null,
            "tagged_file": null
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Insert Audio:</h3>
                <code>POST <?= base_url() ?>v1/audios</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>title</li>
                    <li>bpm</li>
                    <li>key_id</li>
                    <li>image</li>
                    <li>public</li>
                    <li>scheduled</li>
                    <li>date</li>
                    <li>time</li>
                    <li>track_type (1=Song, 2=Beat, 3=Sound Kit)</li>
                    <li>genre_id</li>
                    <li>tags (Text Example: beat, linkstream, audio)</li>
                    <li>beat_packs (JSON ENCODE Example: ["1","2"])</li>
                    <li>collaborators (JSON ENCODE Example: [{"user_id":"1","profit":"60","publishing":"60"},{"user_id":"2","profit":"40","publishing":"40"}])</li>
                    <li>licenses (JSON ENCODE Example: [{"license_id":"1","price":"20","status_id":"1"},{"license_id":"2","price":"40","status_id":"0"}])</li>
                    <li>marketing (JSON ENCODE Example: [{"marketing_id":"1","connect_id":""},{"marketing_id":"1","connect_id":""}])</li>
                    <li>untagged_mp3_name</li>
                    <li>untagged_mp3 (Example: data:audio/mpeg;base64,SUQzAwAAAAAAZlRDT04A.....)</li>
                    <li>untagged_wav_name</li>
                    <li>untagged_wav (Example: data:audio/mpeg;base64,SUQzAwAAAAAAZlRDT04A.....)</li>
                    <li>track_stems_name - Beat & Sound Kit</li>
                    <li>track_stems (Example: data:@file/zip;base64,UEsDBBQACAAIAPxJmE8AAAA.....) - Beat & Sound Kit</li>
                    <li>tagged_file_name - Beat & Sound Kit</li>
                    <li>tagged_file (Example: data:audio/mpeg;base64,SUQzAwAAAAAAZlRDT04A.....) - Beat & Sound Kit</li>
                    <li>price</li>
                    <li>samples</li>
                    <li>description</li>
                    <li>processing (optional - value: TRUE)</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The audio or beat has been created successfully.",
    "id": 164,
    "data": {
        "id": "164",
        "created_at": "2020-06-02 19:06:35",
        "user_id": "35",
        "status_id": "1",
        "title": "BEAT TEST",
        "bpm": "1024",
        "key_id": "10",
        "coverart": "549e0eb0bf2a3eb4a241f4d3adaaf732.jpeg",
        "public": "1",
        "sort": "18",
        "genre_id": "3",
        "track_type": "2",
        "tags": "beat, linkstream, audio",
        "untagged_mp3": "ee92d7354fd40189f1b31a2be78f66f1.mp3",
        "untagged_wav": "ee92d7354fd40189f1b31a2be78f66f1.wav",
        "track_stems": "a922d63cb28dc9004a0a73d3e948cbf6.zip",
        "tagged_file": "8216dd6cab5b0f76b58fed92426d3da4.mp3",
        "scheduled": true,
        "date": "2020-06-01",
        "time": "22:20:00",
        "data_image": "data: image/jpeg;base64,/9j/4AAQSkZJRgAB"
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Update Audio:</h3>
                <code>PUT <?= base_url() ?>v1/audios/{audio_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <!--<li>user_id</li>-->
                    <li>title</li>
                    <li>bpm</li>
                    <li>key_id</li>
                    <li>image</li>
                    <li>public</li>
                    <li>scheduled</li>
                    <li>date</li>
                    <li>time</li>
                    <!--<li>track_type (1=Song, 2=Beat)</li>-->
                    <li>genre_id</li>
                    <li>tags (Text Example: beat, linkstream, audio)</li>
                    <li>beat_packs (JSON ENCODE Example: ["1","2"])</li>
                    <li>collaborators (JSON ENCODE Example: [{"user_id":"1","profit":"60","publishing":"60"},{"user_id":"2","profit":"40","publishing":"40"}])</li>
                    <li>licenses (JSON ENCODE Example: [{"license_id":"1","price":"20","status_id":"1"},{"license_id":"2","price":"40","status_id":"0"}])</li>
                    <li>marketing (JSON ENCODE Example: [{"marketing_id":"1","connect_id":""},{"marketing_id":"1","connect_id":""}])</li>
                    <li>untagged_mp3_name</li>
                    <li>untagged_mp3 (Example: data:audio/mpeg;base64,SUQzAwAAAAAAZlRDT04A.....)</li>
                    <li>untagged_wav_name</li>
                    <li>untagged_wav (Example: data:audio/mpeg;base64,SUQzAwAAAAAAZlRDT04A.....)</li>
                    <li>track_stems_name - Beat & Sound Kit</li>
                    <li>track_stems (Example: data:@file/zip;base64,UEsDBBQACAAIAPxJmE8AAAA.....) - Beat & Sound Kit</li>
                    <li>tagged_file_name - Beat & Sound Kit</li>
                    <li>tagged_file (Example: data:audio/mpeg;base64,SUQzAwAAAAAAZlRDT04A.....) - Beat & Sound Kit</li>
                    <li>price</li>
                    <li>samples</li>
                    <li>description</li>
                    <li>posting (optional - value: TRUE)</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The Audio or Beat info has been updated successfully",
    "id": 164,
    "data": {
        "id": "164",
        "created_at": "2020-06-02 19:06:35",
        "user_id": "35",
        "status_id": "1",
        "title": "BEAT TEST",
        "bpm": "1024",
        "key_id": "10",
        "coverart": "549e0eb0bf2a3eb4a241f4d3adaaf732.jpeg",
        "public": "1",
        "sort": "18",
        "genre_id": "3",
        "track_type": "2",
        "tags": "beat, linkstream, audio",
        "untagged_mp3": "ee92d7354fd40189f1b31a2be78f66f1.mp3",
        "untagged_wav": "ee92d7354fd40189f1b31a2be78f66f1.wav",
        "track_stems": "a922d63cb28dc9004a0a73d3e948cbf6.zip",
        "tagged_file": "8216dd6cab5b0f76b58fed92426d3da4.mp3",
        "scheduled": true,
        "date": "2020-06-01",
        "time": "22:20:00",
        "data_image": "data: image/jpeg;base64,/9j/4AAQSkZJRgAB"
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Sort Audios:</h3>
                <code>POST <?= base_url() ?>v1/audios/sort_audios</code>
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
    "message": "The information of the audios has been updated correctly"
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Delete Audio:</h3>
                <code>DELETE <?= base_url() ?>v1/audios/{audio_id}</code>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The Audio has been deleted successfully."
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Audio Title Availability:</h3>
                <code>GET <?= base_url() ?>v1/audios/availability/{user_id}/{type}/{track_type}/{audio_id}?value={title name}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>type = 'title'</li>
                    <li>track_type</li>
                    <li>audio_id (optional)</li>
                    <li>value</li>
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
                OR
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "false",
    "env": "dev",
    "error": "Title: BEAT TEST is not available"
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Get Sound Kit File:</h3>
                <code>GET <?= base_url() ?>v1/audios/sound_kit_file/{user_id}/{audio_id}/{file_name}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>audio_id (optional)</li>
                    <li>file_name (Example: file_example_MP3_2MG.mp3)</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": {
        "audio": "data:audio/mpeg;base64,SUQzAwAAAAAAZlRDT04AAAAKAAAAQ2luZW1hdGl"
    }
}');
                    echo '</pre>';
                    ?>
                </p> 



                <hr>
                <h3>Early Access:</h3>
                <code>POST <?= base_url() ?>v1/landing/early_access</code>
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
                </p>


                <hr>
                <h3>Get Albums by User:</h3>
                <code>GET <?= base_url() ?>v1/albums/{user_id}/{album_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>album_id (optional)</li>
                    <li>?page={page}&page_size={page_size}</li>

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
            "created_at": "2020-07-09 01:31:20",
            "user_id": "35",
            "status_id": "1",
            "title": "Album Title",
            "coverart": null,
            "public": "1",
            "publish_at": "2020-07-10 10:00:00",
            "genre_id": "1",
            "track_type": "2",
            "price": "10.00",
            "license_id": "2",
            "tags": "beat, linkstream, audio",
            "sort": "3",
            "description": "Album Description",
            "scheduled": true,
            "date": "2020-07-10",
            "time": "10:00:00",
            "data_image": "",
            "beats": [
                {
                    "id_audio": "1"
                },
                {
                    "id_audio": "2"
                }
            ]
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Insert Album:</h3>
                <code>POST <?= base_url() ?>v1/albums</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>title</li>
                    <li>image</li>
                    <li>public</li>
                    <li>scheduled</li>
                    <li>date</li>
                    <li>time</li>
                    <li>genre_id</li>
                    <li>price</li>
                    <li>license_id</li>
                    <li>tags (Text Example: beat, linkstream, audio)</li>
                    <li>beats (JSON ENCODE Example: ["1","2"])</li>
                    <li>description</li>
                    <!--<li></li>-->
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The album has been created successfully.",
    "id": 2,
    "data": {
        "id": "2",
        "created_at": "2020-07-09 01:27:19",
        "user_id": "35",
        "status_id": "1",
        "title": "Album Title",
        "coverart": null,
        "public": "1",
        "publish_at": "2020-07-10 10:00:00",
        "genre_id": "1",
        "track_type": "2",
        "price": "10.00",
        "license_id": "2",
        "tags": "beat, linkstream, audio",
        "sort": "1",
        "description": "Album Description",
        "scheduled": true,
        "date": "2020-07-10",
        "time": "10:00:00",
        "data_image": "",
        "beats": ""
    }
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Update Album:</h3>
                <code>PUT <?= base_url() ?>v1/albums/{album_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>title</li>
                    <li>image</li>
                    <li>public</li>
                    <li>scheduled</li>
                    <li>date</li>
                    <li>time</li>
                    <li>genre_id</li>
                    <li>price</li>
                    <li>license_id</li>
                    <li>tags (Text Example: beat, linkstream, audio)</li>
                    <li>beats (JSON ENCODE Example: ["1","2"])</li>
                    <li>description</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The Album info has been updated successfully.",
    "data": {
        "id": "3",
        "created_at": "2020-07-09 01:29:45",
        "user_id": "35",
        "status_id": "1",
        "title": "Album Title PUT",
        "coverart": "",
        "public": "1",
        "publish_at": "2020-07-13 13:00:00",
        "genre_id": "3",
        "track_type": "2",
        "price": "15.00",
        "license_id": "3",
        "tags": "beat, linkstream, audio,  PUT",
        "sort": "2",
        "description": "Album Description  PUT",
        "date": "2020-07-13",
        "time": "13:00:00",
        "scheduled": true
    }
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Sort Albums:</h3>
                <code>POST <?= base_url() ?>v1/albums/sort_albums</code>
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
    "message": "The information of the albums has been updated correctly"
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Delete Albums:</h3>
                <code>DELETE <?= base_url() ?>v1/albums/{album_id}</code>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The Album has been deleted successfully."
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Get Purchases by User:</h3>
                <code>GET <?= base_url() ?>v1/users/purchases/{user_id}</code>
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
    "env": "dev",
    "data": [
        {
            "id": "1",
            "invoice_number": "LS0000001",
            "created_at": "2020-07-22 02:02:49",
            "user_id": "35",
            "status": "COMPLETED",
            "amount": "17.95",
            "details": [
                {
                    "id": "1",
                    "invoice_id": "1",
                    "item_id": "155",
                    "item_title": "Beat 01",
                    "item_amount": "15.00",
                    "item_track_type": "2",
                    "item_table": "st_audio",
                    "producer_id": "35",
                    "display_name": "Paolo_LinkStream",
                    "track_type": "Beat",
                    "data_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAA"
                },
                {
                    "id": "2",
                    "invoice_id": "1",
                    "item_id": "156",
                    "item_title": "Beat 02",
                    "item_amount": "2.95",
                    "item_track_type": "2",
                    "item_table": "st_audio",
                    "producer_id": "35",
                    "display_name": "Paolo_LinkStream",
                    "track_type": "Beat",
                    "data_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ"
                }
            ]
        },
        {
            "id": "2",
            "invoice_number": "LS0000002",
            "created_at": "2020-07-22 02:02:49",
            "user_id": "35",
            "status": "COMPLETED",
            "amount": "25.00",
            "details": [
                {
                    "id": "3",
                    "invoice_id": "2",
                    "item_id": "305",
                    "item_title": "My Sound Kit",
                    "item_amount": "15.00",
                    "item_track_type": "3",
                    "item_table": "st_audio",
                    "producer_id": "94",
                    "display_name": "Victor",
                    "track_type": "Sound Kit",
                    "data_image": ""
                },
                {
                    "id": "4",
                    "invoice_id": "2",
                    "item_id": "23",
                    "item_title": "My Beat Pack",
                    "item_amount": "10.00",
                    "item_track_type": "4",
                    "item_table": "st_album",
                    "producer_id": "139",
                    "display_name": "Noah Lozevski",
                    "track_type": "Beat Pack",
                    "data_image": ""
                }
            ]
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Insert Payment Method:</h3>
                <code>POST <?= base_url() ?>v1/users/payment_method</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>first_name</li>
                    <li>last_name</li>
                    <li>cc_number</li>
                    <li>expiration_date(mm/yyyy)</li>
                    <li>cvv</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The payment method has been created successfully."
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Get Payment Method by User:</h3>
                <code>GET <?= base_url() ?>v1/users/payment_method/{user_id}</code>
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
    "env": "dev",
    "data": [
        {
            "id": "2",
            "cc_number": "8860",
            "expiration_date": "06/2021",
            "is_default": "1",
            "cc_type": "Visa"
        },
        {
            "id": "3",
            "cc_number": "1018",
            "expiration_date": "06/2021",
            "is_default": "0",
            "cc_type": "Amex"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Update Payment Method:</h3>
                <code>PUT <?= base_url() ?>v1/users/payment_method/{payment_method_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>is_default(1)</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The Payment Method info has been updated successfully."
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Delete Payment Method:</h3>
                <code>DELETE <?= base_url() ?>v1/users/payment_method/{payment_method_id}</code>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The Payment Method has been deleted successfully."
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Get Notification by User::</h3>
                <code>GET <?= base_url() ?>v1/users/notification/{user_id}</code>
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
    "env": "dev",
    "data": [
        {
            "id": "1",
            "user_id": "35",
            "sales_email": "0",
            "sales_push": "0",
            "follows_email": "0",
            "follows_push": "0",
            "likes_email": "0",
            "likes_push": "0",
            "reposts_email": "0",
            "reposts_push": "0",
            "collaborations_email": "0",
            "collaborations_push": "0",
            "ls_features_email": "0",
            "ls_features_push": "0",
            "surveys_email": "0",
            "surveys_push": "0",
            "ls_newsletter_email": "0"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Update Notification:</h3>
                <code>PUT <?= base_url() ?>v1/users/notification/{notification_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>sales_email</li>
                    <li>sales_push</li>
                    <li>follows_email</li>
                    <li>follows_push</li>
                    <li>likes_email</li>
                    <li>likes_push</li>
                    <li>reposts_email</li>
                    <li>reposts_push</li>
                    <li>collaborations_email</li>
                    <li>collaborations_push</li>
                    <li>ls_features_email</li>
                    <li>ls_features_push</li>
                    <li>surveys_email</li>
                    <li>surveys_push</li>
                    <li>ls_newsletter_email</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The Notification info has been updated successfully.",
    "data": {
        "id": "1",
        "user_id": "35",
        "sales_email": "1",
        "sales_push": "1",
        "follows_email": "0",
        "follows_push": "0",
        "likes_email": "0",
        "likes_push": "0",
        "reposts_email": "0",
        "reposts_push": "0",
        "collaborations_email": "0",
        "collaborations_push": "0",
        "ls_features_email": "0",
        "ls_features_push": "0",
        "surveys_email": "0",
        "surveys_push": "0",
        "ls_newsletter_email": "0"
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>GET Profile:</h3>
                <code>GET <?= base_url() ?>v1/profiles/{url}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>url (example: paolo_linkstream)</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": {
        "id": "35",
        "user_name": "paolo_ls",
        "first_name": "Paolo",
        "last_name": "LinkStream",
        "display_name": "Paolo_LinkStream",
        "email": "paul@link.stream",
        "created_at": "2020-02-17 22:34:38",
        "url": "paolo_linkstream",
        "phone": null,
        "image": "54ec6e5b610a3c082d8d6641f59b94f9.jpeg",
        "banner": "b3c8e56a73665ff17156353b17e27888.png",
        "about": null,
        "city": "Fort Lauderdale",
        "country": "US",
        "timezone": null,
        "bio": "Making Progress...",
        "data_image": "data: image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD",
        "data_banner": "data: image/png;base64,iVBORw0KGgoAAAANSUhEUgAABAA"
    }
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>GET Profile Links:</h3>
                <code>GET <?= base_url() ?>v1/profiles/links/{producer_id}/{link_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>producer_id</li>
                    <li>link_id (optional)</li>
                    <li>?page={page}&page_size={page_size}&sort={default or new}&tag={title}</li>
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
            "id": "282",
            "created_at": "2020-08-05 00:23:29",
            "user_id": "35",
            "title": "LinkStream",
            "url": "https://www.linkstream.com",
            "coverart": "38200f9d67ad6f31634da8a46029a230.png",
            "data_image": "data: image/png;base64,iVBORw0KGgoAAAANSUhEUg"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>GET Profile Videos:</h3>
                <code>GET <?= base_url() ?>v1/profiles/videos/{producer_id}/{video_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>producer_id</li>
                    <li>video_id (optional)</li>
                    <li>?page={page}&page_size={page_size}&sort={default or new}&tag={title}&genre={can be a simple id like 1 or 2, can be a list of genres like 1,2,3,4}</li>
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
            "id": "324",
            "created_at": "2020-08-05 00:28:38",
            "user_id": "35",
            "title": "Muchacha. ",
            "url": "https://www.youtube.com/watch?v=OPZE34y3JTM"
        },
        {
            "id": "325",
            "created_at": "2020-08-05 00:29:58",
            "user_id": "35",
            "title": "Macarena  ",
            "url": "https://www.youtube.com/watch?v=8BMnz4i2dM8"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>GET Profile Sound Kits:</h3>
                <code>GET <?= base_url() ?>v1/profiles/sound_kits/{producer_id}/{kit_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>producer_id</li>
                    <li>kit_id (optional)</li>
                    <li>?page={page}&page_size={page_size}&sort={default or new or price_low or price_high or best}&tag={tag or title}&genre={can be a simple id like 1 or 2, can be a list of genres like 1,2,3,4}</li>
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
            "id": "327",
            "created_at": "2020-08-13 07:22:02",
            "user_id": "35",
            "title": "My Sound Kit 1",
            "coverart": "9a8ae0f29db3465d94adb262d7b63983.jpeg",
            "genre_id": "3",
            "track_type": "3",
            "tags": "kit, paolo, linkstream",
            "track_stems_name": "Archive.zip",
            "track_stems": "6292476054fa0d55d174fa313b417433.zip",
            "tagged_file_name": "file_example.mp3",
            "tagged_file": "f208e64af7514de88af0710a262202bf.mp3",
            "price": "150.35",
            "samples": "2",
            "description": "My Sound Kit Test 1",
            "url_user": "",
            "url_title": "",
            "kit_files_name": [],
            "data_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAA",
            "data_track_stems": "",
            "data_tagged_file": ""
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>GET Profile Beats:</h3>
                <!--<code>GET <?= base_url() ?>v1/profiles/beats/{producer_id}/{beats_id}/{beat_type}</code>-->
                <code>GET <?= base_url() ?>v1/profiles/beats/{producer_id}/{beats_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>producer_id</li>
                    <li>beats_id (optional)</li>
                    <!--<li>beat_type (required when beat_id is passed) - (1 = Beat, 2 = Beat_Pack)</li>-->
                    <li>?page={page}&page_size={page_size}&sort={default or new or price_low or price_high or best}&tag={tag or title}&genre={can be a simple id like 1 or 2, can be a list of genres like 1,2,3,4}&bpm_min={bpm_min}&bpm_max={bpm_max}&type={beat, pack or empty in case the end user select both or none}</li>
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
            "id": "321",
            "created_at": "2020-08-05 00:36:24",
            "user_id": "35",
            "title": "My First Beat",
            "bpm": "5",
            "key_id": "1",
            "coverart": "133b78d370da23303d78eaff22222169.jpeg",
            "genre_id": "3",
            "track_type": "2",
            "tags": "paolo, beat, hiphop",
            "untagged_mp3_name": "file_example_MP3_2MG.mp3",
            "untagged_mp3": "1e56e52e39eec3ba8ec64bfb4e12ae0f.mp3",
            "untagged_wav_name": "",
            "untagged_wav": "",
            "track_stems_name": "file_example_MP3_2MG.mp3.zip",
            "track_stems": "fab4ca02d94a31dcae9b90d0bdd60b8a.zip",
            "tagged_file_name": "",
            "tagged_file": "",
            "url_user": "",
            "url_title": "",
            "beat_packs": "",
            "licenses": [
                {
                    "license_id": "1",
                    "price": "30.00",
                    "status_id": "1",
                    "mp3": "1",
                    "wav": "0",
                    "trackout_stems": "0"
                },
                {
                    "license_id": "2",
                    "price": "60.00",
                    "status_id": "1",
                    "mp3": "1",
                    "wav": "1",
                    "trackout_stems": "0"
                }
            ],
            "collaborators": "",
            "marketing": "",
            "data_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQAB",
            "data_untagged_mp3": "",
            "data_untagged_wav": "",
            "data_track_stems": "",
            "data_tagged_file": ""
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>GET Profile Genres:</h3>
                <code>GET <?= base_url() ?>v1/profiles/genres/{producer_id}/{type}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>producer_id</li>
                    <li>type {beats or kits or videos}</li>
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
            "id": "2",
            "genre": "Custom"
        },
        {
            "id": "3",
            "genre": "Hip-hop & Rap"
        },
        {
            "id": "4",
            "genre": "Alternative Rock"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>GET Profile Licenses:</h3>
                <code>GET <?= base_url() ?>v1/profiles/licenses/{producer_id}/{license_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>producer_id</li>
                    <li>license_id (optional)</li>
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
            "id": "1",
            "user_id": "35",
            "status_id": "1",
            "title": "Standard License",
            "descripcion": "Untagged MP3",
            "price": "30.00",
            "mp3": "1",
            "wav": "0",
            "trackout_stems": "0",
            "distribution_copies": "0",
            "free_download": "0",
            "audio_streams": "0",
            "music_videos": "0",
            "video_streams": "0",
            "broadcasting_rights": "0",
            "radio_station": "0",
            "paid_performances": "0",
            "non_profit_performances": "Unlimited",
            "license_available": true
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Get Profile-Sound Kit File:</h3>
                <code>GET <?= base_url() ?>v1/profiles/sound_kit_file/{user_id}/{audio_id}/{file_name}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>audio_id</li>
                    <li>file_name (Example: file_example_MP3_2MG.mp3)</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": {
        "audio": "data:audio/mpeg;base64,SUQzAwAAAAAAZlRDT04AAAAKAAAA"
    }
}');
                    echo '</pre>';
                    ?>
                </p> 


                <hr>
                <h3>GET Messages List:</h3>
                <code>GET <?= base_url() ?>v1/marketing/messages/{user_id}/{message_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>message_id (optional)</li>
                    <li>?page={page}&page_size={page_size}</li>
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
            "id": "3",
            "user_id": "35",
            "type": "Email",
            "status": "Draft",
            "created_at": "2020-09-07 17:31:32",
            "campaing_name": "Campaing 001 - 002",
            "send_to": "all",
            "reply_to": "paul@linkstream.com",
            "subject": "Email Subject",
            "content": "HTML CONTENT",
            "open": "0",
            "click": "0",
            "revenue": "0.00",
            "scheduled": true,
            "date": "2020-09-15",
            "time": "14:00:00"
        },
        {
            "id": "2",
            "user_id": "35",
            "type": "SMS",
            "status": "Draft",
            "created_at": "2020-09-07 17:30:17",
            "campaing_name": "",
            "send_to": "all",
            "reply_to": "",
            "subject": "",
            "content": "SMS TO SENT",
            "open": "0",
            "click": "0",
            "revenue": "0.00",
            "scheduled": true,
            "date": "2020-09-15",
            "time": "14:00:00"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Insert Message:</h3>
                <code>POST <?= base_url() ?>v1/marketing/messages</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>type (Email or SMS)</li>
                    <li>campaing_name</li>
                    <li>send_to</li>
                    <li>reply_to_name</li>
                    <li>reply_to</li>
                    <li>subject</li>
                    <li>content</li>
                    <li>scheduled</li>
                    <li>date</li>
                    <li>time</li>
                    <li>logo</li>
                    <li>artwork</li>
                    <li>button_color</li> 
                    <li>background_color</li> 
                    <li>background_image</li>
                    <li>status (Draft, Scheduled, Pending)</li>
                    <li>headline</li>
                    <li>body</li> 
                    <li>promote_id</li>
                    <li>template_type</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The message has been created successfully.",
    "id": 6,
    "data": {
        "user_id": "35",
        "status": "Draft",
        "type": "Email",
        "campaing_name": "Campaing 001",
        "send_to": "all",
        "reply_to_name": "Paolo_LinkStream",
        "reply_to": "paul@linkstream.com",
        "subject": "Email Subject Doc",
        "content": "HTML CONTENT 1",
        "id": 6,
        "scheduled": true,
        "date": "2020-09-15",
        "time": "14:00:00"
    }
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Update Message:</h3>
                <code>PUT <?= base_url() ?>v1/marketing/messages/{message_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>campaing_name</li>
                    <li>send_to</li>
                    <li>reply_to_name</li>
                    <li>reply_to</li>
                    <li>subject</li>
                    <li>content</li>
                    <li>scheduled</li>
                    <li>date</li>
                    <li>time</li>
                    <li>logo</li>
                    <li>artwork</li>
                    <li>button_color</li> 
                    <li>background_color</li> 
                    <li>background_image</li>
                    <li>status (Draft, Scheduled, Pending)</li>
                    <li>headline</li>
                    <li>body</li> 
                    <li>promote_id</li>
                    <li>template_type</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The message info has been updated successfully.",
    "data": {
        "id": "3",
        "user_id": "35",
        "type": "Email",
        "status": "Draft",
        "created_at": "2020-09-07 17:31:32",
        "campaing_name": "Campaing 001 - 002",
        "send_to": "all",
        "reply_to_name": "Paolo_LinkStream",
        "reply_to": "paul@linkstream.com",
        "subject": "Email Subject Edit",
        "content": "HTML CONTENT",
        "open": "0",
        "click": "0",
        "revenue": "0.00",
        "scheduled": true,
        "date": "2020-09-15",
        "time": "14:00:00"
    }
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Delete Message:</h3>
                <code>DELETE <?= base_url() ?>v1/marketing/messages/{message_id}</code>
                <!--                <h3>Parameters:</h3>
                                 <ul>
                                    <li></li>
                                    <li></li>
                                </ul>-->
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The message has been deleted successfully."
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>GET Message Sent to List:</h3>
                <code>GET <?= base_url() ?>v1/marketing/messages_sent_to/{user_id}</code>
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
    "env": "dev",
    "data": [
        {
    "status": "success",
    "env": "dev",
    "data": {
        "all-subscribers": "All Subscribers in Audience",
        "new-subscribers": "New Subscribers",
        "beats": "beats",
        "links": "links",
        "videos": "videos"
    }
}
    ]
}');
                    echo '</pre>';
                    ?>
                </p>





                <hr>
                <h3>GET Subscribers List:</h3>
                <code>GET <?= base_url() ?>v1/marketing/subscribers/{user_id}/{subscriber_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>subscriber_id (optional)</li>
                    <li>?page={page}&page_size={page_size}&search={search box}</li>
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
            "id": "1",
            "user_id": "35",
            "created_at": "2020-09-07 20:31:53",
            "email": "paul@linkstream.com",
            "phone": "305-970-0000",
            "name": "Paul Test",
            "birthday": "01/15",
            "tags": "beats, links",
            "email_status": "subscribed",
            "sms_status": "subscribed"
        },
        {
            "id": "2",
            "user_id": "35",
            "created_at": "2020-09-07 21:06:13",
            "email": "paul@link.stream",
            "phone": "305-970-0001",
            "name": "Paul Testing",
            "birthday": "01/15",
            "tags": "beats, links, videos",
            "email_status": "subscribed",
            "sms_status": "subscribed"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Insert Subscriber:</h3>
                <code>POST <?= base_url() ?>v1/marketing/subscribers</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>email</li>
                    <li>phone</li>
                    <li>name</li>
                    <li>birthday</li>
                    <li>tags</li>
                    <li>gender</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The subscriber has been created successfully.",
    "id": 2,
    "data": {
        "user_id": "35",
        "email": "pau1@linkstream.com",
        "phone": "305-970-000",
        "name": "Paul Test",
        "birthday": "01/15",
        "tags": "beats, links, videos",
        "email_status": "subscribed",
        "sms_status": "subscribed",
        "id": 2
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Update Subscriber:</h3>
                <code>PUT <?= base_url() ?>v1/marketing/subscribers/{subscriber_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>email</li>
                    <li>phone</li>
                    <li>name</li>
                    <li>birthday</li>
                    <li>tags</li>
                    <li>gender</li>
                    <li>note</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The subscriber info has been updated successfully.",
    "data": {
        "id": "1",
        "user_id": "35",
        "created_at": "2020-09-07 20:31:53",
        "email": "paul@linkstream.com",
        "phone": "305-970-0000",
        "name": "Paul Test",
        "birthday": "01/15",
        "tags": "beats, links",
        "email_status": "subscribed",
        "sms_status": "subscribed"
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>GET Media List:</h3>
                <code>GET <?= base_url() ?>v1/marketing/user_media_files/{user_id}/{media_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>media_id (optional)</li>
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
            "id": "2",
            "user_id": "35",
            "created_at": "2020-09-16 20:47:17",
            "image_url": "https://s3.us-east-2.amazonaws.com/files.link.stream/Dev/Media/",
            "image_name": "35_feef955e6fc886d8e1817ac3b41e47be.jpeg",
            "status": "ACTIVE"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Insert Media:</h3>
                <code>POST <?= base_url() ?>v1/marketing/user_media_files</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>media</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The media has been created successfully.",
    "data": {
        "image_name": "35_91910fc01776a7657aded72c05b1086c.jpeg",
        "image_url": "https://s3.us-east-2.amazonaws.com/files.link.stream/Dev/Media/",
        "user_id": "35",
        "status": "ACTIVE",
        "id": 3
    }
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Delete Media:</h3>
                <code>DELETE <?= base_url() ?>v1/marketing/user_media_files/{media_id}</code>
                <!--                <h3>Parameters:</h3>
                                 <ul>
                                    <li></li>
                                    <li></li>
                                </ul>-->
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The media has been deleted successfully."
}');
                    echo '</pre>';
                    ?>
                </p>



                <hr>
                <h3>GET Tags List:</h3>
                <code>GET <?= base_url() ?>v1/marketing/tags/{user_id}</code>
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
    "env": "dev",
    "data": {
        "beats": "beats",
        "links": "links",
        "videos": "videos"
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Subscribers Actions Bulk:</h3>
                <code>POST <?= base_url() ?>v1/marketing/subscribers_action_bulk</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>action (unsubscribe or resubscribe or unsubscribe_email or resubscribe_email or unsubscribe_sms or resubscribe_sms)</li>
                    <li>list (JSON Array. Example: [{"id":"1"},{"id":"2"}])</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "Subscribers updated successfully."
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>GET Message Report:</h3>
                <code>GET <?= base_url() ?>v1/marketing/messages_report/{user_id}/{message_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>message_id</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": {
        "Message": {
            "id": "77",
            "user_id": "35",
            "type": "Email",
            "status": "Sent",
            "created_at": "2020-10-16 19:27:41",
            "campaing_name": "Message Scheduled",
            "send_to": "paolo_email",
            "reply_to_name": "Paul Ferra",
            "reply_to": "paul@link.stream",
            "subject": "Message Scheduled",
            "headline": "My Moon Kit ",
            "body": "Testin Scheduled Messages",
            "content": "",
            "promote_id": "https://dev-link-vue.link.stream/paolo_linkstream/kits/381",
            "template_type": "release",
            "logo": "35_05fc3000fdced8fa351a487a1cc26ddc.png",
            "artwork": "35_33a56191afca1a684fac8ed7e445f8bd.png",
            "button_color": "#DC2EA6",
            "background_color": "",
            "background_image": "",
            "st_marketing_messagescol": null,
            "sent_at": "2020-10-17 00:27:41",
            "sent_to": "2",
            "open": "2",
            "click": "0",
            "revenue": "0.00",
            "scheduled": true,
            "date": "2020-10-16",
            "time": "00:00:00"
        },
        "Overview": {
            "Total": "2",
            "Open_rate": "100.0%",
            "Click_rate": "0.0%",
            "Orders": "0",
            "Revenue": "$ 0",
            "Unsubscribed": "0",
            "Hours": {
                "000": {
                    "Open": "1",
                    "Click": "0"
                },
                "100": {
                    "Open": "1",
                    "Click": "1"
                },
                "200": {
                    "Open": "0",
                    "Click": "1"
                },
                "300": {
                    "Open": "0",
                    "Click": "1"
                },
                "1400": {
                    "Open": "2",
                    "Click": "0"
                }
            },
            "Location": [
                {
                    "country": {
                        "name": "Japan",
                        "code": "JP"
                    },
                    "opens": "1",
                    "percent": "50.0"
                },
                {
                    "country": {
                        "name": "United States",
                        "code": "US"
                    },
                    "opens": "1",
                    "percent": "50.0"
                }
            ]
        },
        "Activity": {
            "05/23/2020 15:37": "Open",
            "05/23/2020 15:00": "Open",
            "05/23/2020 14:21": "Click: https://www.linkstream.com/",
            "05/23/2020 14:20": "Open",
            "05/23/2020 13:37": "Open",
            "05/23/2020 13:00": "Open",
            "05/23/2020 12:20": "Open"
        }
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Subscribers Import:</h3>
                <code>POST <?= base_url() ?>v1/marketing/subscribers_import</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>list (JSON Array. Example: [{"email":"a@a.com","phone":"1111111111","name":"Name","birthday":"01\/15","tags":"beats, links, videos","email_status":"subscribed","sms_status":"unsubscribed"},{"email":"b@a.com","phone":"1111111112","name":"Name2","birthday":"02\/15","tags":"beats, links, audios","email_status":"subscribed","sms_status":"subscribed"}])</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The subscriber has been created successfully.",
    "id": 2,
    "data": {
        "user_id": "35",
        "email": "pau1@linkstream.com",
        "phone": "305-970-000",
        "name": "Paul Test",
        "birthday": "01/15",
        "tags": "beats, links, videos",
        "email_status": "subscribed",
        "sms_status": "subscribed",
        "id": 2
    }
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>GET Beats Tab:</h3>
                <code>GET <?= base_url() ?>v1/profiles/beats_tab/{url}/{audio_id}/{beat_type}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>url (example: paolo_linkstream)</li>
                    <li>audio_id (optional)</li>
                    <li>beat_type (required if audio_id is not empty. type: beat or pack)</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": {
        "profile": {
            "id": "35",
            "user_name": "paolo_ls",
            "first_name": "Paolo",
            "last_name": "LinkStream",
            "display_name": "Paolo_LinkStream",
            "url": "paolo_linkstream",
            "image": "54ec6e5b610a3c082d8d6641f59b94f9.jpeg",
            "banner": "b3c8e56a73665ff17156353b17e27888.png",
            "city": "Fort Lauderdale",
            "country": "US",
            "bio": "Making Progress...LinkStream is Coming!!!",
            "followers": "0",
            "plays": "0",
            "beats": "0",
            "data_image": "data: image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD",
            "data_banner": "data: image/png;base64,iVBORw0KGgoAAAANSUhEUgAABAA",
        },
        "genres": [
            {
                "id": "2",
                "genre": "Custom"
            },
            {
                "id": "3",
                "genre": "Hip-hop & Rap"
            }
        ],
        "licenses": [
            {
                "id": "1",
                "user_id": "35",
                "status_id": "1",
                "title": "Standard License",
                "descripcion": "Untagged MP3",
                "price": "30.00",
                "mp3": "1",
                "wav": "0",
                "trackout_stems": "0",
                "distribution_copies": "0",
                "free_download": "0",
                "audio_streams": "0",
                "music_videos": "0",
                "video_streams": "0",
                "broadcasting_rights": "0",
                "radio_station": "0",
                "paid_performances": "0",
                "non_profit_performances": "Unlimited",
                "license_available": true
            },
            {
                "id": "2",
                "user_id": "35",
                "status_id": "1",
                "title": "Premium License ",
                "descripcion": "Untagged MP3 - WAV",
                "price": "50.00",
                "mp3": "1",
                "wav": "1",
                "trackout_stems": "0",
                "distribution_copies": "0",
                "free_download": "0",
                "audio_streams": "0",
                "music_videos": "0",
                "video_streams": "0",
                "broadcasting_rights": "0",
                "radio_station": "0",
                "paid_performances": "0",
                "non_profit_performances": "Unlimited",
                "license_available": true
            },
            {
                "id": "3",
                "user_id": "35",
                "status_id": "1",
                "title": "Unlimited License",
                "descripcion": "Untagged MP3 - WAV",
                "price": "100.00",
                "mp3": "1",
                "wav": "1",
                "trackout_stems": "0",
                "distribution_copies": "0",
                "free_download": "0",
                "audio_streams": "0",
                "music_videos": "0",
                "video_streams": "0",
                "broadcasting_rights": "0",
                "radio_station": "0",
                "paid_performances": "0",
                "non_profit_performances": "Unlimited",
                "license_available": true
            },
            {
                "id": "4",
                "user_id": "35",
                "status_id": "1",
                "title": "Unlimited With Trackouts License",
                "descripcion": "Untagged MP3 - WAV - .ZIP/.RAR of track stems",
                "price": "200.00",
                "mp3": "1",
                "wav": "1",
                "trackout_stems": "1",
                "distribution_copies": "0",
                "free_download": "0",
                "audio_streams": "0",
                "music_videos": "0",
                "video_streams": "0",
                "broadcasting_rights": "0",
                "radio_station": "0",
                "paid_performances": "0",
                "non_profit_performances": "Unlimited",
                "license_available": true
            },
            {
                "id": "5",
                "user_id": "35",
                "status_id": "1",
                "title": "Exclusive License",
                "descripcion": "Untagged MP3 - WAV - .ZIP/.RAR of track stems",
                "price": "300.00",
                "mp3": "1",
                "wav": "1",
                "trackout_stems": "1",
                "distribution_copies": "0",
                "free_download": "0",
                "audio_streams": "0",
                "music_videos": "0",
                "video_streams": "0",
                "broadcasting_rights": "0",
                "radio_station": "0",
                "paid_performances": "0",
                "non_profit_performances": "Unlimited",
                "license_available": true
            }
        ],
        "beats": [
            {
                "id": "33",
                "user_id": "35",
                "title": "My Beat Pack",
                "coverart": "121988630b65aa812aacd7906d1cde3d..png",
                "genre_id": "2",
                "track_type": "2",
                "tags": "pack",
                "price": "50.00",
                "samples": "",
                "description": "My Pack",
                "type": "pack",
                "data_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD",
                "beats": [
                    {
                        "id_audio": "378"
                    },
                    {
                        "id_audio": "379"
                    },
                    {
                        "id_audio": "380"
                    }
                ]
            },
            {
                "id": "379",
                "user_id": "35",
                "title": "My Beat 5Mb",
                "coverart": "069e711921ab22c99d991f59ae01418c.png",
                "genre_id": "3",
                "track_type": "2",
                "tags": "beat, example, paolo",
                "type": "beat",
                "licenses": [
                    {
                        "license_id": "1",
                        "price": "30.00",
                        "status_id": "1",
                        "mp3": "1",
                        "wav": "0",
                        "trackout_stems": "0"
                    },
                    {
                        "license_id": "2",
                        "price": "50.00",
                        "status_id": "1",
                        "mp3": "1",
                        "wav": "1",
                        "trackout_stems": "0"
                    }
                ],
                "data_image": "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD"
            },
            {
                "id": "380",
                "user_id": "35",
                "title": "My Beat 10MB",
                "coverart": "b65db62d53337dbade3b7e91bbe690ea.png",
                "genre_id": "3",
                "track_type": "2",
                "tags": "paolo, linkstream, beat",
                "type": "beat",
                "licenses": [
                    {
                        "license_id": "1",
                        "price": "30.00",
                        "status_id": "1",
                        "mp3": "1",
                        "wav": "0",
                        "trackout_stems": "0"
                    },
                    {
                        "license_id": "2",
                        "price": "50.00",
                        "status_id": "1",
                        "mp3": "1",
                        "wav": "1",
                        "trackout_stems": "0"
                    },
                    {
                        "license_id": "3",
                        "price": "100.00",
                        "status_id": "1",
                        "mp3": "1",
                        "wav": "1",
                        "trackout_stems": "0"
                    }
                ],
                "data_image": "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAA0Q"
            }
        ]
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Insert Audio Action:</h3>
                <code>POST <?= base_url() ?>v1/profiles/audio_action</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>audio_id</li>
                    <li>audio_type (beat, pack, kit)</li>
                    <li>action (play)</li>
                    <li>user_id</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The audio action has been created successfully."
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Insert Action:</h3>
                <code>POST <?= base_url() ?>v1/profiles/action</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>id</li>
                    <li>type (beat, pack, kit, video, link)</li>
                    <li>action (play)</li>
                    <li>user_id</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The action has been created successfully."
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>GET Sound Kits Tab:</h3>
                <code>GET <?= base_url() ?>v1/profiles/sound_kits_tab/{url}/{audio_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>url (example: paolo_linkstream)</li>
                    <li>audio_id (optional)</li>

                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": {
        "profile": {
            "id": "35",
            "user_name": "paolo_ls",
            "first_name": "Paolo",
            "last_name": "LinkStream",
            "display_name": "Paolo_LinkStream",
            "url": "paolo_linkstream",
            "image": "54ec6e5b610a3c082d8d6641f59b94f9.jpeg",
            "banner": "b3c8e56a73665ff17156353b17e27888.png",
            "city": "Fort Lauderdale",
            "country": "US",
            "bio": "Making Progress...LinkStream is Coming!!!",
            "followers": "0",
            "plays": "0",
            "beats": "0",
            "data_image": "data: image/jpeg;base64,/9j/4AAQSkZJRgABAQAAA",
            "data_banner": "data: image/png;base64,iVBORw0KGgoAAAANSUhEU"
        },
        "genres": [
            {
                "id": "2",
                "genre": "Custom"
            },
            {
                "id": "3",
                "genre": "Hip-hop & Rap"
            }
        ],
        "licenses": [
            {
                "id": "1",
                "user_id": "35",
                "status_id": "1",
                "title": "Standard License",
                "descripcion": "Untagged MP3",
                "price": "30.00",
                "mp3": "1",
                "wav": "0",
                "trackout_stems": "0",
                "distribution_copies": "0",
                "free_download": "0",
                "audio_streams": "0",
                "music_videos": "0",
                "video_streams": "0",
                "broadcasting_rights": "0",
                "radio_station": "0",
                "paid_performances": "0",
                "non_profit_performances": "Unlimited",
                "license_available": true
            },
            {
                "id": "2",
                "user_id": "35",
                "status_id": "1",
                "title": "Premium License ",
                "descripcion": "Untagged MP3 - WAV",
                "price": "50.00",
                "mp3": "1",
                "wav": "1",
                "trackout_stems": "0",
                "distribution_copies": "0",
                "free_download": "0",
                "audio_streams": "0",
                "music_videos": "0",
                "video_streams": "0",
                "broadcasting_rights": "0",
                "radio_station": "0",
                "paid_performances": "0",
                "non_profit_performances": "Unlimited",
                "license_available": true
            },
            {
                "id": "3",
                "user_id": "35",
                "status_id": "1",
                "title": "Unlimited License",
                "descripcion": "Untagged MP3 - WAV",
                "price": "100.00",
                "mp3": "1",
                "wav": "1",
                "trackout_stems": "0",
                "distribution_copies": "0",
                "free_download": "0",
                "audio_streams": "0",
                "music_videos": "0",
                "video_streams": "0",
                "broadcasting_rights": "0",
                "radio_station": "0",
                "paid_performances": "0",
                "non_profit_performances": "Unlimited",
                "license_available": true
            },
            {
                "id": "4",
                "user_id": "35",
                "status_id": "1",
                "title": "Unlimited With Trackouts License",
                "descripcion": "Untagged MP3 - WAV - .ZIP/.RAR of track stems",
                "price": "200.00",
                "mp3": "1",
                "wav": "1",
                "trackout_stems": "1",
                "distribution_copies": "0",
                "free_download": "0",
                "audio_streams": "0",
                "music_videos": "0",
                "video_streams": "0",
                "broadcasting_rights": "0",
                "radio_station": "0",
                "paid_performances": "0",
                "non_profit_performances": "Unlimited",
                "license_available": true
            },
            {
                "id": "5",
                "user_id": "35",
                "status_id": "1",
                "title": "Exclusive License",
                "descripcion": "Untagged MP3 - WAV - .ZIP/.RAR of track stems",
                "price": "300.00",
                "mp3": "1",
                "wav": "1",
                "trackout_stems": "1",
                "distribution_copies": "0",
                "free_download": "0",
                "audio_streams": "0",
                "music_videos": "0",
                "video_streams": "0",
                "broadcasting_rights": "0",
                "radio_station": "0",
                "paid_performances": "0",
                "non_profit_performances": "Unlimited",
                "license_available": true
            }
        ],
        "beats": [
            {
                "id": "381",
                "created_at": "2020-09-22 03:25:00",
                "user_id": "35",
                "title": "My title",
                "coverart": "d22dbf7178b5b8110e6a8dd21d2f0cad.jpeg",
                "genre_id": "2",
                "track_type": "3",
                "tags": "paolo, kit, linkstream",
                "track_stems_name": "wav.zip",
                "track_stems": "473475ad090ba17648347d09e81d2ee3.zip",
                "tagged_file_name": "file_example_MP3_2MG.mp3",
                "tagged_file": "1416cf525f3a71ed6864e10e0c09f318.mp3",
                "price": "150.00",
                "samples": "4",
                "kit_files_name": "[\"file_example_WAV_1MG.wav\",\"file_example_WAV_2MG.wav\",\"file_example_WAV_5MG.wav\",\"file_example_WAV_10MG.wav\"]",
                "description": "My first Kit",
                "url_user": "",
                "url_title": "",
                "data_image": "data:image/jpeg;base64,/9j/4AAQSkZJR",
                "data_track_stems": "",
                "data_tagged_file": ""
            },
            {
                "id": "387",
                "created_at": "2020-09-22 21:02:57",
                "user_id": "35",
                "title": "Sound KIt Big",
                "coverart": "9c1aa7dd76f40f8b5b580dd0fe254e3c.jpeg",
                "genre_id": "3",
                "track_type": "3",
                "tags": "paolo, kit, big",
                "track_stems_name": "SoundKit.zip",
                "track_stems": "9fbd9d7d781dc9fbdb238f0132acec6b.zip",
                "tagged_file_name": "file_example_MP3_1MG.mp3",
                "tagged_file": "0feaf969a2cf2cd2bfe737913e66a8ee.mp3",
                "price": "450.00",
                "samples": "8",
                "kit_files_name": "[\"file_example_MP3_1MG.mp3\",\"file_example_MP3_2MG.mp3\",\"file_example_MP3_5MG.mp3\",\"file_example_MP3_700KB.mp3\",\"file_example_WAV_1MG.wav\",\"file_example_WAV_2MG.wav\",\"file_example_WAV_5MG.wav\",\"file_example_WAV_10MG.wav\"]",
                "description": "26Mb",
                "url_user": "",
                "url_title": "",
                "data_image": "data:image/jpeg;base64,/9j",
                "data_track_stems": "",
                "data_tagged_file": ""
            }
        ]
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>GET Videos Tab:</h3>
                <code>GET <?= base_url() ?>v1/profiles/videos_tab/{url}/{video_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>url (example: paolo_linkstream)</li>
                    <li>video_id (optional)</li>

                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": {
        "profile": {
            "id": "35",
            "user_name": "paolo_ls",
            "first_name": "Paolo",
            "last_name": "LinkStream",
            "display_name": "Paolo_LinkStream",
            "url": "paolo_linkstream",
            "image": "54ec6e5b610a3c082d8d6641f59b94f9.jpeg",
            "banner": "b3c8e56a73665ff17156353b17e27888.png",
            "city": "Fort Lauderdale",
            "country": "US",
            "bio": "Making Progress...LinkStream is Coming!!!",
            "followers": "0",
            "plays": "0",
            "beats": "0",
            "data_image": "data: image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQ",
            "data_banner": "data: image/png;base64,iVBORw0KGgoAAAANSUhEUg"
        },
        "genres": [
            {
                "id": "3",
                "genre": "Hip-hop & Rap"
            }
        ],
        "videos": [
            {
                "id": "330",
                "created_at": "2020-09-22 02:36:51",
                "user_id": "35",
                "title": "Mas Macarena",
                "url": "https://www.youtube.com/watch?v=8BMnz4i2dM8",
                "genre_id": "3"
            },
            {
                "id": "328",
                "created_at": "2020-09-22 02:35:49",
                "user_id": "35",
                "title": "La Gozadera",
                "url": "https://www.youtube.com/watch?v=VMp55KH_3wo",
                "genre_id": "3"
            },
            {
                "id": "329",
                "created_at": "2020-09-22 02:36:14",
                "user_id": "35",
                "title": "Si No Vuelves",
                "url": "https://www.youtube.com/watch?v=lf8xoMhV8pI",
                "genre_id": "3"
            }
        ]
    }
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>GET Links Tab:</h3>
                <code>GET <?= base_url() ?>v1/profiles/links_tab/{url}/{audio_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>url (example: paolo_linkstream)</li>
                    <li>audio_id (optional)</li>

                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": {
        "profile": {
            "id": "35",
            "user_name": "paolo_ls",
            "first_name": "Paolo",
            "last_name": "LinkStream",
            "display_name": "Paolo_LinkStream",
            "url": "paolo_linkstream",
            "image": "54ec6e5b610a3c082d8d6641f59b94f9.jpeg",
            "banner": "b3c8e56a73665ff17156353b17e27888.png",
            "city": "Fort Lauderdale",
            "country": "US",
            "bio": "Making Progress...LinkStream is Coming!!!",
            "followers": "0",
            "plays": "0",
            "beats": "0",
            "data_image": "data: image/jpeg;base64,/9j/4AAQSkZJRg",
            "data_banner": "data: image/png;base64,iVBORw0KGgoAAA"
        },
        "links": [
            {
                "id": "291",
                "created_at": "2020-09-22 02:52:12",
                "user_id": "35",
                "title": "Marca",
                "url": "https://www.marca.com/",
                "coverart": "539335abf1fb5909e0a876f06739363f.png",
                "data_image": "data: image/png;base64,iVBORw0KGgoA"
            },
            {
                "id": "282",
                "created_at": "2020-08-05 00:23:29",
                "user_id": "35",
                "title": "LinkStream",
                "url": "https://www.linkstream.com",
                "coverart": "38200f9d67ad6f31634da8a46029a230.png",
                "data_image": "data: image/png;base64,iVBORw0KGgoAAAAN"
            },
            {
                "id": "283",
                "created_at": "2020-08-05 00:26:43",
                "user_id": "35",
                "title": "eTags",
                "url": "https://www.etags.com",
                "coverart": "752e1e49efc8fce9a5420fe395e7bc3a.png",
                "data_image": "data: image/png;base64,iVBORw0KGgoAAAANSU"
            },
            {
                "id": "289",
                "created_at": "2020-09-22 02:48:19",
                "user_id": "35",
                "title": "Youtube",
                "url": "https://www.youtube.com/",
                "coverart": "b7d52356c8be1946a34588b38c1aff16.jpeg",
                "data_image": "data: image/jpeg;base64,/9j/4AAQSkZJRgABAQ"
            },
            {
                "id": "290",
                "created_at": "2020-09-22 02:50:57",
                "user_id": "35",
                "title": "Instagram",
                "url": "https://www.instagram.com/",
                "coverart": "fc3948a508728b400922afc3cd6abfdd.jpeg",
                "data_image": "data: image/jpeg;base64,/9j/4AAQSkZJRgABAQA"
            }
        ]
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>GET Config Fees:</h3>
                <code>GET <?= base_url() ?>v1/config/fees</code>
                <!--                <h3>Parameters:</h3>
                                 <ul>
                                    <li></li>
                                    <li></li>
                                </ul>-->
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": {
        "fees": [
            {
                "name": "Credit Card Fee",
                "type": "Percent",
                "value": "3",
                "var": "feeCC"
            },
            {
                "name": "Service Fee",
                "type": "Amount",
                "value": "4.99",
                "var": "feeService"
            }
        ]
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Marketing Promote:</h3>
                <code>GET <?= base_url() ?>v1/marketing/marketing_promote/{user_id}</code>
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
    "env": "dev",
    "data": [
        {
            "id": "33",
            "title": "My Beat Pack",
            "coverart": "121988630b65aa812aacd7906d1cde3d..png",
            "genre_id": "2",
            "type": "pack",
            "data_image": "https://s3.us-east-2.amazonaws.com/files.link.stream/Dev/Coverart/121988630b65aa812aacd7906d1cde3d..png"
        },
        {
            "id": "378",
            "title": "My Beat 2Mb",
            "coverart": "bd1e0c283a1a109c30da90fad315bdda.jpeg",
            "genre_id": "3",
            "type": "beat",
            "data_image": "https://s3.us-east-2.amazonaws.com/files.link.stream/Dev/Coverart/bd1e0c283a1a109c30da90fad315bdda.jpeg"
        },
        {
            "id": "379",
            "title": "My Beat 5Mb",
            "coverart": "069e711921ab22c99d991f59ae01418c.png",
            "genre_id": "3",
            "type": "beat",
            "data_image": "https://s3.us-east-2.amazonaws.com/files.link.stream/Dev/Coverart/069e711921ab22c99d991f59ae01418c.png"
        },
        {
            "id": "380",
            "title": "My Beat 10MB",
            "coverart": "b65db62d53337dbade3b7e91bbe690ea.png",
            "genre_id": "3",
            "type": "beat",
            "data_image": "https://s3.us-east-2.amazonaws.com/files.link.stream/Dev/Coverart/b65db62d53337dbade3b7e91bbe690ea.png"
        },
        {
            "id": "381",
            "title": "My title",
            "coverart": "d22dbf7178b5b8110e6a8dd21d2f0cad.jpeg",
            "genre_id": "2",
            "type": "kit",
            "data_image": "https://s3.us-east-2.amazonaws.com/files.link.stream/Dev/Coverart/d22dbf7178b5b8110e6a8dd21d2f0cad.jpeg"
        },
        {
            "id": "387",
            "title": "Sound KIt Big",
            "coverart": "9c1aa7dd76f40f8b5b580dd0fe254e3c.jpeg",
            "genre_id": "3",
            "type": "kit",
            "data_image": "https://s3.us-east-2.amazonaws.com/files.link.stream/Dev/Coverart/9c1aa7dd76f40f8b5b580dd0fe254e3c.jpeg"
        },
        {
            "id": "389",
            "title": "Testin IMG",
            "coverart": "ls_5f51c713f640460480f244394e2dad74.png",
            "genre_id": "4",
            "type": "beat",
            "data_image": "https://s3.us-east-2.amazonaws.com/files.link.stream/Dev/Coverart/ls_5f51c713f640460480f244394e2dad74.png"
        }
    ]
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Credit Card Payment:</h3>
                <code>POST <?= base_url() ?>v1/payments/cc_payment</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>data (json_array)</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The order was created succefully",
    "id": "LS0000012",
    "email": "paul@link.stream",
    "cc_type": "Visa",
    "billingCC": "4242",
    "url": {
        "33": "http://localhost/api.link.stream/v1/a/download/NDcxZjllOWMxMGM0YzkzMDNhNGI5OWFkOTc1ZDRjMDMzYTYxMGI4MzIwNzc2OGI1YzdhNTdiMTM5NTc2NjRlNjQ1OTQ5NmUyNDEzNTU4N2EwNGZmZTI2YTVmMDYzMmUwYjIzODQ4MGZjNzI1NDU5NzA2MzRiZmYxZDJmM2I5YTRsK2VYK1c0c00xWXVYWFFSOEpKTVJFMFp5VjliOHdnUzRlTFlxSWUxcWp1Z3gwdWJIUHNwTXhTLzVkYW9JQzhURllxb0xDR1lRLzJsRlJSYWdLcU1mT3FNUmVHc29QNkZRQlJtVkN2Slp2ek5YNjVtRkFZa05KTm5PSlRWTkJiRzN2dExoaTFFUC8xVnhWdDlyVm5ldEdxRkk0SFdLajRYenpmSFRnRk5FRnRmV2hjdGlBYTFrenMyVHU0MHU4WnJJZ1A2WThvMFBteEJoRkRiSjVEOWVnPT0.",
        "381": "http://localhost/api.link.stream/v1/a/download/NjU1NWM1YTVhNjI0MWM1NDgwZjQxYzM5YzE0N2VhYTYwNmNiMDUwOTdjOWU5ZjU4NDNlMzZhODg5NWE2MTUzMDJjZDAxMDk5ZDAxNDRhNGJiYzEzMDVkZTIzNDA2ZmI4OTZlZDM5YTVlOTg1ZDBlYjJmOTUzZGI3ZTljNjEyYmZzWW5JQmNFK3NVYW5ZbjdPTjRSMW5uSVJ2MHJ1eXFEc3ZhM1pRa2FzcFZFcm1PajZFeTc4dG9XU1B2eWQ3TXZsT0JmRWlWODB2T1JZdUU1Z0tBeHBrSmVEclJ4TW54ZTR5NlNNK0paaGhDdVRxc29YcUpsTnVtK3VaNGIrTHJNKy9YZkFKa29ucjdZK2JyYWNUTlg1ZWtKUjA2SWJGbzdva2pvb1c2WnFTQlNPeWV1YzhqWElqWllGV01UeklaRjVkUkNqbWRrTTNIVkE0YWNBR2pWT1FRPT0.",
        "67": "http://localhost/api.link.stream/v1/a/download/NTMzZTU1ZDg5MDI2Nzc2OGVhYWZiZmI0ZTdjN2Q3MjEyZDliNGViZWQxYjgzMmYyODVlNTc4Y2JiZjM0YmEzNTk1OTgxNjJiMDgwODlhZjhmYzc2NWJkMzZlNTFhOTBhODI3YWIxOTI2MDUyMDgyY2Q1OGI4ZjAzZjVmMTNhMDkvWExkODlESFMxVlM2TUM2U21ISFRpdkhUV21uR28wKytoZW8yR3hzTGdFN0xHajNEWUwzNjZsQlptRU16US9rLy9iTE1qTVRiVGVtTmlkQm92ZFFMWE9SK2tCSHE0bUp6dEIyVTRzWUhWSlJBYldaZTdhUHJEaWQzdDJRYjJQeG9qSWt2d2FZN2JlN3NIMTlESVBiR3ZXVWxXRG9rYld2V2NhUHJ1ZGgwVWNVZWlzR2FidWQyQjhpZWt6dnpYQnlnbVBKRXpINmRTeXNKMFdqRFZtTnpBPT0."
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Paypal Payment:</h3>
                <code>POST <?= base_url() ?>v1/payments/paypal_payment</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>data (json_array)</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The order was created succefully",
    "id": "LS0000111",
    "email": "paul@link.stream",
    "cc_type": "PayPal",
    "billingCC": "",
    "url": {
        "33": "http://localhost/api.link.stream/v1/a/download/NDcxZjllOWMxMGM0YzkzMDNhNGI5OWFkOTc1ZDRjMDMzYTYxMGI4MzIwNzc2OGI1YzdhNTdiMTM5NTc2NjRlNjQ1OTQ5NmUyNDEzNTU4N2EwNGZmZTI2YTVmMDYzMmUwYjIzODQ4MGZjNzI1NDU5NzA2MzRiZmYxZDJmM2I5YTRsK2VYK1c0c00xWXVYWFFSOEpKTVJFMFp5VjliOHdnUzRlTFlxSWUxcWp1Z3gwdWJIUHNwTXhTLzVkYW9JQzhURllxb0xDR1lRLzJsRlJSYWdLcU1mT3FNUmVHc29QNkZRQlJtVkN2Slp2ek5YNjVtRkFZa05KTm5PSlRWTkJiRzN2dExoaTFFUC8xVnhWdDlyVm5ldEdxRkk0SFdLajRYenpmSFRnRk5FRnRmV2hjdGlBYTFrenMyVHU0MHU4WnJJZ1A2WThvMFBteEJoRkRiSjVEOWVnPT0.",
        "381": "http://localhost/api.link.stream/v1/a/download/NjU1NWM1YTVhNjI0MWM1NDgwZjQxYzM5YzE0N2VhYTYwNmNiMDUwOTdjOWU5ZjU4NDNlMzZhODg5NWE2MTUzMDJjZDAxMDk5ZDAxNDRhNGJiYzEzMDVkZTIzNDA2ZmI4OTZlZDM5YTVlOTg1ZDBlYjJmOTUzZGI3ZTljNjEyYmZzWW5JQmNFK3NVYW5ZbjdPTjRSMW5uSVJ2MHJ1eXFEc3ZhM1pRa2FzcFZFcm1PajZFeTc4dG9XU1B2eWQ3TXZsT0JmRWlWODB2T1JZdUU1Z0tBeHBrSmVEclJ4TW54ZTR5NlNNK0paaGhDdVRxc29YcUpsTnVtK3VaNGIrTHJNKy9YZkFKa29ucjdZK2JyYWNUTlg1ZWtKUjA2SWJGbzdva2pvb1c2WnFTQlNPeWV1YzhqWElqWllGV01UeklaRjVkUkNqbWRrTTNIVkE0YWNBR2pWT1FRPT0.",
        "67": "http://localhost/api.link.stream/v1/a/download/NTMzZTU1ZDg5MDI2Nzc2OGVhYWZiZmI0ZTdjN2Q3MjEyZDliNGViZWQxYjgzMmYyODVlNTc4Y2JiZjM0YmEzNTk1OTgxNjJiMDgwODlhZjhmYzc2NWJkMzZlNTFhOTBhODI3YWIxOTI2MDUyMDgyY2Q1OGI4ZjAzZjVmMTNhMDkvWExkODlESFMxVlM2TUM2U21ISFRpdkhUV21uR28wKytoZW8yR3hzTGdFN0xHajNEWUwzNjZsQlptRU16US9rLy9iTE1qTVRiVGVtTmlkQm92ZFFMWE9SK2tCSHE0bUp6dEIyVTRzWUhWSlJBYldaZTdhUHJEaWQzdDJRYjJQeG9qSWt2d2FZN2JlN3NIMTlESVBiR3ZXVWxXRG9rYld2V2NhUHJ1ZGgwVWNVZWlzR2FidWQyQjhpZWt6dnpYQnlnbVBKRXpINmRTeXNKMFdqRFZtTnpBPT0."
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Cart Details:</h3>
                <code>POST <?= base_url() ?>v1/payments/cart_details</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>data (json_array)</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Get Recommendations:</h3>
                <code>GET <?= base_url() ?>v1/profiles/recommendations/{user_id}</code>
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
    "env": "dev",
    "data": {
        "extra": [
            {
                "id": "396",
                "user_id": "35",
                "title": "King",
                "coverart": "ls_49bfb9af2bce6d9eeea5afe42f817d81.jpeg",
                "genre_id": "3",
                "track_type": "2",
                "tags": "paolo",
                "type": "beat",
                "licenses": [
                    {
                        "license_id": "1",
                        "price": "30.00",
                        "status_id": "1",
                        "mp3": "1",
                        "wav": "0",
                        "trackout_stems": "0"
                    }
                ],
                "data_image": "data:image/jpeg;base64,/9j/4A"
            },
            {
                "id": "398",
                "user_id": "35",
                "title": "Japan",
                "coverart": "ls_e6abb813a7bf539ea348d736f4a4e449.jpeg",
                "genre_id": "3",
                "track_type": "2",
                "tags": "paolo",
                "type": "beat",
                "licenses": [
                    {
                        "license_id": "1",
                        "price": "30.00",
                        "status_id": "1",
                        "mp3": "1",
                        "wav": "0",
                        "trackout_stems": "0"
                    }
                ],
                "data_image": "data:image/jpeg;base64,/9j/4AAQS"
            },
            {
                "id": "391",
                "user_id": "35",
                "title": "One Way",
                "coverart": "ls_6d3a98518beaab2b6b6b39cd4d9583e4.jpeg",
                "genre_id": "3",
                "track_type": "2",
                "tags": "paolo, linkstream",
                "type": "beat",
                "licenses": [
                    {
                        "license_id": "1",
                        "price": "30.00",
                        "status_id": "1",
                        "mp3": "1",
                        "wav": "0",
                        "trackout_stems": "0"
                    }
                ],
                "data_image": "data:image/jpeg;base64,/9j/4"
            },
            {
                "id": "394",
                "user_id": "35",
                "title": "Wide Eyes",
                "coverart": "ls_675042f3f09ae9345d53d126fb1a16c8.png",
                "genre_id": "3",
                "track_type": "2",
                "tags": "paolo",
                "type": "beat",
                "licenses": [
                    {
                        "license_id": "1",
                        "price": "30.00",
                        "status_id": "1",
                        "mp3": "1",
                        "wav": "0",
                        "trackout_stems": "0"
                    }
                ],
                "data_image": "data:image/png;base64,iVB"
            }
        ]
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Youtube Uploader:</h3>
                <code>POST <?= base_url() ?>v1/marketing/youtube_uploader</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>audio_id</li>
                    <li>title</li>
                    <li>description</li>
                    <li>tags(json_array Example: ["beat","tag","youtube"])</li>
                    <li>privacy (public or private)</li>
                    <li>access_token</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "Video Create and Uploaded Succefully",
    "data": {
        "title": "Example",
        "id": "Og0QGuiJCiM",
        "status": true,
        "link": "https://www.youtube.com/watch?v=Og0QGuiJCiM"
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3> Pre Signed Url:</h3>
                <code>GET <?= base_url() ?>v1/audios/pre_signed_url/{user_id}/{file_name}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>file_name</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": {
        "formAttributes": {
            "action": "https://s3.us-east-2.amazonaws.com/files.link.stream",
            "method": "POST",
            "enctype": "multipart/form-data"
        },
        "formInputs": {
            "acl": "public-read",
            "key": "Dev/Audio/jjj.mp3",
            "X-Amz-Credential": "AKIAXBDC73PHUL3JUCGP/20201014/us-east-2/s3/aws4_request",
            "X-Amz-Algorithm": "AWS4-HMAC-SHA256",
            "X-Amz-Date": "20201014T225806Z",
            "Policy": "eyJleHBpcmF0aW9uIjoiMjAyMC0xMC0xNVQwMDo1ODowNloiLCJjb25kaXRpb25zIjpbeyJhY2wiOiJwdWJsaWMtcmVhZCJ9LHsiYnVja2V0IjoiZmlsZXMubGluay5zdHJlYW0ifSxbInN0YXJ0cy13aXRoIiwiJGtleSIsIkRldlwvQXVkaW9cL2pqai5tcDMiXSxbImNvbnRlbnQtbGVuZ3RoLXJhbmdlIiwxMDAsMTAwMDAwMDBdLHsiWC1BbXotRGF0ZSI6IjIwMjAxMDE0VDIyNTgwNloifSx7IlgtQW16LUNyZWRlbnRpYWwiOiJBS0lBWEJEQzczUEhVTDNKVUNHUFwvMjAyMDEwMTRcL3VzLWVhc3QtMlwvczNcL2F3czRfcmVxdWVzdCJ9LHsiWC1BbXotQWxnb3JpdGhtIjoiQVdTNC1ITUFDLVNIQTI1NiJ9XX0=",
            "X-Amz-Signature": "3527bb71aeff947723cb7791f4aab322ba984fd8308f739c89a8c941cfcbeba2"
        }
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Get Dashboard Info:</h3>
                <code>GET <?= base_url() ?>v1/users/dashboard/{$user_id}</code>
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
    "env": "dev",
    "data": {
        "beat": true,
        "store": true,
        "campaign": true,
        "email_confirmed": false,
        "plays": 705,
        "free_downloads": 1,
        "sales_count": 31,
        "sales_amount": 2150.25,
        "conversion": "4.40",
        "top_5": [
            {
                "audio_id": "378",
                "Count": "174",
                "title": "My Beat 2Mb",
                "coverart": "ls_b010473bdb62681c47a8c1ba59198454.jpeg",
                "image_url":""
            },
            {
                "audio_id": "390",
                "Count": "49",
                "title": "Stranger",
                "coverart": "ls_a74914a07ca51a5a088ac31e557ed444.png",
                "image_url":""
            },
            {
                "audio_id": "379",
                "Count": "44",
                "title": "My Beat 5Mb",
                "coverart": "ls_9a758af0b9484b33635c279da6e7fdb2.jpeg",
                "image_url":""
            },
            {
                "audio_id": "402",
                "Count": "43",
                "title": "Soul Defense",
                "coverart": "ls_b3ace07b48e9eed423fcbfdbfd31dc0d.jpeg",
                "image_url":""
            },
            {
                "audio_id": "400",
                "Count": "42",
                "title": "Up Go",
                "coverart": "ls_64743c6c60c1f8947bc9f8278f8655be.jpeg",
                "image_url":""
            }
        ],
        "activity": [
            {
                "id": "4",
                "transDateTime": "2020-10-15 13:25:22",
                "user_id": "35",
                "action": "BUY",
                "log": "bought your Beat \"Stranger\"",
                "display_name": "paulferra46",
                "first_name": "Paul",
                "last_name": "Ferra",
                "image": "4875d11f3618da0bf638f7e53210fcc2.png",
                "url": "paulferra46",
                "image_url":""
            },
            {
                "id": "3",
                "transDateTime": "2020-10-15 13:25:22",
                "user_id": "35",
                "action": "FREE_DOWNLOAD",
                "log": "downloaded your Beat \"Sould Defense\"",
                "display_name": "paulferra",
                "first_name": "Paul",
                "last_name": "Ferra",
                "image": "3b1111642f5133fa7d5052c4d46fc030.png",
                "url": "paulferra",
                "image_url":""
            },
            {
                "id": "2",
                "transDateTime": "2020-10-15 13:22:34",
                "user_id": "35",
                "action": "FOLLOW",
                "log": "started following you",
                "display_name": "paulferra46",
                "first_name": "Paul",
                "last_name": "Ferra",
                "image": "4875d11f3618da0bf638f7e53210fcc2.png",
                "url": "paulferra46",
                "image_url":""
            },
            {
                "id": "1",
                "transDateTime": "2020-10-15 13:21:59",
                "user_id": "35",
                "action": "FOLLOW",
                "log": "started following you",
                "display_name": "paulferra",
                "first_name": "Paul",
                "last_name": "Ferra",
                "image": "3b1111642f5133fa7d5052c4d46fc030.png",
                "url": "paulferra",
                "image_url":""
            }
        ]
    }
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Get Analytics Info:</h3>
                <code>GET <?= base_url() ?>v1/users/analytics/{$user_id}/{days}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>days (optional - default = 7)</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "data": {
        "plays": 170,
        "free_downloads": 1,
        "sales_count": 101,
        "sales_amount": 5810,
        "conversion": "59.41",
        "beats_info": [
            {
                "TransDate": "2020-10-15",
                "Count": "3",
                "Total": "75.00"
            },
            {
                "TransDate": "2020-10-16",
                "Count": "5",
                "Total": "170.00"
            },
            {
                "TransDate": "2020-10-17",
                "Count": "40",
                "Total": "1730.00"
            },
            {
                "TransDate": "2020-10-18",
                "Count": "5",
                "Total": "155.00"
            },
            {
                "TransDate": "2020-10-19",
                "Count": "22",
                "Total": "940.00"
            },
            {
                "TransDate": "2020-10-20",
                "Count": "26",
                "Total": "2740.00"
            }
        ],
        "free_downloads_info": [
            {
                "TransDate": "2020-10-14",
                "Count": "1"
            }
        ],
        "plays_info": [
            {
                "TransDate": "2020-10-14",
                "Count": "32"
            },
            {
                "TransDate": "2020-10-15",
                "Count": "67"
            },
            {
                "TransDate": "2020-10-16",
                "Count": "12"
            },
            {
                "TransDate": "2020-10-18",
                "Count": "8"
            },
            {
                "TransDate": "2020-10-19",
                "Count": "30"
            },
            {
                "TransDate": "2020-10-20",
                "Count": "21"
            }
        ],
        "marketing_info": [
            {
                "TransDate": "2020-10-17",
                "Count": "40",
                "Total": "1730.00"
            },
            {
                "TransDate": "2020-10-18",
                "Count": "5",
                "Total": "155.00"
            },
            {
                "TransDate": "2020-10-19",
                "Count": "22",
                "Total": "940.00"
            },
            {
                "TransDate": "2020-10-20",
                "Count": "26",
                "Total": "2740.00"
            }
        ],
        "top_beat_sales": [
            {
                "item_title": "My Beat 2Mb",
                "Count": "22"
            },
            {
                "item_title": "Picture Perfect",
                "Count": "15"
            },
            {
                "item_title": "One Way",
                "Count": "15"
            },
            {
                "item_title": "Stranger",
                "Count": "15"
            },
            {
                "item_title": "Wide Eyes",
                "Count": "5"
            }
        ],
        "top_referrers": [
            {
                "utm_source": "email_campaign",
                "Count": "3"
            },
            {
                "utm_source": "test",
                "Count": "2"
            }
        ],
        "visitors": [
            {
                "TransDate": "2020-10-18",
                "Count": "2"
            },
            {
                "TransDate": "2020-10-19",
                "Count": "2"
            },
            {
                "TransDate": "2020-10-20",
                "Count": "3"
            }
        ]
    }
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Get Subscribers Count by Segment:</h3>
                <code>GET <?= base_url() ?>v1/marketing/subscribers_count_by_segment/{user_id}/{segment}/{type}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>segment</li>
                    <li>type (sms or email)</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "count": "1"
}');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Get Customer Orders:</h3>
                <code>GET <?= base_url() ?>v1/users/orders/{user_id}/{invoice_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>invoice_id {optional}</li>
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
            "id": "42",
            "invoice_number": "LS0000042-35",
            "created_at": "2020-10-16 15:25:13",
            "first_name": "Paolo",
            "last_name": "LinkStream",
            "total": "30.00",
            "items": "1"
        },
        {
            "id": "41",
            "invoice_number": "LS0000041-35",
            "created_at": "2020-10-16 12:58:21",
            "first_name": "Paolo",
            "last_name": "LinkStream",
            "total": "80.00",
            "items": "2"
        },
        {
            "id": "40",
            "invoice_number": "LS0000040-35",
            "created_at": "2020-10-16 09:18:52",
            "first_name": "Paolo",
            "last_name": "LinkStream",
            "total": "60.00",
            "items": "2"
        },
        {
            "id": "39",
            "invoice_number": "LS0000039-35",
            "created_at": "2020-10-15 17:12:07",
            "first_name": "Paolo",
            "last_name": "LinkStream",
            "total": "30.00",
            "items": "1"
        },
        {
            "id": "38",
            "invoice_number": "LS0000038-35",
            "created_at": "2020-10-15 17:09:32",
            "first_name": "Paolo",
            "last_name": "LinkStream",
            "total": "30.00",
            "items": "1"
        },
        {
            "id": "37",
            "invoice_number": "LS0000037-35",
            "created_at": "2020-10-15 15:23:01",
            "first_name": "Paolo",
            "last_name": "LinkStream",
            "total": "15.00",
            "items": "1"
        }
    ]
}');
                    echo '</pre>';
                    ?>

                <hr>
                <h3>Action Click:</h3>
                <code>POST <?= base_url() ?>v1/a/action_click/{ref_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>ref_id</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('true');
                    echo '</pre>';
                    ?>
                </p>


                <hr>
                <h3>Get Visitor IP:</h3>
                <code>GET <?= base_url() ?>v1/profiles/visitor</code>
                <h3>Parameters:</h3>
                <!--                <ul>
                                    <li></li>
                                    <li></li>
                                </ul>-->
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "visitor_ip": "170.55.19.206"
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Insert Visitor:</h3>
                <code>POST <?= base_url() ?>v1/profiles/visitor</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>session_id</li>
                    <li>agent (Example: Firefox 72.0 or Chrome 79.0.3945.117 or Safari 604.1)</li>
                    <!--<li>platform (Example: Mac OS X or iOS)</li>-->
                    <li>url (Example: https://linsktream.com/paolo_linkstream or https://dev-link-vue.link.stream/paolo_linkstream/beats/378)</li>
                    <li>utm_source</li>
                    <li>ref_id</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "message": "The visitor has been created successfully."
}');
                    echo '</pre>';
                    ?>
                </p>



                <hr>
                <h3>Connect Stripe Account:</h3>
                <code>POST <?= base_url() ?>v1/users/connect_stripe_account</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <!--<li></li>-->
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "account_id": "acct_1HhKuZFJzzyt8Xjh",
    "account_url": "https://connect.stripe.com/express/onboarding/8tf5IE8brQBl"
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Confirm Stripe Account:</h3>
                <code>POST <?= base_url() ?>v1/users/confirm_stripe_account</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>account_id</li>
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "account_id": "acct_1HhSdzFvTagLaUpi",
    "payouts_enabled": true,
    "login_links": "https://connect.stripe.com/express/Q25HctkLI2fp"
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Get Stripe Account:</h3>
                <code>GET <?= base_url() ?>v1/users/stripe_account/{user_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <!--<li></li>-->
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "payouts_enabled": true,
    "paypal_email": "sb-mmzz31809258@business.example.com"
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Get Stripe Account:</h3>
                <code>GET <?= base_url() ?>v1/users/stripe_account/{user_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <!--<li></li>-->
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "payouts_enabled": true,
    "paypal_email": "sb-mmzz31809258@business.example.com"
}');
                    echo '</pre>';
                    ?>
                </p>

                <hr>
                <h3>Declined Stripe Account:</h3>
                <code>POST <?= base_url() ?>v1/users/decline_stripe_account</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>account_id</li>
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
                <h3>Declined Stripe Account:</h3>
                <code>DELETE <?= base_url() ?>v1/users/decline_stripe_account/{user_id}/{account_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <li>account_id</li>
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
                <h3>Get Paypal Account:</h3>
                <code>GET <?= base_url() ?>v1/users/paypal_account/{user_id}</code>
                <h3>Parameters:</h3>
                <ul>
                    <li>user_id</li>
                    <!--<li></li>-->
                </ul>
                <h3>Response Example:</h3>
                <p>
                    <?php
                    echo '<pre>';
                    print_r('{
    "status": "success",
    "env": "dev",
    "payouts_enabled": true,
    "paypal_email": "sb-mmzz31809258@business.example.com"
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