<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="../../docs-assets/ico/favicon.png">

    <title>The Racers Media - News</title>

    <!-- Bootstrap core CSS -->
    <link href="/static/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="/static/css/offcanvas.css" rel="stylesheet">

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../docs-assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
@include ('master/navbar')

    <div class="container">

      <div class="row row-offcanvas row-offcanvas-right">

        <div class="col-xs-12 col-sm-9">
          <div class="jumbotron">
            <h1>TRM - News!</h1>
            <p>This is our news section, where you can stay up to date on all things at The Racers Media!</p>
          </div>
          <div class="row">
            <div class="col-6 col-sm-6 col-lg-8">
              <h2>New Features + Changes</h2>
              <p>The Developers at The Racers Media have been busy over the last few days, as you can see by the new features. Check out the list of new features by clicking "View More"</p>
              <p><a class="btn btn-default" href="/news/1.blade.php" role="button">View More &raquo;</a></p>
            </div><!--/span-->
            <div class="col-md-4 hidden-xs">
				<div class="panel panel-default panel-body">
              <h2>Note</h2>
              <p>The News section is updated at least once a week, depending on what is happening with Site Updates and what not.  </p>
				</div>
			</div><!--/span-->
          </div><!--/row-->
        </div><!--/span-->

        <div class="col-xs-6 col-sm-3 sidebar-offcanvas" id="sidebar" role="navigation">
          <div class="list-group">
            <a href="http://facebook.com/theracersmedia" class="list-group-item active">Live Updates via Facebook</a>
            <a href="http://twitter.com/theracersmedia" class="list-group-item">Live Updates via Twitter</a>

          </div>
        </div><!--/span-->
      </div><!--/row-->

      <hr>

      <footer>
@include ('master/footer')
      </footer>

    </div><!--/.container-->



    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="/staic/css/bootstrap.min.js"></script>
    <script src="/static/css/offcanvas.js"></script>
  </body>
</html>
