# Friday Lab
Leap of faith assumption: I can make a simple push notification service in a language that I have never even seen on a db I have never used in a day.

### 05:15
Yesterday a client of mine sent me a requirement for an extension of an already existing PHP + MySQL service that is a custom push notificatin platform. The extension is essentially allowing their clients to use the push not from their iOS/Android SDK but through a RESTful API. This is of course easy and just a couple of weeks ago I made one for my own purposes in Node.js (Heroku) + CouchDb (Cloudant) but I've never seen PHP nor MySQL.

But how hard can it be? The unbounded developer optimist in me says it *can't* be that hard but the truth is - I don't know. So if I have a hypothesis ("this should be easy") I should construct an experiment about it ("let's do it in a day") and then run it. So today I will try to make a simple push notification service in PHP with backend in MySQL and a simple SDK in Objective-. If I have enough material at the end of the day - I'll make a blog post out of it, fail or succeed.

### 05:30
Closing the Xcode, iPhone simulator and other things that I don't need just yet. First let's see what what's "out there" already so let's google "php push notification service". Easy APNS seems to be just what I'm trying to do: "Apple Push Notification Service using PHP & MySQL". Ok so that's all done and done but I'm actually trying to learn somethign here so I'll skip looking at source code. If this was production-time I would definitelly take a look at it.

So, what are the moving parts of a system like this:
 1. A PHP service receiving HTTP requests.
 2. A PHP library to actually make push notifications (on Node.js I use [apn](https://github.com/argon/node-apn))
 3. A configuration module for pointing our PHP service to the database, storing certificate passwords and so on.
 4. A MySQL database storing configuration and, if needed, temporary states like notification badge counts.

Let's start with setting up PHP on one of my Linux VMs and see how that goes.

### 06:17
I followed the instructions [How to install LAMP](https://www.digitalocean.com/community/articles/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu) from DigitalOcean and it went smoothly on my Ubuntu 12.04. But I skipped setting up Apache. I don't want to be serving HTML pages but replying to HTTP requests so why bring Apache into the fight?

Reading up on PHP I'm starting to remember stuff that I read about it at some point (and of course I heard and read a *lot* of bad stuff about it over the years). It was designed and is predominantly used as embedded server-side scripting (which brings flashbacks*) but I won't be using it like that but to build a "real" backend.

> *** When I started doing contract work for LockwoodTech (later ApexSQL), back in year 2000, Brian Lockwood had this product called ProcBlaster which would generate code based on table screma. It featured templates with embeddable VBScript that worked on an object model allowing you access to the database objects' metadata. When .NET and C# appeared I made a bunch of templates to produce C#, VB.NET OOP-to-DB coupling code. On another job I also used it to create a C++ COM object layer for a specific (and complex) database. It was... frustrating fun. ApexSQL retired the product some years later but still, for a couple of years I used it a lot.

### 06:31
So I want to create a basic web service (and not an HTML server) with PHP and of course I google that (btw, I also use [DuckDuckGo](https://duckduckgo.com) search engine especially when I don't want Google changing my results based on my temporary location and stuff like that). So the first thing that comes up is [Create a Basic Web Service Using PHP, MySQL, XML and JSON](http://davidwalsh.name/web-service-php-mysql-xml-json) by David Walsh. Not sure why I need XML but sounds like a match so I'll give it a try.

### 09:21
After couple of hours of detours I'm *finally* ready to restart this again. I have looked into some different articles and of course I really don't want to build my own RESTful framework for PHP so after searching on Stack Overflow and googling a bit, I found [PHP REST API Frameworks](http://davss.com/tech/php-rest-api-frameworks/) article which as the first entry indicates a framework called Epiphany as "fast, easy, clean and RESTful". Good enough for me - I just want something up and running fast. To be on the safe side I scanned the rest of the article and there seem to be a lot of good frameworks out there but after looking at its example on GitHub I'll start with Epiphany.

### 09:35
Epiphany framework needs Apache so what's a practical developer to do? Setup Apache...

### 10:01
More detours and other things to do but I'm back setting up API routes through Epiphany's [example](https://github.com/jmathai/epiphany/blob/master/docs/Route.markdown).

### 10:40
But of course I had more work to do before I could even start pushing stuff through Git to the test server. That's now all setup through [gitolite](https://github.com/sitaramc/gitolite/).

### 12:19
Back from the depths of silly conventions! I found a nice little [web services tutorial](http://www.lornajane.net/resource/web-services-tutorial-2) but I originally found it on another site that didn't have source code and the tutorial states this as an example of a service *file*:

    $data = array(
    'format' => 'json',
    'status' => 'live'
    );
    echo json_encode($data);

Does this work as written? No, it doesn't - Apache just spits it out as a plain text. So I tried restarting Apache, re-installing mod for php5, re-enabling it and so on. None of it worked. Then I tried running it directly from command line:

    php -f index.php

That didn't work either - just got spat out as plain text (at least that's consistent). So I tried running the same code on REPL and that at least worked confirming that the syntax was alrigh. It also worked with `php -r` switch.

Then I figured to try to running it as a bash script by specifying the interpreter `#!/usr/bin/php` on the first line. That didn't work either but at least it told me that the access was denied! Fixing the access didn't work either and code would just be echoed.

So I finally added `<?php` to index.ph and lo and behold - the script ran and spat out JSON as it should have been the case all along... and then I re-checked on Apache and now at last it was working as well (outputting that `#!/usr/bin/php` as text before the JSON but that's fine).

Okay, one (more) papercut in the life of papercuts... On to the Epiphany example.

### 17:26
After the lunch and christmass shopping I'm back for more paperecuts. Main Epiphany module is loading but it cannot load its submodules due to some issues with include paths. I tried setting those up with `ini_set("include_path"...` but to no avail. So I resorted to hacking the Epi.php source file to define the base directory (from which other submodules are included) during its own initialization. Unfortunatelly, at one point I decided that Epiphany was going to be a Git submodule and since now I changed its source code I can't push it to my test server as Git wants to push my code to my server and Epi's code to its server (which is the main branch on GitHub; I guess I should have branched it before including it in my Git tree).

### 18:05
I solved the submodule issues by switching to `git subtree` and then hacked a little bit more and *finally* my Epiphany example is working.

### 18:26
But the example was working only so-so - it seems that I've somehow screwed up Apache configuration but I can't figure out how. It tail spins into a recursive loop on redirects.

### 18:40
But of course... I screwed up .htaccess file which I did create at one point but then it got lost in all the Git stuff and moving files around and so on and I didn't notice it as it was of course hidden from `ls -l` (note to self: always `ls -la`).

So I have an example of a PHP web service working - so now I'm going to hack my API.

### 19:07
In one of my earlier posts I described a technique I use during project development and I compared it to [drilling](http://www.softwaremarbles.com/people/ivan-erceg/blog/2013/5/25/product-development-as-drilling). I just drilled the tiniest of holes in this problem - there is an API that returns hard-coded data but data nonetheless. What I'm going to do next is implement the entire service with data being kept in memory. Once that is done - I'll persist the needed states into the database.

### 19:16
For simplicity's sake I will be extracting all API parameters from URL's query parameters.

### 20:27
"registerApp" API is working (and I've built some reusable common code) but I was mixing up the PHP's memory model, which is essentially tied to a requests, and very different from Node.js memory model which I've been working with lately. I caught it early on - it just sprang to my mind so it's no big deal. I just need to start persisting the data on MySQL (famous last words) but there is an example for that in Epiphany's documentation.

### 21:18
MySQL syntax error reporting is awful. It took me a while to find that `IF NOT EXIST` should actually be `IF NOT EXISTS` (note the `S` at the end).

### 21:23
The database creation and access is finally working - now it's time for the big drill.

### 21:34
The rows are getting inserted on API calls... but my wife just came home and it's time for some [Cola de Mono](http://en.wikipedia.org/wiki/Cola_de_mono). Today, according to my trustworthy RescuteTime, I've spent 11 hours and 40 minutes on my Mac, out of which working 11 hours and 16 minutes were productive. The two applications where I've spent most time were Terminal with 4 hours and 36 minutes and Sublime Text 2 with 3 hours and 3 minutes. From this I would estimate that the actual coding time was less than 4 hours as I did most of my coding in Sublime Text 2 and just some minor tweaks in Terminal.

### 05:12 - The next day
Looking at the work I did yesterday, I made several choices that slowed me down considerably:

 1. I chose not to install PHP on my Mac. I hate installing software for software's sake and PHP, for now at least, is just an experiment for me.
 2. Since I chose not to have PHP on the workstation, I had to set it up on one of my remote Linux VMs. But since I don't usually work like that, I was ill prepared for it. I tried setting up FTP access with Sublime Text 2 so that I could just code on the workstation and automatically see the results on the server but that didn't work out due to some issues with FTP setup. I wasted quite some time on this.
 3. There are many alternatives to using FTP but the one I chose was to leverage Git. While this again slowed me down and I again wasted time on Git setup issues on the server (and a bunch of wasted time with modules/submodules/subtrees), this actually led to a quite interesting consequence of having almost all of my "builds" recorded in Git so now I can see that for example I had 12 "fixed a typo" commits out of a total of 65.
 4. Constant commit/push/pull on Git isn't very efficient but by the end I was already so trained in the mechanics of it that it wasn't such a big time waster.

The rest like how to properly structure a PHP file were just growing pains - stuff that I would have had to deal with even if my setup was already working great.

At the end, I failed. There is no push notification service, just a mere skeleton of the same. I'll wrap it up tomorrow as in an hour or so I'm climbing [Pochoco](http://es.wikipedia.org/wiki/Cerro_Pochoco) and then doing a traverse toward the barbeque part of the [park](http://es.wikipedia.org/wiki/El_Arrayán) where some friends will be waiting.

Sunday, 2013-12-22

### 06:52
As I mentioned the other day, today I'm going to try to wrap up my PHP + MySQL push notification web service. I'll also allow myself a bit more time and do a production-quality job on module organization and abstraction. I'll be abstracting data access and the actual push notification mechanism.

### 07:29
The insertions and querying of apps table are now working. All the database code has been abstracted into a separate Database class - nothing sophisticated, just a bunch of static functions being invoked from the main module. But it will allow easy replacement if needed be.

Now I'm wondering how do I do unit tests in PHP. I'll definitelly add a black box test which will delete all the data (not so black box but it's a setup) and then test the entire API.

### 09:06
After some minor detours, the logins are working and so is token validation. Next on is the message sending - at least the API part.

I find PHP documentation (as found on php.net) to be truly great: its complete, clean, easily digestible and community feedback is much ritcher than say on MSDN.

### 10:13
I've added states for logins and messages, fatal error handling, bunch of other stuff. The one thing that isn't working yet is payload parsing for send API method. Or I'm sending badly formed JSON data...

### 10:30
I finally tracked down the problem to not requesting json_decode to return the result in an associative array. But it's Sunday so it's kids' time as well and we are now off to [MIM](http://http://www.mim.cl). I'll wrap this up in PM.

### 20:50
Though shall not make work plans on Sunday! But back in the saddle...

### 22:10
Everything should be working now but now I need to test it against APNS and for that I need test certificate. I'm using ApnsPHP library and this time I forked it before I included it as a Git submodule. So any pushes I might have against it will go to my Git repository and not the original.

Anyway, I got my certificate on the local drive together with my private key and I'm now going to convert them to PEM files used by ApnsPHP. I usually follow the instructions described in [this](http://www.raywenderlich.com/32960/apple-push-notification-services-in-ios-6-tutorial-part-1) article ("Making the App ID and SSL Certificate" section).
