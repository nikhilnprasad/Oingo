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
    <title>Oingo - Update State</title>
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
    <h2>Update State</h2>
</div>
<div class="content">
    <?php  if (isset($_SESSION['username'])) : ?>
    <?php
    $username = $_SESSION['username'];
    $uID = $_SESSION['uid'];
    mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
    $stateResult = mysqli_query($db, "SELECT statename from state natural join userstate where uid='" . $uID . "' and isCurrent = 1");
    if (mysqli_num_rows($stateResult) < 1) {
        mysqli_free_result($stateResult);
        array_push($errors, "State could not be retrieved");
    }
    $stateArray = mysqli_fetch_array($stateResult);
    $currentState = $stateArray["statename"];
    //$allStatesResult = mysqli_query($db, "SELECT statename from state natural join userstate where uid='" . $uID . "'");
    $allStatesResult = mysqli_query($db, "SELECT statename from state");
    if ($stateResult and $allStatesResult) {
        mysqli_commit($db);
    } else {
        mysqli_rollback($db);
    }
    ?>
    <p>Current State: <strong><?php echo $currentState; ?></strong></p>
    <p>Select a state or create a new state: </p>
    <form method="post" action="selectstate.php">
        <?php include('errors.php'); ?>
        <div class="input-group">
            <label>Select a state</label>
            <select name="stateNameDrop">
                <?php
                while ($rows = $allStatesResult->fetch_assoc()) {
                    $state = $rows['statename'];
                    echo "<option value='$state'>$state</option>";
                }
                ?>
            </select>
        </div>
        <p>
        <input type='hidden' value='' name='latitude'/>
        <input type='hidden' value='' name='longitude'/>
      </p>
        <div>
            <input type="submit" class="btn" name="selectPreviousState" value="Update"/>
        </div>
    </form>
    <form method="post" action="selectstate.php">
        <?php include('errors.php'); ?>
        <div class="input-group">
            <label>Create a new state</label>
            <input type="text" name="stateName">
        </div>
        <p>
        <input type='hidden' value='' name='latitude'/>
        <input type='hidden' value='' name='longitude'/>
      </p>
        <div class="input-group">
            <button type="submit" class="btn" name="createState">Create</button>
        </div>
    </form>
    <?php endif ?>
    <p><a href="index.php">Back to home</a></p>
</div>
</body>
</html>
<?php mysqli_close($db); ?>

