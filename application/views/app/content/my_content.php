<?php
$data = array();
$data['title'] = 'Streamy';
$data['page'] = 'My Content';
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
        text-align: left;

    }
    ._embed_video_card-button{
        display:none !important;
    }

    ._embed_video_card-wrapper{
        display:none !important;
    }



    @media (min-width: 768px) {

        /* show 3 items */
        .carouselPrograms .carousel-inner .active,
        .carouselPrograms .carousel-inner .active + .carousel-item,
        .carouselPrograms .carousel-inner .active + .carousel-item + .carousel-item {
            display: block;
        }

        .carouselPrograms .carousel-inner .carousel-item.active:not(.carousel-item-right):not(.carousel-item-left),
        .carouselPrograms .carousel-inner .carousel-item.active:not(.carousel-item-right):not(.carousel-item-left) + .carousel-item,
        .carouselPrograms .carousel-inner .carousel-item.active:not(.carousel-item-right):not(.carousel-item-left) + .carousel-item + .carousel-item {
            transition: none;
        }

        .carouselPrograms .carousel-inner .carousel-item-next,
        .carouselPrograms .carousel-inner .carousel-item-prev {
            position: relative;
            transform: translate3d(0, 0, 0);
        }

        .carouselPrograms .carousel-inner .active.carousel-item + .carousel-item + .carousel-item + .carousel-item {
            position: absolute;
            top: 0;
            right: -33.333%;
            z-index: -1;
            display: block;
            visibility: visible;
        }

        /* left or forward direction */
        .carouselPrograms .active.carousel-item-left + .carousel-item-next.carousel-item-left,
        .carouselPrograms .carousel-item-next.carousel-item-left + .carousel-item,
        .carouselPrograms .carousel-item-next.carousel-item-left + .carousel-item + .carousel-item,
        .carouselPrograms .carousel-item-next.carousel-item-left + .carousel-item + .carousel-item + .carousel-item {
            position: relative;
            transform: translate3d(-100%, 0, 0);
            visibility: visible;
        }

        /* farthest right hidden item must be abso position for animations */
        .carouselPrograms .carousel-inner .carousel-item-prev.carousel-item-right {
            position: absolute;
            top: 0;
            left: 0%;
            z-index: -1;
            display: block;
            visibility: visible;
        }

        /* right or prev direction */
        .carouselPrograms .active.carousel-item-right + .carousel-item-prev.carousel-item-right,
        .carouselPrograms .carousel-item-prev.carousel-item-right + .carousel-item,
        .carouselPrograms .carousel-item-prev.carousel-item-right + .carousel-item + .carousel-item,
        .carouselPrograms .carousel-item-prev.carousel-item-right + .carousel-item + .carousel-item + .carousel-item {
            position: relative;
            transform: translate3d(100%, 0, 0);
            visibility: visible;
            display: block;
            visibility: visible;
        }
    }

    .owl-carousel .owl-stage::after {
        margin-bottom: 10px !important;

    }
    
 



</style>
<link rel="stylesheet" href="<?= HTTP_ASSETS ?>dist-assets/css/plugins/datatables.min.css" />
<!--<script src="<?= HTTP_ASSETS ?>mini-music-player-master/css/styles.css"></script>-->


<!-- Owl Stylesheets -->
<link rel="stylesheet" href="<?= HTTP_ASSETS ?>owlcarousel/assets/owl.carousel.min.css">
<link rel="stylesheet" href="<?= HTTP_ASSETS ?>owlcarousel/assets/owl.theme.default.min.css">
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
                <div class="row js-streamy_list">
                    <?= (!empty($streamys_view)) ? '' : '' ?>
                    <div class="owl-carousel owl-theme">
                        <?php
                        if (!empty($soundCloud)) {
                            $i = 0;
                            foreach ($soundCloud as $streamy) {
                                $i++;
                                ?>
                                <div class="col-12 mt-3">
                                    <div class="item">
                                        <div class="card" id="card_<?= $streamy['id'] ?>">
                                            <div class="<?= ($streamy['type'] == '1' || $streamy['type'] == '2') ? 'd-block w-100  rounded rounded embed-responsive embed-responsive-16by9 z-depth-1-half js-streamy_content' : 'embed-responsive' ?>">
                                                <?= $streamy['embeed'] ?>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title mb-2"><?= $streamy['type_icon'] ?> <?= $streamy['name'] ?></h5>
                                                <!--<p class="card-text text-mute"><?= $streamy['url'] ?></p>-->
                                                <div class="row">
                                                    <div class="col-5">
                                                        <button class="btn btn-outline-success m-1 js-edit" id="<?= $streamy['id'] ?>" type="button"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</button>

                                                    </div>
                                                    <div class="col-2">
                                                    </div>
                                                    <div class="col-5">
                                                        <button class="btn btn-outline-danger m-1 js-delete" id="<?= $streamy['id'] ?>" type="button"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                    <div class="owl-carousel owl-theme">
                        <?php
                        if (!empty($youTube)) {
                            $i = 0;
                            foreach ($youTube as $streamy) {
                                $i++;
                                ?>
                                <div class="col-12 mt-3">
                                    <div class="item">
                                        <div class="card" id="card_<?= $streamy['id'] ?>">
                                            <div class="<?= ($streamy['type'] == '1' || $streamy['type'] == '2') ? 'd-block w-100  rounded rounded embed-responsive embed-responsive-16by9 z-depth-1-half js-streamy_content' : 'embed-responsive' ?>">
                                                <?= $streamy['embeed'] ?>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title mb-2"><?= $streamy['type_icon'] ?> <?= $streamy['name'] ?></h5>
                                                <!--<p class="card-text text-mute"><?= $streamy['url'] ?></p>-->
                                                <div class="row">
                                                    <div class="col-5">
                                                        <button class="btn btn-outline-success m-1 js-edit" id="<?= $streamy['id'] ?>" type="button"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</button>

                                                    </div>
                                                    <div class="col-2">
                                                    </div>
                                                    <div class="col-5">
                                                        <button class="btn btn-outline-danger m-1 js-delete" id="<?= $streamy['id'] ?>" type="button"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                    <div class="owl-carousel owl-theme">
                        <?php
                        if (!empty($linkStreams)) {
                            $i = 0;
                            foreach ($linkStreams as $streamy) {
                                $i++;
                                ?>
                                <div class="col-12 mt-3">
                                    <div class="item">
                                        <div class="card" id="card_<?= $streamy['id'] ?>">
                                            <div class="<?= ($streamy['type'] == '1' || $streamy['type'] == '2') ? 'd-block w-100  rounded rounded embed-responsive embed-responsive-16by9 z-depth-1-half js-streamy_content' : 'embed-responsive' ?>">
                                                <?= $streamy['embeed'] ?>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title mb-2"><?= $streamy['type_icon'] ?> <?= $streamy['name'] ?></h5>
                                                <!--<p class="card-text text-mute"><?= $streamy['url'] ?></p>-->
                                                <div class="row">
                                                    <div class="col-5">
                                                        <button class="btn btn-outline-success m-1 js-edit" id="<?= $streamy['id'] ?>" type="button"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</button>

                                                    </div>
                                                    <div class="col-2">
                                                    </div>
                                                    <div class="col-5">
                                                        <button class="btn btn-outline-danger m-1 js-delete" id="<?= $streamy['id'] ?>" type="button"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                    <div class="owl-carousel owl-theme">
                        <?php
                        if (!empty($mystream1)) {
                            $i = 0;
                            foreach ($mystream as $streamy) {
                                $i++;
                                ?>
                                <div class="col-12 mt-3">
                                    <div class="item">
                                        <div class="card" id="card_<?= $streamy['id'] ?>">
                                            <div class="<?= ($streamy['type'] == '1' || $streamy['type'] == '2') ? 'd-block w-100  rounded rounded embed-responsive embed-responsive-16by9 z-depth-1-half js-streamy_content' : 'embed-responsive' ?>">
                                                <?= $streamy['embeed'] ?>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title mb-2"><?= $streamy['type_icon'] ?> <?= $streamy['name'] ?></h5>
                                                <!--<p class="card-text text-mute"><?= $streamy['url'] ?></p>-->
                                                <div class="row">
                                                    <div class="col-5">
                                                        <button class="btn btn-outline-success m-1 js-edit" id="<?= $streamy['id'] ?>" type="button"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</button>

                                                    </div>
                                                    <div class="col-2">
                                                    </div>
                                                    <div class="col-5">
                                                        <button class="btn btn-outline-danger m-1 js-delete" id="<?= $streamy['id'] ?>" type="button"><i class="fa fa-trash" aria-hidden="true"></i> Delete</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </div>
                <!--                <nav aria-label="Page navigation example">
                                    <ul class="pagination justify-content-end js-streamys_nav">
                <?= (!empty($streamys_nav)) ? '' : '' ?>
                                    </ul>
                                </nav>-->
                <!-- END NEW CONTENT -->
            </div>
            <?php $this->load->view('app/_inc/main-footer', $data); ?>
        </div>
    </div>
    <!-- Detail Modal -->
    <div class="modal fade" id="orderDetail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false"></div>
    <?php $this->load->view('app/_inc/footer', $data); ?>
    <script src="<?= HTTP_ASSETS ?>dist-assets/js/plugins/datatables.min.js"></script>
    <script src="<?= HTTP_ASSETS ?>dist-assets/js/scripts/datatables.script.min.js"></script>
    <script src="<?= HTTP_ASSETS ?>dist-assets/js/scripts/customizer.script.min.js"></script>
    <!--bootbox--> 
    <script src="<?= HTTP_ASSETS ?>vendor/bootbox-4.4.0/bootbox.min.js" type="text/javascript"></script> 

    <!--<script src="<?= HTTP_ASSETS ?>mini-music-player-master/js/musicplayer-min.js"></script>-->

    <script src="<?= HTTP_ASSETS ?>owlcarousel/owl.carousel.js"></script>
    <script> var urlBase = "<?= base_url(); ?>";</script>
    <script>
        $(document).ready(function () {
            
            
            console.log('load');
            
            
            
            $('body').on('click', '.js-edit', function (e) {
                var id = this.id;
                console.log(id);
                $.ajax({
                    type: 'post',
                    url: urlBase + "app/streamy_view",
                    data: {id: id},
                    success: function (r)
                    {
                        $('#orderDetail').html(r);
                        $('#orderDetail').modal('show');
                    }
                });
            });

            $('body').on('click', '.js-delete', function (e) {
                var id = this.id;
                bootbox.confirm('Are you sure you want to delete this content', function (result) {
                    if (result) {
                        $.ajax({
                            type: "POST",
                            dataType: 'json',
                            url: urlBase + "app/streamy_remove",
                            data: {id: id},
                            beforeSend: function () {
                                //$(".js-send").attr("disabled", "disabled");
                                //$(".js-process").attr("disabled", "disabled");
                            },
                            success: function (r)
                            {
                                if (r.status === 'Success') {
                                    //var url = urlBase + "pay?refid=" + r.id + "&cpid=" + r.payment_id;
                                    //window.open(url, '_blank');
                                    //$('#paymentModal').modal('hide');
                                    $('.js-streamy_list').html(r.streamys_view);
                                    $('.js-streamys_nav').html(r.streamys_nav);
                                }
                            },
                            error: function (textStatus, errorThrown) {
                                window.location.href = 'app/';
                            }
                        });
                    }
                });
                console.log(id);
            });

//            $('body').on('click', '.js-nav_button', function (e) {
//                e.preventDefault();
//                var id = this.id;
//                console.log(id);
//                $.ajax({
//                    type: "POST",
//                    dataType: 'json',
//                    url: urlBase + "app/streamy_nav_action",
//                    data: {id: id},
//                    beforeSend: function () {
//                        //$(".js-send").attr("disabled", "disabled");
//                        //$(".js-process").attr("disabled", "disabled");
//                    },
//                    success: function (r)
//                    {
//                        if (r.status === 'Success') {
//                            //var url = urlBase + "pay?refid=" + r.id + "&cpid=" + r.payment_id;
//                            //window.open(url, '_blank');
//                            //$('#paymentModal').modal('hide');
//                            $('.js-streamy_list').html(r.streamys_view);
//                            $('.js-streamys_nav').html(r.streamys_nav);
//                        }
//                    },
//                    error: function (textStatus, errorThrown) {
//                        window.location.href = 'app/';
//                    }
//                });
//            });

            $('body').on('click', '.js-update', function (e) {
                var form_name = '#my_content_update';
                if ($(form_name).valid()) {
                    $.ajax({
                        type: 'post',
                        url: urlBase + "app/streamy_update",
                        data: $(form_name).serialize(),
                        dataType: 'json',
                        success: function (r)
                        {
                            if (r.status === 'Success') {
                                var streamy = r.streamy;
                                var card = '#card_' + streamy.id;
                                $(card + ' .ul-widget-card_title').html(streamy.type_icon + ' ' + streamy.name);
                                //$(card + ' .card-text').text(streamy.url);
                                $(card + ' .public_desc').text('Visibility: ' + streamy.public_desc);
                                $(card + ' .priority_desc').text('Priority: ' + streamy.priority_desc);
                                $(card + ' .publish_at').text('Scheduled: ' + streamy.publish_at);
                                $(card + ' .genre_desc').text(streamy.genre_desc);
                                $('#orderDetail').modal('hide');
                            }
                        },
                        error: function (textStatus, errorThrown) {
                            window.location.href = 'app/';
                        }
                    });
                }
            });

            $('.owl-carousel').owlCarousel({
                loop: true,
                margin: 5,
                responsiveClass: true,
                //navText : ["",""],
                responsive: {
                    0: {
                        items: 1,
                        nav: true
                    },
                    650: {
                        items: 2,
                        nav: true
                    },
                    992: {
                        items: 3,
                        nav: true,
                        loop: false
                    },
                    1200: {
                        items: 4,
                        nav: true,
                        loop: false
                    },
                    1600: {
                        items: 5,
                        nav: true,
                        loop: false
                    },
                    2000: {
                        items: 6,
                        nav: true,
                        loop: false
                    }
                }
            });

            //$(".example").musicPlayer();
            //
            //
            //
            //
            var owl = $('.owl-carousel');
            owl.owlCarousel();
// Listen to owl events:
            owl.on('changed.owl.carousel', function (event) {
                console.log(event);
            });
//
        });
        var x = document.getElementById("myAudio");

        function playAudio() {
            console.log(x);
            x.play();
        }

        function pauseAudio() {
            x.pause();
        }




//        $(".example").musicPlayer({
//
//            autoPlay: false,
//
//            volume: 80,
//
//            loop: false,
//
//            timeSeparator: ' / ',
//
//            playerAbovePlaylist: true,
//
//            infoElements: ['title', 'artist'],
//
//            elements: ['artwork', 'information', 'controls', 'progress', 'time', 'volume'],
//
//            timeElements: ['current', 'duration'],
//
//            controlElements: ['backward', 'play', 'forward', 'stop'],
//
        //});
    </script>
</body>
</html>