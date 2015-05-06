<?php

$output = new stdClass();
$output->formHelper = array();

// Connect to database server
$dbhost = (getenv('OPENSHIFT_MYSQL_DB_HOST') ? getenv('OPENSHIFT_MYSQL_DB_HOST') : "localhost");
$dbuser = (getenv('OPENSHIFT_MYSQL_DB_USERNAME') ? getenv('OPENSHIFT_MYSQL_DB_USERNAME') : "phptojs_dev");
$dbpwd = (getenv('OPENSHIFT_MYSQL_DB_PASSWORD') ? getenv('OPENSHIFT_MYSQL_DB_PASSWORD') : "mylocaldev");

$mysqli = new mysqli($dbhost, $dbuser, $dbpwd, "rpgaid");


// Get all the metadata.  This is used to assemble the final gen_data variable in the JS, which is used by the
// generator.js file.
$datakeys_sql = "SELECT DISTINCT
  mdid,datakey,title
  FROM gen_metadata
  ORDER BY title";

$datakeys_results = $mysqli->query($datakeys_sql);


while ($row = $datakeys_results->fetch_array(MYSQLI_ASSOC)) {
  $stringholder = new stdClass();
  /*
    print "<pre>row: ";
    print_r($row);
    print "</pre>";
    // */

  // for each metadata datakey, grab all the actual data and prepare/assemble it for output
  $mystring = $row['datakey'];
  $strings_sql = "SELECT gdata.string, gdata.`range`, gmd.title
          FROM gen_data gdata
          INNER JOIN gen_metadata gmd ON gdata.mdid = gmd.mdid
          where datakey = '$mystring' ORDER BY title";
  $strings_result = $mysqli->query($strings_sql);

  $i = 1;
  while ($stringrow = $strings_result->fetch_array(MYSQLI_ASSOC)) {
    if ($stringrow['range'] != "") {
      $stringholder->$stringrow['range'] = $stringrow['string'];
    } else {
      $stringholder->$i = $stringrow['string'];
      $i++;
    }
    /*
      print "<pre>bobo: ";
      print_r($bobo);
      print "</pre>";
      // */
  }

  $output->$row['datakey'] = $stringholder;
  // This formHelper is used to populate select lists...and maybe more in the future.
  $output->formHelper[$row['datakey']] = $row['title'];
};


// Encode and spit out the output
/*
  print "<pre>output: ";
  print_r($output);
  print "</pre>";
  // */
echo json_encode($output);
?>