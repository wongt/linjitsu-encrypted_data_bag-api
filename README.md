Encrypted Data Bag API
=================
The intention of this API is to able to dynamically encrypt/decrypt Chef data bags without needing to install chef-client. 

Pre-Requisites
-----------

* Apache 2.2.15+
* PHP 5.6.5+
* Phalcon 1.3.4+
* Chef 11.10.4+

Installation
-----------

1. Insert your client key in DOCUMENT_ROOT/../.chef/client.pem 
2. Insert your secret key in DOCUMENT_ROOT/../.chef/encrypted_data_bag_secret
3. Modify knife.rb in DOCUMENT_ROOT/../.chef/knife.rb. 
```
node_name	'node_name'
client_key	'/var/www/html/.chef/client.pem'
chef_server_url	'https://api.opscode.com/organizations/ORG'
```


Usage
-----------

Encrypt:
```
curl -X POST -H "Content-Type: application/json" --data @decrypted.json http://4.4.4.22/data_bag/encrypt 
```

<pre><code>
{
  "id": "configs",
  "production": {
    "encrypted_data": "O2xw435Uk4t3sH/ETc7h9Jb9keoExcmk9kvHTqm6VVsWagvdtaTF6M55FN2U\nDjZP\n",
    "iv": "2BnS8yvufC75kbkiLLRPnw==\n",
    "version": 1,
    "cipher": "aes-256-cbc"
  },
  "staging": {
    "encrypted_data": "1Ddy2iBbdfEbGU7PBPVvzmSTsXBh0G2mRKnr1nfjjNo4S+hyjgbvofOszhca\n5K99\n",
    "iv": "aZ4J7lI1l1IPx13eY6Durg==\n",
    "version": 1,
    "cipher": "aes-256-cbc"
  },
  "development": {
    "encrypted_data": "cKC1Sk3IMLZ9ekxk9v6kV7iW/Vb+6JJ537L11PmXvGD6GARAU/VYhFhwiqpT\nfSCj\n",
    "iv": "7J4QPCsSO/4yPVRva3BP6Q==\n",
    "version": 1,
    "cipher": "aes-256-cbc"
  }
}
</code></pre>

Decrypt:
```
curl -X POST -H "Content-Type: application/json" --data @encrypted.json http://4.4.4.22/data_bag/decrypt
```
<pre><code>
{
  "id": "configs",
  "production": {
    "secret": "notthatsecret"
  },
  "staging": {
    "secret": "notthatsecret"
  },
  "development": {
    "secret": "notthatsecret"
  }
}
</pre></code>
