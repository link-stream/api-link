<?php
$data = array();
$data['title'] = 'Streamy';
$data['page'] = 'Upgrade';
$this->load->view('app/_inc/header', $data);
?>
<style>
    .btn{
        width: 100%;
    }
    .button {
        cursor: pointer;
        font-weight: 500;
        left: 3px;
        line-height: inherit;
        position: relative;
        text-decoration: none;
        text-align: center;
        border-style: solid;
        border-width: 1px;
        border-radius: 3px;
        -webkit-appearance: none;
        -moz-appearance: none;
        display: inline-block;
    }

    .button--small {
        padding: 10px 20px;
        font-size: 0.875rem;
    }

    .button--green {
        outline: none;
        background-color: #64d18a;
        border-color: #64d18a;
        color: white;
        transition: all 200ms ease;
    }

    .button--green:hover {
        background-color: #8bdda8;
        color: white;
    }
    .checkbox, .radio{
        display:  inline;
    }
    .list-group-item {
        border: 0px solid rgba(0, 0, 0, 0.125);
        text-align: center;

    }
</style>
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
                <!-- NEW CONTENT -->
                 <div class="row">
                        <div class="col-lg-2 col-xl-2 mt-3"></div>
                        <div class="col-lg-8 col-xl-8 mt-3">
                            
                             <div class="card text-center" id="card_2" style="">
                    <div class="card-body">
                        <h2 class="ul-widget-card__title">Select Your Plan</h2>
                        <p class="card-text text-mute">Complete Your Account</p>
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-4 mb-4">
                                    <h3>Rookie</h3>
                                    <h3>Free </h3>
                                    <div class="col-md-12 mb-2">
                                        <label class="radio radio-outline-success display-inline">
                                            <input type="radio" name="radio"  checked="" value="1"/><span></span><span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <ul class="list-group">
                                        <li class="list-group-item"><i class="fa fa-check text-success" aria-hidden="true"></i> 5 Streamy</li>
                                        <li class="list-group-item"><i class="fa fa-check text-success" aria-hidden="true"></i> SoundCloud</li>
                                        <li class="list-group-item"><i class="fa fa-check text-success" aria-hidden="true"></i> YouTube</li>
                                        <li class="list-group-item"><i class="fa fa-check text-success" aria-hidden="true"></i> Link</li>
                                    </ul>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <h3>Pro</h3>
                                    <h3>$ 4.95 / Month </h3>
                                    <div class="col-md-12 mb-2">
                                        <label class="radio radio-outline-success display-inline">
                                            <input type="radio" name="radio"  value="2"/><span></span><span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <ul class="list-group">
                                        <li class="list-group-item"><i class="fa fa-check text-success" aria-hidden="true"></i> Unlimited Streamy</li>
                                        <li class="list-group-item"><i class="fa fa-check text-success" aria-hidden="true"></i> SoundCloud</li>
                                        <li class="list-group-item"><i class="fa fa-check text-success" aria-hidden="true"></i> YouTube</li>
                                        <li class="list-group-item"><i class="fa fa-check text-success" aria-hidden="true"></i> Link</li>
                                        <li class="list-group-item"><i class="fa fa-check text-success" aria-hidden="true"></i> Schedule</li>
                                    </ul>
                                </div>
                                <div class="col-md-4 mb-4">
                                    <h3>Start</h3>
                                    <h3>$ 24.95 / Month </h3>
                                    <div class="col-md-12 mb-2">
                                        <label class="radio radio-outline-success display-inline">
                                            <input type="radio" name="radio"  value="3"/><span></span><span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <ul class="list-group">
                                        <li class="list-group-item"><i class="fa fa-check text-success" aria-hidden="true"></i> Unlimited Streamy</li>
                                        <li class="list-group-item"><i class="fa fa-check text-success" aria-hidden="true"></i> SoundCloud</li>
                                        <li class="list-group-item"><i class="fa fa-check text-success" aria-hidden="true"></i> YouTube</li>
                                        <li class="list-group-item"><i class="fa fa-check text-success" aria-hidden="true"></i> Link</li>
                                        <li class="list-group-item"><i class="fa fa-check text-success" aria-hidden="true"></i> Schedule</li>
                                        <li class="list-group-item"><i class="fa fa-check text-success" aria-hidden="true"></i> Custom Domain</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <p></p>
                        <div class="row">
                            <div class="col-4">

                            </div>
                            <div class="col-4"></div>
                            <div class="col-4">

                            </div>
                        </div>
                    </div>
                </div>
                        </div>
                        <div class="col-lg-2 col-xl-2 mt-3"></div>
                 </div>
               

                <!-- END NEW CONTENT -->
            </div>
            <?php $this->load->view('app/_inc/main-footer', $data); ?>
        </div>
    </div>
    <?php $this->load->view('app/_inc/footer', $data); ?>
</body>
</html>