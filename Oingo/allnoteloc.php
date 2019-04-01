<?php

function parseToXML($htmlStr)
{
$xmlStr=str_replace('<','&lt;',$htmlStr);
$xmlStr=str_replace('>','&gt;',$xmlStr);
$xmlStr=str_replace('"','&quot;',$xmlStr);
$xmlStr=str_replace("'",'&#39;',$xmlStr);
$xmlStr=str_replace("&",'&amp;',$xmlStr);
return $xmlStr;
}

// Opens a connection to a MySQL server
$db=mysqli_connect ('localhost', 'root', 'password');
if (!$db) {
  die('Not connected : ' . mysqli_error());
}

// Set the active MySQL database
mysqli_set_charset($db, 'utf-8');
mysqli_select_db($db, "oingo");


// Select all the rows in the markers table
$query = "SELECT nID, uid, note, nlatitude, nlongitude from note"; 
$result = mysqli_query($db, $query);
if (!$result) {
  die('Invalid query: ' . mysqli_error());
}

header("Content-type: text/xml");

// Start XML file, echo parent node
echo "<?xml version='1.0' ?>";
echo '<markers>';
$ind=0;
// Iterate through the rows, printing XML nodes for each
while ($row = @mysqli_fetch_assoc($result)){
  // Add to XML document node
  $type = 'note';
  echo '<marker ';
  echo 'nID="' . $row['nID'] . '" ';
  echo 'uid="' . $row['uid'] . '" ';
  echo 'note1="' . parseToXML($row['note']) . '" ';
  echo 'lat="' . $row['nlatitude'] . '" ';
  echo 'lng="' . $row['nlongitude'] . '" ';
  echo 'type="' . parseToXML($type) . '" ';
  echo '/>';
  $ind = $ind + 1;
}

// End XML file
echo '</markers>';

?>