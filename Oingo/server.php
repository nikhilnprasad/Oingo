<?php
session_start();

//initialize variables
$username = "";
$email = "";
$errors = array();

//Connect to db
$db = mysqli_connect("localhost", "root", "password");
if (!$db) {
    exit('Connect error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
}
mysqli_set_charset($db, 'utf-8');
mysqli_select_db($db, "oingo");

//Register User
if (isset($_POST['reg_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password_1 = $_POST['password_1'];
    $password_2 = $_POST['password_2'];
    $lat = $_POST['latitude'];
    $lon = $_POST['longitude'];

    //registration form validation
    if (empty($username)) {array_push($errors, "Username is required");}
    if (empty($email)) {array_push($errors, "Email is required");}
    if (empty($password_1)) {array_push($errors, "Password is required");}
    if ($password_1 != $password_2) {
        array_push($errors, "The two passwords do not match");
    }


    //Verify if user exists
    if (count($errors) == 0) {
        mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
        $user_exist_query = "Select * from user where uname='" . $username . "' or uemail='" . $email . "' LIMIT 1";
        mysqli_commit($db);
        $result = mysqli_query($db, $user_exist_query);
        $user = mysqli_fetch_assoc($result);

        if ($user) { //if user already exists
            if ($user['uname'] === $username) {
                array_push($errors, "Username already exists");
            }

            if ($user['uemail'] === $email) {
                array_push($errors, "Email already exists");
            }
        }
    }

    //Register if there are no errors
    if (count($errors) == 0) {
        $password = password_hash($password_1, PASSWORD_DEFAULT);
        mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
        $query = "Insert into user (uname, uemail, utimestamp, upassword)
                  VALUES(?, ?, CURRENT_TIMESTAMP(3), ?)";
        if ($stmt = mysqli_prepare($db, $query)) {
            $stmt->bind_param("sss", $username_insert, $email_insert, $password_insert);
            $username_insert = $username;
            $email_insert = $email;
            $password_insert = $password;
            if ($stmt->execute()) {
                $userIDResult = mysqli_query($db, "select uid from user where uname='" . $username . "'");
                $userIDRow = mysqli_fetch_array($userIDResult);
                $userID = $userIDRow["uid"];
                $_SESSION['username'] = $username;
                $_SESSION['uid'] = $userID;
                $_SESSION['autolocation'] = 1;
                $_SESSION["hiddenlocation"] = 0;
                $_SESSION['success'] = "You are now logged in";
                header('location: index.php');
                mysqli_commit($db);
            } else {
                array_push($errors, $stmt->error);
                mysqli_rollback($db);
            }
        }
        $stmt->close();

        $uid = $_SESSION['uid'];
        $uid1 = (string)$uid;
        $act = "uid ".$uid1." created";

        //Insert new user to userlocation (activity log)
        mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
        $insertlocquery = "INSERT into userlocation(uid, ulatitude, ulongitude, utimestamp, activity) VALUES(?,?,?,CURRENT_TIMESTAMP(3),?)";
        if ($insertlocstmt = mysqli_prepare($db, $insertlocquery)) {
        $insertlocstmt->bind_param("idds", $uid, $lat, $lon, $act);
        if ($insertlocstmt->execute()) {
            mysqli_commit($db);
            header('location: index.php');
        } else {
            echo mysqli_errno($db) . ": " . mysqli_error($db) . "\n";
            mysqli_rollback($db);
            array_push($errors,"Could not change current location");
        }
    }
    }
}

//LOGIN USER
if (isset($_POST['login_user'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $activity = 'user logged in';
    $lat = 0;
    $long = 0;
    
    if(isset($_POST['latitude']) and isset($_POST["longitude"])){
        $lat = $_POST['latitude'];
        $long = $_POST['longitude'];
    }

    if (empty($username)) {
        array_push($errors, "Username is required");
    }
    if (empty($password)) {
        array_push($errors, "Password is required");
    }

    if (count($errors) == 0) {
        mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
        $query = "SELECT upassword FROM user where uname=?";
        if ($stmt = mysqli_prepare($db, $query)) {
            $stmt->bind_param("s", $username);
            if ($stmt->execute()){
                mysqli_commit($db);
                $results = $stmt->get_result();
                //Verify if user exists
                if (mysqli_num_rows($results) == 1) {
                    $row = mysqli_fetch_row($results);
                    $hashpass = $row[0];
                    //Verify passwords match
                    if (!password_verify($password, $hashpass)) {
                        array_push($errors, "Wrong username/password combination");
                    } else {
                        $userIDResult = mysqli_query($db, "select uid from user where uname='" . $username . "'");
                        $userIDRow = mysqli_fetch_array($userIDResult);
                        $userID = $userIDRow["uid"];
                        $_SESSION['username'] = $username;
                        $_SESSION['uid'] = $userID;
                        $_SESSION['autolocation'] = 1;
                        $_SESSION["hiddenlocation"] = 0;
                        $_SESSION['success'] = "You are now logged in";
                        // header('location: index.php');
                    }
                } else {
                    array_push($errors, "Wrong username/password combination");
                }
            }

            mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
            $insertlocquery = "INSERT into userlocation(uid, ulatitude, ulongitude, utimestamp, activity) VALUES(?,?,?,CURRENT_TIMESTAMP(3),?)";
            if ($insertlocstmt = mysqli_prepare($db, $insertlocquery)) {
                $insertlocstmt->bind_param("idds", $uid, $lat, $long, $activity);
                $uid = $_SESSION["uid"];
                if ($insertlocstmt->execute()) {
                    mysqli_commit($db);
                    header('location: index.php');
                } else {
                    echo mysqli_errno($db) . ": " . mysqli_error($db) . "\n";
                    mysqli_rollback($db);
                    array_push($errors,"Could not update in User Activiy");
                }
            }

            else {
                mysqli_rollback($db);
            }
            $stmt->close();
        }
    }
}


//Update State
if (isset($_POST['selectPreviousState'])) {
    $username = $_SESSION['username'];
    $uid = $_SESSION['uid'];
    $statename = $_POST['stateNameDrop'];
    $activity = 'updated state';
    $lat = 0;
    $long = 0;
    if ($_SESSION['autolocation'] == 1) {
        $lat = $_POST['latitude'];
        $long = $_POST['longitude'];
    } else{
        $curloc1 = mysqli_query($db, "SELECT ulatitude, ulongitude FROM userlocation WHERE utimestamp IN (SELECT max(utimestamp) FROM userlocation where uid = '" . $uid . "')");
        $curloc = mysqli_fetch_row($curloc1);
        $lat = $curloc[0];
        $long = $curloc[1];
        }
    if (count($errors) == 0) {
        mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
        $stateIDResult = mysqli_query($db, "select stateID from state where statename='" . $statename . "'");
        if (!$stateIDResult) {
            array_push($errors, "Could not get the stateID");
            mysqli_rollback($db);
        } else {
            mysqli_commit($db);
            $stateIDRow = $stateIDResult->fetch_array();
            $stateID = $stateIDRow["stateID"];
            $verifyStateUserResult = mysqli_query($db, "select * from userstate where uid='" . $uid . "' and stateID='" . $stateID . "'");
            if (mysqli_num_rows($verifyStateUserResult) < 1) {
                mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
                $updateuserstate = mysqli_query($db, "Update userstate set isCurrent=0 where uid='" . $uid . "' and isCurrent=1");
                $insertuserstate = mysqli_query($db, "Insert into userstate (uID, stateID, isCurrent) VALUES('" . $uid . "','" . $stateID . "', 1)");
                if ($updateuserstate and $insertuserstate) {
                    mysqli_commit($db);
                    header('location: index.php');
                } else {
                    mysqli_rollback($db);
                }
            } else {
                mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
                $updateuserstate1 = mysqli_query($db, "Update userstate set isCurrent=0 where uid='" . $uid . "' and isCurrent=1");
                $updateuserstate2 = mysqli_query($db, "Update userstate set isCurrent=1 where uid='" . $uid . "' and stateID='" . $stateID . "'");
                if ($updateuserstate1 and $updateuserstate2) {
                    mysqli_commit($db);
                    // header('location: index.php');
                } else {
                    mysqli_rollback($db);
                }
            }
        }

        mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
        $insertlocquery = "INSERT into userlocation(uid, ulatitude, ulongitude, utimestamp, activity) VALUES(?,?,?,CURRENT_TIMESTAMP(3),?)";
        if ($insertlocstmt = mysqli_prepare($db, $insertlocquery)) {
            $insertlocstmt->bind_param("idds", $uid, $lat, $long, $activity);
            if ($insertlocstmt->execute()) {
                mysqli_commit($db);
                header('location: index.php');
            } else {
                echo mysqli_errno($db) . ": " . mysqli_error($db) . "\n";
                mysqli_rollback($db);
                array_push($errors,"Could not update in User Activiy");
            }
        }
    }
}

//User creates a new state
if (isset($_POST['createState'])) {
    $username = $_SESSION['username'];
    $uid = $_SESSION['uid'];
    $statename = $_POST['stateName'];
    $activity = 'created new state';
    $lat = 0;
    $long = 0;
    if ($_SESSION['autolocation'] == 1) {
        $lat = $_POST['latitude'];
        $long = $_POST['longitude'];
    } else{
        $curloc1 = mysqli_query($db, "SELECT ulatitude, ulongitude FROM userlocation WHERE utimestamp IN (SELECT max(utimestamp) FROM userlocation where uid = '" . $uid . "')");
        $curloc = mysqli_fetch_row($curloc1);
        $lat = $curloc[0];
        $long = $curloc[1];
        }
    if (empty($statename)) {
        array_push($errors, "State field cannot be empty");
    }
    if (count($errors) == 0) {
        mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
        $query = "select * from state where statename=?";
        if ($stmt = mysqli_prepare($db, $query)) {
            $stmt->bind_param("s", $statename);
            if ($stmt->execute()){
                mysqli_commit($db);
                if (($stmt->get_result()->num_rows) > 0) {
                    array_push($errors, "The state already exists. Please select from drop-down.");
                } else {
                    mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
                    $maxIDResult = mysqli_query($db,"SELECT MAX(stateID) as maxid FROM state");
                    $maxIDArray = mysqli_fetch_array($maxIDResult);
                    $maxID = $maxIDArray["maxid"];
                    $insertstatequery = "Insert into state (stateID, statename) VALUES(?,?)";
                    if ($insertstatestmt = mysqli_prepare($db, $insertstatequery)) {
                        $insertstatestmt->bind_param("is", $stateID, $statename);
                        $stateID = $maxID + 1;
                        if ($insertstatestmt->execute()) {
                            $updateuserstate = mysqli_query($db, "Update userstate set isCurrent=0 where uid='" . $uid . "' and isCurrent=1");
                            $insertuserstate = mysqli_query($db, "Insert into userstate (uID, stateID, isCurrent) VALUES('" . $uid . "','" . $stateID . "',1)");
                            if ($updateuserstate and $insertuserstate) {
                                mysqli_commit($db);
                                // header('location: index.php');
                            } else {
                                array_push($errors,"Could not update in userstate");
                            }
                        } else {
                            mysqli_rollback($db);
                            array_push($errors,"Could not insert into state");
                        }
                    }

                    mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
                    $insertlocquery = "INSERT into userlocation(uid, ulatitude, ulongitude, utimestamp, activity) VALUES(?,?,?,CURRENT_TIMESTAMP(3),?)";
                    if ($insertlocstmt = mysqli_prepare($db, $insertlocquery)) {
                        $insertlocstmt->bind_param("idds", $uid, $lat, $long, $activity);
                        if ($insertlocstmt->execute()) {
                            mysqli_commit($db);
                            header('location: index.php');
                        } else {
                            echo mysqli_errno($db) . ": " . mysqli_error($db) . "\n";
                            mysqli_rollback($db);
                            array_push($errors,"Could not update in User Activiy");
                        }
                    }
                }
            } else {
                mysqli_rollback($db);
                array_push($errors,"Could not query state");
            }
        }
    }
}

//Create Comment
if (isset($_POST['comment'])) {
    $username = $_SESSION['username'];
    $uid = $_SESSION['uid'];
    $noteid = $_POST['noteid'];
    $cid = $_POST['cid'];
    $ctext = $_POST['usercomment'];
    $activity = 'created comment';
    $lat = 0;
    $long = 0;
    if ($_SESSION['autolocation'] == 1) {
        $lat = $_POST['latitude'];
        $long = $_POST['longitude'];
    } else{
        $curloc1 = mysqli_query($db, "SELECT ulatitude, ulongitude FROM userlocation WHERE utimestamp IN (SELECT max(utimestamp) FROM userlocation where uid = '" . $uid . "')");
        $curloc = mysqli_fetch_row($curloc1);
        $lat = $curloc[0];
        $long = $curloc[1];
        }
    if (empty($noteid)) {
        array_push($errors, "No note selected.");
    }
    if(empty($ctext)) {
        array_push($errors, "Please enter a comment");
    }
    if (strlen($ctext) >= 250){
        array_push($errors,"Please enter a comment of length less than 250");
    }
    if (count($errors) == 0) {

        if ($cid == -1 or empty($cid)) {
            mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
            $commentquery = "insert into comments (nid, uid, replyToCid, cText, ctimestamp) VALUES(?, ?, null, ?, CURRENT_TIMESTAMP(3))";
            if ($commentstmt = mysqli_prepare($db, $commentquery)) {
                $commentstmt->bind_param("iis",$noteid,$uid,$ctext);
                if ($commentstmt->execute()) {
                    mysqli_commit($db);
                    header('location: viewnote.php?noteid=' . $noteid);
                } else {
                    mysqli_rollback($db);
                    array_push($errors, "Error in query execute");
                }
            } else {
                mysqli_rollback($db);
                array_push($errors, "error in prepare");
            }
        } else {
            mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
            $commentquery = "INSERT into comments (nid, uid, replyToCid, cText, ctimestamp) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP(3))";
            if ($commentstmt = mysqli_prepare($db, $commentquery)) {
                $commentstmt->bind_param("iiis",$noteid,$uid,$cid,$ctext);
                if ($commentstmt->execute()) {
                    mysqli_commit($db);
                    header('location: viewnote.php?noteid=' . $noteid);
                } else {
                    mysqli_rollback($db);
                    array_push($errors, mysqli_error($db));
                }
            } else {
                mysqli_rollback($db);
                array_push($errors, mysqli_error($db));
            }
        }

        mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
        $insertlocquery = "INSERT into userlocation(uid, ulatitude, ulongitude, utimestamp, activity) VALUES(?,?,?,CURRENT_TIMESTAMP(3),?)";
        if ($insertlocstmt = mysqli_prepare($db, $insertlocquery)) {
            $insertlocstmt->bind_param("idds", $uid, $latname, $longname, $activity);
            if ($insertlocstmt->execute()) {
                mysqli_commit($db);
                // header('location: index.php');
            } else {    
                echo mysqli_errno($db) . ": " . mysqli_error($db) . "\n";
                mysqli_rollback($db);
                array_push($errors,"Could not import into activity log");
            }
        }

    }
}

//Create User Comment
if (isset($_POST['usercommentsubmit'])) {
    $username = $_SESSION['username'];
    $uid = $_SESSION['uid'];
    $noteid = $_POST['noteid'];
    $cid = $_POST['cid'];
    $ctext = $_POST['usercomment'];
    $activity = 'created comment';
    $lat = 0;
    $long = 0;
    if ($_SESSION['autolocation'] == 1) {
        $lat = $_POST['latitude'];
        $long = $_POST['longitude'];
    } else{
        $curloc1 = mysqli_query($db, "SELECT ulatitude, ulongitude FROM userlocation WHERE utimestamp IN (SELECT max(utimestamp) FROM userlocation where uid = '" . $uid . "')");
        $curloc = mysqli_fetch_row($curloc1);
        $lat = $curloc[0];
        $long = $curloc[1];
    }
    if (empty($noteid)) {
        array_push($errors, "No note selected.");
    }
    if(empty($ctext)) {
        array_push($errors, "Please enter a comment");
    }
    if (strlen($ctext) >= 250){
        array_push($errors,"Please enter a comment of length less than 250");
    }
    if (count($errors) == 0) {

        if ($cid == -1 or empty($cid)) {
            mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
            $commentquery = "insert into comments (nid, uid, replyToCid, cText, ctimestamp) VALUES(?, ?, null, ?, CURRENT_TIMESTAMP(3))";
            if ($commentstmt = mysqli_prepare($db, $commentquery)) {
                $commentstmt->bind_param("iis",$noteid,$uid,$ctext);
                if ($commentstmt->execute()) {
                    mysqli_commit($db);
                    header('location: viewyournote.php?noteid=' . $noteid);
                } else {
                    mysqli_rollback($db);
                    array_push($errors, "Error in query execute");
                }
            } else {
                mysqli_rollback($db);
                array_push($errors, "error in prepare");
            }
        } else {
            mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
            $commentquery = "INSERT into comments (nid, uid, replyToCid, cText, ctimestamp) VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP(3))";
            if ($commentstmt = mysqli_prepare($db, $commentquery)) {
                $commentstmt->bind_param("iiis",$noteid,$uid,$cid,$ctext);
                if ($commentstmt->execute()) {
                    mysqli_commit($db);
                    header('location: viewyournote.php?noteid=' . $noteid);
                } else {
                    mysqli_rollback($db);
                    array_push($errors, mysqli_error($db));
                }
            } else {
                mysqli_rollback($db);
                array_push($errors, mysqli_error($db));
            }
        }

        mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
        $insertlocquery = "INSERT into userlocation(uid, ulatitude, ulongitude, utimestamp, activity) VALUES(?,?,?,CURRENT_TIMESTAMP(3),?)";
        if ($insertlocstmt = mysqli_prepare($db, $insertlocquery)) {
            $insertlocstmt->bind_param("idds", $uid, $latname, $longname, $activity);
            if ($insertlocstmt->execute()) {
                mysqli_commit($db);
                // header('location: index.php');
            } else {
                echo mysqli_errno($db) . ": " . mysqli_error($db) . "\n";
                mysqli_rollback($db);
                array_push($errors,"Could not import into activity log");
            }
        }

    }
}

//User Search
if (isset($_POST['usersearch'])) {
    $username = $_SESSION['username'];
    $uid = $_SESSION['uid'];
    $uname = $_POST['username'];
    $activity = 'searched for another user';
    $lat = 0;
    $long = 0;
    if ($_SESSION['autolocation'] == 1) {
        $lat = $_POST['latitude'];
        $long = $_POST['longitude'];
    } else{
        $curloc1 = mysqli_query($db, "SELECT ulatitude, ulongitude FROM userlocation WHERE utimestamp IN (SELECT max(utimestamp) FROM userlocation where uid = '" . $uid . "')");
        $curloc = mysqli_fetch_row($curloc1);
        $lat = $curloc[0];
        $long = $curloc[1];
        }
    if (empty($uname)) {
        array_push($errors, "Please enter the username to search for");
    }
    if (count($errors) == 0) {
        mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
        $usernamequery = "select uid from user where uname=?";
        if ($usersearchstmt = mysqli_prepare($db, $usernamequery)) {
            $usersearchstmt->bind_param("s",$uname);
            if ($usersearchstmt->execute()) {
                mysqli_commit($db);
                $usersearchresult = $usersearchstmt->get_result();
                if (mysqli_num_rows($usersearchresult) < 1) {
                    array_push($errors, "No user with this name exists");
                } else {
                    $usersearcharray = mysqli_fetch_array($usersearchresult);
                    $usersearchid = $usersearcharray["uid"];
                    if ($usersearchid == $uid) {
                        header('location: index.php');
                    } else {
                        header('location: userprofile.php?userprofileid=' . $usersearchid);
                    }
                }

                mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
                $insertlocquery = "INSERT into userlocation(uid, ulatitude, ulongitude, utimestamp, activity) VALUES(?,?,?,CURRENT_TIMESTAMP(3),?)";
                if ($insertlocstmt = mysqli_prepare($db, $insertlocquery)) {
                    $insertlocstmt->bind_param("idds", $uid, $latname, $longname, $activity);
                    if ($insertlocstmt->execute()) {
                        mysqli_commit($db);
                        // header('location: index.php');
                    } else {    
                        echo mysqli_errno($db) . ": " . mysqli_error($db) . "\n";
                        mysqli_rollback($db);
                        array_push($errors,"Could not import into activity log");
                    }
                }

            } else {
                mysqli_rollback($db);
            }
        } else {
            mysqli_rollback($db);
            array_push($error, "Error in prepare statement");
        }
    }
}

//Send Friend Request
if (isset($_POST['friendrequest'])) {
    $username = $_SESSION['username'];
    $uid = $_SESSION['uid'];
    $friendid = $_POST['friend'];
    $activity = 'sent friend request';
    $lat = 0;
    $long = 0;
    if ($_SESSION['autolocation'] == 1) {
        $lat = $_POST['latitude'];
        $long = $_POST['longitude'];
    } else{
        $curloc1 = mysqli_query($db, "SELECT ulatitude, ulongitude FROM userlocation WHERE utimestamp IN (SELECT max(utimestamp) FROM userlocation where uid = '" . $uid . "')");
        $curloc = mysqli_fetch_row($curloc1);
        $lat = $curloc[0];
        $long = $curloc[1];
        }
    mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
    if (mysqli_query($db, "Insert into friendship (uid1, uid2, isAccepted) VALUES('" . $uid . "', '" . $friendid . "', 0)")) {
        mysqli_commit($db);
    } else {
        array_push($errors,"Insert error " . $uid . " " . $friendid . " " . mysqli_error($db));
        mysqli_rollback($db);
    }

    mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
    $insertlocquery = "INSERT into userlocation(uid, ulatitude, ulongitude, utimestamp, activity) VALUES(?,?,?,CURRENT_TIMESTAMP(3),?)";
    if ($insertlocstmt = mysqli_prepare($db, $insertlocquery)) {
        $insertlocstmt->bind_param("idds", $uid, $lat, $long, $activity);
        if ($insertlocstmt->execute()) {
            mysqli_commit($db);
            // header('location: index.php');
        } else {    
            echo mysqli_errno($db) . ": " . mysqli_error($db) . "\n";
            mysqli_rollback($db);
            array_push($errors,"Could not update in User Activiy");
        }
    }
}

//Accept Friend Request
//TODO: allow user to accept friend requests
if (isset($_POST['friendaccept'])) {
    $username = $_SESSION['username'];
    $uid = $_SESSION['uid'];
    $friendid = $_POST['friendid'];
    $activity = 'accepted friend request';
    $lat = 0;
    $long = 0;
    if ($_SESSION['autolocation'] == 1) {
        $lat = $_POST['latitude'];
        $long = $_POST['longitude'];
    } else{
        $curloc1 = mysqli_query($db, "SELECT ulatitude, ulongitude FROM userlocation WHERE utimestamp IN (SELECT max(utimestamp) FROM userlocation where uid = '" . $uid . "')");
        $curloc = mysqli_fetch_row($curloc1);
        $lat = $curloc[0];
        $long = $curloc[1];
    }
    mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
    if (mysqli_query($db, "Update friendship set isAccepted=1 where uid1='" . $friendid . "' and uid2='" . $uid . "'")) {
        mysqli_commit($db);
    } else {
        array_push($errors,"Update error " . $uid . " " . $friendid . " " . mysqli_error($db));
        mysqli_rollback($db);
    }
    mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
    $insertlocquery = "INSERT into userlocation(uid, ulatitude, ulongitude, utimestamp, activity) VALUES(?,?,?,CURRENT_TIMESTAMP(3),?)";
    if ($insertlocstmt = mysqli_prepare($db, $insertlocquery)) {
        $insertlocstmt->bind_param("idds", $uid, $lat, $long, $activity);
        if ($insertlocstmt->execute()) {
            mysqli_commit($db);
            // header('location: index.php');
        } else {    
            echo mysqli_errno($db) . ": " . mysqli_error($db) . "\n";
            mysqli_rollback($db);
            array_push($errors,"Could not update in User Activiy");
        }
    }
}


//Create Filters
if (isset($_POST['createFilter'])) {
    $uid = $_SESSION['uid'];
    // $uid = 1;
    $tagname = $_POST['tagDrop'];
    $repeatname = $_POST['repeatDrop'];
    $visibilityname = $_POST['visibilityDrop'];
    $stdatename = $_POST['selectstartdate'];
    $sttimename = $_POST['selectstarttime'];
    $enddatename = $stdatename;
    $endtimename = $_POST['selectendtime'];
    $latname = $_POST['latitudeName'];
    $longname = $_POST['longitudeName'];
    $radiusname = $_POST['radiusName'];
    $activity = 'created filter';
    $lat = 0;
    $long = 0;
    if ($_SESSION['autolocation'] == 1) {
        $lat = $_POST['latitude'];
        $long = $_POST['longitude'];
    } else{
         $uID = $_SESSION['uid'];
        $curloc1 = mysqli_query($db, "SELECT ulatitude, ulongitude FROM userlocation WHERE utimestamp IN (SELECT max(utimestamp) FROM userlocation where uid = '" . $uID . "')");
        $curloc = mysqli_fetch_row($curloc1);
        $lat = $curloc[0];
        $long = $curloc[1];
        }
    if (empty($stdatename)) {
        $stdatename = '0000-00-00';
    }
    if (empty($enddatename)) {
        $enddatename = '0000-00-00';
    }

    if(!empty($sttimename) && empty($endtimename)){
        array_push($errors, "End Time field cannot be empty.");
    }elseif(!empty($endtimename) && empty($sttimename)){
        array_push($errors, "Start Time field cannot be empty.");
    }elseif(empty($sttimename) && empty($endtimename)){
        $sttimename = '00:00';
        $endtimename = '00:00';
    } else{
        $stime24 = date("H:i", strtotime($sttimename));
        $etime24 = date("H:i", strtotime($endtimename));
        $a = ':00';
        $stime = $stime24.$a;
        $etime = $etime24.$a;
        $sdt = $stdatename." ".$stime;
        $edt = $enddatename." ".$etime;
        if (new datetime($sdt) > new datetime($edt)){
            array_push($errors, "End Time cannot be before the Start Time");
        }
    }

    if(!empty($latname) && empty($longname)){
        array_push($errors, "Longitude field cannot be empty.");
    }elseif(!empty($longname) && empty($latname)){
        array_push($errors, "Latitude field cannot be empty.");
    }elseif(empty($latname) && empty($longname)){
        $latname = '-99.00000000';
        $longname = '-999.00000000';
    }
    if (empty($radiusname)) {
        $radiusname = '0.000000';
    }
    if (count($errors) == 0) {
        $stateid = mysqli_query($db, "SELECT stateID from userstate where uid ='" . $uid . "' and isCurrent = 1");
        $sid = mysqli_fetch_array($stateid);
        $sid1 = $sid[0];
        echo $sid1;
        $tagid = mysqli_query($db,"SELECT tagid from tag where tagname ='" . $tagname . "'");
        $tid = mysqli_fetch_row($tagid);
        $tid1 = $tid[0];
        echo $tid1;
        $rid = mysqli_query($db,"SELECT rid from repeatnote where rdesc ='" . $repeatname . "'");
        $rid1 = mysqli_fetch_row($rid);
        $rid2 = $rid1[0];
        echo $rid2;
        $vid = mysqli_query($db,"SELECT vid from visibility where visibleRelation ='" . $visibilityname . "'");
        $vid1 = mysqli_fetch_row($vid);
        $vid2 = $vid1[0];
        echo $vid2;
        $stime24 = date("H:i", strtotime($sttimename));
        $etime24 = date("H:i", strtotime($endtimename));
        echo $stime24;
        echo $etime24;
        $a = ':00';
        $stime = $stime24.$a;
        $etime = $etime24.$a;
        $sdt = $stdatename." ".$stime;
        $edt = $enddatename." ".$etime;
        echo $sdt;
        echo $edt;
        mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
        $query = "SELECT * from filter where uid=? and stateid=? and tagID=? and fstarttimestamp=? and fendtimestamp=? and rid=? and flatitude=? and flongitude=? and fradius=? and vid=?";
        if ($stmt = mysqli_prepare($db, $query)) {
            $stmt->bind_param("iiissidddi", $uid, $sid1, $tid1, $sdt, $edt, $rid2, $latname, $longname, $radiusname, $vid2);
            if ($stmt->execute()){
                mysqli_commit($db);
                if (($stmt->get_result()->num_rows) > 0) {
                    array_push($errors, "The filter already exists. Please create a new filter.");
                } else {
                    mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
                    $insertfilterquery = "INSERT into filter (uid, stateid, tagID, fstarttimestamp, fendtimestamp, rid, flatitude, flongitude, fradius, vid) VALUES(?,?,?,?,?,?,?,?,?,?)";
                    if ($insertfilterstmt = mysqli_prepare($db, $insertfilterquery)) {
                        $insertfilterstmt->bind_param("iiissidddi", $uid, $sid1, $tid1, $sdt, $edt, $rid2, $latname, $longname, $radiusname, $vid2);
                        if ($insertfilterstmt->execute()) {
                            mysqli_commit($db);
                            // header('location: index.php');
                        } else {
                            echo mysqli_errno($db) . ": " . mysqli_error($db) . "\n";
                            mysqli_rollback($db);
                            array_push($errors,"Could not insert into filter");
                        }
                    }

                    mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
                    $insertlocquery = "INSERT into userlocation(uid, ulatitude, ulongitude, utimestamp, activity) VALUES(?,?,?,CURRENT_TIMESTAMP(3),?)";
                    if ($insertlocstmt = mysqli_prepare($db, $insertlocquery)) {
                        $insertlocstmt->bind_param("idds", $uid, $lat, $long, $activity);
                        if ($insertlocstmt->execute()) {
                            mysqli_commit($db);
                            header('location: index.php');
                        } else {
                            echo mysqli_errno($db) . ": " . mysqli_error($db) . "\n";
                            mysqli_rollback($db);
                            array_push($errors,"Could not update in User Activiy");
                        }
                    }
                }
            } else {
                mysqli_rollback($db);
                array_push($errors,"Could not query filter");
            }
        }
    }
}

//Create Note Request
if (isset($_POST['createNote'])) {
    $uid = $_SESSION['uid'];
    $unote = $_POST['usernote'];
    $tagname = $_POST['selecttags'];
    $repeatname = $_POST['repeatDrop'];
    $visibilityname = $_POST['visibilityDrop'];
    $stdatename = $_POST['selectstartdate'];
    $sttimename = $_POST['selectstarttime'];
    $enddatename = $stdatename;
    $endtimename = $_POST['selectendtime'];
    $latname = $_POST['latitudeName'];
    $longname = $_POST['longitudeName'];
    $radiusname = $_POST['radiusName'];
    $alcom = $_POST['commentDrop'];
    $tid1 = 0;
    $chk = 0;
    $tagchk = 0;
    $lat = 0;
    $long = 0;
    $newtagids = array();
    $alltags = array();
    $activity = 'inserted new note';
    if ($_SESSION['autolocation'] == 1) {
        $lat = $_POST['latitude'];
        $long = $_POST['longitude'];
    } else{
        $curloc1 = mysqli_query($db, "SELECT ulatitude, ulongitude FROM userlocation WHERE utimestamp IN (SELECT max(utimestamp) FROM userlocation where uid = '" . $uid . "')");
        $curloc = mysqli_fetch_row($curloc1);
        $lat = $curloc[0];
        $long = $curloc[1];
    }
        
    if (empty($stdatename)) {
        $stdatename = '0000-00-00';
    }
    if (empty($enddatename)) {
        $enddatename = '0000-00-00';
    }

    if(!empty($sttimename) && empty($endtimename)){
        array_push($errors, "End Time field cannot be empty.");
    }elseif(!empty($endtimename) && empty($sttimename)){
        array_push($errors, "Start Time field cannot be empty.");
    }elseif(empty($sttimename) && empty($endtimename)){
        $sttimename = '00:00';
        $endtimename = '00:00';
    } else{
        $stime24 = date("H:i", strtotime($sttimename));
        $etime24 = date("H:i", strtotime($endtimename));
        $a = ':00';
        $stime = $stime24.$a;
        $etime = $etime24.$a;
        $sdt = $stdatename." ".$stime;
        $edt = $enddatename." ".$etime;
        if (new datetime($sdt) > new datetime($edt)){
            array_push($errors, "End Time cannot be before the Start Time");
        }
    }

    if(!empty($latname) && empty($longname)){
        array_push($errors, "Longitude field cannot be empty.");
    }elseif(!empty($longname) && empty($latname)){
        array_push($errors, "Latitude field cannot be empty.");
    }elseif(empty($latname) && empty($longname)){
        $latname = '-99.00000000';
        $longname = '-999.00000000';
    }

    if (empty($radiusname)) {
        $radiusname = '0.000000';
    }
    if(!empty($tagname)){
        $str = ltrim($tagname, '#');
        $alltags = (explode("#",$str));
        foreach ($alltags as &$value) {
            $value = '#'.$value;
        }
        unset($value);
        $chk = 1;
    }

    if (count($errors) == 0) {
        $tagnames = mysqli_query($db, "SELECT tagname from tag");
        $b = array();
        while($row = mysqli_fetch_assoc($tagnames)) {
            array_push($b, $row["tagname"]);
        }
        if ($chk == 0){
            $tid1 = -1;
            array_push($newtagids, $tid1);
        }else{
            $newtags = array_diff($alltags, $b);
            echo "new tags";
            print_r($newtags);
            if(!empty($newtags)){
                mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
                $inserttagquery = "INSERT into tag(tagname) VALUES(?)";
                foreach($newtags as &$val){
                    if ($inserttagstmt = mysqli_prepare($db, $inserttagquery)) {
                        $inserttagstmt->bind_param("s", $val);
                        if ($inserttagstmt->execute()) {
                            $tagchk++;
                            mysqli_commit($db);
                        } else {
                            echo mysqli_errno($db) . ": " . mysqli_error($db) . "\n";
                            mysqli_rollback($db);
                            array_push($errors,"Could not insert into tag");
                        }
                    }
                }
                unset($val);
            }

            foreach($alltags as $insertedtag) {
                $insertedtagidsresult = mysqli_query($db, "SELECT tagid from tag where tagname='" . $insertedtag . "'");
                while($insertedtagrow = $insertedtagidsresult->fetch_assoc()) {
                    array_push($newtagids, $insertedtagrow["tagid"]);
                }
            }
            unset($insertedtag);

        }

        $rid = mysqli_query($db,"SELECT rid from repeatnote where rdesc ='" . $repeatname . "'");
        $rid1 = mysqli_fetch_row($rid);
        $rid2 = $rid1[0];
        $vid = mysqli_query($db,"SELECT vid from visibility where visibleRelation ='" . $visibilityname . "'");
        $vid1 = mysqli_fetch_row($vid);
        $vid2 = $vid1[0];
        if (count($errors) == 0) {
            mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
            $insertnotequery = "INSERT into note (uid, note, ntimestamp, nlatitude, nlongitude, nradius, nstarttimestamp, nendtimestamp, rid, vid, allowcomments)  VALUES(?,?,CURRENT_TIMESTAMP(3),?,?,?,?,?,?,?,?)";
            if ($insertnotestmt = mysqli_prepare($db, $insertnotequery)) {
                $insertnotestmt->bind_param("isdddssiii", $uid, $unote, $latname, $longname, $radiusname, $sdt, $edt, $rid2, $vid2, $alcom);
                if ($insertnotestmt->execute()) {
                    mysqli_commit($db);
                    $newnoteid = mysqli_query($db, "select max(nid) from note where uid='" . $uid . "'")->fetch_array()[0];
                    foreach($newtagids as &$val4){
                        mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
                        $inserttagnotes = mysqli_query($db, "INSERT into tagnotes(nid, tagid) VALUES('" . $newnoteid . "','" . $val4 . "')");
                        if ($inserttagnotes) {
                            mysqli_commit($db);
                        } else {
                            mysqli_rollback($db);
                        }
                    }
                    unset($val4);
                } else {
                    echo mysqli_errno($db) . ": " . mysqli_error($db) . "\n";
                    mysqli_rollback($db);
                    array_push($errors,"Could not insert into note");
                }
            }

            mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
            $insertlocquery = "INSERT into userlocation(uid, ulatitude, ulongitude, utimestamp, activity) VALUES(?,?,?,CURRENT_TIMESTAMP(3),?)";
            if ($insertlocstmt = mysqli_prepare($db, $insertlocquery)) {
                $insertlocstmt->bind_param("idds", $uid, $lat, $long, $activity);
                if ($insertlocstmt->execute()) {
                    mysqli_commit($db);
                    header('location: index.php');
                } else {
                    echo mysqli_errno($db) . ": " . mysqli_error($db) . "\n";
                    mysqli_rollback($db);
                    array_push($errors,"Could not update in User Activiy");
                }
            }
        }

    }
}

if (isset($_POST['updatelocation'])){
    $uid = $_SESSION['uid'];
    $latname = $_POST['newlat'];
    $longname = $_POST['newlong'];
    $activity = 'updated current location';
    if(!empty($latname) && empty($longname)){
        array_push($errors, "Longitude field cannot be empty.");
    }elseif(!empty($longname) && empty($latname)){
        array_push($errors, "Latitude field cannot be empty.");
    }elseif(empty($latname) && empty($longname)){
        array_push($errors, "Please insert values for both fields to update location");
    }
    if(count($errors) == 0){
    mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
    $insertlocquery = "INSERT into userlocation(uid, ulatitude, ulongitude, utimestamp, activity) VALUES(?,?,?,CURRENT_TIMESTAMP(3),?)";
    if ($insertlocstmt = mysqli_prepare($db, $insertlocquery)) {
        $insertlocstmt->bind_param("idds", $uid, $latname, $longname, $activity);
        if ($insertlocstmt->execute()) {
            mysqli_commit($db);
            header('location: index.php');
        } else {
            echo mysqli_errno($db) . ": " . mysqli_error($db) . "\n";
            mysqli_rollback($db);
            array_push($errors,"Could not change current location");
        }
    }
    }
}

if (isset($_POST['getlocationupdate'])) {
    if (isset($_POST['locationvalue'])) {
        $_SESSION['autolocation'] = $_POST['locationvalue'];
    }
   // array_push($errors, "location value " . $_SESSION['autolocation']);
}


//Get auto lat long without button press
if (isset($_POST["autolat"]) and isset($_POST["autolong"])) {
    $uid = $_SESSION['uid'];
    // $autolat = -1;
    // $autolong = -1;
    // array_push($errors, "lat: " . $autolat . " long: " . $autolong);
    $activity = 'user views filtered notes';
    if ($_SESSION['autolocation'] == 1) {
        $autolat = $_POST['autolat'];
        $autolong = $_POST['autolong'];
    } else{
        $curloc1 = mysqli_query($db, "SELECT ulatitude, ulongitude FROM userlocation WHERE utimestamp IN (SELECT max(utimestamp) FROM userlocation where uid = '" . $uid . "')");
        $curloc = mysqli_fetch_row($curloc1);
        $autolat = $curloc[0];
        $autolong = $curloc[1];
    }
    // array_push($errors, "lat: " . $autolat . " long: " . $autolong);
    if (count($errors) == 0) {
        mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_WRITE);
        $insertlocquery = "INSERT into userlocation(uid, ulatitude, ulongitude, utimestamp, activity) VALUES(?,?,?,CURRENT_TIMESTAMP(3),?)";
        if ($insertlocstmt = mysqli_prepare($db, $insertlocquery)) {
            $insertlocstmt->bind_param("idds", $uid, $autolat, $autolong, $activity);
            if ($insertlocstmt->execute()) {
                mysqli_commit($db);
                $_SESSION["hiddenlocation"] = 1;
            } else {
                echo mysqli_errno($db) . ": " . mysqli_error($db) . "\n";
                mysqli_rollback($db);
                array_push($errors,"Could not update location in User Activiy");
                header('location: index.php');
            }
        }   
    }
    
}

mysqli_close($db);
?>