<?php
$data = array();
$data['title'] = 'Streamy - My Content';
$data['page'] = 'Add Content';
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
    .checkbox, .radio {

        display: inline;


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
                <section class="widget-card">
                    <div class="row">
                        <div class="col-lg-2 col-xl-2 mt-3"></div>
                        <div class="col-lg-8 col-xl-8 mt-3">

                            <form action="" method="post" role="form" id="my_content_url" >  
                                <!-- First -->
                                <div class="card text-center" id="card_1">
                                    <div class="card-body">
                                        <h2 class="ul-widget-card__title">Select your content type</h2>
                                        <p class="card-text text-mute">Step 1 / 4 </p>
                                        <div class="col-md-12 ">
                                            <div class="table-responsive">
                                                <table class="table table-striped">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">  
                                                                <i class="i-Soundcloud"></i> SoundCloud
                                                            </th>
                                                            <th scope="col">
                                                                <i class="i-Youtube"></i> YouTube 
                                                            </th>

                                                            <th scope="col">  
                                                                <i class="i-Link-2"></i> LinkStreams
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <th scope="row" class=""> 
                                                                <label class="radio radio-outline-success">
                                                                    <input type="radio" name="radio"  checked="" value="1"/><span class="checkmark"></span>
                                                                </label></th>
                                                            <td><label class="radio radio-outline-success">
                                                                    <input type="radio" name="radio" value="2"/><span class="checkmark"></span>
                                                                </label></td>     
                                                            <td><label class="radio radio-outline-success">
                                                                    <input type="radio" name="radio" value="3"/><span class="checkmark"></span>
                                                                </label></td>   
                                                        </tr>
                                                    </tbody>
                                                </table>
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
                                <!-- Second -->
                                <div class="card text-center" id="card_2" style="display:none">
                                    <div class="card-body">
                                        <h2 class="ul-widget-card__title">Add your url</h2>
                                        <p class="card-text text-mute">Step 2 / 4</p>
                                        <div class="col-md-12 text-left">
                                            <div class="card mb-5">
                                                <div class="card-body">
                                                    <div class="d-flex flex-column">
                                                        <div class="form-group">
                                                            <input class="form-control" type="text" id="streamy_url" name="streamy_url" placeholder="Enter your url" required="" value=""/>
                                                        </div>
                                                        <div class="table-responsive">
                                                            <table class="table table-striped">
                                                                <thead>
                                                                    <tr>
                                                                        <th scope="col">
                                                                            Examples
                                                                        </th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <tr>
                                                                        <th scope="row" class="">
                                                                            <i class="i-Soundcloud"></i> https://soundcloud.com/iamstarinthesky/go-hard-prod-silo
                                                                        </th>

                                                                    </tr>
                                                                    <tr>
                                                                        <th scope="row" class="">
                                                                            <i class="i-Youtube"></i> https://www.youtube.com/watch?v=h_D3VFfhvs4
                                                                        </th>

                                                                    </tr>

                                                                    <tr>
                                                                        <th scope="row" class="">
                                                                            <i class="i-Link-2"></i> https://www.google.com/?gws_rd=ssl
                                                                        </th>

                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <p></p>
                                        <div class="row">
                                            <div class="col-4">
                                                <button class="btn btn-secondary m-1 js-prev" id="prev_2" data-step="2" data-next="1" type="button">Back</button>
                                            </div>
                                            <div class="col-4"></div>
                                            <div class="col-4">
                                                <button class="btn btn-success m-1 js-next" id="next_2" data-step="2" data-next="3" type="button">Next</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p></p>
                                <!-- Third -->
                                <div class="card text-center" id="card_3" style="display:none">
                                    <div class="card-body">
                                        <h2 class="ul-widget-card__title">View your content.</h2>
                                        <p class="card-text text-mute">Step 3 / 4</p>
                                        <div class="col-md-12 text-left">
                                            <div class="card mb-5">
                                                <div class="card-body p-0">
                                                    <div class="embed-responsive embed-responsive-16by9 z-depth-1-half js-streamy_content">
                                                    </div>
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
                                                <button class="btn btn-success m-1 js-next"  id="next_3" data-step="3" data-next="4" type="button">Next</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p></p>
                                <!-- Fourth -->
                                <div class="card text-center" id="card_4" style="display:none">
                                    <div class="card-body">
                                        <h2 class="ul-widget-card__title">Publish your content</h2>
                                        <p class="card-text text-mute">Step 4 / 4 </p>
                                        <div class="col-md-12 text-left">
                                            <div class="row">

                                                <div class="col-md-12 form-group mb-3">
                                                    <label for="song_name">Name</label>
                                                     <input class="form-control" type="text" id="song_name" name="song_name" placeholder="Enter name" required="" value=""/>
                                                </div>
                                                
                                            </div>
                                            <div class="row">

                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="priority">Priority</label>
                                                    <select class="form-control" id="priority" name="priority">
                                                        <option value="1">Top</option>
                                                        <option value="2" selected="">Normal</option>
                                                        <option value="3">Low</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="visibility">Visibility</label>
                                                    <select class="form-control" id="visibility" name="visibility">
                                                        <option value="1" selected="">Public</option>
                                                        <option value="2">Private</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 form-group mb-3">
                                                    <label for="date">Schedule</label>
                                                    <input class="form-control" id="date"  name="date" type="text" placeholder="Date" value="<?= date('m/d/Y') ?>"/>
                                                </div>
                                            </div>
                                        </div>
                                        <p></p>
                                        <div class="row">
                                            <div class="col-4">   
                                                <button class="btn btn-secondary m-1 js-prev" id="prev_4" data-step="4" data-next="3" type="button">Back</button>
                                            </div>
                                            <div class="col-4">
                                            </div>
                                            <div class="col-4">
                                                <button class="btn btn-success m-1 js-publish" id=""  type="button">Publish</button> 
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p></p>
                                <!-- Five -->
                                <div class="card text-center" id="card_5" style="display:none">
                                    <div class="card-body">
                                        <h2 class="ul-widget-card__title">Your Content was added</h2>
                                        <p class="card-text text-mute">Add more content</p>
                                        <div class="col-md-12 text-left">
                                            <div class="card mb-5">
                                                <div class="card-body">
                                                    <a href="<?= base_url() ?>app/my_content_add" class="btn btn-success m-1 js-content" type="button">Add New Content</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p></p>
                            </form>
                        </div>
                        <div class="col-lg-2 col-xl-2 mt-3"></div>
                    </div>
                </section>

                <!-- END NEW CONTENT -->
            </div>
            <?php $this->load->view('app/_inc/main-footer', $data); ?>
        </div>
    </div>
    <?php $this->load->view('app/_inc/footer', $data); ?>
    <script> var urlBase = "<?= base_url(); ?>";</script>
    <script>

        $.validator.addMethod("url_type", function (value, element) {
            var url_type = $('#my_content_url input[name=radio]:checked').val();
            console.log(url_type);
            if (url_type === '1') {
                var p = /^https?:\/\/(soundcloud\.com|snd\.sc)\/(.*)$/;
                return (value.match(p)) ? RegExp.$1 : false;
            } else if (url_type === '2') {
                var p = /^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?(?=.*v=((\w|-){11}))(?:\S+)?$/;
                return (value.match(p)) ? RegExp.$1 : false;
            } else {
                return true;
            }

        }, "Enter correct URL");

        $("#my_content_url").validate({
            onkeyup: false,
            errorClass: "state-error",
            validClass: "state-success",
            errorElement: "em",
            errorPlacement: function (label, element) {
                label.addClass('invalid-tooltip');
                label.insertAfter(element);
            },
            rules: {
                streamy_url: {
                    required: true,
                    url: true,
                    url_type: "#streamy_url"
                },
                priority: {
                    required: true
                },
                visibility: {
                    required: true
                },
                date: {
                    required: true,
                    date: true
                },
                song_name: {
                    required: true
                }
            },
            messages: {
//                username: {
//                    remote: 'User Name Already Register'
//                }
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
            var form_name = '#my_content_url';
            if ($(form_name).valid()) {
                var card_out = '#card_' + step;
                var card_in = '#card_' + next;
                if (button_id === 'next_2') {
                    var selValue = $('input[name=radio]:checked').val();
                    if (selValue !== '3') {
                        $.ajax({
                            type: 'post',
                            url: urlBase + "app/streamy_content_view",
                            data: $(form_name).serialize(),
                            success: function (r)
                            {
                                $('.js-streamy_content').html(r);
                                $(card_out).fadeOut(200, function () {
                                    $(card_in).fadeIn(100);
                                });
                            }, error: function (textStatus, errorThrown) {
                                window.location.href = 'app/';
                            }
                        });
                    } else {
                        card_in = '#card_4';
                        $(card_out).fadeOut(200, function () {
                            $(card_in).fadeIn(100);
                        });
                    }
                } else {
                    $(card_out).fadeOut(200, function () {
                        $(card_in).fadeIn(100);
                    });
                }
            }
        });

        $('body').on('click', '.js-publish', function (e) {
            e.preventDefault();
            var card_out = '#card_4';
            var card_in = '#card_5';
            var form_name = '#my_content_url';
            if ($(form_name).valid()) {
              $.ajax({
                dataType: 'json',
                type: 'post',
                url: urlBase + "app/streamy_content_add",
                data: $('#my_content_url').serialize(),
                success: function (r)
                {
                    $(card_out).fadeOut(200, function () {
                        $(card_in).fadeIn(100);
                    });
                }
            }); 
            }
            
        });

        $('body').on('click', '.js-prev', function (e) {
            e.preventDefault();
            //var button_id = this.id;
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