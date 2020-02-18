<?php
if (!empty($streamys)) {
    foreach ($streamys as $streamy) {
        ?>
<div class="col-lg-4 col-xl-4 4 col-md-6 mt-3">
    <div class="card" id="card_<?= $streamy['id'] ?>">
        <!--<img class="d-block w-100 rounded rounded" src="../../dist-assets/images/products/iphone-1.jpg" alt="First slide" />-->
        <div class="<?= ($streamy['type'] == '1' || $streamy['type'] == '2') ? 'd-block w-100  rounded rounded embed-responsive embed-responsive-16by9 z-depth-1-half js-streamy_content' : 'embed-responsive' ?>">
            <?= $streamy['embeed'] ?>
        </div>
        <div class="card-body">
            <h5 class="card-title mb-2"><?= $streamy['type_icon'] ?> <?= $streamy['name'] ?></h5>
            <p class="card-text text-mute"><?= $streamy['url'] ?></p>
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
        <?php
    }
} else {
    ?>
    <div class="col-md-12 col-lg-12 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <h2 class="ul-widget-card__title">Your Content is Empty</h2>
                <p class="card-text text-mute">Start inserting your content.</p>
                <div class="row">
                    <div class="col-md-3"></div>
                    <div class="col-md-6">
                        <div class="card mb-5">
                            <div class="card-body">
                                <a href="<?= base_url() ?>app/my_content_add" class="btn btn-success m-1 js-content" type="button">My Content</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3"></div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

</style>

<?php
//$url = 'https://www.tiktok.com/@scout2015/video/6718335390845095173';
//$embed_url = '<iframe width="420" height="315" '
//        . 'src="https://www.tiktok.com/oembed?url=' . $url . '"> '
//        . 'frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen '
//        . '</iframe>';
//$url = "https://www.tiktok.com/oembed?url=https://www.tiktok.com/@scout2015/video/6718335390845095173";
//$ch = curl_init();
//curl_setopt($ch, CURLOPT_URL, $url);
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
//$json = curl_exec($ch);
//if (!$json) {
//    echo curl_error($ch);
//}
//curl_close($ch);
////var_dump(json_decode($json));
//$tik_tok = json_decode($json);
//$embed_url = $tik_tok->html;
//echo $embed_url;
//
//$embed_url = '';
//
//
//$url = 'https://www.tiktok.com/embed/6718335390845095173';
//$embed_url = '<iframe width="200" height="350" '
//        . 'src="' . $url . '"> '
//        . 'frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen '
//        . '</iframe>';
//$embed_url = '<div style="overflow: hidden; margin-top: -100px; margin-left: -15px;">
//<iframe 
//src="https://www.tiktok.com/embed/6718335390845095173" scrolling="no" 
//style="height: 600px; border: 0px none; width: 550px; margin-top: -55px; margin-left: 5px; 
//margin-right: 0px; ">
//</iframe>
//</div>';
//$embed_url = '<iframe style="height: 600px; border: 0px none; width: 550px; margin-top: -55px; margin-left: 5px; 
////margin-right: 0px; " '
//        . 'src="https://www.tiktok.com/@scout2015/video/6718335390845095173"> '
//        . 'frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen '
//        . '</iframe>';
?>
<!--<div class="col-md-6 col-lg-4 mb-4">
    <div class="card text-center" id="card_10000">
        <div class="card-body">
            <h3 class="ul-widget-card_title">TIK TOK</h3>
            <p class="card-text text-mute">https://www.tiktok.com/@scout2015/video/6718335390845095173</p>
            <div class="col-md-12 text-left p-0">
                <div class="card mb-2">
                    <div class="card-body p-0">
                        <div class=" js-streamy_content">
<?= $embed_url ?> 
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 text-left p-1">
                <div class="row">
                    <div class="col-md-6 text-mute genre_desc"></div>
                    <div class="col-md-6 text-mute public_desc">Visibility: </div>

                    <div class="col-md-6 text-mute priority_desc">Priority: </div>
                    <div class="col-md-6 text-mute publish_at">Scheduled: </div>
                </div>

            </div>
            <div class="row">
                <div class="col-3">

                </div>
                <div class="col-6">
                    <button class="btn btn-primary m-1 js-edit" id="<?= $streamy['id'] ?>" type="button"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</button>
                </div>
                <div class="col-3">
                    <button class="btn btn-danger m-1 js-delete" id="<?= $streamy['id'] ?>" type="button"><i class="fa fa-trash" aria-hidden="true"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>-->
<script>
//        var iframe = document.getElementsByClassName('tiktok-embed');
//   console.log(iframe);
//var style = document.createElement('style');
//style.textContent =
//  'body {' +
//  '  background-color: some-color;' +
//  '  background-image: some-image;' +
//  '}' 
//;
//iframe.appendChild(style);



//var block = document.getElementsByClassName('tiktok-embed');
//    var iframe = document.getElementsByClassName('tiktok-embed').find("iframe");
//    console.log(iframe);

</script>


<!--<div class="col-lg-4 col-xl-4 mt-3">
    <div class="card">
        <img class="d-block w-100 rounded rounded" src="../../dist-assets/images/products/iphone-1.jpg" alt="First slide" />
        <div class="<?= ($streamy['type'] == '1' || $streamy['type'] == '2') ? 'd-block w-100  rounded rounded embed-responsive embed-responsive-16by9 z-depth-1-half js-streamy_content' : 'embed-responsive' ?>">
            <?= $streamy['embeed'] ?>
        </div>
        <div class="card-body">
            <h5 class="card-title mb-2"><?= $streamy['type_icon'] ?> <?= $streamy['name'] ?></h5>
            <p class="card-text text-mute"><?= $streamy['url'] ?></p>
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
                        <button class="btn btn-primary ul-btn-raised--v2 m-1" type="button">Download</button>
                        <button class="btn btn-outline-success ul-btn-raised--v2 m-1 float-right" type="button">Preview</button>
        </div>
    </div>
</div>-->
