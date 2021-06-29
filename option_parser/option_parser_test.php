<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Example of Fetching PHP Associative Array Values</title>
</head>
<body>

<?php
$cities = array("France"=>"Paris", "India"=>"Mumbai", "UK"=>"London", "USA"=>"New York");
$g = array_values($cities);
// Get values from cities array
print_r($g);
?>

</body>
</html>