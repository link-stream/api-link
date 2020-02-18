<?php
$data = array();
$this->load->view('home/_inc/header', $data);
?>
<body data-spy="scroll" data-target=".scrollspy" class="bg-dark" style="overflow:  auto !important">

    <div id="page">

        <!-- ============================
             BG & Overlays
        ================================= -->

        <!-- Solid BG -->
        <div class="section-overlay bg-solid-color overlay-opacity"></div>
        <!-- /End Solid BG -->

        <div class="container-fluid">
            <div class="row">

                <!-- ============================
                     Info
                ================================= -->

                <div id="info" class="col-md-12 text-white text-center  col-transform">
                    <div class="vert-middle">
                        <div class="reveal scale-out">

                            <!-- Logo -->
                            <div class="p-t-b-15">
                                <img src="<?= HTTP_ASSETS ?>solid-bg/images/streamy_logos_outlined_RGB_streamy_logo_vertical_negative_RGB.png" width="360px" alt="streamy logo">
                            </div>
                            <!-- /End Logo -->

                            <div class="p-t-b-15">
                                <!-- Headline & Description -->
                                <h2><span class="font-weight-200">Your streams come true </span><br>Spring 2020</h2>

<!--                                <p>Musicians, Podcasters, YouTubers, Creators and Streamers: <br>Lead the wave with free early access to Streamy in Spring 2020.<br>
                                </p>-->
                                <!-- /End Headline & Description -->
                            </div>

                            <!-- Social Media Links -->
                            <div class="p-t-b-30">
                                <p>
                                    <a href="https://www.instagram.com/_linkstream" target="_blank" class="link-white"><i
                                            class="fa fa-instagram"></i>Join the fam on Instgram!</a>&nbsp&nbsp&nbsp    
                                </p>
                            </div>
                            <!-- Social Media Links -->
                            <div class="p-t-b-1">
                                <p>
                                    ©2020 LinkStream LLC. All Rights Reserved.
                                </p><br><br>
                            </div>
                            <!-- Social Media Links -->

                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
    <!-- /#page -->
    <div id="photoswipe"></div>

    <!-- Scripts -->
    <script src="<?= HTTP_ASSETS ?>solid-bg/js/plugins/jquery-1.12.4.min.js"></script>
    <script src="<?= HTTP_ASSETS ?>solid-bg/js/plugins/bootstrap.min.js"></script>
    <script src="<?= HTTP_ASSETS ?>solid-bg/js/plugins/scrollreveal.min.js"></script>
    <script src="<?= HTTP_ASSETS ?>solid-bg/js/plugins/contact-form.js"></script>
    <script src="<?= HTTP_ASSETS ?>solid-bg/js/plugins/newsletter-form.js"></script>
    <script src="<?= HTTP_ASSETS ?>solid-bg/js/plugins/jquery.ajaxchimp.min.js"></script>
    <script src="<?= HTTP_ASSETS ?>solid-bg/js/plugins/photoswipe/photoswipe.min.js"></script>
    <script src="<?= HTTP_ASSETS ?>solid-bg/js/plugins/photoswipe/photoswipe-ui-default.min.js"></script>
    <script src="<?= HTTP_ASSETS ?>solid-bg/js/plugins/jquery.countdown.min.js"></script>
    <script src="<?= HTTP_ASSETS ?>solid-bg/js/plugins/prefixfree.min.js"></script>
    <script src="<?= HTTP_ASSETS ?>jquery-validation-1.19.1/dist/jquery.validate.min.js"></script>
    <script src="<?= HTTP_ASSETS ?>jquery-validation-1.19.1/dist/additional-methods.min.js"></script>
    <script> var urlBase = "<?= base_url(); ?>";</script>
    <!-- Custom Script -->
    <script src="<?= HTTP_ASSETS ?>solid-bg/js/custom.js"></script>
</body>
</html>
