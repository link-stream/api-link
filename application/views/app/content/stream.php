<?php
$data = array();
$data['title'] = 'Streamy - My Content';
$data['page'] = 'Add Audio Content';
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

    .form-group label {

        font-size: 16px;
    }
</style>
<link rel="stylesheet" href="<?= HTTP_ASSETS ?>bootstrap-fileinput-master/css/fileinput.min.css" />
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
                                <div class="card" id="card_1">
                                    <div class="card-body">
                                        <h2 class="ul-widget-card__title text-center">Select the content type</h2>
                                        <p class="card-text text-mute text-center">Step 1 / 4 </p>
                                        <div class="col-md-12 ">
                                            <div class="card mb-5">
                                                <div class="card-body">
                                                    <div class="d-flex flex-column text-center">
                                                        <div class="col-md-6 form-group mb-3 m-auto">
                                                            <label for="radio">Streamy Type</label>
                                                            <select class="form-control" id="radio" name="radio">
                                                                <option value="" <?= ($type == '' ? 'selected=""' : '') ?>>Select</option>
                                                                <option value="1"<?= ($type == '1' ? 'selected=""' : '') ?>>SoundCloud</option>
                                                                <option value="2"<?= ($type == '2' ? 'selected=""' : '') ?>>YouTube</option>
                                                                <option value="4"<?= ($type == '4' ? 'selected=""' : '') ?>>Streamy</option>
                                                                <option value="6"<?= ($type == '6' ? 'selected=""' : '') ?>>Spotify</option>
                                                            </select>
                                                        </div>
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
                                <!-- Second -->
                                <div class="card text-center" id="card_2" style="display:none">
                                    <div class="card-body">
                                        <h2 class="ul-widget-card__title">Add your <?= $type_url ?></h2>
                                        <p class="card-text text-mute">Step 2 / 4</p>
                                        <div class="col-md-12 text-left">
                                            <div class="card mb-5">
                                                <div class="card-body">
                                                    <div class="d-flex flex-column">
                                                        <div class="form-group text-center">
                                                            <label for="streamy_url">Streamy Url</label>
                                                            <input class="form-control" type="text" id="streamy_url" name="streamy_url" placeholder="<?= $placeholder_url ?>" required="" value=""/>
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
                                <!-- Third -->
                                <div class="card text-center" id="card_3" style="display:none">
                                    <div class="card-body">
                                        <h2 class="ul-widget-card__title">View your content.</h2>
                                        <p class="card-text text-mute">Step 3 / 4</p>
                                        <div class="col-md-12 text-left">
                                            <div class="card mb-5">
                                                <div class="card-body p-0">
                                                    <div class="js-streamy_content"> </div>
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
                                                <div class="col-md-6 form-group mb-3">
                                                    <label for="genre">Genre</label>
                                                    <select class="form-control" id="genre" name="genre">
                                                        <!--                                                        <option value="" selected="">Select Genre</option>-->
                                                        <?php
                                                        if (!empty($genres)) {
                                                            foreach ($genres as $genre) {
                                                                ?>
                                                                <option value="<?= $genre['id'] ?>" <?= ($genre['id'] == '1') ? 'selected=""' : '' ?>><?= $genre['genre'] ?></option>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 form-group mb-3">
                                                    <label for="priority">Priority</label>
                                                    <select class="form-control" id="priority" name="priority">
                                                        <option value="2" selected="">Normal</option>
                                                        <option value="1" <?= ($user['plan_id'] == '1') ? 'disabled=""' : '' ?> ><?= ($user['plan_id'] == '1') ? 'Spotlight - Not available for Free Account' : 'Spotlight' ?></option>

                                                    </select>
                                                </div>
                                                <div class="col-md-6 form-group mb-3">
                                                    <label for="visibility">Visibility</label>
                                                    <select class="form-control" id="visibility" name="visibility">
                                                        <option value="1" selected="">Public</option>
                                                        <option value="2">Private</option>
                                                        <option value="3" <?= ($user['plan_id'] == '1') ? 'disabled=""' : '' ?>><?= ($user['plan_id'] == '1') ? 'Scheduled - Not available for Free Account' : 'Scheduled' ?> </option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 form-group mb-3 js-scheduled" style="display:none">
                                                    <label for="date">Scheduled</label>
                                                    <input class="form-control" id="date"  name="date" type="text" placeholder="Date" value="<?= date('m/d/Y') ?>" <?= ($user['plan_id'] == '1') ? 'readonly=""' : '' ?> />
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
                                <!-- Five -->
                                <div class="card text-center" id="card_5" style="display:none">
                                    <div class="card-body">
                                        <h2 class="ul-widget-card__title">Your Content was added</h2>
                                        <p class="card-text text-mute">Select your option</p>
                                        <div class="col-md-12 text-left">
                                            <div class="card mb-5">
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-4 ">   
                                                            <a href="<?= base_url() ?>app/content" class="btn btn-secondary m-1 js-content" type="button">Manage</a>
                                                        </div>
                                                        <div class="col-md-4 ">
                                                        </div>
                                                        <div class="col-md-4 ">
                                                            <a href="<?= base_url() ?>app/stream" class="btn btn-success m-1 js-content" type="button">Add New Stream</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Streamy First -->
                                <div class="card text-center" id="card_6" style="display:none">
                                    <div class="card-body">
                                        <h2 class="ul-widget-card__title">Add Streamy</h2>
                                        <p class="card-text text-mute">Step 2 / 4</p>
                                        <div class="col-md-12 text-left">
                                            <div class="card mb-5">
                                                <div class="card-body">
                                                    <input id="input_b1" name="input_b1" type="file" class="file" data-browse-on-zone-click="true">
                                                </div>
                                            </div>
                                        </div>
                                        <p></p>
                                        <div class="row">
                                            <div class="col-4">
                                                <button class="btn btn-secondary m-1 js-prev" id="prev_6" data-step="6" data-next="1" type="button">Back</button>
                                            </div>
                                            <div class="col-4"></div>
                                            <div class="col-4">
                                                <button class="btn btn-success m-1 js-next" id="next_6" data-step="6" data-next="7" type="button">Next</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Streamy Second -->
                                <div class="card text-center" id="card_7" style="display:none">
                                    <div class="card-body">
                                        <h2 class="ul-widget-card__title">Add Cover Art</h2>
                                        <p class="card-text text-mute">Step 3 / 4</p>
                                        <div class="col-md-12 text-left">
                                            <div class="card mb-5">
                                                <div class="card-body">
                                                    <input id="input_b2" name="input_b2" type="file" class="file" data-browse-on-zone-click="true">
                                                </div>
                                            </div>
                                        </div>
                                        <p></p>
                                        <div class="row">
                                            <div class="col-4">
                                                <button class="btn btn-secondary m-1 js-prev" id="prev_7" data-step="7" data-next="6" type="button">Back</button>
                                            </div>
                                            <div class="col-4"></div>
                                            <div class="col-4">
                                                <button class="btn btn-success m-1 js-next" id="next_7" data-step="7" data-next="4" type="button">Next</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
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
    <script src="<?= HTTP_ASSETS ?>bootstrap-fileinput-master/js/fileinput.min.js"></script>
    <script> var urlBase = "<?= base_url(); ?>";</script>
    <script> var role = "<?= $user['plan_id'] ?>";</script>
    <script>
        $(document).ready(function () {

            $('body').on('change', '#radio', function (e) {
                var id = this.id;
                var value = $(this).val();
                var streamy_content = $('.js-streamy_content');
                streamy_content.removeClass("embed-responsive embed-responsive-16by9 z-depth-1-half").addClass("embed-responsive embed-responsive-16by9 z-depth-1-half");
                if ($.trim(value) === '1') {
                    $('#card_2 .ul-widget-card__title').text('Add your SoundCloud URL');
                    $('#streamy_url').attr("placeholder", "https://soundcloud.com/iamstarinthesky/go-hard-prod-silo");
                } else if ($.trim(value) === '2') {
                    $('#card_2 .ul-widget-card__title').text('Add your YouTube URL');
                    $('#streamy_url').attr("placeholder", "https://www.youtube.com/watch?v=h_D3VFfhvs4");
                } else if ($.trim(value) === '3') {
                    $('#card_2 .ul-widget-card__title').text('Add your URL');
                    $('#streamy_url').attr("placeholder", "https://www.link.stream");
                } else if ($.trim(value) === '5') {
                    $('#card_2 .ul-widget-card__title').text('Add your TikTok URL');
                    $('#streamy_url').attr("placeholder", "https://www.tiktok.com/@scout2015/video/6718335390845095173");
                    streamy_content.removeClass("embed-responsive embed-responsive-16by9 z-depth-1-half");
                } else if ($.trim(value) === '6') {
                    $('#card_2 .ul-widget-card__title').text('Add your Spotify URL');
                    $('#streamy_url').attr("placeholder", "https://open.spotify.com/track/7mkRzIiioITA6ETM7QQ1d8");
                }
            });

            $('body').on('click', '.js-prev', function (e) {
                e.preventDefault();
                var step = $(this).attr("data-step");
                var next = $(this).attr("data-next");
                var card_out = '#card_' + step;
                var card_in = '#card_' + next;
                $(card_out).fadeOut(200, function () {
                    $(card_in).fadeIn(100);
                });
            });

            $.validator.addMethod("url_type", function (value, element) {
                var url_type = $('#my_content_url #radio').val();
                if (url_type === '1') {
                    var p = /^https?:\/\/(soundcloud\.com|snd\.sc)\/(.*)$/;
                    return (value.match(p)) ? RegExp.$1 : false;
                } else if (url_type === '2') {
                    var p = /^(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?(?=.*v=((\w|-){11}))(?:\S+)?$/;
                    return (value.match(p)) ? RegExp.$1 : false;
                } else if (url_type === '5') {
                    var p = /^(?:https?:\/\/)?(?:www\.)?tiktok\.com\/(.*)\/video\/(.*)$/;
                    return (value.match(p)) ? RegExp.$1 : false;
                } else if (url_type === '6') {
                    var p = /^(?:https?:\/\/)?(?:www\.)?open\.spotify\.com\/track\/(.*)$/;
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
                    radio: {
                        required: true
                    },
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
                    },
                    genre: {
                        required: true
                    },
                    input_b1: {
                        required: true
                    },
                    input_b2: {
                        required: true
                    }
                },
                messages: {},
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
                    var selValue = $('#my_content_url #radio').val();
                    if (button_id === 'next_1') {
                        if (selValue === '1' || selValue === '2' || selValue === '6') {
                            $(card_out).fadeOut(200, function () {
                                $(card_in).fadeIn(100);
                            });
                        } else {
                            card_in = '#card_6';
                            $(card_out).fadeOut(200, function () {
                                $(card_in).fadeIn(100);
                            });
                        }

                    } else if (button_id === 'next_2') {
                        if (selValue === '1' || selValue === '2' || selValue === '6') {
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
                            $(card_out).fadeOut(200, function () {
                                $(card_in).fadeIn(100);
                            });
                        }
                    } else if (button_id === 'next_3') {
                        var prev = $('#prev_4');
                        prev.attr("data-next", '3');
                        $(card_out).fadeOut(200, function () {
                            $(card_in).fadeIn(100);
                        });
                    } else if (button_id === 'next_4') {
                        $(card_out).fadeOut(200, function () {
                            $(card_in).fadeIn(100);
                        });
                    } else if (button_id === 'next_5') {
                        $(card_out).fadeOut(200, function () {
                            $(card_in).fadeIn(100);
                        });
                    } else if (button_id === 'next_6') {
                        $(card_out).fadeOut(200, function () {
                            $(card_in).fadeIn(100);
                        });
                    } else if (button_id === 'next_7') {
                        card_in = '#card_4';
                        var prev = $('#prev_4');
                        prev.attr("data-next", '7');
                        $(card_out).fadeOut(200, function () {
                            $(card_in).fadeIn(100);
                        });
                    }
                }
            });

            $('body').on('change', '#visibility', function (e) {
                var id = this.id;
                var value = $(this).val();
                var d = new Date();
                var strDate = (d.getMonth() + 1) + "/" + d.getDate() + "/" + d.getFullYear();
                if ($.trim(value) === '3' && role !== '1') {
                    $('#date').attr('readonly', false);
                    $('.js-scheduled').css('display', '');
                } else {
                    $('#date').attr('readonly', true);
                    $('#date').val(strDate);
                    $('.js-scheduled').css('display', 'none');
                }
            });

            $('body').on('click', '.js-publish', function (e) {
                e.preventDefault();
                var card_out = '#card_4';
                var card_in = '#card_5';
                var form_name = '#my_content_url';
                if ($(form_name).valid()) {
//                    $.ajax({
//                        dataType: 'json',
//                        type: 'post',
//                        url: urlBase + "app/streamy_content_add",
//                        data: $('#my_content_url').serialize(),
//                        success: function (r)
//                        {
//                            $(card_out).fadeOut(200, function () {
//                                $(card_in).fadeIn(100);
//                            });
//                        }
//                    });
                    var formData = new FormData(document.getElementById("my_content_url"));
                    $.ajax({
                        type: "POST",
                        dataType: 'json',
                        url: urlBase + "app/streamy_content_add",
                        data: formData,
                        processData: false, // tell jQuery not to process the data
                        contentType: false, // tell jQuery not to set contentType
                        beforeSend: function () {
                            $(".js-publish").attr("disabled", "disabled");
                        },
                        success: function (data)
                        {
                            if (data.status === 'Success') {
                                $(card_out).fadeOut(200, function () {
                                    $(card_in).fadeIn(100);
                                });
                            }
                        },
                        error: function (textStatus, errorThrown) {
                            window.location.href = 'app/';
                        }
                    });
                }

            });



        });
        $("#input_b1").fileinput({
            rtl: true,
            //dropZoneEnabled: false,
            allowedFileExtensions: ["mp3", "mp4", "mp2", "wave", "flac", "alac", "aiff"],
            showUpload: false,
            mainClass: "input-group-lg"
        });

        $("#input_b2").fileinput({
            rtl: true,
            //dropZoneEnabled: false,
            allowedFileExtensions: ["jpg", "png", "gif"],
            showUpload: false,
            mainClass: "input-group-lg"
        });
    </script>
</body>
</html>