<?php
  require_once('delay_func.php');
  include_once('v_info.html');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>FTTH - Daily Partner SLA Report Converter</title>

    <link rel="apple-touch-icon" sizes="57x57" href="../../ref/images/favicons/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="../../ref/images/favicons/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="../../ref/images/favicons/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="../../ref/images/favicons/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="../../ref/images/favicons/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="../../ref/images/favicons/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="../../ref/images/favicons/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="../../ref/images/favicons/apple-touch-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../../ref/images/favicons/apple-touch-icon-180x180.png">
    <link rel="icon" type="image/png" href="../../ref/images/favicons/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="../../ref/images/favicons/android-chrome-192x192.png" sizes="192x192">
    <link rel="icon" type="image/png" href="../../ref/images/favicons/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="../../ref/images/favicons/favicon-16x16.png" sizes="16x16">
    <link rel="manifest" href="../../ref/images/favicons/manifest.json">
    <link rel="shortcut icon" href="../../ref/images/favicons/favicon.ico">
    <meta name="msapplication-TileColor" content="#2d89ef">
    <meta name="msapplication-TileImage" content="../../ref/images/favicons/mstile-144x144.png">
    <meta name="msapplication-config" content="../../ref/images/favicons/browserconfig.xml">
    <meta name="theme-color" content="#ffffff">

    <!-- Alertify -->
    <link rel="stylesheet" href="libs/alertifyjs/css/alertify.min.css"/>
    <link rel="stylesheet" href="libs/alertifyjs/css/themes/default.min.css"/>

    <link href="css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/3db7fc1628.js"></script>
    <link href='https://fonts.googleapis.com/css?family=Cabin:400,700' rel='stylesheet' type='text/css'>
    <link href="css/custom.css" rel="stylesheet"/>

  </head>
  <body>
    <div class="se-pre-con"></div>
    <div class="container">
      <br/><br/><br/><br/>
      <div class="row">
        <div class="col-md-12">
          <h1 class="text-center">Daily Partner SLA Report Converter</h1>
        </div>
      </div>

      <br/>

      <div class="text-center subscribe-form-wrapper">
        <form action="" method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <input type="file" name="ofile" id="ofile" accept=".xlsx, .xls, .csv" class="center-block form-control" />
          </div>

          <label class="switch form-group">
            <h4 class="label-text">Don't count Today (<?php echo $today; ?>)</h4>
            <input type="checkbox" name="notToday" id="notToday" checked="">
            <span class="slider round"></span>
          </label>

          <div>
            <button type="submit" name="delaySLA" id="delaySLA" class="btn btn-default" disabled="">Calculate Delay SLA</button>
          </div>

          <br />
          <br />

          <div class="form-group">
            <input type="date" name="pdDate" id="pdDate" class="center-block form-control"  max="<?php echo date('Y-m-d'); ?>" style="width: 180px !important;" disabled="" />
          </div>

          <div>
            <button type="submit" name="partnerDelay" id="partnerDelay" class="btn btn-default" disabled="">Calculate Partner Delay</button>
          </div>

        </form>
      </div>

      <br />

    <br />
    <br />
    <br />

    <div class="row">
      <div class="col-md-12">
        <div class="text-center" style="width: 100%; bottom: 0%; margin: 5% auto; left: 0; right: 0;">
          <div>Daily Partner SLA Report Converter - Web App: <?php echo $web_app_version; ?> - <a style="cursor: pointer;" onclick="v_info()">Version info</a></div>
        </div>

    </div>

      </div>
    </div>

    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/scripts.js"></script>
    <script type="text/javascript" src="libs/alertifyjs/alertify.min.js"></script>

  </body>
</html>
