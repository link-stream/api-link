<?php
$data = array();
$data['title'] = 'Streamy';
$data['page'] = 'Dashboard';
$this->load->view('app/_inc/header', $data);
?>
<style>
    .highcharts-figure, .highcharts-data-table table {
        min-width: 310px; 
        max-width: 800px;
        margin: 1em auto;
    }

    #container {
        height: 400px;
    }

    .highcharts-data-table table {
        font-family: Verdana, sans-serif;
        border-collapse: collapse;
        border: 1px solid #EBEBEB;
        margin: 10px auto;
        text-align: center;
        width: 100%;
        max-width: 500px;
    }
    .highcharts-data-table caption {
        padding: 1em 0;
        font-size: 1.2em;
        color: #555;
    }
    .highcharts-data-table th {
        font-weight: 600;
        padding: 0.5em;
    }
    .highcharts-data-table td, .highcharts-data-table th, .highcharts-data-table caption {
        padding: 0.5em;
    }
    .highcharts-data-table thead tr, .highcharts-data-table tr:nth-child(even) {
        background: #f8f8f8;
    }
    .highcharts-data-table tr:hover {
        background: #f1f7ff;
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
                <div class="row ">
                    <div class="col-lg-4 col-xl-4 col-md-12 mt-3">
                        <table class="display table table-striped table-bordered" id="deafult_ordering_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th colspan="2" class="text-center">Visitor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1,000</td>
                                    <td>7,250</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Last 24Hrs</th>
                                    <th>Last 7 Days</th>

                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="col-lg-4 col-xl-4 col-md-12 mt-3">
                        <table class="display table table-striped table-bordered" id="deafult_ordering_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th colspan="2" class="text-center">Streams</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1,250</td>
                                    <td>6,000</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Last 24Hrs</th>
                                    <th>Last 7 Days</th>

                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="col-lg-4 col-xl-4 col-md-12 mt-3">
                        <table class="display table table-striped table-bordered" id="deafult_ordering_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th colspan="2" class="text-center">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>$50</td>
                                    <td>$2,000</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Last 24Hrs</th>
                                    <th>Last 7 Days</th>

                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="col-lg-12 col-xl-4 col-md-12 mt-3">
                        <div class="panel panel-default">
                            <div class="panel-body" style=" border: 1px solid #EBEBEB;">
                                <figure class="highcharts-figure">
                                    <div id="container-2"></div>
                                </figure>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-xl-4 col-md-12 mt-3">
                        <div class="panel panel-default">
                            <div class="panel-body" style=" border: 1px solid #EBEBEB;">
                                <figure class="highcharts-figure">
                                    <div id="container"></div>
                                </figure>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12 col-xl-4 col-md-12 mt-3">
                        <table class="display table table-striped table-bordered" id="deafult_ordering_table" style="width:100%">
                            <thead>
                                <tr>
                                    <th colspan="2" class="text-center">Top 10 Location / Last 7 Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>USA</td>
                                    <td>3,000</td>
                                </tr>
                                <tr>
                                    <td>Canada</td>
                                    <td>1,500</td>
                                </tr>
                                <tr>
                                    <td>United Kindom</td>
                                    <td>1,000</td>
                                </tr>
                                <tr>
                                    <td>Italy</td>
                                    <td>800</td>
                                </tr>
                                <tr>
                                    <td>China</td>
                                    <td>700</td>
                                </tr>
                                <tr>
                                    <td>Brazil</td>
                                    <td>700</td>
                                </tr>
                                <tr>
                                    <td>Mexico</td>
                                    <td>200</td>
                                </tr>
                                <tr>
                                    <td>Puerto Rico</td>
                                    <td>50</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- END NEW CONTENT -->
            </div>
            <?php $this->load->view('app/_inc/main-footer', $data); ?>
        </div>
    </div>
    <!-- Detail Modal -->
    <div class="modal fade" id="orderDetail" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form action="" method="post" role="form" id="my_content_update" >
                    <input name="id" id="id" type="hidden" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Upgrade Your Plan</h5>
                        <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                    </div>
                    <div class="modal-body">

                    </div>
                    <div class="modal-footer">
                        <!--<button class="btn btn-secondary" type="button" data-dismiss="modal">Close</button>-->
                        <!--<button class="btn btn-primary ml-2 js-update" type="button">Save changes</button>-->
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php $this->load->view('app/_inc/footer', $data); ?>
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/export-data.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script> var urlBase = "<?= base_url(); ?>";</script>
    <script>
        var $plan_modal = "<?= $plan_modal; ?>";
        $(document).ready(function () {
            if ($plan_modal) {
                console.log('OK');
                $('#orderDetail').modal('show');
            }


            Highcharts.chart('container', {
                chart: {
                    type: 'column'
                },
                title: {
                    text: 'Streams by Source '
                },
                subtitle: {
                    text: 'Last 7 Days'
                },
                xAxis: {
                    categories: [
                        '01/20/2020',
                        '01/21/2020',
                        '01/22/2020',
                        '01/23/2020',
                        '01/24/2020',
                        '01/25/2020',
                        '01/26/2020'
                    ],
                    crosshair: true
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Count'
                    }
                },
                tooltip: {
                    headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
                    pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
                            '<td style="padding:0"><b>{point.y}</b></td></tr>',
                    footerFormat: '</table>',
                    shared: true,
                    useHTML: true
                },
                plotOptions: {
                    column: {
                        pointPadding: 0.2,
                        borderWidth: 0
                    }
                },
                series: [{
                        name: 'All',
                        data: [215, 191, 205, 257, 231, 282, 278]

                    }, {
                        name: 'Streamy',
                        data: [83, 78, 98, 93, 106, 84, 105]

                    }, {
                        name: 'SoundCloud',
                        data: [48, 38, 39, 41, 47, 48, 59]

                    }, {
                        name: 'Youtube',
                        data: [42, 33, 34, 39, 52, 75, 57]

                    }, {
                        name: 'Spotify',
                        data: [42, 33, 34, 39, 52, 75, 57]

                    }]
            });

            Highcharts.chart('container-2', {
                chart: {
                    type: 'bar'
                },
                title: {
                    text: 'Top 10 Streams'
                },
                subtitle: {
                    text: 'Last 7 Days'
                },
                xAxis: {
                    categories: ['Title_1', 'Title_2', 'Title_3', 'Title_4', 'Title_5', 'Title_6', 'Title_7', 'Title_8', 'Title_9', 'Title_10'],
                    title: {
                        text: null
                    }
                },
                yAxis: {
                    min: 0,
                    title: {
                        text: 'Streams',
                        align: 'high'
                    },
                    labels: {
                        overflow: 'justify'
                    }
                },
                tooltip: {
                    //valueSuffix: ' millions'
                },
                plotOptions: {
                    bar: {
                        dataLabels: {
                            enabled: false
                        }
                    }
                },
                legend: {
                    layout: 'vertical',
                    align: 'right',
                    verticalAlign: 'top',
                    x: -40,
                    y: 80,
                    floating: true,
                    borderWidth: 1,
                    backgroundColor:
                            Highcharts.defaultOptions.legend.backgroundColor || '#FFFFFF',
                    shadow: true
                },
                credits: {
                    enabled: false
                },
                series: [{
                        name: 'Streams',
                        data: [1000, 900, 800, 700, 600, 500, 400, 300, 200, 100]
                    }]
            });


        });
    </script>    
</body>
</html>