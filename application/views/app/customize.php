<?php
$data = array();
$data['title'] = 'Streamy';
$data['page'] = 'Page Cutomize';
$this->load->view('app/_inc/header', $data);
?>
<style>
    .float {
        float: right;
        position: relative;
        z-index: 1000;
        top: 10px;
        right: 10px;
    }

    /*    .float_2 {
            float: right;
            position: relative;
            z-index: 1000;
            top: 10px;
            right: 10px;
        }*/


    .header-cover{
        z-index: 1;
    }

    /*.float:after {
      content: 'Float';
      color: red;
    }*/

    .cursor-pointer{
        cursor: pointer;
    }

    /*    .tooltip {
            position: relative;
            display: inline-block;
        }
    
        .tooltip .tooltiptext {
            visibility: hidden;
            width: 140px;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px;
            position: absolute;
            z-index: 1;
            bottom: 150%;
            left: 50%;
            margin-left: -75px;
            opacity: 0;
            transition: opacity 0.3s;
        }
    
        .tooltip .tooltiptext::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 50%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #555 transparent transparent transparent;
        }
    
        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }*/


    /*    #sortable{
            cursor: -webkit-grab; cursor: grab;
        }*/

    /*    li:hover {
            cursor: -webkit-grab; cursor: grab; 
        }*/

    /*    li:focus {
            cursor: -webkit-grabbing; cursor: grabbing;
        }
    
        li:active {
            cursor: -webkit-grabbing; cursor: grabbing;
        }*/
    /*cursor: -webkit-grabbing; cursor: grabbing;*/

    .list-sorteable{
        cursor: -webkit-grab; cursor: grab;
        background-color: #F0F0F0;-webkit-box-shadow:  0px 0px 10px 1px rgba(0, 0, 0, .1);
        box-shadow:  0px 0px 10px 1px rgba(0, 0, 0, .1);
    }

    .list-sorteable li {

        /*width: 80px;*/
        height: 80px;
        /*    overflow: hidden;
            margin: auto;
                margin-bottom: auto;
            border-radius: 50%;*/

    }

    /*    .list-sorteable li{
           
            box-shadow: 0 0 5px black;
        }*/
</style>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<link rel="stylesheet" href="https://rawgit.com/mervick/emojionearea/master/dist/emojionearea.min.css">
<!--<link rel="stylesheet" href="/resources/demos/style.css">-->
<style>
    /*  #sortable { list-style-type: none; margin: 0; padding: 0; width: 60%; }
      #sortable li { margin: 0 3px 3px 3px; padding: 0.4em; padding-left: 1.5em; font-size: 1.4em; height: 18px; }
      #sortable li span { position: absolute; margin-left: -1.3em; }*/
</style>

<link rel="stylesheet" href="<?= HTTP_ASSETS ?>bootstrap-fileinput-master/css/fileinput.min.css" />
<link rel="stylesheet" href="<?= HTTP_ASSETS ?>Croppie-master/croppie.css" />

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
                <div class="card user-profile o-hidden mb-4">
<!--<div class="header-cover" style="background-image: url('<?= HTTP_ASSETS ?>dist-assets/images/photo-wide-4.jpg')"></div>-->
                    <div id="header-cover" class="header-cover" style="background-image: url('<?= base_url() . 'app/get_banner' ?>')">
                        <div class="float">
                            <!--<button class="btn btn-icon-text btn-secondary js_banner-edit"><i class="fa fa-camera" aria-hidden="true"></i> Update Header</button>-->
                            <input type="file" class="file" data-browse-on-zone-click="true" id="file_photo2"  name="file_photo2"/>
                        </div>

                    </div>
                    <!--<div class="user-info"><img class="profile-picture avatar-lg mb-2" src="<?= HTTP_ASSETS ?>dist-assets/images/faces/1.jpg" alt="" />-->
                    <div class="user-info"><img class="profile-picture avatar-lg mb-2" src="<?= base_url() . 'app/get_avatar' ?>" alt="" id="profile-picture"/>
                        <p class="m-0 text-24"><?= $user['display_name'] ?></p>
                        <p class="text-muted m-0"><?= $user['about'] ?></p>
                        <div class="">
                            <!--<button class="btn btn-icon-text btn-secondary btn-sm js_avatar-edit"><i class="fa fa-camera" aria-hidden="true"></i> Update Avatar</button>--> 
                            <input type="file" class="file" data-browse-on-zone-click="true" id="file_photo3"  name="file_photo3"/>
                        </div>
                    </div>
                    <div class="card-body">
                        <ul class="nav nav-tabs profile-nav mb-4" id="profileTab" role="tablist">
                            <li class="nav-item"><a class="nav-link active" id="timeline-tab" data-toggle="tab" href="#url" role="tab" aria-controls="timeline" aria-selected="false">My URL</a></li>
                            <li class="nav-item"><a class="nav-link" id="friends-tab" data-toggle="tab" href="#friends" role="tab" aria-controls="friends" aria-selected="false">Content Order</a></li>
                            <!--<li class="nav-item"><a class="nav-link" id="timeline-tab" data-toggle="tab" href="#timeline" role="tab" aria-controls="timeline" aria-selected="false">Avatar & Banner</a></li>-->
                            <li class="nav-item"><a class="nav-link" id="about-tab" data-toggle="tab" href="#about" role="tab" aria-controls="about" aria-selected="true">Profile</a></li>
                            <!--<li class="nav-item"><a class="nav-link" id="photos-tab" data-toggle="tab" href="#photos" role="tab" aria-controls="photos" aria-selected="false">Photos</a></li>-->
                        </ul>

                        <div class="tab-content" id="profileTabContent">
                            <div class="tab-pane fade active show" id="url" role="tabpanel" aria-labelledby="timeline-tab">
                                <div class="row">
                                    <label for="basic-url">Your streamy URL</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend"><span class="input-group-text" id="basic-addon3"><?= base_url() ?></span></div>
                                        <input class="form-control" id="basic-url" type="text" aria-describedby="basic-addon3" value="<?= $user['url'] ?>" />
                                        <div class="input-group-append">
                                            <button class="btn btn-icon-text btn-success"  id="copyButton">Copy</button>
                                        </div>
                                    </div>
                                    <div class="col-md-12 text-center p-3">
                                        <button class="btn btn-icon-text btn-warning">Update
                                        </button> 
                                    </div>
                                </div>




                                <!--Example-->
                                <style>
                                    label.cabinet{
                                        display: block;
                                        cursor: pointer;
                                    }

                                    label.cabinet input.file{
                                        position: relative;
                                        height: 100%;
                                        width: auto;
                                        opacity: 0;
                                        -moz-opacity: 0;
                                        filter:progid:DXImageTransform.Microsoft.Alpha(opacity=0);
                                        margin-top:-30px;
                                    }

                                    #upload-demo{
                                        width: 350px;
                                        height: 350px;
                                        padding-bottom:25px;
                                    }
                                    figure figcaption {
                                        position: absolute;
                                        bottom: 0;
                                        color: #fff;
                                        width: 100%;
                                        padding-left: 9px;
                                        padding-bottom: 5px;
                                        text-shadow: 0 0 10px #000;
                                    }
                                </style>
                                <!--                                <link rel='stylesheet prefetch' href='https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'>
                                                                <div class="row">
                                                                    <div class="col-xs-12">
                                
                                                                        <form action="test-image.php" id="form" method="post">
                                
                                                                            <label class="cabinet center-block">
                                                                                <figure>
                                                                                    <img src="" class="gambar img-responsive img-thumbnail" id="item-img-output" />
                                                                                    <figcaption><i class="fa fa-camera"></i></figcaption>
                                                                                </figure>
                                                                                <input type="file" class="item-img file center-block" name="file_photo"/>
                                                                            </label>
                                
                                                                        </form>
                                
                                                                    </div>
                                                                </div>-->


                                <div class="modal fade" id="cropImagePop" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title" id="myModalLabel"> </h4>
                                            </div>
                                            <div class="modal-body">
                                                <div id="upload-demo" class="center-block"></div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                                <button type="button" id="cropImageBtn" class="btn btn-primary">Crop</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--Example End-->




                            </div>
                            <div class="tab-pane fade" id="friends" role="tabpanel" aria-labelledby="friends-tab">
                                <div class="row">
                                    <div class="col-md-12 text-center mb-4">
                                        <h4>Drag to Order Your Content</h4>
                                    </div> 
                                </div>
                                <ul id="sortable" class="list-group list-sorteable">
                                    <?php if (empty($order)) { ?>
                                        <li class="list-group-item"><i class="fa fa-bars"></i>&nbsp;&nbsp;<img src="<?= HTTP_ASSETS ?>images/logo/sc.png" alt="" height="40px"/> SoundCloud</li>
                                        <li class="list-group-item"><i class="fa fa-bars"></i>&nbsp;&nbsp;<img src="<?= HTTP_ASSETS ?>images/logo/yb.png" alt="" height="40px"/> Youtube</li>
                                        <li class="list-group-item"><i class="fa fa-bars"></i>&nbsp;&nbsp;<img src="<?= HTTP_ASSETS ?>images/logo/streamy_icon_RGB.png" alt="" height="40px"/> Streamy</li>
                                        <li class="list-group-item"><i class="fa fa-bars"></i>&nbsp;&nbsp;<img src="<?= HTTP_ASSETS ?>images/logo/streamy_icon_RGB.png" alt="" height="40px"/> LinkStreams</li>
                                        <?php
                                    } else {
                                        foreach ($order as $type_row) {
                                            if ($type_row['content_type_id'] == '1') {
                                                ?>
                                                <li class="list-group-item"><i class="fa fa-bars"></i>&nbsp;&nbsp;<img src="<?= HTTP_ASSETS ?>images/logo/sc.png" alt="" height="40px"/> SoundCloud</li>
                                                <?php
                                            } elseif ($type_row['content_type_id'] == '2') {
                                                ?>
                                                <li class="list-group-item"><i class="fa fa-bars"></i>&nbsp;&nbsp;<img src="<?= HTTP_ASSETS ?>images/logo/yb.png" alt="" height="40px"/> Youtube</li>
                                                <?php
                                            } elseif ($type_row['content_type_id'] == '3') {
                                                ?>
                                                <li class="list-group-item"><i class="fa fa-bars"></i>&nbsp;&nbsp;<img src="<?= HTTP_ASSETS ?>images/logo/streamy_icon_RGB.png" alt="" height="40px"/> Streamy</li>
                                                <?php
                                            } elseif ($type_row['content_type_id'] == '4') {
                                                ?>
                                                <li class="list-group-item"><i class="fa fa-bars"></i>&nbsp;&nbsp;<img src="<?= HTTP_ASSETS ?>images/logo/streamy_icon_RGB.png" alt="" height="40px"/> LinkStreams</li>
                                                <?php
                                            }
                                            ?>

                                            <?php
                                        }
                                    }
                                    ?>
                                </ul>
                                <!--                                <div class="row list"  id="list1">
                                
                                
                                                                    <div class="col-md-12 js-view cursor-pointer">
                                                                        <div class="card card-profile-1 mb-4">
                                                                            <div class="mt-3 ml-3">
                                                                               <i class="i-Drag text-16 mr-1"></i> 
                                                                                <i class="fa fa-arrows-alt" aria-hidden="true"></i>
                                
                                                                            </div>
                                                                            <div class="card-body text-center">
                                
                                                                                <div class="avatar box-shadow-2 mb-3"><img src="<?= HTTP_ASSETS ?>images/logo/sc.png" alt="" /></div>
                                                                                <h5 class="m-0">SoundCloud</h5>
                                                                                <p class="mt-0">UI/UX Designer</p>
                                                                                <p>Embeed your SoundCloud Content</p>
                                                                                <button class="btn btn-primary btn-rounded js-up">UP</button> <button class="btn btn-primary btn-rounded js-down">Down</button>
                                                                                <div class="card-socials-simple mt-4"><a href=""><i class="i-Linkedin-2"></i></a><a href=""><i class="i-Facebook-2"></i></a><a href=""><i class="i-Twitter"></i></a></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 js-view cursor-pointer">
                                                                        <div class="card card-profile-1 mb-4">
                                                                            <div class="mt-3 ml-3">
                                                                               <i class="i-Drag text-16 mr-1"></i> 
                                                                                <i class="fa fa-arrows-alt" aria-hidden="true"></i>
                                
                                                                            </div>
                                                                            <div class="card-body text-center">
                                                                                <div class="avatar box-shadow-2 mb-3"><img src="<?= HTTP_ASSETS ?>images/logo/yb.png" alt="" /></div>
                                                                                <h5 class="m-0">Youtube</h5>
                                                                                <p class="mt-0">UI/UX Designer</p>
                                                                                <p>Embeed your Youtube Content</p>
                                                                                <button class="btn btn-primary btn-rounded js-up">UP</button> <button class="btn btn-primary btn-rounded js-down">Down</button>
                                                                                <div class="card-socials-simple mt-4"><a href=""><i class="i-Linkedin-2"></i></a><a href=""><i class="i-Facebook-2"></i></a><a href=""><i class="i-Twitter"></i></a></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                
                                                                    <div class="col-md-12 js-view cursor-pointer">
                                                                        <div class="card card-profile-1 mb-4">
                                                                            <div class="mt-3 ml-3">
                                                                               <i class="i-Drag text-16 mr-1"></i> 
                                                                                <i class="fa fa-arrows-alt" aria-hidden="true"></i>
                                
                                                                            </div>
                                                                            <div class="card-body text-center">
                                                                                <div class="avatar box-shadow-2 mb-3"><img src="<?= HTTP_ASSETS ?>images/logo/streamy_icon_RGB.png" alt="" /></div>
                                                                                <h5 class="m-0">Streamy</h5>
                                                                                <p class="mt-0">UI/UX Designer</p>
                                                                                <p>Stream your Streamy Content</p>
                                                                                <button class="btn btn-primary btn-rounded js-up">UP</button> <button class="btn btn-primary btn-rounded js-down">Down</button>
                                                                                <div class="card-socials-simple mt-4"><a href=""><i class="i-Linkedin-2"></i></a><a href=""><i class="i-Facebook-2"></i></a><a href=""><i class="i-Twitter"></i></a></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 js-view cursor-pointer">
                                                                        <div class="card card-profile-1 mb-4">
                                                                            <div class="mt-3 ml-3">
                                                                               <i class="i-Drag text-16 mr-1"></i> 
                                                                                <i class="fa fa-arrows-alt" aria-hidden="true"></i>
                                
                                                                            </div>
                                                                            <div class="card-body text-center">
                                                                                <div class="avatar box-shadow-2 mb-3"><img src="<?= HTTP_ASSETS ?>images/logo/streamy_icon_RGB.png" alt="" /></div>
                                                                                <h5 class="m-0">LinkStreams</h5>
                                                                                <p class="mt-0">UI/UX Designer</p>
                                                                                <p>Show your LinkStreams Content</p>
                                                                                <button class="btn btn-primary btn-rounded js-up">UP</button> <button class="btn btn-primary btn-rounded js-down">Down</button>
                                                                                <div class="card-socials-simple mt-4"><a href=""><i class="i-Linkedin-2"></i></a><a href=""><i class="i-Facebook-2"></i></a><a href=""><i class="i-Twitter"></i></a></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>-->
                                <div class="row">
                                    <div class="col-md-12 text-center p-3">
                                        <button class="btn btn-icon-text btn-warning">Update
                                        </button> 
                                    </div>
                                </div>
                            </div>
                            <!--                            <div class="tab-pane fade  show" id="timeline" role="tabpanel" aria-labelledby="timeline-tab">
                                <div class="row">
                                    <div class="col-md-6 text-left p-3">
                                        <div class="form-group text-center">
                                            <label for="input_b1">Avatar</label>
                                            <input id="input_b1" name="input_b1" type="file" class="file" data-browse-on-zone-click="true">
                                        </div>
                                        <div class="demo" id="upload-demo">
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-left p-3">
                                        <div class="form-group text-center">
                                            <label for="input_b2">Banner</label>
                                            <input id="input_b2" name="input_b2" type="file" class="file" data-browse-on-zone-click="true">  
                                        </div>

                                    </div>
                                    <div class="col-md-12 text-center p-3">
                                        <button class="btn btn-icon-text btn-warning">Update
                                        </button> 
                                    </div>
                                </div>
                            </div>-->
                            <style>
                                .row:after, .row:before {
                                    content: " ";
                                    display: table;
                                    clear: both;
                                }
                                .span6 {
                                    float: left;
                                    width: 48%;
                                    padding: 1%;
                                }

                                .emojionearea-standalone {
                                    float: right;
                                }

                                .divOutside {
                                    height: 20px;
                                    width: 20px;
                                    background-position: -1px -26px;
                                    background-repeat: no-repeat;
                                    background-image: url(data:image/png;base64,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);
                                }
                                .emojionearea-button
                                {
                                    opacity:0 !important;
                                }


                            </style>
                            <div class="tab-pane fade" id="about" role="tabpanel" aria-labelledby="about-tab">
                                <label for="basic-url">What do you do?</label>
                                <div class="input-group mb-3">
                                    <input class="form-control" id="about" type="text" aria-describedby="basic-addon3" value="<?= $user['about'] ?>" />
                                </div>
                                <label for="basic-url">Display Name</label>
                                <div class="input-group mb-3">
                                    <input class="form-control" id="display_name" type="text" aria-describedby="basic-addon3" value="<?= $user['display_name'] ?>" />
                                </div>
                                <h4>Bio:</h4>
                                <div class="form-group">
                                    <textarea class="form-control" id="bio" rows="3">
                                        <?= $user['bio'] ?>
                                    </textarea>
                                    <div id="divOutside" class="divOutside">
                                    </div>
                                    <hr />
                                    <h4>Social Network</h4>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label" for="facebook">Facebook</label>
                                                <div class="col-sm-10">
                                                    <input class="form-control" id="facebook" type="text" placeholder="www.facebook.com" value="<?= $user['facebook'] ?>"/>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label" for="instagram">Instagram</label>
                                                <div class="col-sm-10">
                                                    <input class="form-control" id="instagram" type="text" placeholder="www.instagram.com" value="<?= $user['instagram'] ?>"/>
                                                </div>
                                            </div>
                                        </div> 
                                        <div class="col-lg-6">
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label" for="youtube">Youtube</label>
                                                <div class="col-sm-10">
                                                    <input class="form-control" id="youtube" type="text" placeholder="www.youtube.com" value="<?= $user['youtube'] ?>"/>
                                                </div>
                                            </div>
                                        </div> 
                                        <div class="col-lg-6">
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label" for="twitter">Twitter</label>
                                                <div class="col-sm-10">
                                                    <input class="form-control" id="twitter" type="text" placeholder="www.twitter.com" value="<?= $user['twitter'] ?>"/>
                                                </div>
                                            </div>
                                        </div> 
                                        <div class="col-lg-6">
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label" for="soundcloud">Soundcloud</label>
                                                <div class="col-sm-10">
                                                    <input class="form-control" id="soundcloud" type="text" placeholder="www.soundcloud.com" value="<?= $user['soundcloud'] ?>"/>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 text-center p-3">
                                            <button class="btn btn-icon-text btn-warning">Update
                                            </button> 
                                        </div>
                                    </div>
                                </div> 
                                <!--                            <div class="tab-pane fade" id="photos" role="tabpanel" aria-labelledby="photos-tab">
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="card text-white o-hidden mb-3"><img class="card-img" src="<?= HTTP_ASSETS ?>images/logo/streamy_icon_RGB.png" alt="" />
                                                                            <div class="card-img-overlay">
                                                                                <div class="p-1 text-left card-footer font-weight-light d-flex"><span class="mr-3 d-flex align-items-center"><i class="i-Speach-Bubble-6 mr-1"></i>12</span><span class="d-flex align-items-center"><i class="i-Calendar-4 mr-2"></i>03.12.2018</span></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="card text-white o-hidden mb-3"><img class="card-img" src="../../dist-assets/images/products/headphone-2.jpg" alt="" />
                                                                            <div class="card-img-overlay">
                                                                                <div class="p-1 text-left card-footer font-weight-light d-flex"><span class="mr-3 d-flex align-items-center"><i class="i-Speach-Bubble-6 mr-1"></i>12</span><span class="d-flex align-items-center"><i class="i-Calendar-4 mr-2"></i>03.12.2018</span></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="card text-white o-hidden mb-3"><img class="card-img" src="../../dist-assets/images/products/headphone-3.jpg" alt="" />
                                                                            <div class="card-img-overlay">
                                                                                <div class="p-1 text-left card-footer font-weight-light d-flex"><span class="mr-3 d-flex align-items-center"><i class="i-Speach-Bubble-6 mr-1"></i>12</span><span class="d-flex align-items-center"><i class="i-Calendar-4 mr-2"></i>03.12.2018</span></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="card text-white o-hidden mb-3"><img class="card-img" src="../../dist-assets/images/products/iphone-1.jpg" alt="" />
                                                                            <div class="card-img-overlay">
                                                                                <div class="p-1 text-left card-footer font-weight-light d-flex"><span class="mr-3 d-flex align-items-center"><i class="i-Speach-Bubble-6 mr-1"></i>12</span><span class="d-flex align-items-center"><i class="i-Calendar-4 mr-2"></i>03.12.2018</span></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="card text-white o-hidden mb-3"><img class="card-img" src="../../dist-assets/images/products/iphone-2.jpg" alt="" />
                                                                            <div class="card-img-overlay">
                                                                                <div class="p-1 text-left card-footer font-weight-light d-flex"><span class="mr-3 d-flex align-items-center"><i class="i-Speach-Bubble-6 mr-1"></i>12</span><span class="d-flex align-items-center"><i class="i-Calendar-4 mr-2"></i>03.12.2018</span></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="card text-white o-hidden mb-3"><img class="card-img" src="../../dist-assets/images/products/watch-1.jpg" alt="" />
                                                                            <div class="card-img-overlay">
                                                                                <div class="p-1 text-left card-footer font-weight-light d-flex"><span class="mr-3 d-flex align-items-center"><i class="i-Speach-Bubble-6 mr-1"></i> 12</span><span class="d-flex align-items-center"><i class="i-Calendar-4 mr-2"></i>03.12.2018</span></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>-->
                            </div>
                        </div>
                    </div>

                    <!-- END NEW CONTENT -->
                </div>
                <?php $this->load->view('app/_inc/main-footer', $data); ?>
            </div>
        </div>
        <?php $this->load->view('app/_inc/footer', $data); ?>
        <script src="<?= HTTP_ASSETS ?>bootstrap-fileinput-master/js/fileinput.min.js"></script>
        <script src="<?= HTTP_ASSETS ?>js/Sortable.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
        <script src="<?= HTTP_ASSETS ?>Croppie-master/croppie.js"></script>
        <script src="https://rawgit.com/mervick/emojionearea/master/dist/emojionearea.js"></script>
        <script> var urlBase = "<?= base_url(); ?>";</script>
        <script>
            $("#input_b1").fileinput({
                rtl: true,
                dropZoneEnabled: false,
                allowedFileExtensions: ["jpg", "png", "gif", "jpeg"],
                showUpload: false,
                mainClass: "input-group-lg",
                showPreview: false
            });

            $("#input_b2").fileinput({
                rtl: true,
                //showCaption: false,
                dropZoneEnabled: false,
                allowedFileExtensions: ["jpg", "png", "gif", "jpeg"],
                showUpload: false,
                mainClass: "input-group-lg",
                showPreview: true

            });


            $("#file_photo3").fileinput({
                rtl: true,
                dropZoneEnabled: false,
                allowedFileExtensions: ["jpg", "png", "gif", "jpeg"],
                showUpload: false,
                mainClass: "input-group-sm",
                browseClass: "btn btn-icon-text btn-secondary btn-sm fileinput-btns",
                showPreview: false, showCaption: false, showDelete: false, showRemove: false,
                browseIcon: '<i class="fa fa-camera" aria-hidden="true"></i>&nbsp;', browseLabel: 'Update Avatar'
            });

            var $el1 = $("#file_photo2");
            $el1.fileinput({
                rtl: true,
                dropZoneEnabled: false,
                allowedFileExtensions: ["jpg", "png", "gif", "jpeg"],
                showUpload: false,
                mainClass: "input-group-sm",
                browseClass: "btn btn-icon-text btn-secondary btn-sm fileinput-btns",
                showPreview: false, showCaption: false, showDelete: false, showRemove: false,
                browseIcon: '<i class="fa fa-camera" aria-hidden="true"></i>&nbsp;', browseLabel: 'Update Header',
                uploadAsync: false,
                uploadUrl: urlBase + "app/banner_upload_ajax",
            }).on("filebatchselected", function (event, files) {
                $el1.fileinput("upload");
            }).on('filebatchuploadsuccess', function (event, data) {
                console.log(data.response);
                console.log(document.getElementById('header-cover'));
                document.getElementById('header-cover').style.backgroundImage = "url()";
                console.log(document.getElementById('header-cover'));
                var randomId = new Date().getTime();
                document.getElementById('header-cover').style.backgroundImage = "url(" + urlBase + "app/get_banner?random=" + randomId + ")";
                console.log(document.getElementById('header-cover'));

            });



            $(document).ready(function () {

                $(function () {
                    $("#sortable").sortable();
                    $("#sortable").disableSelection();
                });



                $('.list-sorteable').mousedown(function () {
                    //console.log('D');
                    $(this).css('cursor', 'grabbing');
                });
                $('.list-sorteable').mouseup(function () {
                    //console.log('E');
                    $(this).css('cursor', 'grab');
                });

                $('#copyButton').click(function () {
                    var $txt = urlBase + $('#basic-url').val();
                    copyToClipboard($txt);
                });

                function copyToClipboard(text) {
                    var $temp = $("<input>");
                    $("body").append($temp);
                    $temp.val(text).select();
                    document.execCommand("copy");
                    $temp.remove();
                }
                //
                //            $uploadCrop = $('#upload-demo').croppie({
                //                enableExif: true,
                //                viewport: {
                //                    width: 200,
                //                    height: 200,
                //                    type: 'circle'
                //                },
                //                boundary: {
                //                    width: 300,
                //                    height: 300
                //                }
                //            });

                //$('#upload-demo').croppie('bind');



                $('#input_b1').on('change', function () {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $uploadCrop.croppie('bind', {
                            url: e.target.result
                        }).then(function () {
                            console.log('jQuery bind complete');
                        });

                    }
                    reader.readAsDataURL(this.files[0]);
                });

                $('.upload-result').on('click', function (ev) {
                    $uploadCrop.croppie('result', {
                        type: 'canvas',
                        size: 'viewport'
                    }).then(function (resp) {

                        $.ajax({
                            url: "components/testcrop.php",
                            type: "POST",
                            data: {"image": resp},
                            success: function (data) {
                                if (data == 'Image Uploaded Successfully') {
                                    html = '<img src="' + resp + '" />';
                                    $("#upload-demo-i").html(html);
                                } else {
                                    $("body").append("<div class='upload-error'>" + data + "</div>");

                                }
                            }
                        });
                    });
                });

                $('.js_banner-edit').on('click', function () {
                    console.log('EDIT');
                });


                $("#bio").emojioneArea({

                    pickerPosition: "right",
                    tonesStyle: "bullet",
                    events: {
                        keyup: function (editor, event) {
                            console.log(editor.html());
                            console.log(this.getText());
                        }
                    }
                });

                $('#divOutside').click(function () {
                    $('.emojionearea-button').click()
                });


            });

            // Start upload preview image
            //$(".gambar").attr("src", "http://erssolucoes.com.br/profile_male.jpg");
            var $uploadCrop,
                    tempFilename,
                    rawImg,
                    imageId;
            function readFile(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('.upload-demo').addClass('ready');
                        $('#cropImagePop').modal('show');
                        rawImg = e.target.result;
                    }
                    reader.readAsDataURL(input.files[0]);
                } else {
                    swal("Disculpe, su navegador no soporta FileReader API.");
                }
            }

            $uploadCrop = $('#upload-demo').croppie({
                viewport: {
                    width: 270,
                    height: 270,
                    type: 'circle'
                },
                enforceBoundary: false,
                enableExif: true
            });
            $('#cropImagePop').on('shown.bs.modal', function () {
                // alert('Shown pop');
                $uploadCrop.croppie('bind', {
                    url: rawImg
                }).then(function () {
                    console.log('jQuery bind complete');
                });
            });

            //        $('.item-img').on('change', function () {
            //            imageId = $(this).data('id');
            //            tempFilename = $(this).val();
            //            $('#cancelCropBtn').data('id', imageId);
            //            readFile(this);
            //        });

            $('#file_photo3').on('change', function () {
                imageId = $(this).data('id');
                tempFilename = $(this).val();
                console.log('change');
                $('#cancelCropBtn').data('id', imageId);
                readFile(this);
            });
            $('#cropImageBtn').on('click', function (ev) {
                $uploadCrop.croppie('result', {
                    type: 'base64',
                    format: 'jpeg',
                    size: {width: 270, height: 270}
                }).then(function (resp) {
                    $('#profile-picture').attr('src', resp);
                    //		$('#cropImagePop').modal('hide');
                    console.log('update');
                    $.ajax({
                        url: urlBase + "app/avatar_upload_ajax",
                        type: "POST",
                        data: {"image": resp},
                        success: function (data) {
                            html = '<img src="' + resp + '" />';
                            $("#upload-image-i").html(html);
                            $('#cropImagePop').modal('hide');
                        }
                    });
                    $('#cropImagePop').modal('hide');
                });







            });
            // End upload preview image  



        </script>
</body>
</html>