<?php
include('server.php') ?>
<?php
if (!isset($_SESSION['username'])) {
    $_SESSION['msg'] = "You must log in first";
    header('location: login.php');
}
if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['username']);
    header("location: login.php");
}
?>
<html>
<head>
    <title>Oingo - User Search</title>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
    <script>
      var x = document.getElementById("demo");
      function getLocation()
      {
        if (navigator.geolocation)
        {
          navigator.geolocation.getCurrentPosition(bindPosition);
        }
        else {
          x.innerHTML = "Geolocation is not supported by this browser.";
        }
      }
      function bindPosition(position) {
        $("input[name='latitude']").val(position.coords.latitude);
        $("input[name='longitude']").val(position.coords.longitude);
      }
    </script>
</head>
<body onload="getLocation()">
<div class="header">
    <h2>Search for user</h2>
</div>
<form method="post" action="usersearch.php">
    <?php include('errors.php'); ?>
    <div class="input-group">
        <label>Username</label>
        <input type="text" name="username">
    </div>
    <p>
        <input type='hidden' value='' name='latitude'/>
        <input type='hidden' value='' name='longitude'/>
      </p>
    <div class="input-group">
        <button type="submit" class="btn" name="usersearch">Search</button>
    </div>
    <p><a href="index.php">Back to Home</a></p>
</form>
</body>
</html>
