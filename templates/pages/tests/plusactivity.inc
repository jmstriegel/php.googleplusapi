<!DOCTYPE html>
<html>
<head>
    <title><?= $template['title'] ?></title>
    <meta charset="utf-8" />
</head>
<body>
<h1><?= $template['person']->first_name ?> <?= $template['person']->last_name ?> (<a href="http:<?= $template['person']->profile_photo ?>">photo</a>)</h1>

<h2><?= $template['person']->first_name ?>'s Posts:</h2>

<?php
foreach( $template['posts'] as $post ) {

    if ( $post->shared_postid == "" )
    {

        //normal post
?>
    <hr />
    <div style="margin-bottom:40px;">
    <a href='https://plus.google.com/<?= $post->googleplus_postid ?>'><?= $post->googleplus_postid ?></a><br />
    <?= $post->post_data ?>
    </div>

<?php
    } else {

        //sharing another person's post
?>

    <hr />
    <div style="margin-bottom:40px;">
    <a href='https://plus.google.com/<?= $post->googleplus_postid ?>'><?= $post->googleplus_postid ?></a><br />
    <?= $post->share_content ?>
    <div style="margin-left:40px;margin-top:20px;">
    Shared post:<br />
    <a href='https://plus.google.com/<?= $post->shared_postid ?>'><?= $post->shared_postid ?></a><br />
    <?= $post->post_data ?>
    </div>
    </div>

<?php
    }

    $attchs = $post->getAttachments();
    foreach ( $attchs as $attch ) {
?>

    <div style="margin-bottom:40px;margin-top:-20px;margin-left:40px;">
<?php
        if ( $attch->preview_img != "" ) {
?>
        <a href="<?= $attch->url ?>"><img src="<?= $attch->preview_img ?>"/></a>
<?php
        } else {
?>
        <a href="<?= $attch->url ?>"><?= $attch->title ?></a><br />
        <?= $attch->description ?>
<?php
        }
?>

    </div>

<?php
    }
}
?>

</body>
</html>
