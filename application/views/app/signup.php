<?php
$data = array();
$data['title'] = 'Streamy - Register';
$data['page'] = 'Template';
$this->load->view('app/_inc/header-sign', $data);
?>

<body>
    <div class="auth-layout-wrap" style="background-image: url(<?= HTTP_ASSETS ?>dist-assets/images/photo-wide-4.jpg)">
        <div class="auth-content">
            <div class="card o-hidden">
                <div class="row">
                    <div class="col-md-6 text-center" style="background-size: cover;background-image: url(<?= HTTP_ASSETS ?>dist-assets/images/photo-long-3.jpg)">
                        <div class="pl-3 auth-right">
                            <div class="auth-logo text-center mt-4"><img src="<?= HTTP_ASSETS ?>images/logo/streamy_icon_RGB.png" alt=""></div>
                            <div class="flex-grow-1"></div>
                            <div class="w-100 mb-4">
                                <a class="btn btn-outline-primary btn-block btn-icon-text btn-rounded" href="<?= base_url() . 'login' ?>"><i class="i-Mail-with-At-Sign"></i> Sign in with Email</a>
                                <a class="btn btn-outline-facebook btn-block btn-icon-text btn-rounded" href="<?= base_url() . 'instagram_register' ?>"><i class="i-Instagram"></i> Sign in with Instagram</a>
                                <a id="customBtn" class="btn btn-outline-google btn-block btn-icon-text btn-rounded"><i class="i-Google"></i> Sign in with Google</a>
                            </div>
                            <div class="flex-grow-1"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-4">
                            <h1 class="mb-3 text-18">Sign Up</h1>
                            <form action="" method="post" role="form" id="register">
                                <div class="form-group">
                                    <label for="username">User Name</label>
                                    <input class="form-control form-control-rounded" id="username" name="username" type="text" required="">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email address</label>
                                    <input class="form-control form-control-rounded" id="email" name="email" type="email" required="">
                                </div>
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input class="form-control form-control-rounded" id="password" name="password" type="password" required="">
                                </div>
                                <div class="form-group">
                                    <label for="repassword">Retype password</label>
                                    <input class="form-control form-control-rounded" id="repassword" name="repassword" type="password" required="">
                                </div>
                                <button type="submit" class="btn btn-primary btn-block btn-rounded mt-3">Sign Up</button>
                                <div id="error"><?= (!empty($msg)) ? '<br><div class="addr_fail alert alert-danger">' . $msg . '</div>' : '' ?></div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $this->load->view('app/_inc/footer-sign', $data); ?>
</body>
</html>