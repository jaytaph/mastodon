require 'digest'
require 'rdf'
require 'rdf/normalize'
require 'json/ld'

# data = {
#  'type'    => 'RsaSignature2017',
#  'creator' => 'https://mastodon.nl/users/jaytest#main-key',
#  'created' => '2022-11-08T12:25:53Z',
#  '@context' => 'https://w3id.org/identity/v1'
#}

data = '{"@context":["https://www.w3.org/ns/activitystreams",{"ostatus":"http://ostatus.org#","atomUri":"ostatus:atomUri","inReplyToAtomUri":"ostatus:inReplyToAtomUri","conversation":"ostatus:conversation","sensitive":"as:sensitive","toot":"http://joinmastodon.org/ns#","votersCount":"toot:votersCount"}],"id":"https://mastodon.nl/users/jaytest/statuses/109308172951550799/activity","type":"Create","actor":"https://mastodon.nl/users/jaytest","published":"2022-11-08T12:25:53Z","to":["https://www.w3.org/ns/activitystreams#Public"],"cc":["https://mastodon.nl/users/jaytest/followers","https://dhpt.nl/users/jaytaph"],"object":{"id":"https://mastodon.nl/users/jaytest/statuses/109308172951550799","type":"Note","summary":null,"inReplyTo":null,"published":"2022-11-08T12:25:53Z","url":"https://mastodon.nl/@jaytest/109308172951550799","attributedTo":"https://mastodon.nl/users/jaytest","to":["https://www.w3.org/ns/activitystreams#Public"],"cc":["https://mastodon.nl/users/jaytest/followers","https://dhpt.nl/users/jaytaph"],"sensitive":false,"atomUri":"https://mastodon.nl/users/jaytest/statuses/109308172951550799","inReplyToAtomUri":null,"conversation":"tag:mastodon.nl,2022-11-08:objectId=13555231:objectType=Conversation","content":"<p><span class=\"h-card\"><a href=\"https://dhpt.nl/users/jaytaph\" class=\"u-url mention\">@<span>jaytaph</span></a></span> first post!</p>","contentMap":{"en":"<p><span class=\"h-card\"><a href=\"https://dhpt.nl/users/jaytaph\" class=\"u-url mention\">@<span>jaytaph</span></a></span> first post!</p>"},"attachment":[],"tag":[{"type":"Mention","href":"https://dhpt.nl/users/jaytaph","name":"@jaytaph@dhpt.nl"}],"replies":{"id":"https://mastodon.nl/users/jaytest/statuses/109308172951550799/replies","type":"Collection","first":{"type":"CollectionPage","next":"https://mastodon.nl/users/jaytest/statuses/109308172951550799/replies?only_other_accounts=true&page=true","partOf":"https://mastodon.nl/users/jaytest/statuses/109308172951550799/replies","items":[]}}}}'

def load_jsonld_context(url, _options = {}, &_block)
    readData = File.open("test.jsonld")
    json = readData.read
    readData.close

    doc = JSON::LD::API::RemoteDocument.new(json, documentUrl: url)

    block_given? ? yield(doc) : doc
end

def canonicalize(json)
    json = JSON.parse(json)
    graph = RDF::Graph.new << JSON::LD::API.toRdf(json, documentLoader: method(:load_jsonld_context))
    graph.dump(:normalize)
end

def calchash(obj)
    obj = canonicalize(obj)
    print obj
    Digest::SHA256.hexdigest(obj)
end

def normalize(str)
    ASCIIFolding.new.fold(str)
end

options_hash = calchash(data)
print options_hash

