<?php
$data = array();
$data['title'] = 'Streamy';
$data['page'] = 'Dashboard';
//$this->load->view('app/_inc/header', $data);
?>

<body class="text-left">
    <div class="app-admin-wrap layout-sidebar-large">
       Test: 15
    </div>

    <?php $this->load->view('app/_inc/footer', $data); ?>
    <script> var urlBase = "<?= base_url(); ?>";</script>
    <script>
        var settings = {
            "url": "https://api-dev.link.stream/v1/users/login",
            "method": "POST",
            "timeout": 0,
            "headers": {
                "X-API-KEY": "F5CE12A3-27BD-4186-866C-D9D019E85076",
                "Content-Type": "application/x-www-form-urlencoded",
                "Authorization": "Basic bGlua3N0cmVhbTpMaW5rU3RyZWFtQDIwMjA="
            },
            "data": {
                "email": "pa@link.stream",
                "password": "12345"
            }
        };

        $.ajax(settings).done(function (response) {
            console.log(response);
        });
    </script>    
</body>
</html>