<?php
$data = array();
$data['title'] = 'Streamy';
$data['page'] = 'User Profile';
$this->load->view('app/_inc/header', $data);
?>

<body class="text-left">
    <div class="app-admin-wrap layout-sidebar-large">
        <?php $this->load->view('app/_inc/main-header', $data); ?>
        <?php $this->load->view('app/_inc/menu-left', $data); ?>
        <!-- =============== Left side End ================-->
        <div class="main-content-wrap sidenav-open d-flex flex-column">
            <!-- ============ Body content start ============= -->
            <div class="main-content">
                <div class="breadcrumb">
                    <h1><?= $data['page'] ?></h1>
                    <!--                    <ul>
                                            <li><a href="">Pages</a></li>
                                            <li>Blank</li>
                                        </ul>-->
                </div>
                <div class="separator-breadcrumb border-top"></div>
                <!-- end of main-content -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="card-title mb-3">Personal Information</div>
                                <form>
                                    <div class="row">
                                        <div class="col-md-6 form-group mb-3">
                                            <label for="firstName1">First name *</label>
                                            <input class="form-control" id="firstName1" type="text" placeholder="Enter your first name" />
                                        </div>
                                        <div class="col-md-6 form-group mb-3">
                                            <label for="lastName1">Last name *</label>
                                            <input class="form-control" id="lastName1" type="text" placeholder="Enter your last name" />
                                        </div>
                                        <div class="col-md-6 form-group mb-3">
                                            <label for="exampleInputEmail1">Email address *</label>
                                            <input class="form-control" id="exampleInputEmail1" type="email" placeholder="Enter email" />
                                            <!--  <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small> -->
                                        </div>
                                        <div class="col-md-6 form-group mb-3">
                                            <label for="phone">Phone</label>
                                            <input class="form-control" id="phone" placeholder="Enter phone" />
                                        </div>
                                        <div class="col-md-6 form-group mb-3">
                                            <label for="username">Username *</label>
                                            <input class="form-control" id="username" placeholder="Username" />
                                        </div>
                                        <div class="col-md-6 form-group mb-3">
                                            <label for="url">Url *</label>
                                            <input class="form-control" id="url" placeholder="https://www.link.stream/myurl" />
                                        </div>
                                        <div class="col-md-6 form-group mb-3">
                                            <label for="youtube">Youtube</label>
                                            <input class="form-control" id="youtube" placeholder="https://www.youtube.com/mychannel" />
                                        </div>
                                        <div class="col-md-6 form-group mb-3">
                                            <label for="facebook">Facebook</label>
                                            <input class="form-control" id="facebook" placeholder="https://www.facebook.com/myprofile" />
                                        </div>
                                        <div class="col-md-6 form-group mb-3">
                                            <label for="instagram">Instagram</label>
                                            <input class="form-control" id="instagram" placeholder="https://www.instagram.com/myprofile" />
                                        </div>
                                        <div class="col-md-6 form-group mb-3">
                                            <label for="twitter">Twitter</label>
                                            <input class="form-control" id="twitter" placeholder="https://twitter.com/myprofile" />
                                        </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php $this->load->view('app/_inc/main-footer', $data); ?>
        </div>
    </div>
    <?php $this->load->view('app/_inc/footer', $data); ?>
</body>
</html>