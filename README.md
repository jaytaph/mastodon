# Proof of concept of a mastodon server

## What is this?
A proof of concept of a mastodon server. It should be able to join the federated network and post toots. 


## How to run it?
Not that easy at the moment, as it is a bit hardcoded to my server (dhpt.nl). However, the `App\Config` should have 
all the settings you need to change.

- You need to change the `App\Config` to reflect the correct settings for your server.
- Set your ENV settings by creating a .env.local
- You need to create the database (postgress) and run the migrations.
- You need to create a JWT token for oauth stuff. You can do this with the `./bin/console app:oauth:generate-key` command.
- You need to create a user. These are currently hardcoded in the `security.yml` config as in-memory users.
- Make sure you have `public/media/image` writeable by the webserver. It will be used to store images. We need to change this 
- to a proper storage engine to allow easy scaling.
- Once completed, you MIGHT be able to connect to the API. To test this, USE THE `./bin/console app:test:oauth` command.
    ```
    $ ./bin/console app:test:oauth <domain-without-https://> --verify
    ```

    Here you will be asked to visit an url. Click that link, enter your username/password if your in-memory user, and you will be 
    redirected to a page with a token in the url. Copy that token and paste it in the console. If all goes well, you will see
    a json response with your user info and a json with your access token which you can use to do your own curl requests for instance.

- If you want to test during development via a browser, you can set the `OAUTH_OVERRIDE=true` and set the `OAUTH_OVERRIDE_USER` to the user who will be automatically logged in when calling an API url.
- Try and setup an account on the mastodon mobile client, whalebird or any other client. Most should work ok enough.

## How to contribute?
Think of stuff that we miss, and create an issue for it. Or create a PR with a fix or feature. If it's large, please create an issue first to see if we can incorporate the feature, otherwise it might be waste of your time.

Note that this is written just to get acquainted with the mastodon API. It is not meant to be a production ready server.

## What is missing?
Besides a lot:

- Better datbase entities to cover everything we need.
- Make sure we handle all the API endpoints.
- A proper way to handle/create users from the web (create account, email verification, etc).
- A proper storage engine for media.
- A proper way to handle the federation.
- Setup the streaming API, as some clients seem to use that.

## How to run the tests?
Hah! Well, you can run `make test` to check coding style and run the test(s).

## Global architecture
There are a few major parts to this application. The `Controller\Api` directory will cover all the API functionality.
The webfinger endpoint and the user login page for oauth are found in the main `Controllers` directory. 

There are a few commands in the `Command` directory, but they probably do not do what you expect them to do, so don't run them blindly.

There is mention of an "inbox", basically this is a big json file which captures all the incoming messages. It's one of the first 
pre-db things and it is not used anymore. It's still there for debugging purposes and to easily replay a large dataset of messages.

Most things are confined in their own service class. But this needs a bit of tidying up.


## How to debug?
- This cannot run on php8.2+ because of a dependency on `digitalbazaar/jsonld` which is not compatible with php8.2+.
- Local users are stored as fixed in-memory users. There is no option to create a new user directly on this server.
- Webfinger is working
- The server is not federating with other servers yet.
- Toots can be created and stored locally. THere are not federated to other servers
- Media Attachment can be created and stored locally
- This server has a basic working api that allows to communicate with clients (mastodon mobile client, and whalebird seems to be working)
- Home timelines for user is shown.
- Tags, trends and searching works.
- Following users works.
