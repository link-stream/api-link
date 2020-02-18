<?php
$data = array();
$data['title'] = 'Streamy - 404';
$data['page'] = 'Template';
$this->load->view('app/_inc/header_sign', $data);
?>

<body>
    <div class="not-found-wrap text-center">
        <h1 class="text-60">404</h1>
        <p class="text-36 subheading mb-3">Error!</p>
        <p class="mb-5 text-muted text-18">Sorry! The page you were looking for doesn&apos;t exist.</p><a class="btn btn-lg btn-primary btn-rounded" href="<?= base_url() ?>">Go back to home</a>
    </div>
</body>
</html>