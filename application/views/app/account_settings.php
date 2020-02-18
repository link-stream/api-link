<?php
$data = array();
$data['title'] = 'Streamy';
$data['page'] = 'Account Setting';
$data['user'] = $user;
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

                <section class="widget-card">
                    <div class="row">
                        <div class="col-lg-2 col-xl-2 mt-3"></div>
                        <div class="col-lg-8 col-xl-8 mt-3">
                            <!-- First -->
                            <?php if ($user['platform'] != 'Streamy') { ?>
                                <form action="" method="post" role="form" id="account_settings_user" >  
                                    <div class="card text-center" id="card_1">
                                        <div class="card-body">
                                            <h2 class="ul-widget-card__title">Complete Your Account</h2>
                                            <p class="card-text text-mute">User Information</p>
                                            <div class="col-md-12 text-left">
                                                <div class="card mb-5">
                                                    <div class="card-body">
                                                        <div class="d-flex flex-column">
                                                            <div class="form-group">
                                                                <input class="form-control" type="text" id="username" name="username" placeholder="Enter your username" required="" value="<?= (!empty($user['user_name'])) ? $user['user_name'] : '' ?>"/>
                                                            </div>
                                                            <div class="form-group">
                                                                <input class="form-control" type="email" id="email" name="email" placeholder="Enter your email" required="" value="<?= (!empty($user['email'])) ? $user['email'] : '' ?>" <?= (($user['platform'] == 'Google') ? 'readonly=""' : '') ?>/>
                                                            </div>
                                                            <!--<button class="btn btn-primary pd-x-20">Sign In</button>-->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <p></p>
                                            <div class="row">
                                                <div class="col-4"></div>
                                                <div class="col-4"></div>
                                                <div class="col-4">
                                                    <button class="btn btn-success m-1 js-next" id="next_1" data-step="1" data-next="2" type="button">Next</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p></p>
                                </form>
                            <?php } ?>
                            <!-- Second -->
                            <form action="" method="post" role="form" id="account_settings_plan" >  
                                <div class="card text-center" id="card_2" style="<?= ($user['platform'] == 'Streamy' ? '' : 'display:none') ?>">
                                    <div class="card-body">
                                        <h2 class="ul-widget-card__title">Select Your Plan</h2>
                                        <p class="card-text text-mute">Complete Your Account</p>
                                        <div class="col-md-12 text-left">
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col"></th>
                                                            <th scope="col">
                                                                <label class="radio radio-outline-success">
                                                                    <input type="radio" name="radio"  checked="" value="1"/><span>Rookie</span><span class="checkmark"></span>
                                                                </label>
                                                            </th>
                                                            <th scope="col">  <label class="radio radio-outline-success">
                                                                    <input type="radio" name="radio" value="2"/><span>Pro</span><span class="checkmark"></span>
                                                                </label>
                                                            </th>
                                                            <th scope="col"> <label class="radio radio-outline-success">
                                                                    <input type="radio" name="radio" value="3"/><span>Star</span><span class="checkmark"></span>
                                                                </label>
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                        <tr>
                                                            <th scope="row">Price</th>
                                                            <td>Free</td>
                                                            <td>$ 4.95 / m</td>
                                                            <td>$ 24.99 / m</td>                                                      
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Streamy Content</th>
                                                            <td>5</td>
                                                            <td>Unlimited</td>
                                                            <td>Unlimited</td>                                                      
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Youtube</th>
                                                            <td><span class="badge badge-success">Active</span></td>
                                                            <td><span class="badge badge-success">Active</span></td>
                                                            <td><span class="badge badge-success">Active</span></td>                                                      
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">SoundCloud</th>
                                                            <td><span class="badge badge-success">Active</span></td>
                                                            <td><span class="badge badge-success">Active</span></td>
                                                            <td><span class="badge badge-success">Active</span></td>                                                      
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Schedule</th>
                                                            <td><span class="badge badge-warning">Not Active</span></td>
                                                            <td><span class="badge badge-success">Active</span></td>
                                                            <td><span class="badge badge-success">Active</span></td>                                                      
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Custom Domain</th>
                                                            <td><span class="badge badge-warning">Not Active</span></td>
                                                            <td><span class="badge badge-warning">Not Active</span></td>
                                                            <td><span class="badge badge-success">Active</span></td>                                                      
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <p></p>
                                        <div class="row">
                                            <div class="col-4">
                                                <?php if ($user['platform'] != 'Streamy') { ?><button class="btn btn-secondary m-1 js-prev" id="prev_2" data-step="2" data-next="1" type="button">Back</button><?php } ?>
                                            </div>
                                            <div class="col-4"></div>
                                            <div class="col-4">
                                                <button class="btn btn-success m-1 js-next"  id="next_2" data-step="2" data-next="3" type="button">Next</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p></p>
                            </form>
                            <!-- Third -->
                            <form action="" method="post" role="form" id="account_settings_payment" > 
                                <input name="pln_id" id="pln_id" type="hidden" value="">
                                <div class="card text-center" id="card_3" style="display:none">
                                    <div class="card-body">
                                        <h2 class="ul-widget-card__title">Payment.</h2>
                                        <p class="card-text text-mute">Complete Your Account</p>
                                        <div class="col-md-12 text-left">
                                            <div class="card mb-5">
                                                <div class="card-body">
                                                    Credit Card Information
                                                    <div id="dropin-container"></div>
                                                    <!--                                                    <button id="submit-button" class="button button--small button--green">Purchase</button>-->
                                                </div>
                                            </div>
                                        </div>
                                        <p></p>
                                        <div class="row">
                                            <div class="col-4">
                                                <button class="btn btn-secondary m-1 js-prev" id="prev_3" data-step="3" data-next="2" type="button">Back</button>
                                            </div>
                                            <div class="col-4"></div>
                                            <div class="col-4">
                                                <button class="btn btn-success m-1 js-next"  id="next_3" data-step="3" data-next="4" type="button">Pay $X.XX</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <p></p>
                            <!-- Fourth -->
                            <div class="card text-center" id="card_4" style="display:none">
                                <div class="card-body">
                                    <h2 class="ul-widget-card__title">Account setup completed</h2>
                                    <p class="card-text text-mute">From this moment you can start inserting your content.</p>
                                    <div class="col-md-12 text-left">
                                        <div class="card mb-5">
                                            <div class="card-body">
                                                <a href="<?= base_url() ?>app/my_content_add" class="btn btn-success m-1 js-content" type="button">My Content</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p></p>
                        </div>
                        <div class="col-lg-2 col-xl-2 mt-3"></div>
                    </div>
                </section>
            </div>
            <?php $this->load->view('app/_inc/main-footer', $data); ?>
        </div>
    </div>
    <?php $this->load->view('app/_inc/footer', $data); ?>
    <script> var urlBase = "<?= base_url(); ?>";</script>
    <script>
        $("#account_settings_user").validate({
            onkeyup: false,
            errorClass: "state-error",
            validClass: "state-success",
            errorElement: "em",
            errorPlacement: function (label, element) {
                label.addClass('invalid-tooltip');
                label.insertAfter(element);
            },
            rules: {
                email: {
                    required: true,
                    email: true,
                    remote: {
                        url: urlBase + "app/verify_account_email",
                        type: "post",
                        data: {
                            field_value: function () {
                                return $("#email").val();
                            },
                            field_name: 'email'
                        }
                    }
                },
                username: {
                    required: true,
                    remote: {
                        url: urlBase + "app/verify_account_username",
                        type: "post",
                        data: {
                            field_value: function () {
                                return $("#username").val();
                            },
                            field_name: 'username'
                        }
                    }
                }
            }, messages: {
                username: {
                    remote: 'User Name Already Register'
                },
                email: {
                    remote: 'Email Already Register'
                }
            },
            submitHandler: function (form) {
                form.submit();
            }
        });

        $('body').on('click', '.js-next', function (e) {
            e.preventDefault();
            var button_id = this.id;
            var step = $(this).attr("data-step");
            var next = $(this).attr("data-next");
            var form = '';
            if (button_id === 'next_1') {
                form = '#account_settings_user';
            } else if (button_id === 'next_2') {
                form = '#account_settings_plan';
            } else if (button_id === 'next_3') {
                form = '#account_settings_payment';
            }
            if ($(form).valid()) {
                var card_out = '#card_' + step;
                var card_in = '#card_' + next;
                if (form === '#account_settings_user') {
                    $.ajax({
                        type: 'post',
                        dataType: 'json',
                        url: urlBase + "app/account_setting_update_ajax",
                        data: $(form).serialize(),
                        error: function (textStatus, errorThrown) {
                            window.location.href = 'app/';
                        }
                    });
                } else if (form === '#account_settings_plan') {
                    var selValue = $('input[name=radio]:checked').val();
                    $('#account_settings_payment #pln_id').val(selValue);
                    if (selValue === '1') {
                        var card_in = '#card_4';
                        $.ajax({
                            type: 'post',
                            dataType: 'json',
                            url: urlBase + "app/account_setting_complete_ajax",
                            data: $(form).serialize(),
                            error: function (textStatus, errorThrown) {
                                window.location.href = 'app/';
                            }
                        });
                    }
                } else if (form === '#account_settings_payment') {
                    $.ajax({
                        type: 'post',
                        dataType: 'json',
                        url: urlBase + "app/account_setting_payment_ajax",
                        data: $(form).serialize(),
                        error: function (textStatus, errorThrown) {
                            window.location.href = 'app/';
                        }
                    });
                }
                $(card_out).fadeOut(200, function () {
                    $(card_in).fadeIn(100);
                });

            }
//        var id = this.id;
//        $.ajax({
//            type: 'post',
//            dataType: 'json',
//            url: urlBase + "account/edu/next_question_ajax",
//            data: $("#card_question_form").serialize(),
////                    beforeSend: function () {
////                        
////                    },
//            success: function (r)
//            {
//                $('.js-page-number').text('Question ' + r.cur_page + ' / ' + r.num_pages)
//                $('.card').fadeOut(200, function () {
//                    $('.card').html(r.view);
//                    $('.card').fadeIn(100);
//                });
//            }
//        });
        });

        $('body').on('click', '.js-prev', function (e) {
            e.preventDefault();
            var button_id = this.id;
            var step = $(this).attr("data-step");
            var next = $(this).attr("data-next");
            var card_out = '#card_' + step;
            var card_in = '#card_' + next;
            $(card_out).fadeOut(200, function () {
                $(card_in).fadeIn(100);
            });
        });
    </script>
</body>
</html>