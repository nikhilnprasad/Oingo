<?php
include('server.php');
//session_start();
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
    <!DOCTYPE html>
    <html>
    <head>
        <title>Oingo - Home</title>
        <link rel="stylesheet" type="text/css" href="css/style.css">

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

    </head>
    <body>
    <div class="header">
        <h2>Home</h2>
    </div>
    <div class="content">
        <!-- notification message -->
        <?php if (isset($_SESSION['success'])) : ?>
            <div class="error success">
                <h3>
                    <?php
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </h3>
            </div>
        <?php endif ?>

        <!-- logged in user information -->
        <?php if (isset($_SESSION['username'])) : ?>
            <p>Welcome <strong><?php echo $_SESSION['username']; ?></strong></p>
            <?php
            //include('errors.php');
            $username = $_SESSION['username'];
            $_SESSION["hiddenlocation"] = 0;
            mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
            $userresult = mysqli_query($db, "SELECT uemail from user where uname='" . $username . "'");
            if (mysqli_num_rows($userresult) < 1) {
                mysqli_free_result($userresult);
                array_push($errors, "Email could not be retrieved");
            }
            $userrow = mysqli_fetch_array($userresult);
            $uID = $_SESSION['uid'];
            $useremail = $userrow["uemail"];
            $currstateresult = mysqli_query($db, "SELECT statename from state natural join userstate where isCurrent=1 and uid='" . $uID . "'");
            if (mysqli_num_rows($currstateresult) < 1) {
                mysqli_free_result($currstateresult);
                array_push($errors, "Statename could not be retrieved");
            }
            $currstaterow = mysqli_fetch_row($currstateresult);
            $currentState = $currstaterow[0];
            if ($userresult and $currstateresult) {
                mysqli_commit($db);
            } else {
                mysqli_rollback($db);
            }
            ?>
            <p>Email ID: <strong><?php echo $useremail; ?></strong></p>
            <p>Current state: <strong><?php echo $currentState; ?></strong>&nbsp<a href="selectstate.php">Update
                    State</a></p>
            <p><a href="createnote.php">Create note</a></p>
            <p><a href="notes.php">View filtered notes</a></p>
            <p><a href="filters.php">Create filters</a></p>
            <p><a href="usersearch.php">Search for users</a></p>
            <p><a href="index.php?logout='1'" style="color: red;">Logout</a></p>
        <?php endif ?>
    </div class="content">
    <div class="content">
        <p><a href="viewfriends.php">View friends</a></p>
        <?php if (isset($_SESSION['username'])) :
        //TODO: Allow users to see pending friend requests
        mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
        if ($friendresult = mysqli_query($db, "select uid1, uname from friendship, user where uid1=uid and isAccepted=0 and uid2='" . $uID . "'")) {
        mysqli_commit($db);
        if (mysqli_num_rows($friendresult) < 1) {
        ?>
        <p><strong>No pending friend requests for you.</strong></p></div>

    <?php
    } else {
        ?>
        <p>Pending friend requests</p>
        <?php
        while ($friendrow = $friendresult->fetch_assoc()) {
            $friendid = $friendrow["uid1"];
            $friendname = $friendrow["uname"];
            ?>
            <form class="friendaccept" action="index.php" method="post">
                <label><a href="userprofile.php?userprofileid=<?php echo $friendid; ?>"><?php echo $friendname ?></a></label>
                <input type="hidden" name="friendid" value="<?php echo $friendid; ?>">
                <input type='hidden' value='' name='latitude'/>
                <input type='hidden' value='' name='longitude'/>
                <button type="submit" class="btn" name="friendaccept">Accept</button>
            </form>

            <?php
        }
    }
    } else {
        mysqli_rollback($db);
    }
    ?>
    <?php
    mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
    //$notesresult = mysqli_query($db, "call GETNOTES('" . $uID . "')");
    $notesresult = mysqli_query($db, "select nid, note from note where uid='" . $uID . "'");
    if ($notesresult) {
        mysqli_commit($db);
        ?>
    <div class="header">
        <h4>Notes by you</h4>
    </div>
    <?php
    if (mysqli_num_rows($notesresult) < 1) {
    ?>
    <div class="content">
        <p><strong>You have created no notes.</strong></p>
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
        <a href="viewyournote.php?noteid=<?php echo $noteid ?>"><p>NoteID: <?php echo $noteid; ?></p>
            <p>Note: <?php echo $notetext; ?></p></a>
    </div>
    <?php
    }
    }

    } else {
        mysqli_rollback($db);
    }
    ?>
    <div class="header">
        <h4>User's current location</h4>
    </div>
    </div>
    <div class="content">
        <?php
        $uID = $_SESSION['uid'];
        $curloc1 = mysqli_query($db, "SELECT ulatitude, ulongitude FROM userlocation WHERE utimestamp IN (SELECT max(utimestamp) FROM userlocation where uid = '" . $uID . "')");
        $curloc = mysqli_fetch_row($curloc1);
        $curlat = $curloc[0];
        $curlong = $curloc[1];
        ?>
        <!-- <p>url: <?php echo $url; ?></p> -->

        <div id="map" style="height: 300px; width: 430px;"></div>

        <script>
            var customLabel = {
                note: {
                    label: 'N'
                }
            };

            function initMap() {
                var map = new google.maps.Map(document.getElementById('map'), {
                    center: new google.maps.LatLng(<?php echo $curlat; ?>, <?php echo $curlong;?>),
                    zoom: 11
                });
                var infoWindow = new google.maps.InfoWindow;

                // Change this depending on the name of your PHP or XML file
                downloadUrl("allnoteloc.php", function (data) {
                    var xml = data.responseXML;
                    var markers = xml.documentElement.getElementsByTagName('marker');
                    Array.prototype.forEach.call(markers, function (markerElem) {
                        var nid = markerElem.getAttribute('nID');
                        var uid = markerElem.getAttribute('uid');
                        var note1 = markerElem.getAttribute('note1');
                        var type = markerElem.getAttribute('type');
                        var point = new google.maps.LatLng(
                            parseFloat(markerElem.getAttribute('lat')),
                            parseFloat(markerElem.getAttribute('lng')));

                        var infowincontent = document.createElement('div');
                        var strong = document.createElement('strong');
                        strong.textContent = uid;
                        infowincontent.appendChild(strong);
                        infowincontent.appendChild(document.createElement('br'));

                        var text = document.createElement('text');
                        text.textContent = note1;
                        infowincontent.appendChild(text);
                        var icon = customLabel[type] || {};
                        var marker = new google.maps.Marker({
                            map: map,
                            position: point,
                            label: icon.label
                        });
                        marker.addListener('click', function () {
                            infoWindow.setContent(infowincontent);
                            infoWindow.open(map, marker);
                        });
                    });
                });

                function userlocation() {
                    var alat = <?php echo $curlat ?>;
                    var along = <?php echo $curlong ?>;
                    var myLatlng = new google.maps.LatLng(alat, along);
                    var marker = new google.maps.Marker({
                        position: myLatlng,
                        map: map,
                        title: 'location marker',
                        draggable: true
                    });
                    document.getElementById('newlat').value = alat
                    document.getElementById('newlong').value = along
                    // marker drag event
                    google.maps.event.addListener(marker, 'drag', function (event) {
                        document.getElementById('newlat').value = event.latLng.lat();
                        document.getElementById('newlong').value = event.latLng.lng();
                    });

                    //marker drag event end
                    google.maps.event.addListener(marker, 'dragend', function (event) {
                        document.getElementById('newlat').value = event.latLng.lat();
                        document.getElementById('newlong').value = event.latLng.lng();
                        // alert("lat=>"+event.latLng.lat());
                        // alert("long=>"+event.latLng.lng());
                    });
                }

                google.maps.event.addDomListener(window, 'load', userlocation);

            }

            function downloadUrl(url, callback) {
                var request = window.ActiveXObject ?
                    new ActiveXObject('Microsoft.XMLHTTP') :
                    new XMLHttpRequest;

                request.onreadystatechange = function () {
                    if (request.readyState == 4) {
                        request.onreadystatechange = doNothing;
                        callback(request, request.status);
                    }
                };

                request.open('GET', url, true);
                request.send(null);
            }

            function doNothing() {
            }

        </script>

        <script async defer
                src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBPBnziBZuUaydHaUZKNgGuk38heaaJCfs&callback=initMap">
        </script>

    </div>
    <div class="content">
        <form action="index.php" method="post">
            <?php include('errors.php') ?>
            <div class="input-group">
                <?php
                if ($_SESSION['autolocation'] == 1) {
                    ?>
                    <input type="hidden" name="locationvalue" value="0">
                    <button type="submit" name="getlocationupdate" class="btn">Manually update location</button>
                    <?php
                } else {
                    ?>
                    <input type="hidden" name="locationvalue" value="1">
                    <button type="submit" name="getlocationupdate" class="btn">Auto update location</button>
                    <?php
                }
                ?>
            </div>
        </form>
    </div>
    <?php
    if ($_SESSION['autolocation'] == 0) {
        ?>
        <div class="content">
            <p>Set User's Current Location:</p>
            <form method="post" action="index.php">
                <?php include('errors.php'); ?>
                <div class="input-group">
                    <p>Move the current user location marker to the desired location and click the button below to
                        update location</p>
                    <input type='hidden' name='newlat' id='newlat'>
                    <input type='hidden' name='newlong' id='newlong'>
                    <button type="submit" class="btn" name="updatelocation">Update Location</button>
                </div>
            </form>
        </div>
        <?php
    }
    endif;
    mysqli_close($db);
    ?>

    </body>
    </html>