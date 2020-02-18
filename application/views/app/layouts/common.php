<html>
    <head>
    <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="google-signin-client_id" content="<?= GOOGLE_LOGIN_CLIENT_ID ?>">
        <link rel="icon" href="<?= HTTP_ASSETS ?>images/favicon/streamy_favicon_color.png" type="image/png">
        <link rel="apple-touch-icon" href="<?= HTTP_ASSETS ?>images/favicon/streamy_favicon_color.png">
        <link rel="shortcut icon" href="<?= HTTP_ASSETS ?>images/favicon/streamy_favicon_color_BkH_icon.ico" type="image/x-icon">
        <link href="<?= HTTP_ASSETS ?>css/streamy.css" rel="stylesheet" >
        <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons" rel="stylesheet" type="text/css">
        <link href="<?= HTTP_ASSETS ?>node_modules/quasar/dist/quasar.min.css" rel="stylesheet" >
        <link href="<?= HTTP_ASSETS ?>node_modules/typeface-montserrat/index.css" rel="stylesheet" >   
    </head>
    <body>
        <div id="q-app">  
            <q-layout class="horizontal-center"> 
                <q-page-container id="loginContainer">  
                    <?= $body_content ?>    
                </q-page-container>
            </q-layout>            
        </div>        
    </body>
</html>
<script> var urlBase = "<?= base_url(); ?>";</script>
<script> var httpAssets = "<?= HTTP_ASSETS ?>";</script>
<script src="<?= HTTP_ASSETS ?>js/jquery-3.3.1.min.js"></script>
<script src="https://apis.google.com/js/api:client.js"></script>
<script> var google_id = "<?= GOOGLE_LOGIN_CLIENT_ID ?>";</script>
<script src="<?= HTTP_ASSETS ?>js/google-login/google-register.min.js"></script>
<script src="<?= HTTP_ASSETS ?>public/app.js"></script>


