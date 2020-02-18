<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Welcome to Streamy</title>

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
            <h1>Welcome to Streamy! <?= (!empty($user_name)) ? $user_name : '' ?></h1>

            <div id="body">
                <p>The page you are looking at is being generated dynamically by CodeIgniter.</p>

                <p>If you would like to login this page you'll find it located at:</p>
                <code><a href="<?= base_url() . 'login' ?>"><?= base_url() . 'login' ?></a></code>

                <p>If you would like to login this page you'll find it located at:</p>
                <code><a href="<?= base_url() . 'register' ?>"><?= base_url() . 'register' ?></a></code>
                
                 <p>Create Streamy:</p>
                <code><a href="<?= base_url() . 'app/streamy' ?>"><?= base_url() . 'Streamy' ?></a></code>

<!--                <p>The corresponding controller for this page is found at:</p>
                <code>application/controllers/Welcome.php</code>

                <p>If you are exploring CodeIgniter for the very first time, you should start by reading the <a href="user_guide/">User Guide</a>.</p>-->
            </div>

            <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds. <?php echo (ENVIRONMENT === 'development') ? 'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '' ?></p>
        </div>

        <div id="container">
            <iframe width="420" height="315"
                    src="https://www.youtube.com/embed/tgbNymZ7vqY">
            </iframe>

            <iframe width="420" height="315"
                    src="https://www.youtube.com/embed/XqZsoesa55w">
            </iframe>

            <iframe width="420" height="315"
                    src="https://www.youtube.com/embed/Y66j_BUCBMY">
            </iframe>
        </div>
        <div id="container">
            <iframe width="425" height="315" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=https://soundcloud.com/iamstarinthesky/snow-angel-feat-skele-and-shigaraki-prod-sogimura&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true&visual=true"></iframe>

            <iframe width="425" height="315" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=https://soundcloud.com/iamstarinthesky/go-hard-prod-silo&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true&visual=true"></iframe>

            <iframe width="425" height="315" scrolling="no" frameborder="no" allow="autoplay" src="https://w.soundcloud.com/player/?url=https://soundcloud.com/iamstarinthesky/ride-with-me-prod-magickid&color=%23ff5500&auto_play=false&hide_related=false&show_comments=true&show_user=true&show_reposts=false&show_teaser=true&visual=true"></iframe>

        </div>

        <div id="container">
            <a href="https://www.link.stream" target="_blank">Streamy</a>

            <a href="https://www.google.com" target="_blank">Google</a>

            <a href="https://www.facebook.com" target="_blank">Facebook</a>

            <a href="https://www.youtube.com" target="_blank">Youtube</a>
        </div>

    </body>
</html>