<?php
$data = array();
$data['title'] = 'Streamy - Login';
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
                            <h1 class="mb-3 text-18">Sign In</h1>
                            <form action="" method="post" role="form" id="login" >
                                <div class="form-group">
                                    <label for="email">Email address</label>
                                    <input class="form-control form-control-rounded" id="email" name="email" type="email" required="required">
                                </div>
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input class="form-control form-control-rounded" id="password" name="password" type="password" required="required">
                                </div>
                                <button class="btn btn-rounded btn-primary btn-block mt-2">Sign In</button>
                            </form>
                            <div class="mt-3 text-center"><a class="text-muted" href="<?= base_url() . 'forgot' ?>">
                                    <u>Forgot Password?</u></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-center" style="background-size: cover;background-image: url(<?= HTTP_ASSETS ?>dist-assets/images/photo-long-3.jpg)">
                        <div class="pr-3 auth-right">
                            <a class="btn btn-rounded btn-outline-primary btn-outline-email btn-block btn-icon-text" href="<?= base_url() . 'register' ?>"><i class="i-Mail-with-At-Sign"></i> Sign up with Email</a>
                            <a class="btn btn-rounded btn-block btn-icon-text btn-outline-facebook" href="<?= base_url() . 'instagram_register' ?>"><i class="i-Instagram"></i> Sign in with Instagram</a>
                            <a id="customBtn" class="btn btn-rounded btn-outline-google btn-block btn-icon-text"><i class="i-Google"></i> Sign up with Google</a>  
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $this->load->view('app/_inc/footer-sign', $data); ?>
</body>
</html>