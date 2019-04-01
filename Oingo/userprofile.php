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
    <title>Oingo - User Profile</title>
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
    <h2>User Profile</h2>
</div>
<?php  if (isset($_SESSION['username'])) : ?>
<?php
$username = $_SESSION['username'];
$uID = $_SESSION['uid'];
$userProfileID = $_GET["userprofileid"];

mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
$userprofilequery = "select uname, uemail, statename from user natural join userstate natural join state where uid=? and isCurrent=1";
if ($userprofilestmt = mysqli_prepare($db, $userprofilequery)) {
    $userprofilestmt->bind_param("i", $userProfileID);
    if ($userprofilestmt->execute()) {
        mysqli_commit($db);
        $userprofileresult = $userprofilestmt->get_result();
        if (mysqli_num_rows($userprofileresult) < 1) {
            ?>
<div class="content">
    <p><strong>This user does not exist</strong></p>
</div>
            <?php
        } else {
            $userprofilearray = mysqli_fetch_array($userprofileresult);
            $userprofilename = $userprofilearray["uname"];
            $userprofileemail = $userprofilearray["uemail"];
            $userprofilestate = $userprofilearray["statename"];
            ?>

<div class="content">
    <p>Welcome to <strong><?php echo $userprofilename; ?>'s profile</strong></p>
    <p>Email: <strong><?php echo $userprofileemail; ?></strong></p>
    <p>Current state: <strong><?php echo $userprofilestate; ?></strong></p>
</div>

<?php
            mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
            if ($userfriendsresult = mysqli_query($db, "select uid2 as Friends, isAccepted from friendship where uid1='" . $uID . "' union select uid1 as Friends, isAccepted from friendship where uid2='" . $uID . "'")) {
                mysqli_commit($db);
                $verifyinviteflag = 0;
                $verifyfriendflag = 0;
                while ($userfriendrow = $userfriendsresult->fetch_assoc()) {
                    if ($userfriendrow["Friends"] == $userProfileID) {
                        $verifyinviteflag = 1;
                        if ($userfriendrow["isAccepted"] == 1) {
                            $verifyfriendflag = 1;
                        }
                        break;
                    }
                }
                if ($verifyinviteflag == 1 and $verifyfriendflag == 1) {
                    ?>
<div class="content">
    <p>You are friends with <strong><?php echo $userprofilename; ?></strong></p>
</div>
<?php
                } else if ($verifyinviteflag == 1 and $verifyfriendflag == 0) {
                    ?>
<div class="content">
    <p>Friend request is pending.</p>
</div>
<?php
                } else {
                    ?>
<form action="userprofile.php?userprofileid=<?php echo $userProfileID ?>" method="post">
    <?php include('errors.php'); ?>
    <p>
        <input type='hidden' value='' name='latitude'/>
        <input type='hidden' value='' name='longitude'/>
    </p>
    <div class="input-group">
        <input type="hidden" name="friend" value="<?php echo $userProfileID; ?>">
        <button type="submit" class="btn" name="friendrequest">Send Friend Request</button>
    </div>
</form>
<?php
                }
            } else {
                mysqli_rollback($db);
            }
        }

    } else {
        mysqli_rollback($db);
    }
} else {
    mysqli_rollback($db);
}
?>
<?php endif;
mysqli_close($db);?>
<div class="content">
    <p><a href="index.php">Back to home</a></p>
</div>
</body>
</html>