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
</style>
<link rel="stylesheet" href="<?= HTTP_ASSETS ?>dist-assets/css/plugins/datatables.min.css" />
<body class="text-left">
    <div class="app-admin-wrap layout-sidebar-large">
        <?php $this->load->view('app/_inc/main-header-live', $data); ?>


        <!-- =============== Left side End ================-->
        <div class="main-content-wrap  d-flex flex-column">
            <!-- ============ Body content start ============= -->
            <div class="main-content">            
                <!-- end of main-content -->
                <!-- NEW CONTENT -->
                <div class="card user-profile o-hidden mb-4">
                    <div id="header-cover" class="header-cover" style="background-image: url('<?= base_url() . 'home/get_banner/'.$user['id'] ?>')">
                    </div>
                    <div class="user-info"><img class="profile-picture avatar-lg mb-2" src="<?= base_url() . 'home/get_avatar/'.$user['id'] ?>" alt="" id="profile-picture"/>
                        <p class="m-0 text-24"><?= $user['display_name'] ?></p>
                        <p class="text-muted m-0"><?= $user['about'] ?></p>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs profile-nav mb-4" id="profileTab" role="tablist">
                            <li class="nav-item"><a class="nav-link active" id="timeline-tab" data-toggle="tab" href="#url" role="tab" aria-controls="timeline" aria-selected="false">Streamy</a></li>
                            <li class="nav-item"><a class="nav-link" id="friends-tab" data-toggle="tab" href="#friends" role="tab" aria-controls="friends" aria-selected="false">Links</a></li>
                            <li class="nav-item"><a class="nav-link" id="about-tab" data-toggle="tab" href="#about" role="tab" aria-controls="about" aria-selected="true">Tracks</a></li>
                        </ul>

                        <div class="tab-content" id="profileTabContent">
                            <div class="tab-pane fade active show" id="url" role="tabpanel" aria-labelledby="timeline-tab">
                                A
                            </div>
                            <div class="tab-pane fade" id="friends" role="tabpanel" aria-labelledby="friends-tab">
                                B
                            </div>
                            <div class="tab-pane fade" id="about" role="tabpanel" aria-labelledby="about-tab">
                                C
                            </div>
                        </div>
                    </div>

                    <!-- END NEW CONTENT -->
                </div>
                <!-- END NEW CONTENT -->
            </div>
            <?php $this->load->view('app/_inc/main-footer', $data); ?>
        </div>
    </div>
    <!-- Detail Modal -->
    <?php $this->load->view('app/_inc/footer', $data); ?>

    <script> var urlBase = "<?= base_url(); ?>";</script>
    <script>
        $(document).ready(function () {
//            $('body').on('click', '.js-edit', function (e) {
//                var id = this.id;
//                console.log(id);
//                $.ajax({
//                    type: 'post',
//                    url: urlBase + "app/streamy_view",
//                    data: {id: id},
//                    success: function (r)
//                    {
//                        $('#orderDetail').html(r);
//                        $('#orderDetail').modal('show');
//                    }
//                });
//            });
//            $('body').on('click', '.js-delete', function (e) {
//
//                var id = this.id;
//                bootbox.confirm('Are you sure you want to process this content', function (result) {
//                    if (result) {
//                        $.ajax({
//                            type: "POST",
//                            dataType: 'json',
//                            url: urlBase + "app/streamy_remove",
//                            data: {id: id},
//                            beforeSend: function () {
//                                //$(".js-send").attr("disabled", "disabled");
//                                //$(".js-process").attr("disabled", "disabled");
//                            },
//                            success: function (r)
//                            {
//                                if (r.status === 'Success') {
//                                    //var url = urlBase + "pay?refid=" + r.id + "&cpid=" + r.payment_id;
//                                    //window.open(url, '_blank');
//                                    //$('#paymentModal').modal('hide');
//                                    $('.js-streamy_list').html(r.streamys_view);
//                                    $('.js-streamys_nav').html(r.streamys_nav);
//                                }
//                            },
//                            error: function (textStatus, errorThrown) {
//                                window.location.href = 'app/';
//                            }
//                        });
//                    }
//                });
//                console.log(id);
//            });
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
        });
    </script>
</body>
</html>