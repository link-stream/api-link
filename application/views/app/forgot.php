<?php
$data = array();
$data['title'] = 'Streamy - Forgot';
$data['page'] = 'Template';
$this->load->view('app/_inc/header-sign', $data);
?>

<body>
    <div class="auth-layout-wrap" style="background-image: url(<?= HTTP_ASSETS ?>dist-assets/images/photo-wide-4.jpg)">
        <div class="auth-content">
            <div class="card o-hidden">
                <div class="row">
                    <div class="col-md-6">
                        <div class="p-4">
                            <div class="auth-logo text-center mb-4"><img src="<?= HTTP_ASSETS ?>images/logo/streamy_icon_RGB.png" alt=""></div>
                            <h1 class="mb-3 text-18">Forgot Password</h1>
                            <form action="" method="post" role="form" id="forgot">
                                <div class="form-group">
                                    <label for="email">Email address</label>
                                    <input class="form-control form-control-rounded" id="email" name="email" type="email" required="">
                                </div>
                                <button class="btn btn-primary btn-block btn-rounded mt-3">Reset Password</button>
                            </form>
                            <div class="mt-3 text-center"><a class="text-muted" href="<?= base_url() . 'login' ?>">
                                    <u>Sign in</u></a></div>
                        </div>
                    </div>
                    <div class="col-md-6 text-center" style="background-size: cover;background-image: url(<?= HTTP_ASSETS ?>dist-assets/images/photo-long-3.jpg)">
                        <div class="pr-3 auth-right">
                            <a class="btn btn-outline-primary btn-outline-email btn-block btn-icon-text btn-rounded" href="<?= base_url() . 'register' ?>"><i class="i-Mail-with-At-Sign"></i> Sign up with Email</a>
                            <a class="btn btn-outline-primary btn-outline-facebook btn-block btn-icon-text btn-rounded" href="<?= base_url() . 'instagram_register' ?>"><i class="i-Instagram"></i> Sign in with Instagram</a>
                            <a id="customBtn" class="btn btn-outline-primary btn-outline-google btn-block btn-icon-text btn-rounded"><i class="i-Google"></i> Sign in with Google</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $this->load->view('app/_inc/footer-sign', $data); ?>
</body>
</html>