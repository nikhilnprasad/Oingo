<?php include('server.php') ?>
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

//Connect to db
$db = mysqli_connect("localhost", "root", "password");
if (!$db) {
    exit('Connect error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
}
mysqli_set_charset($db, 'utf-8');
mysqli_select_db($db, "oingo");
?>
<html>
<head>
    <title>Oingo - Notes</title>
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
          <?php
          if ($_SESSION["hiddenlocation"] == 0) {
            $_SESSION["hiddenlocation"] = 1;
          ?>
              document.autolatlong.autolat.value = position.coords.latitude;
              document.autolatlong.autolong.value = position.coords.longitude;  
              document.forms['autolatlong'].submit();
          <?php
            }
          ?>
      }
    </script>
</head>
<body onload="getLocation()">

<form method="post" action="notes.php" id="autolatlong" name='autolatlong'>
<?php include('errors.php') ?>
<input type="hidden" name='autolat' id='autolat' value=''>
<input type="hidden" name='autolong' id='autolong' value=''>
</form>
<div class="header">
    <h2>Filtered notes</h2>
</div>
<?php  if (isset($_SESSION['username'])) : ?>
<?php
$username = $_SESSION['username'];
$uID = $_SESSION['uid'];
mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
$notesresult = mysqli_query($db, "call GETNOTES('" . $uID . "')");
//$notesresult = mysqli_query($db, "call GETNOTES(1)");
if ($notesresult) {
    mysqli_commit($db);
} else {
    mysqli_rollback($db);
}
?>
<?php
//If not notes found
if (mysqli_num_rows($notesresult) < 1) {
?>
<div class="content">
    <p><strong>There are currently no notes for filters set by the user. Please consider updating the filters or changing your current location.</strong></p>
</div>
<?php
} else {
    while ($noterow = $notesresult->fetch_assoc()) {
        $noteid = $noterow["nid"];
        $notetext = $noterow["note"];
        //Consider only first 50 characters of the note for title
        $notetext = substr($notetext, 0, 50);
        $notetext = $notetext . "...";
        //Show the notes that can be viewed by the user depending on filters and current location.
?>
<div class="content">
    <a href="viewnote.php?noteid=<?php echo $noteid ?>"><p>NoteID: <?php echo $noteid; ?></p>
        <p>Note: <?php echo $notetext; ?></p></a>
</div>
<?php
    }
}
?>
<?php endif;
mysqli_close($db);?>
<div class="content">
    <p><a href="index.php">Back to home</a></p>
</div>
</body>
</html>
