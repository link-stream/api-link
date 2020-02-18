<?php
$data = array();
$data['title'] = 'Streamy - My Content';
$data['page'] = 'My Content';
$this->load->view('app/_inc/header', $data);
?>
<style>
    .btn{
        _width: 100%;
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

                <div class="col-md-12 mb-4">
                    <div class="card text-left">
                        <div class="card-body">
                            <!--                            <h4 class="card-title mb-3">Default ordering (sorting)</h4>
                                                        <p>With DataTables you can alter the ordering characteristics of the table at initialisation time. Using the order initialisation parameter, you can set the table to display the data in exactly the order that you want.</p>
                                                       
                            -->




                            <div class="row">
                                <div class="col-lg-12 col-md-12">
                                    <div class="card mb-5">
                                        <div class="card-body">
                                            <div class="row row-xs">
                                                <div class="col-md-4">
                                                    <input class="form-control mb-1" id="name" name="name" type="text" placeholder="Name" />
                                                </div>

                                                <div class="col-md-4">
                                                    <!--<label for="visibility">Visibility</label>-->
                                                    <select class="form-control mb-1" id="type" name="type">
                                                        <option value="">All Types</option>
                                                        <?php
                                                        if (!empty($types)) {
                                                            foreach ($types as $type_item) {
                                                                ?>
                                                                <option value="<?= $type_item['id'] ?>"><?= $type_item['type'] ?></option>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-4 ">
                                                    <!--                                                    <label for="genre">Genre</label>-->
                                                    <select class="form-control mb-1" id="genre" name="genre">
                                                        <option value="">All Genres</option>
                                                        <?php
                                                        if (!empty($genres)) {
                                                            foreach ($genres as $genre_item) {
                                                                ?>
                                                                <option value="<?= $genre_item['id'] ?>"><?= $genre_item['genre'] ?></option>
                                                                <?php
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 mt-3 mt-md-10">
                                                   
                                                </div>
                                                <div class="col-md-6 mt-3 mt-md-10">
                                                   <button class="btn btn-primary btn-block" id="search">Search</button>
                                                </div>
                                                <div class="col-md-3 mt-3 mt-md-10">
                                                    
                                                </div>
                                            </div>
                                            <div class="row row-xs">

                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </div>


                            <div class="table-responsive">
                                <table class="display table table-striped table-bordered" id="deafult_ordering_table" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
<!--                                        <tbody>
                                        <tr>
                                            <td>Tiger Nixon</td>
                                            <td>System Architect</td>
                                            <td>Edinburgh</td>
                                            <td>61</td>
                                            <td>2011/04/25</td>
                                            <td>$320,800</td>
                                        </tr>
                                        <tr>
                                            <td>Garrett Winters</td>
                                            <td>Accountant</td>
                                            <td>Tokyo</td>
                                            <td>63</td>
                                            <td>2011/07/25</td>
                                            <td>$170,750</td>
                                        </tr>
                                        <tr>
                                            <td>Ashton Cox</td>
                                            <td>Junior Technical Author</td>
                                            <td>San Francisco</td>
                                            <td>66</td>
                                            <td>2009/01/12</td>
                                            <td>$86,000</td>
                                        </tr>
                                        <tr>
                                            <td>Gavin Joyce</td>
                                            <td>Developer</td>
                                            <td>Edinburgh</td>
                                            <td>42</td>
                                            <td>2010/12/22</td>
                                            <td>$92,575</td>
                                        </tr>
                                        <tr>
                                            <td>Jennifer Chang</td>
                                            <td>Regional Director</td>
                                            <td>Singapore</td>
                                            <td>28</td>
                                            <td>2010/11/14</td>
                                            <td>$357,650</td>
                                        </tr>
                                        <tr>
                                            <td>Brenden Wagner</td>
                                            <td>Software Engineer</td>
                                            <td>San Francisco</td>
                                            <td>28</td>
                                            <td>2011/06/07</td>
                                            <td>$206,850</td>
                                        </tr>
                                        <tr>
                                            <td>Fiona Green</td>
                                            <td>Chief Operating Officer (COO)</td>
                                            <td>San Francisco</td>
                                            <td>48</td>
                                            <td>2010/03/11</td>
                                            <td>$850,000</td>
                                        </tr>
                                        <tr>
                                            <td>Gavin Cortez</td>
                                            <td>Team Leader</td>
                                            <td>San Francisco</td>
                                            <td>22</td>
                                            <td>2008/10/26</td>
                                            <td>$235,500</td>
                                        </tr>
                                        <tr>
                                            <td>Martena Mccray</td>
                                            <td>Post-Sales support</td>
                                            <td>Edinburgh</td>
                                            <td>46</td>
                                            <td>2011/03/09</td>
                                            <td>$324,050</td>
                                        </tr>
                                        <tr>
                                            <td>Unity Butler</td>
                                            <td>Marketing Designer</td>
                                            <td>San Francisco</td>
                                            <td>47</td>
                                            <td>2009/12/09</td>
                                            <td>$85,675</td>
                                        </tr>
                                        <tr>
                                            <td>Howard Hatfield</td>
                                            <td>Office Manager</td>
                                            <td>San Francisco</td>
                                            <td>51</td>
                                            <td>2008/12/16</td>
                                            <td>$164,500</td>
                                        </tr>
                                        <tr>
                                            <td>Hope Fuentes</td>
                                            <td>Secretary</td>
                                            <td>San Francisco</td>
                                            <td>41</td>
                                            <td>2010/02/12</td>
                                            <td>$109,850</td>
                                        </tr>
                                    </tbody>-->
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END NEW CONTENT -->
            </div>
            <?php $this->load->view('app/_inc/main-footer', $data); ?>
        </div>
    </div>
    <!-- Detail Modal -->
    <div class="modal fade" id="orderDetail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false"></div>
    <?php $this->load->view('app/_inc/footer', $data); ?>
    <script src="<?= HTTP_ASSETS ?>dist-assets/js/plugins/datatables.min.js"></script>
    <!--<script src="<?= HTTP_ASSETS ?>dist-assets/js/scripts/datatables.script.min.js"></script>-->
    <script src="<?= HTTP_ASSETS ?>dist-assets/js/scripts/customizer.script.min.js"></script>
    <!--bootbox--> 
    <script src="<?= HTTP_ASSETS ?>vendor/bootbox-4.4.0/bootbox.min.js" type="text/javascript"></script> 
    <script> var urlBase = "<?= base_url(); ?>";</script>
    <script>
        $(document).ready(function () {

            //DataTable
            var dt = $('#deafult_ordering_table');
            var search_button = $('#search');
            dt.dataTable({
                "order": [[1, "asc"]],
                dom: 'tlip',
                //responsive: true,
                bServerSide: true,
                //bSort: false,
                scrollX: true,
                scrollY: '100%',
                sAjaxSource: urlBase + "app/my_content_ajax",
                fnServerData: function (sSource, aoData, rfrshCallbk) {
                    aoData.push(
                            {"name": "name", "value": $("[name='name']").val()},
                            {"name": "type", "value": $("[name='type']").val()},
                            {"name": "genre", "value": $("[name='genre']").val()}
                    );
                    $.ajax({
                        dataType: 'json',
                        type: 'POST',
                        url: sSource,
                        data: aoData,
                        beforeSend: function () {
                            dt.css({opacity: 0.1});
                            search_button.attr("disabled", "disabled");
                        },
                        success: rfrshCallbk,
                        complete: function () {
                            dt.css({opacity: 100});
                            search_button.removeAttr("disabled");
                        },
                        error: function (xhr, error, thrown) {
                            //location.href = urlBase + "app/login";
                        }
                    });
                },
                columns: [
//                    {
//                        className: 'details-control details-panel',
//                        orderable: false,
//                        data: null,
//                        defaultContent: ''
//                    },
                    {"sClass": 'text-center'}, ///// THIS COLUMN IS NOT EDITABLE
                    null, ///// THIS COLUMN IS NOT EDITABLE
                    null, ///// THIS COLUMN IS NOT EDITABLE
                    null, ///// THIS COLUMN IS NOT EDITABLE
                    {"sClass": 'text-center'}
                ],
                columnDefs: [
                    {"orderable": false, "targets": [0, 4]}
                ]
            });

            search_button.on('click', function () {
                dt.DataTable().draw();
            });

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
//                                    $('.js-streamy_list').html(r.streamys_view);
//                                    $('.js-streamys_nav').html(r.streamys_nav);
                                    dt.DataTable().draw();
                                }
                            },
                            error: function (textStatus, errorThrown) {
                                //window.location.href = 'app/';
                            }
                        });
                    }
                });
                console.log(id);
            });

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
                                dt.DataTable().draw();
                                $('#orderDetail').modal('hide');
                            }
                        },
                        error: function (textStatus, errorThrown) {
                            window.location.href = 'app/';
                        }
                    });
                }
            });


        });

    </script>
</body>
</html>