<?php
/**
 * Twitter Feed Reader
 */
require_once "twitter/TwitterKeys.php";

require_once "vendor/autoload.php";

use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterFeedReader {

    private $type;
    private $value;
    private $limit;

    public $tweet;
    public $tweets;


    public function __construct($type = 'user', $value = 'twitter', $limit = 10) {
        $this->type = $type;
        $this->value = $value;
        $this->limit = intval($limit);
        $this->tweets = $this->getData();
        if ($this->type == 'search') {
            $this->tweets = $this->tweets->statuses;
        }
    }

    private function getData() {
        $c = new TwitterOAuth(API_KEY, API_SECRET, ACCESS_KEY, ACCESS_SECRET);

        switch ($this->type) {
            case 'user':
                $twitter = $c->get("statuses/user_timeline", array(
                    "screen_name" => $this->value,
                    "count" => $this->limit,
                    "include_rts" => true
                ));
                break;
            case 'search':
                $twitter = $c->get("search/tweets", array("q" => $this->value, "count" => $this->limit));
                break;
            default:
                trigger_error('Unknown Twitterfeed type "'.$this->type.'"', E_USER_ERROR);
        }
        return $twitter;
    }
    public function userProfile() {

        $c = new TwitterOAuth(API_KEY, API_SECRET, ACCESS_KEY, ACCESS_SECRET);

        $userProfile = $c->get("users/show", array("screen_name" => $this->value));

        return $userProfile;
    }

    public function retweetCount() {
        if ($this->tweet) {

            $retweetCount = $this->isRetweet() ? $this->tweet->retweeted_status->retweet_count : $this->tweet->retweet_count;
            return $retweetCount;
        } else
            trigger_error('Unknown tweet', E_USER_ERROR);

    }

    public function hasTweets() {
        return $this->tweets ? true : false;
    }

    public function isType($type) {
        return $this->type == $type ? true : false;
    }

    public function isRetweet() {
        if (!$this->tweet)
            trigger_error('Unknown tweet', E_USER_ERROR);
        return isset($this->tweet->retweeted_status) ? true : false;
    }

    public function isReply() {
        if (!$this->tweet)
            trigger_error('Unknown tweet', E_USER_ERROR);
        return $this->tweet->in_reply_to_screen_name ? true : false;
    }

    public function user($property = null, $echo = true, $user = null) {
        if ($this->type == 'user' && !$user) {
            $user = $this->tweets[0]->user;
        } else {
            if (!$user)
                trigger_error('Unknown user', E_USER_ERROR);
        }
        if ($property) {
           return $user->$property;
        } else
            return $user;
    }

    public function author($property = null, $echo = true) {
        if ($this->tweet) {
            $author = $this->isRetweet() ? $this->tweet->retweeted_status->user : $this->tweet->user;
            $result = $this->user($property, $echo, $author);

                return $result;
        } else
            trigger_error('Unknown tweet', E_USER_ERROR);
    }

    public function tweet($property = null, $echo = true) {
        if (isset($this->tweet)) {
            $tweet = $this->isRetweet() ? $this->tweet->retweeted_status : $this->tweet;
        } else
            trigger_error('Unknown tweet', E_USER_ERROR);
        if ($property) {
            if ($echo) echo $tweet->$property;
            else return $tweet->$property;
        } else
            return $tweet;
    }

    public function tweetText($echo = true) {
        if ($echo)
            echo $this->tweet('text', false);
        else
            return $this->tweet('text', false);
    }

    public function tweetHTML($links = true, $hashtags = true, $mentions = true, $mediaLinks = true, $echo = true) {
        $content = $this->tweetText(false);
        $entities = $this->tweet('entities', false);
        foreach ($entities->urls as $link) {
            $replacer = $links ? '<a class="url" href="'.$link->url.'">'.$link->url.'</a>' : '<span class="url">'.$link->url.'</span>';
            $content = str_replace($link->url, $replacer, $content);
        }
        foreach ($entities->hashtags as $hashtag) {
            $replacer = $hashtags ? '<a class="hashtag" href="http://twitter.com/hashtag/'.$hashtag->text.'">#'.$hashtag->text.'</a>' : '<span class="hashtag">#'.$hashtag->text.'</span>';
            $content = str_replace('#'.$hashtag->text, $replacer, $content);
        }
        foreach ($entities->user_mentions as $mention) {
            $replacer = $mentions ? '<a class="mention" href="http://twitter.com/'.$mention->screen_name.'" title="'.$mention->name.'">@'.$mention->screen_name.'</a>' : '<span class="mention">@'.$mention->screen_name.'</span>';
            $content = str_replace('@'.$mention->screen_name, $replacer, $content);
        }
        if (isset($entities->media)) {
            if ($mediaLinks) {
                foreach ($entities->media as $media) {
                    $replacer = $links ? '<a class="url" href="'.$media->url.'">'.$media->url.'</a>' : '<span class="url">'.$media->url.'</span>';
                    $content = str_replace($media->url, $replacer, $content);
                }
            } else {
                foreach ($entities->media as $media) {
                    $content = str_replace($media->url, '', $content);
                }
            }
        }

            return trim($content);
    }

    public function userAvatar($echo = true) {

            return str_replace('_normal', '', $this->user('profile_image_url', false));
    }

    public function authorAvatar($echo = true) {

            return str_replace('_normal', '', $this->author('profile_image_url', false));
    }

    public function userURL($echo = true) {
        if ($echo)
            echo 'http://twitter.com/'.$this->user('screen_name', false);
        else
            return 'http://twitter.com/'.$this->user('screen_name', false);
    }

    public function authorURL($echo = true) {
        if ($echo)
            echo 'http://twitter.com/'.$this->author('screen_name', false);
        else
            return 'http://twitter.com/'.$this->author('screen_name', false);
    }

    public function tweetURL($echo = true) {
        if ($echo)
            echo 'http://twitter.com/'.$this->author('screen_name',false).'/statuses/'.$this->tweet('id_str',false);
        else
            return 'http://twitter.com/'.$this->author('screen_name',false).'/statuses/'.$this->tweet('id_str',false);
    }

    public function hasMedia() {
        return isset($this->tweet('entities', false)->media) && count($this->tweet('entities', false)->media) > 0 ? true : false;
    }

    public function media($index = null, $property = 'media_url', $echo = true) {
        if ($index === null) {
            return $this->tweet('entities', false)->media;
        } else {
            if ($echo)
                echo $this->tweet('entities', false)->media[$index]->$property;
            else
                return $this->tweet('entities', false)->media[$index]->$property;
        }
    }

    public function inReplyTo($echo = false) {
        if (!$this->tweet)
            trigger_error('Unknown tweet', E_USER_ERROR);
        if (!$echo)
            return $this->tweet->in_reply_to_screen_name;
        else
            echo $this->tweet->in_reply_to_screen_name;
    }

}