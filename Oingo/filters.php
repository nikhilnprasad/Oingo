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
    <title>Oingo - Filters</title>
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

    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
      }
      /* Optional: Makes the sample page fill the window. */
      html, body {
        height: 100%;
        margin: 0;
        padding: 0;
      }
    </style>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="/resources/demos/style.css">
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.11/jquery-ui.min.js"></script>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script>
        $( function() {
            $( "#datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });
        } );

    </script>

</head>
<body onload="getLocation()">
<div class="filterheader">
    <h2>Filters</h2>
</div>
<?php  if (isset($_SESSION['username'])) : ?>
    <?php
    $username = $_SESSION['username'];
    $uID = $_SESSION['uid'];

    mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
    $fil = mysqli_query($db, "SELECT * from filter natural join state natural join repeatnote natural join visibility natural join tag where uid='" . $uID . "'");

    if(mysqli_num_rows($fil) < 1){
        mysqli_free_result($fil);
        ?>
        <div class="content">
            <?php
        echo "Current user has no set filters";
        ?>
        </div>
        <?php
    }
    else{
        ?>
        <div class="filtercontent">
        <table border="black" class="center">
        <tr>
            <th>Tag</th>
            <th>State</th>
            <th>Start Date</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Latitude</th>
            <th>Longitude</th>
            <th>Radius (in KM)</th>
            <th>Repeat</th>
            <th>Visibility</th>
        </tr>

        <?php
        //echo "Current filters<br/><br/>";
        while($filrow = mysqli_fetch_array($fil)){

            $filtagname = $filrow[13];
            $filstate = $filrow[10];
            $filstarttimestamp = $filrow[5];
            $filendtimestamp = $filrow[6];
            $fillatitude = $filrow[7];
            $fillongitude = $filrow[8];
            $filradius = $filrow[9];
            $filrepeat = $filrow[11];
            $filvisibility = $filrow[12];

            list($filstartdate, $filstarttime) = explode(" ", $filrow["fstarttimestamp"]);
            list($filenddate, $filendtime) = explode(" ", $filrow["fendtimestamp"]);

// $splitStartTimeStamp = explode(" ",$filstarttimestamp);
// $splitEndTimeStamp = explode(" ",$filendtimestamp);

// $filstartdate = $splitStartTimeStamp[0];
// $filstarttime = $splitStartTimeStamp[1];
// $filenddate = $splitEndTimeStamp[0];
// $filendtime = $splitEndTimeStamp[1];

            echo "<tr><td>" . $filtagname . "</td>";
            echo "<td>" . $filstate . "</td>";
            echo "<td>" . $filstartdate . "</td>";
            echo "<td>" . $filstarttime . "</td>";
            echo "<td>" . $filendtime . "</td>";
            echo "<td>" . $fillatitude . "</td>";
            echo "<td>" . $fillongitude . "</td>";
            echo "<td>" . $filradius . "</td>";
            echo "<td>" . $filrepeat . "</td>";
            echo "<td>" . $filvisibility . "</td></tr>";
        }
    }
    $allRepeatResult = mysqli_query($db, "SELECT rdesc from repeatnote");
    $allvisibilityResult = mysqli_query($db, "SELECT visibleRelation from visibility");
    $allTagResult = mysqli_query($db, "SELECT tagname from tag");
    ?>
    </table>
    </div>
    <div class="header">
        <h4>Create new filter</h4>
    </div>
    <form method="post" action="filters.php">
        <?php include('errors.php'); ?>
        <div class="input-group">
            <label>Select a Tag</label>
            <select name="tagDrop">
                <?php
                while ($rows3 = $allTagResult->fetch_assoc()) {
                    $tagval = $rows3['tagname'];
                    echo "<option value='$tagval'>$tagval</option>";
                }
                ?>
            </select>
        </div>
        <div class="input-group">
            <label>Select a Repeat</label>
            <select name="repeatDrop">
                <?php
                while ($rows1 = $allRepeatResult->fetch_assoc()) {
                    $repeatval = $rows1['rdesc'];
                    echo "<option value='$repeatval'>$repeatval</option>";
                }
                ?>
            </select>
        </div>
        <div class="input-group">
            <label>Select a Visibility</label>
            <select name="visibilityDrop">
                <?php
                while ($rows2 = $allvisibilityResult->fetch_assoc()) {
                    $visibilityval = $rows2['visibleRelation'];
                    echo "<option value='$visibilityval'>$visibilityval</option>";
                }
                ?>
            </select>
        </div>
        <div class="input-group">

            <!-- <p>Select a Start Date <input type="text" id="startdatepicker" name="selectstartdate"></p> -->
            <p>Select a Start Date <input type="text" name="selectstartdate" id="datepicker"></p>

        </div>
        <div class="input-group">
            <p>Select a Start Time: <input type="time" name="selectstarttime"></p>
        </div>
        <div class="input-group">
            <p>Select an End Time: <input type="time" name="selectendtime"></p>
        </div>

        <div class="input-group">
        <div id="map" style="height: 300px; width: 430px;"></div>

        <script type="text/javascript">
        function initMap() {
        var map = new google.maps.Map(document.getElementById('map'), {
          center: new google.maps.LatLng(40.7128, -74.0060),
          zoom: 12
        });
        var myLatlng = new google.maps.LatLng(40.7128,-74.0060);
        var marker = new google.maps.Marker({
        position: myLatlng,
        map: map,
        title: 'location marker',
        draggable:true  
        });

        document.getElementById('latitudeName').value= 40.7128
        document.getElementById('longitudeName').value= -74.0060  
        // marker drag event
        google.maps.event.addListener(marker,'drag',function(event) {
            document.getElementById('latitudeName').value = event.latLng.lat();
            document.getElementById('longitudeName').value = event.latLng.lng();
        });

        //marker drag event end
        google.maps.event.addListener(marker,'dragend',function(event) {
            document.getElementById('latitudeName').value = event.latLng.lat();
            document.getElementById('longitudeName').value = event.latLng.lng();
            // alert("lat=>"+event.latLng.lat());
            // alert("long=>"+event.latLng.lng());
        });
        }

        google.maps.event.addDomListener(window, 'load', initialize);

        </script>

        <script async defer
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBPBnziBZuUaydHaUZKNgGuk38heaaJCfs&callback=initMap">
        </script>

        <p>Select the filter location by dragging the marker on the map to the desired location</p>
        <input type='hidden' name='latitudeName' id='latitudeName'>  
        <input type='hidden' name='longitudeName' id='longitudeName'> 
        </div>

        <!-- <div class="input-group">

            <p>Latitude: <input type="text" name="latitudeName"></p>
        </div>
        <div class="input-group">

            <p>Longitude: <input type="text" name="longitudeName"></p>
        </div> -->

        <div class="input-group">

            <p>Enter Radius (in km): <input type="text" name="radiusName"></p>
        </div>
        <p>
        <input type='hidden' value='' name='latitude'/>
        <input type='hidden' value='' name='longitude'/>
      </p>
        <div class="input-group">
            <button type="submit" class="btn" name="createFilter">Create Filter</button>
        </div>
    </form>
    <div class="content">
    <p><a href="index.php">Back to Home</a></p>
    </div>
<?php endif;
mysqli_close($db);?>
</body>
</html>
