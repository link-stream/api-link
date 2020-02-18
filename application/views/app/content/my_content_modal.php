<div class="modal-dialog" role="document">
    <div class="modal-content">
        <form action="" method="post" role="form" id="my_content_update" >
            <input name="id" id="id" type="hidden" value="<?= $id ?>">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">My Content Edit</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <!--<h3 class="ul-widget-card__title"><?= $type_icon ?> <?= $type_desc ?></h3>-->
                <!--<p class="card-text text-mute"><?= ($type_id != '4') ? 'URL: ' . $url : '' ?></p>-->

                <div class="col-md-12 text-left">
                    <div class="row">
                        <div class="col-md-12 form-group mb-3">
                            <label for="song_name">Name</label>
                            <input class="form-control" type="text" id="song_name" name="song_name" placeholder="Enter name" required="" value="<?= $name ?>"/>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label for="genre">Genre</label>
                            <select class="form-control" id="genre" name="genre">
                                <!--<option value="">Select Genre</option>-->
                                <?php
                                if (!empty($genres)) {
                                    foreach ($genres as $genre_item) {
                                        ?>
                                        <option value="<?= $genre_item['id'] ?>" <?= ($genre_item['id'] == $genre_id ? 'selected=""' : '') ?>><?= $genre_item['genre'] ?></option>
                                        <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="priority">Priority</label>
                            <select class="form-control" id="priority" name="priority">
                                <option value="1" <?= ('1' == $priority_id ? 'selected=""' : '') ?> <?= ($user['plan_id'] == '1') ? 'disabled=""' : '' ?> ><?= ($user['plan_id'] == '1') ? 'Spotlight - Not available for Free Account' : 'Spotlight' ?></option>
                                <option value="2" <?= ('2' == $priority_id ? 'selected=""' : '') ?>>Normal</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label for="visibility">Visibility</label>
                            <select class="form-control" id="visibility" name="visibility">
                                <option value="1" <?= ('1' == $public ? 'selected=""' : '') ?>>Public</option>
                                <option value="2" <?= ('2' == $public ? 'selected=""' : '') ?>>Private</option>
                                <option value="3" <?= ('3' == $public ? 'selected=""' : '') ?> <?= ($user['plan_id'] == '1') ? 'disabled=""' : '' ?>><?= ($user['plan_id'] == '1') ? 'Scheduled - Not available for Free Account' : 'Scheduled' ?> </option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group mb-3 js-scheduled" style="<?= ('3' == $public ? '' : 'display:none') ?>">
                            <label for="date">Scheduled</label>
                            <input class="form-control" id="date"  name="date" type="text" placeholder="Date" value="<?= $publish_at ?>" <?= ($user['plan_id'] == '1' || $public != '3') ? 'readonly=""' : '' ?> />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <!--<button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>-->
                <button class="btn btn-primary ml-2 js-update" type="button">Save changes</button>
            </div>
        </form>
    </div>
</div>
<script> var role = "<?= $user['plan_id'] ?>";</script>
<script>
    $(document).ready(function () {

        $("#my_content_update").validate({
            onkeyup: false,
            errorClass: "state-error",
            validClass: "state-success",
            errorElement: "em",
            errorPlacement: function (label, element) {
                label.addClass('invalid-tooltip');
                label.insertAfter(element);
            },
            rules: {
//                    radio: {
//                        required: true
//                    },
//                    streamy_url: {
//                        required: true,
//                        url: true,
//                        url_type: "#streamy_url"
//                    },
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

    });


</script>