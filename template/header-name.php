<?php
$page= '../index.php';
if($_SESSION['user_role'] === 1){
    $page= '../admin/';
} else if($_SESSION['user_role'] == 2){
    $page= '../user/';
} else if($_SESSION['user_role'] == 3){
    $page= '../index.php';
} else if($_SESSION['user_role'] === 4){
    $page= '../index.php';
}
    $welcome_name = $_SESSION['username'] ?? 'Guest';
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eRail</title>
    <link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <link rel="stylesheet" href="<?php echo WEB_PAGE_TO_ROOT; ?>css/style.css" type="text/css" />
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top">
  <a class="navbar-brand brand" href="<?php echo WEB_PAGE_TO_ROOT; ?>index">eRail</a>
 
  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="<?php echo WEB_PAGE_TO_ROOT; ?>admin/">Home</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?php echo WEB_PAGE_TO_ROOT; ?>about">About Us</a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="<?php echo WEB_PAGE_TO_ROOT; ?>contact">Contact Us</a>
      </li>
    </ul>
    <ul class="pull-right nav navbar-nav">
      <li class="nav-item">
        <a class="nav-link" href="<?php echo WEB_PAGE_TO_ROOT; ?>logout">Logout</a>
      </li>
    </ul>
  </div>
</nav>
