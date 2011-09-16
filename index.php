<?php

require_once './vendor/php-github-api/lib/Github/Autoloader.php';
$config = include './config.php';

Github_Autoloader::register();

$github = new Github_Client();

$message = FALSE;
if (isset($_POST['github_user'])) {
  $github->authenticate('githugs', $config['github']['secret']);
  $github->getUserApi()->follow($_POST['github_user']);
  $message = "You've been added!  Prepare yourself for hugs.";
}

try {
  $m = new Mongo($config['db']['connection_string']);
  $db = $m->selectDB($config['db']['name']);
}
catch (MongoConnectionException $e) {
  echo '<h1>Database connection failure</h1><hr><p>I blame myself :(';
  exit();
}

$hugs = $db->hugs->find();

?>
<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title></title>
  <meta name="description" content="">
  <meta name="author" content="">

  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- CSS concatenated and minified via ant build script-->
  <link rel="stylesheet" href="css/style.css?heyy">
  <!-- end CSS-->

  <script src="js/libs/modernizr-2.0.6.min.js"></script>
</head>

<body>

  <div id="container">
    <?php if ($message): ?>
      <div class="message"><?php echo $message ?></div>
    <?php endif ?>
    <header>
      <h1>githu.gs</h1>
    </header>
    <div id="main" role="main">
      <div id="banner">free hugs for hardworking opensource devs</div>
      <form action="index.php" method="post">
        <input id="github_user" type="text" name="github_user" placeholder="Github Name" />
      </form>
      <div id="recent_container">
        <h2>Recent Hugs</h2>
        <ul id="recent_hugs">
          <?php foreach ($hugs as $hug): ?>
            <li>
              <a href="<?php echo $hug['url'] ?>"><?php echo $hug['project'] ?></a> was just hugged.
            </li>
          <?php endforeach ?>
        </ul>
      </div>
    </div>
    <footer>

    </footer>
  </div> <!--! end of #container -->


  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>


  <!-- scripts concatenated and minified via ant build script-->
  <script>
    $(function(){
      var $recent = $('#recent_container').hide();
      $('#main').css('margin-top', '-340px').animate({'margin-top':'0'}, 700, function(){
        $recent.fadeIn();
      });
      var $banner = $('#banner');
      $('#github_user').focus(function(){
        $banner.fadeOut();
      }).blur(function(){
        $banner.fadeIn();
      });
    });
  </script>
  <!-- end scripts-->


  <script>
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'XXXXXXX']);
    _gaq.push(['_setDomainName', 'none']);
    _gaq.push(['_setAllowLinker', true]);
    _gaq.push(['_trackPageview']);
    _gaq.push(['_trackPageLoadTime']);
    Modernizr.load({
      load: ('https:' == location.protocol ? '//ssl' : '//www') + '.google-analytics.com/ga.js'
    });
  </script>


  <!--[if lt IE 9 ]>
    <script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
    <script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
  <![endif]-->

</body>
</html>
