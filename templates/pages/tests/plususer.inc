<!DOCTYPE html>
<html>
<head>
    <title><?= $template['title'] ?></title>
    <meta charset="utf-8" />
</head>
<body>
<h1><?= $template['person']->first_name ?> <?= $template['person']->last_name ?> (<a href="http:<?= $template['person']->profile_photo ?>">photo</a>)</h1>

<h2>In <?= $template['person']->first_name ?>'s Circles:</h2>

<p>
<?php
foreach( $template['hascircled'] as $followee ) {
?>
    <a href='plususer.php?plusid=<?= $followee->googleplus_id ?>'><?= $followee->googleplus_id ?> <?= $followee->first_name ?> <?= $followee->last_name ?></a><br />
<?php
}
?>
</p>


<h2>Have <?= $template['person']->first_name ?> in Circles:</h2>
<p>

<?php
foreach( $template['circledby'] as $follower ) {
?>
    <a href='plususer.php?plusid=<?= $follower->googleplus_id ?>'><?= $follower->googleplus_id ?> <?= $follower->first_name ?> <?= $follower->last_name ?></a><br />
<?php
}
?>

</p>

</body>
</html>
