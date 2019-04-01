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
        <title>Oingo - Friends</title>
        <link rel="stylesheet" type="text/css" href="css/style.css">
    </head>
<body>
    <div class="header">
        <h2>View Friends</h2>
    </div>
<div class="content">
    <?php  if (isset($_SESSION['username'])) : ?>
    <?php
    $username = $_SESSION['username'];
    $uID = $_SESSION['uid'];
    //TODO: Show friends of users as hyperlinks so that user can view their profile.
    mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);

    if ($friendresult = mysqli_query($db, "select uname, uid from user, (select uid2 as Friends from friendship where uid1='" . $uID . "' and isAccepted=1 union select uid1 as Friends from friendship where uid2='" . $uID . "' and isAccepted=1) as f where uid = Friends ;")) {
        mysqli_commit($db);
        while ($friendrow = $friendresult->fetch_assoc()) {
            $friendid = $friendrow["uid"];
            $friendname = $friendrow["uname"];
            ?>

<div>
    <p><strong><a href="userprofile.php?userprofileid=<?php echo $friendid ?>"><?php echo $friendname ?></a></strong></p>
</div>

            <?php
        }
    } else {
        mysqli_rollback($db);
    }
    ?>
    <?php endif ?>
</div>
<div class="content">
    <p><a href="index.php">Back to home</a></p>
</div>
</body>
    </html>
<?php mysqli_close($db); ?>
