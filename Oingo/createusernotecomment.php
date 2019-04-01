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
    <title>Oingo - Create Comment</title>
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
    <h2>Create Comment</h2>
</div>
<?php  if (isset($_SESSION['username'])) : ?>
    <?php
    $username = $_SESSION['username'];
    $uID = $_SESSION['uid'];
    $noteid = $_GET['nid'];
    $cid = -1;
    if (isset($_GET['cid'])) {
        $cid = $_GET['cid'];
    }
    ?>
    <form method="post" action="createusernotecomment.php?nid=<?php echo $noteid; ?>&cid=<?php echo $cid; ?>" id="usercommentform">
        <div class="input-group">
            <input name="noteid" type="hidden" value="<?php echo $noteid; ?>">
            <input name="cid" type="hidden" value="<?php echo $cid; ?>">
            <textarea rows="4" cols="50" name="usercomment" form="usercommentform">Enter comment here...</textarea>
        </div>
        <?php include('errors.php'); ?>
        <p>
            <input type='hidden' value='' name='latitude'/>
            <input type='hidden' value='' name='longitude'/>
        </p>
        <div class="input-group">
            <button class="btn" type="submit" name="usercommentsubmit">Create Comment</button>
        </div>
    </form>



<?php endif;
mysqli_close($db);?>
</body>
</html>