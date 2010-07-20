<!DOCTYPE html
  PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <title>Listing most viewed videos</title>
    <style type="text/css">
        div.item {
            border-top: solid black 1px;
            margin: 10px;
            padding: 2px;
            width: auto;
            padding-bottom: 20px;
        }
        .item p{
            display: block;
            overflow: hidden;
        }
        span.thumbnail {
            float: left;
            margin-right: 20px;
            padding: 2px;
            border: solid silver 1px;
            font-size: x-small;
            text-align: center;
        }
        span.attr {
            font-weight: bolder;
        }
        span.title {
            font-weight: bolder;
            font-size: x-large;
        }
        img {
            border: 0px;
        }
        a {
            color: brown;
            text-decoration: none;
        }
    </style>
  </head>
  <body>
    <?php

    require_once dirname(__FILE__).'/youtube.lib.php';

    $yt = new Youtube(array('limit' => 5));
    $videos = $yt->getStandardFeed(array('feed' => 'most_viewed', 'time' => 'today'));

    foreach ($videos as $i => $video) {
        $embed = $yt->getEmbedHTML($video); ?>
        <div class="item">
            <span class="title">
                <a href="<?php echo $video['link']; ?>"><?php echo $video['title']; ?></a>
            </span>
            <p><?php echo $video['description']; ?></p>
            <p>
                <span class="thumbnail">
                    <?php echo $embed; ?>
                </span>
                <span class="attr">By:</span> <?php echo $video['author']['name']; ?> <br/>
                <span class="attr">Duration:</span> <?php echo $video['duration']; ?> min. <br/>
                <span class="attr">Views:</span> <?php echo $video['views']; ?> <br/>
                <span class="attr">Rating:</span> <?php echo $video['rating']; ?>
            </p>
        </div>
        <?php
    }
    ?>
  </body>
</html>