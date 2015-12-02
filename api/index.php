<?php
require_once ('TwitterFeedReader.php');

$twitter = new TwitterFeedReader('user', 'nabazmaaruf', 10);

foreach ($twitter->tweets as $twitter->tweet) :
    $data[] = array(
       'name' => $twitter->author('name'),
       'screen_name' => '@'.$twitter->author('screen_name'),
        'profile_img' => $twitter->userAvatar(),
        'twitter_content' => $twitter->tweetHTML(),
        'retweet_count' => 'Retweeted: '.$twitter->retweetCount()
    );
endforeach;
echo json_encode($data);
