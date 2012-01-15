<?php

define('MYSQL_USER', 'root');
define('MYSQL_PASSWORD', '<PASSWORD>');

$mc = mysql_connect("localhost", MYSQL_USER, MYSQL_PASSWORD);
if(! $mc) die("Could not connect to MySQL");

mysql_query("CREATE DATABASE IF NOT EXISTS openmash", $mc);

$sdb = mysql_select_db("openmash");
if(! $sdb) die("Could not select database");

mysql_query("CREATE TABLE IF NOT EXISTS Images (
	ImageID BIGINT UNSIGNED PRIMARY KEY NOT NULL AUTO_INCREMENT,
	Filename VARCHAR(255) UNIQUE NOT NULL,
	Score INT NOT NULL
)");

if(! is_dir('./images')) die("Please create images directory");

$files = scandir('./images');

function removeNonImages($file)
{
	$exts = array('.jpg', '.jpeg', '.gif', '.png');

	foreach($exts as $ext)
	{
		if(substr($file, - strlen($ext)) == $ext) return true;
	}

	return false;
}

$images = array_filter($files, 'removeNonImages');

if(count($images) <= 1) die("Must have at least two images in directory");

$image1 = $files[array_rand($images)];
$image2 = $files[array_rand($images)];

while($image2 == $image1)
{
	$image2 = $files[array_rand($images)];
}

if(isset($_GET['win']) && isset($_GET['lose']))
{
	$safeFilename1 = mysql_escape_string($_GET['win']);
	$query = mysql_query("SELECT * FROM Images WHERE Filename = '$safeFilename2' LIMIT 1");
	$row = mysql_fetch_assoc($query);

	$score1 = 1400;
	if(! empty($row)) $score1 = $row['Score'];

	$safeFilename2 = mysql_escape_string($_GET['lose']);
	$query = mysql_query("SELECT * FROM Images WHERE Filename = '$safeFilename2' LIMIT 1");
	$row = mysql_fetch_assoc($query);

	$score2 = 1400;
	if(! empty($row)) $score2 = $row['Score'];

	$newScore1 = (int) round($score1 + (32 * (1 / (1 + ($score1 * (pow(10, $score2 - $score1) / 400))))));
	$newScore2 = (int) round($score2 - (32 * (1 / (1 + ($score2 * (pow(10, $score1 - $score2) / 400))))));

	mysql_query("INSERT INTO Images (Filename, Score) VALUES ('$safeFilename1', $newScore1) ON DUPLICATE KEY UPDATE Score = $newScore1");
	mysql_query("INSERT INTO Images (Filename, Score) VALUES ('$safeFilename2', $newScore2) ON DUPLICATE KEY UPDATE Score = $newScore2");
}

mysql_close($mc);

?>
<html>
<head>
<title>FM</title>
</head>
<body style="text-align:center;">

<h1>FM</h1>

<div>
	<a href="./index.php?win=<?php echo urlencode($image1); ?>&amp;lose=<?php echo urlencode($image2); ?>">
		<img src="./images/<?php echo $image1; ?>" width="auto" height="250" alt="image1" />
	</a>
	<b>VS</b>
	<a href="./index.php?win=<?php echo urlencode($image2); ?>&amp;lose=<?php echo urlencode($image1); ?>">
		<img src="./images/<?php echo $image2; ?>" width="auto" height="250" alt="image2" />
	</a>
</div>

</body>
</html>