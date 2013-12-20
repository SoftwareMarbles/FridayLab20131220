Friday Sprint
Leap of faith assumption: I can make a simple push notification service in a language that I have never even seen on a db I have never used in a day

05:15
Yesterday a client of mine sent me a requirement for an extension of an already existing PHP + MySQL service that is a custom push notificatin platform. The extension is essentially allowing their clients to use the push not from their iOS/Android SDK but through a RESTful API. This is of course easy and just a couple of weeks ago I made one for my own purposes in Node.js (Heroku) + CouchDb (Cloudant) but I've never seen PHP nor MySQL.

But how hard can it be? The unbounded developer optimist in me says it *can't* be that hard but the truth is - I don't know. So if I have a hypothesis ("this should be easy") I should construct an experiment about it ("let's do it in a day") and then run it. So today I will try to make a simple push notification service in PHP with backend in MySQL and a simple SDK in Objective-. If I have enough material at the end of the day - I'll make a blog post out of it, fail or succeed.

05:30
Let's close the Xcode, iPhone simulator and other things that I don't need just yet. First let's see what what's "out there" already so let's google "php push notification service". Easy APNS seems to be just what I'm trying to do: "Apple Push Notification Service using PHP & MySQL". Ok so that's all done and done but I'm actually trying to learn somethign here so I'll skip looking at source code. Not sure why I even bothered then but that's fine.

What are the moving parts of a system like this:
 1. A PHP service receiving HTTP requests.
 2. A PHP library to actually make push notifications (on Node.js I use [apn](https://github.com/argon/node-apn))
 3. A configuration module for pointing our PHP service to the database, storing certificate passwords and so on.
 4. A MySQL database storing configuration and, if needed, temporary states like bad counts

So let's start with starting a PHP process on one of my Linux VMs and see how that goes.

06:17
I followed the instructions [How to install LAMP](https://www.digitalocean.com/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu) from DigitalOcean and it went smoothly on my Ubuntu 12.04. But I skipped setting up Apache. I don't want to be serving HTML pages but replying to HTTP requests so why bring Apache into the fight?

Reading up on PHP I'm starting to remember that I did read about it at some point (and of course I heard and read a *lot* of bad stuff about it over the years). It was designed and is predominantly used as embedded server-side scripting (which brings flashbacks*) but I won't be using it like that but to build a "real" backend.

* When I started doing contract work for LockwoodTech (later ApexSQL), back in year 2000, Brian Lockwood had this product called ProcBlaster which would generate code based on table screma. It featured templates with embeddable VBScript that worked on an object model allowing you access to . When .NET and C# appeared I made a bunch of templates to produce C#, VB.NET code and so on. I also used it on another job to create a C++ COM object layer for a specific (and complex) database. It was... frustrating fun. ApexSQL retired the product some years later but still, for a couple of years I used it a lot.

"moot"

06:31
So I want to create a basic web service (and not a web server) with PHP and of course I google that (btw, I also use [DuckDuckGo](https://duckduckgo.com) search engine especially when I don't want Google changing my results based on my temporary location and stuff like that). So the first thing that comes up is [Create a Basic Web Service Using PHP, MySQL, XML and JSON](http://davidwalsh.name/web-service-php-mysql-xml-json) by David Walsh. Not sure why I need XML but sounds like a match so I'll give it a try.

09:21
After couple of hours of detours I'm *finally* ready to restart this again. I have looked into some different articles and of course I really don't want to build my own RESTful framework for PHP so after searching on Stack Overflow and googling a bit, I found [PHP REST API Frameworks](http://davss.com/tech/php-rest-api-frameworks/) article which as the first entry indicates a framework called Epiphany as "fast, easy, clean and RESTful". Good enough for me - I just want something up and running fast. To be on the safe side I scanned the rest of the article and there seem to be a lot of good frameworks out there but after looking at its example on GitHub I'll start with Epiphany.

09:35
Epiphany framework needs Apache so what's a practical developer to do? Setup Apache...

10:01
More detours and other things to do but I'm back setting up API routes through Epiphany's [example](https://github.com/jmathai/epiphany/blob/master/docs/Route.markdown).

10:40
But of course I had more work to do before I could even start pushing stuff through git to the test server. That's now all setup through [gitolite](https://github.com/sitaramc/gitolite/).

12:19
Back from the depths of silly conventions! I found a nice little [web services tutorial](http://www.lornajane.net/resource/web-services-tutorial-2) but I originally found it on another site that didn't have source code and the tutorial states this as an example of a service:

    $data = array(
    'format' => 'json',
    'status' => 'live'
    );
    echo json_encode($data);

Does this work? No, it doesn't - Apache just spits it out as a plain text. So I tried restarting Apache, re-installing mod for php5, re-enabling it and so on. None of it worked. Then I tried running it directly from command line:

    php -f index.php

That didn't work either - just got spit out as plain text (at least that's consistent). So I tried running the same code on REPL and that (Turing be blessed) worked. It also worked with `php -r` switch.

Then I figured to try to running it as a bash script by specifying the interpreter `#!/usr/bin/php` on the first line. That didn't work either but at least it told me that the access was denied! But fixing that didn't work either - code would just be echoed.

So I finally added `<?php` to index.ph and lo and behold - the script ran and spat out JSON as it should have been the case all along... and then I re-checked on Apache and now at last it was working as well (outputting that `#!/usr/bin/php` as text before the JSON but that's fine).

Okay, one (more) papercut in the life of papercuts... On to the Epiphany example.

17:26
After the lunch and christmass shopping I'm back for more paperecuts. Main Epiphany module is loading but it cannot load its submodules due to some issues with include paths. I tried setting those up with `ini_set("include_path"...` but to no avail. So I resorted to hacking the Epi.php source file to define the base directory (from which other submodules are included) during its own initialization. Unfortunatelly, at one point I decided that Epiphany was going to be a git submodule and since now I changed its source code I can't push it to my test server as Git wants to push my code to my server and Epi's code to its server (which is GitHub)