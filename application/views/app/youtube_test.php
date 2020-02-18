<?php
$data = array();
$data['title'] = 'Streamy';
$data['page'] = 'Dashboard';
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
                </div>
                <div class="separator-breadcrumb border-top"></div>
                <!-- end of main-content -->
                <!-- NEW CONTENT -->
                <!--<iframe id="video" src="//www.youtube.com/embed/oRdxUFDoQe0?enablejsapi=1&html5=1" frameborder="0" allowfullscreen></iframe>-->


                <div>
                    <div id="player1"></div>
                    <div id="player2"></div>
                    <div id="player3"></div>
                    <div id="player4"></div>
                    <div id="player5"></div>
                    <div id="player6"></div>
                    <div id="player7"></div>
                    <div id="player8"></div>
                    <div id="player9"></div>
                    <div id="player10"></div>
                    <div id="player11"></div>
                    <div id="player12"></div>
                </div>

                <!-- END NEW CONTENT -->
            </div>
            <?php $this->load->view('app/_inc/main-footer', $data); ?>
        </div>
    </div>

    <?php $this->load->view('app/_inc/footer', $data); ?>
    <script> var urlBase = "<?= base_url(); ?>";</script>
    <script>
//        // global variable for the player
//        var player;
//
//// this function gets called when API is ready to use
//        function onYouTubePlayerAPIReady() {
//            // create the global player from the specific iframe (#video)
//            player = new YT.Player('video', {
//                events: {
//                    // call this function when player is ready to use
//                    'onReady': onPlayerReady,
//                    'onStateChange': onPlayerStateChange
//                }
//            });
//        }
//
////        function onPlayerReady(event) {
////
////            // bind events
//////            var playButton = document.getElementById("play-button");
//////            playButton.addEventListener("click", function () {
//////                player.playVideo();
//////            });
//////
//////            var pauseButton = document.getElementById("pause-button");
//////            pauseButton.addEventListener("click", function () {
//////                player.pauseVideo();
//////            });
////
////        }
//
//        // 4. The API will call this function when the video player is ready.
//        function onPlayerReady(event) {
//            event.target.playVideo();
//        }
////
////        // 5. The API calls this function when the player's state changes.
////        //    The function indicates that when playing a video (state=1),
////        //    the player should play for six seconds and then stop.
////        var done = false;
//        function onPlayerStateChange(event) {
//            console.log('StateChange');
//            //console.log(event.data);
//            //console.log(YT.PlayerState);
////            if (event.data == YT.PlayerState.PLAYING && !done) {
////                setTimeout(stopVideo, 6000);
////                done = true;
////            }
//            if (event.data === YT.PlayerState.PLAYING) {
//                console.log('Play');
//            }
//            if (event.data === YT.PlayerState.PAUSED) {
//                console.log('Paused');
//            }
//            if (event.data === YT.PlayerState.ENDED) {
//                console.log('Ended');
//            }
//        }
////        function stopVideo() {
////            player.stopVideo();
////        }
//
//// Inject YouTube API script
//        var tag = document.createElement('script');
//        tag.src = "//www.youtube.com/player_api";
//        var firstScriptTag = document.getElementsByTagName('script')[0];
//        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);



        var tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        var firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
        


        var playerInfoList = [{
                id: 'player1',
                height: '390',
                width: '640',
                videoId: 'oRdxUFDoQe0'
            }, {
                id: 'player2',
                height: '390',
                width: '640',
                videoId: 'oRdxUFDoQe0'
            }, {
                id: 'player3',
                height: '390',
                width: '640',
                videoId: 'oRdxUFDoQe0'
            }, {
                id: 'player4',
                height: '390',
                width: '640',
                videoId: 'oRdxUFDoQe0'
            }, {
                id: 'player5',
                height: '390',
                width: '640',
                videoId: 'oRdxUFDoQe0'
            }, {
                id: 'player6',
                height: '390',
                width: '640',
                videoId: 'oRdxUFDoQe0'
            }, {
                id: 'player7',
                height: '390',
                width: '640',
                videoId: 'oRdxUFDoQe0'
            }, {
                id: 'player8',
                height: '390',
                width: '640',
                videoId: 'oRdxUFDoQe0'
            }, {
                id: 'player9',
                height: '390',
                width: '640',
                videoId: 'oRdxUFDoQe0'
            }, {
                id: 'player10',
                height: '390',
                width: '640',
                videoId: 'oRdxUFDoQe0'
            }, {
                id: 'player11',
                height: '390',
                width: '640',
                videoId: 'oRdxUFDoQe0'
            }, {
                id: 'player12',
                height: '390',
                width: '640',
                videoId: 'oRdxUFDoQe0'
            }];

        function onYouTubeIframeAPIReady() {
            if (typeof playerInfoList === 'undefined')
                return;

            for (var i = 0; i < playerInfoList.length; i++) {
                var curplayer = createPlayer(playerInfoList[i]);
                players[i] = curplayer;
            }
        }

        var players = new Array();

        function createPlayer(playerInfo) {
            return new YT.Player(playerInfo.id, {
                height: playerInfo.height,
                width: playerInfo.width,
                videoId: playerInfo.videoId,
                events: {
                    // call this function when player is ready to use
                    'onReady': onPlayerReady,
                    'onStateChange': onPlayerStateChange
                }
            });
        }
        
        function onPlayerReady(event) {
            event.target.playVideo();
        }
//
//        // 5. The API calls this function when the player's state changes.
//        //    The function indicates that when playing a video (state=1),
//        //    the player should play for six seconds and then stop.
//        var done = false;
        function onPlayerStateChange(event) {
            console.log('StateChange');
            //console.log(event.data);
            //console.log(YT.PlayerState);
//            if (event.data == YT.PlayerState.PLAYING && !done) {
//                setTimeout(stopVideo, 6000);
//                done = true;
//            }
            if (event.data === YT.PlayerState.PLAYING) {
                console.log('Play');
            }
            if (event.data === YT.PlayerState.PAUSED) {
                console.log('Paused');
            }
            if (event.data === YT.PlayerState.ENDED) {
                console.log('Ended');
            }
        }
    </script>    
</body>
</html>