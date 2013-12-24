FridayLab20131220
=================

Experimental PHP + MySQL Push Notification Service

### NOTE: The code in this repository is **NOT** meant to be used in production.

### Hypothesis
I can make a simple push notification service in a language that I have never even seen on a db I have never used in a day.

### Experiment
Make a PHP + MySQL Push Notification Service in a day.

### Results
Mixed. I failed to do it within a day but it had to do more with getting the infrastructure in place and working correctly than with development difficulties in themselves. On the 2nd day the service was working correctly.

### Setup
Assuming your web server is correctly setup to serve PHP pages, then do the following:
 1. Copy the files into a directory where your web server can see them.
 2. Create `certs` subdirectory in the same directory and put your APNS PEM file there. Download [Entrust root certification authority PEM](https://www.entrust.net/downloads/binary/entrust_2048_ca.cer) and copy it to the same subdirectory with the name of `entrust_root_certification_authority.pem`.
 3. Make a virtual host pointing to the directory where the service is installed.
 4. Rename `FridayLab20131220-Example.ini` to `FridayLab20131220.ini` and change the configuration values accordingly.

### Common features of API methods
 1. All the communication should be conducted through HTTPS.
 2. The GET methods obtain all their input params from the query parameters (e.g. `getStatus?messageId=<message ID>`)
 3. The POST methods obtain their input params from the query parameters and/or from the payload in JSON format.
 4. All replies by the service are in JSON format and contain `status` and `timestamp` fields. Additionally, if the `status` is `fail`, an `error` field is also present.

### Usage
Assuming a service is running at [fridaylab.net](http://fridaylab.net)), you can run the following:
 1. Get a heartbeat: `curl https://fridaylab.net/20131220`.
 2. Login: `curl --data '' https://fridaylab.net/20131220/login?appId=<the assigned app ID>\&secret=<the assigned app secret>`. This will return a session token. The session token is valid for 24 hours or until logout method is invoked.
 3. Send a message: `curl --data '{"type":0,"recepient":"<the device token as assigned by iOS>","messageText":"<the text of the message>"}'   https://fridaylab.net/20131220/send?token=<the session token>`. This will return the complete message row (as JSON) from messages table including the message ID.
 4. Send messages that failed to be sent earlier: `curl --data '' https://fridaylab.net/20131220/sendWaiting?appId<the assigned app ID>`. This will return the list of message IDs that were sent.
 5. Get a status of a message: `curl https://fridaylab.net/20131220/getStatus?messageId=<the message ID>`. This will return the complete row from the message table.
 6. Get statistics: `curl https://fridaylab.net/20131220/getStatistics?appId<the assigned app ID>`. This will return the number of waiting messages and the number of sent messages for the given application.
 7. Logout: `curl --data '' https://fridaylab.net/20131220/logout?token=<the session token>`.

### Links
 * The projects' [development log](DevLog.md). This can also be found on [my blog](http://www.softwaremarbles.com/people/ivan-erceg/blog)

License
=======
MIT
