# Proof of concept of a mastodon server

- Local users are stored as fixed in-memory users. There is no option to create a new user directly on this server.
- Webfinger is working
- All incoming posts to inboxes are stored in the user-inbox.json file. THere is no automated processing of these posts yet.
- The server is not federating with other servers yet.
- Toots can be created and stored locally. THere are not federated to other servers
- Following/followers are not automated yet
- Media Attachment can be created and stored locally
- This server has a basic working api that allows to communicate with clients (mastodon mobile client, and whalebird seems to be working)
- Home timelines for user is shown. Incoming posts that are processed manually are displayed as well.