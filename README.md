# ispconfig-rest-api-example

This is an example class to create client, site_domain, shell account, etc.. using the REST api of ISPconfig.

It is not always clear what fields are required and what type are the fields. This should help, but ISPconfig's code is very easy to understand when you get the gist of it. I recommand you dive in for more functions.

It requires Guzzle but you can use any other HTTP client library.

You need to change the class variables $host to your ISPconfig hostname, and the $username/$password to the one you setup in ISPconfig.

Do not forget to add a "Remote user" not a regular one and set the right access rights.

I hope it can help someone. More to come...

