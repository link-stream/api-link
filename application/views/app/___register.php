<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Streamy Register</title>
        <meta name="google-signin-client_id" content="<?= GOOGLE_LOGIN_CLIENT_ID ?>">
        <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet" type="text/css">
        <style type="text/css">
            #customBtn {
                display: inline-block;
                background: white;
                color: #444;
                width: 190px;
                border-radius: 5px;
                border: thin solid #888;
                box-shadow: 1px 1px 1px grey;
                white-space: nowrap;
            }
            #customBtn:hover {
                cursor: pointer;
            }
            span.label {
                font-family: serif;
                font-weight: normal;
            }
            span.icon {
                ___background: url('/identity/sign-in/g-normal.png') transparent 5px 50% no-repeat;
                display: inline-block;
                vertical-align: middle;
                width: 42px;
                height: 42px;
            }
            span.buttonText {
                display: inline-block;
                vertical-align: middle;
                padding-left: 42px;
                padding-right: 42px;
                font-size: 14px;
                font-weight: bold;
                /* Use the Roboto font that is loaded in the <head> */
                font-family: 'Roboto', sans-serif;
            }
        </style>
    </head>
    <body>
        <div id="gSignInWrapper">
            <div id="customBtn" class="customGPlusSignIn">
                <!--<span class="icon"></span>-->
                <span class="buttonText">Google</span>
            </div>
        </div>
        <br> 
        <div class="form-field-group">
            <a href="<?= base_url() . 'instagram_register' ?>">Register With Instagram</a>
        </div> 
        <br> 
        <!--<div class="g-signin2" data-onsuccess="onSignIn"></div>-->
        <!--<a href="#" onclick="signIn();">Google Sign In</a>-->
        <br>
        <form action="" method="post" class="form-inline" role="form" id="login" >
            <div class="smart-forms">
                <div class="form-field-group">
                    <div class="form-group">
                        <label class="field">
                            <input type="text" id="email" name="email" class="gui-input ignore xinput" placeholder="" value="" tabindex="1">
                            <label for="email" class="placeholder">Email</label>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="field">
                            <input type="text" id="user" name="user" class="gui-input ignore xinput" placeholder="" value=""   tabindex="1">
                            <label for="user" class="placeholder">User Name</label>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="field">
                            <input type="password" id="password" name="password" class="gui-input ignore xinput" placeholder="" value=""   tabindex="2">
                            <label for="password" class="placeholder">Password</label>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="field">
                            <input type="password" id="confirm_password" name="confirm_password" class="gui-input ignore xinput" placeholder="" value=""   tabindex="2">
                            <label for="confirm_password" class="placeholder">Confirm Password</label>
                        </label>
                    </div>
                </div>
                <div id="error"><?= (!empty($msg)) ? '<div class="addr_fail alert alert-danger">' . $msg . '</div>' : '' ?></div>
                <br />
                <button type="submit" class="btn-pill btn-pill-outline-neg btn-lg btn-block">Register Now</button>
            </div>
        </form> 
        <br>
        <div class="form-field-group">
            <a href="login">Already have an account?</a>
        </div> 
        <script src="<?= HTTP_ASSETS ?>js/jquery-3.3.1.min.js"></script>
        <script src="https://apis.google.com/js/api:client.js"></script>
        <script> var urlBase = "<?= base_url(); ?>";</script>
        <script> var google_id = "<?= GOOGLE_LOGIN_CLIENT_ID ?>";</script>
        <script src="<?= HTTP_ASSETS ?>js/google-login/google-register.min.js"></script>
    </body>
</html>
