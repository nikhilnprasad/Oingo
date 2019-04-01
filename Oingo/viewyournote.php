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
</head>
<body>
<div class="header">
    <h2>View Note</h2>
</div>
<?php  if (isset($_SESSION['username'])) : ?>
    <?php
    $username = $_SESSION['username'];
    $uID = $_SESSION['uid'];
    $noteid = $_GET['noteid'];
//TODO: Verify if the note can be viewed by the user
//TODO: Retrieve the note, tags and other details
//TODO: Enable comments on notes and reply to comments
    mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
    $getnotesresult = mysqli_query($db, "select nid from note where uid='" . $uID . "'");
    if ($getnotesresult) {
        mysqli_commit($db);
        if (mysqli_num_rows($getnotesresult) < 1) {
            ?>
            <div class="content">
                <p><strong>You do not have any notes. Please create some before viewing.</strong></p>
            </div>
            <?php
        } else {
            $verifyflag = 0;
            while ($noterow = $getnotesresult->fetch_assoc()) {
                if ($noteid == $noterow["nid"]) {
                    $verifyflag = 1;
                    break;
                }
            }
            if ($verifyflag == 0) {
                ?>
                <div class="content">
                    <p><strong>This note does not belong to you!</strong></p>
                </div>
                <?php
            } else {
                while(mysqli_next_result($db));
                $notequery = "SELECT * from note natural join user natural join repeatnote natural join visibility where nid=?";
                mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
                if ($notestmt = mysqli_prepare($db, $notequery)) {
                    $notestmt->bind_param("i", $noteid);
                    if ($notestmt->execute()) {
                        $noteresult = $notestmt->get_result();
                        $noteresultarray = mysqli_fetch_array($noteresult);
                        $notetext = $noteresultarray["note"];
                        $noteuser = $noteresultarray["uname"];
                        list($notedate, $notetime) = explode(" ", $noteresultarray["ntimestamp"]);
                        //$notetime = $noteresultarray["ntimestamp"];
                        list($notestartdate, $notestarttime) = explode(" ", $noteresultarray["nstarttimestamp"]);
                        //$notestarttime = $noteresultarray["nstarttimestamp"];
                        list($noteenddate, $noteendtime) = explode(" ", $noteresultarray["nendtimestamp"]);
                        //$noteendtime = $noteresultarray["nendtimestamp"];
                        $noterepeat = $noteresultarray["rdesc"];
                        $notevisibility = $noteresultarray["visibleRelation"];
                        $notelat = $noteresultarray["nlatitude"];
                        $notelong = $noteresultarray["nlongitude"];
                        $noteradius = $noteresultarray["nradius"];
                        $allowcomments = $noteresultarray["allowcomments"];
                        $url = "https://maps.google.com/maps?q=$notelat,$notelong&hl=es;z=14&amp;output=embed";

                        if ($tagresult = mysqli_query($db, "SELECT tagid, tagname from tag natural join tagnotes where nid='" . $noteid . "'")) {
                            mysqli_commit($db);
                            ?>
                            <div class="content">
                                <p>Note:</p>
                                <p><strong><?php echo $notetext; ?></strong></p><br>
                                <?php
                                if (mysqli_num_rows($tagresult) > 0) {
                                $tagsinglearray = mysqli_fetch_array($tagresult);
                                if (mysqli_num_rows($tagresult) == 1 and $tagsinglearray["tagid"] != -1) {
                                    ?>
                                    <p>Tag: <strong><?php echo $tagsinglearray["tagname"]; ?></strong></p>
                                    <?php
                                } else if (mysqli_num_rows($tagresult) > 1) {
                                ?>
                                <p>Tags:
                                    <?php
                                    while ($tagrow = $tagresult->fetch_assoc()) {
                                        $tagid = $tagrow["tagid"];
                                        $tag = $tagrow["tagname"];
                                        ?>
                                        <strong><?php echo $tag; ?></strong>
                                        <?php
                                    }
                                    }
                                    }
                                    ?>
                                </p><p>By: <strong><?php echo $noteuser; ?></strong></p>
                                <p>Created on: <strong><?php echo $notedate; ?></strong> at <strong><?php echo $notetime; ?></strong></p>
                            </div>
                            <div class="content">
                                <p>Start date: <strong><?php echo $notestartdate ?></strong> at <strong><?php echo $notestarttime; ?></strong></p>
                                <p>End time: <strong><?php echo $noteendtime; ?></strong></p>
                                <p>Repeat: <strong><?php echo $noterepeat; ?></strong></p>
                                <p>Visibility: <strong><?php echo $notevisibility; ?></strong></p>

                                <iframe
                                    width="400"
                                    height="300"
                                    frameborder="0"
                                    scrolling="no"
                                    marginheight="0"
                                    marginwidth="0"
                                    src= <?php echo $url; ?>
                                >
                                </iframe>

                                <p>Radius: <strong><?php echo $noteradius . "meters"; ?></strong></p>
                            </div>
                            <?php
                            if ($allowcomments == 1) {
                                mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
                                if ($notecommentresult = mysqli_query($db, "SELECT cid, cText, uid, uname from comments natural join user where nid='" . $noteid . "' and replyToCid is null order by ctimestamp")) {
                                    mysqli_commit($db);
                                    ?>
                                    <div class="header">
                                        <h4>Comments</h4>
                                    </div>
                                    <div class="content">
                                        <a href="createusernotecomment.php?nid=<?php echo $noteid; ?>">Create comment</a>
                                    </div>
                                    <?php
                                    while($notecommentrow = $notecommentresult->fetch_assoc()) {
                                        $notecommentname = $notecommentrow["uname"];
                                        $notecommentuid = $notecommentrow["uid"];
                                        $notecid = $notecommentrow["cid"];
                                        $notecommenttext = $notecommentrow["cText"];
                                        ?>
                                        <div class="content">
                                        <p><?php echo $notecommentname; ?>:</p>
                                        <p><?php echo $notecommenttext; ?></p>
                                        <p><a href="createusernotecomment.php?cid=<?php echo $notecid; ?>&nid=<?php echo $noteid; ?>">Reply</a> </p>
                                        <?php
                                        mysqli_begin_transaction($db, MYSQLI_TRANS_START_READ_ONLY);
                                        if ($replyresult = mysqli_query($db, "SELECT cid, cText, uid, uname from comments natural join user where nid='" . $noteid . "' and replyToCid='" . $notecid . "' order by ctimestamp")) {
                                            mysqli_commit($db);
                                            if (mysqli_num_rows($replyresult) > 0) {
                                                while ($replyrow = $replyresult->fetch_assoc()) {
                                                    $replyuid = $replyrow["uid"];
                                                    $replyuname = $replyrow["uname"];
                                                    $replytext = $replyrow["cText"];
                                                    $replycid = $replyrow["cid"];
                                                    ?>
                                                    <div>
                                                        <table>
                                                            <tr>
                                                                <td>&nbsp;</td>
                                                            </tr>
                                                            <tr>
                                                                <td>&ensp;&ensp;</td>
                                                                <td><p><?php echo $replyuname; ?>:</p></td>
                                                            </tr>
                                                            <tr>
                                                                <td>&ensp;&ensp;</td>
                                                                <td><p><?php echo $replytext; ?></p></td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <?php
                                                }
                                            }
                                        } else {
                                            mysqli_rollback($db);
                                        }
                                        ?></div><?php
                                    }

                                } else {
                                    mysqli_rollback($db); ?>
                                    <div><p><?php mysqli_error($db); ?></p></div>
                                <?php                            }
                            }
                        } else {
                            mysqli_rollback($db);
                        }
                    } else {
                        mysqli_rollback($db);
                    }
                }
            }
        }
    } else {
        mysqli_rollback($db); ?>
        <div class="content">
            <p><strong>After query fail</strong></p>
        </div>
        <?php
    }
    ?>

    <div class="content">
        <p><a href="index.php">Back to home</a></p>
    </div>
<?php endif;
mysqli_close($db);?>
</body>
</html>
