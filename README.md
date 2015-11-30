# TwitterFeed

> To show Twitter Feed from User profile using oAuth twitter v 2

# Installation:

> Create a file TwitterKeys.php inside ./api/twitter add the following to it:
```
define('API_KEY', 'YOUR_API_KEY');
define('API_SECRET', 'YOUR_SECRET_KEY');
define('ACCESS_KEY', '108222848-YOUR_ACCESS_KEY');
define('ACCESS_SECRET', 'YOUR_ACCESS_SECRET_KEY');
```
> inside ./api run
 `
 composer update
 `
 > change the username inside the ./api/index.php to your twitter username (Line 4)
 `
 $twitter = new TwitterFeedReader('user', 'YOUR_USER_NAME', 10);

 `
 > you can run this command to test the code locally (Note you have to have php  > 5.4 installed on your machine in order to test)

 `
 cd into installation folder
 PHP -S localhost:3000 -t router.php
 `

 > done.


# Dependencies
 > abraham/twitteroauth.
