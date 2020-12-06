## omega-api-decks

This is a service for converting a deck list to any of the following things:

- another format
- an image of the deck and all its cards
- a `JSON` object containing all information about the deck

It can also be used to simply detect the format of your input.

---

### Configuration

You can configure the behaviour of the API in [`config/config.php`](/config/config.php).

### Environment Variables

In order to run this application, you need to configure some environment variables:

```bash
DATABASE_URL=https://example.com/database/cards.cdb
CARD_IMAGE_URL=https:///example.com/images
CARD_IMAGE_URL_EXT=jpg
WEBHOOK_UPDATE_TOKEN=somerandomstring
PORT=8080
```

URLs for images are built like this: `{CARD_IMAGE_URL}/{passcode}.{CARD_IMAGE_URL_EXT}`.

The `WEBHOOK_UPDATE_TOKEN` exists to prevent unauthorized requests to the [`webhook/update`](/public/webhook/update.php) endpoint.

### Development

Run the following commands to set up your development environment:

```
$ composer install
# docker-compose up -d --build development
# docker-compose exec development update-database
# scripts/permissions.sh
```

You currently also need to have [`composer`](https://getcomposer.org/) installed on your local machine and install the required packages with it because the development service relies on the existence of the `vendor` folder and its contents in the root directory of the project.

[`update-database`](/scripts/update-database.php) will automatically download and store the newest card database from your configured source (`DATABASE_URL`). This might take a bit depending of the size of the download and your bandwidth. After that you won't have to download it again.

The last script fixes permissions of the `data`-folder in the root of the project folder, so that it can be written to by the development container.

### Production

Run the following commands and you're ready to go:

```
# docker-compose up -d --build production
# docker-compose exec production populate-cache
```

Populating the image cache will take a while, as all card images are downloaded and scaled down.

---

### Supported Deck Formats

|Format|Identifier|
|:-|:-:|
|[**`YDK`**](examples/formats/ydk.txt)|`ydk`|
|[**`YDKE`**](examples/formats/ydke.txt)|`ydke`|
|[**`Omega code`**](examples/formats/omega.txt)|`omega`|
|[**`List of card names`**](examples/formats/names.txt)|`names`|
|[**`JSON object`**](examples/formats/json.json)|`json`|

Cards in the list of names are associated with a deck by the following rules:

- The first 40 non-Extra Deck cards go to the Main Deck
- The first 15 Extra Deck cards go to the Extra Deck
- Up to 60 cards before the first Extra Deck card go to the Main Deck
- The remaining cards are put into the Side Deck

Alternatively, one can describe where cards belong by putting a line in front of them that contains the name of the respective deck (`main`, `side` or `extra`, case-insensitive), similar to how it works with the [`YDK`](examples/formats/ydk.txt) format.

### Common Query Parameters

All endpoints have the following set of query parameters in common:

**`?list=<input>`** — A deck list in any format. This format may be any of the above and is detected on the fly.

**`?<identifier>=<input>`** — `<identifier>` may be any valid identifier and informs the service about the input format. This way the service does not have to guess based on the input. This is the recommended option in case the input format is known at the time of requesting this endpoint.

`<input>` resembles the deck list that is to be handled by the request.

All JSON endpoints also have the `?pretty` query parameter which formats JSON nicely.

`NOTE`: Query parameters must be URL encoded (e.g. with `encodeURIComponent()` in JavaScript).

---

### Endpoints

##### `/imageify`
Generates an image of the deck list like you know it from YGOPro and friends.

#### JSON endpoints

##### `/detect`
Parses input and returns its format.

##### `/parse`
Parses input and outputs deck information in form of a `JSON` object.

##### `/convert`
Converts a deck list from one format to all other formats.  
The optional query parameter `&to=<identifier>` restricts the conversion to only one format.

#### JSON structure

The JSON for a successful response is structure in the following way:
```json
{
  "success": true,
  "meta": {
    "format": "<identifier>"
  },
  "data": {

  }
}
```
The `meta` object contains meta information about the request like the type of the input.
The `data` object contains the generated data of the respective endpoint.

An erroneous request returns JSON of this structure:
```json
{
  "success": false,
  "meta": {
    "error": "<message>"
  },
  "data": {}
}
```
The `error` field contains an error message describing why your request failed.

---

### Examples

Using the [JSON input from the examples directory](examples/formats/json.json):

`GET /convert?pretty&to=omega&list={"main":[27204311,2720...`

Converts the deck list to an Omega code. This is the response:
```json
{
    "success": true,
    "meta": {
        "format": "json"
    },
    "data": {
        "formats": {
            "omega": "0+a6LjWfEYbv\/L\/MAMIXps0AY4kjoiww\/PbQdlYYFuz7zgDDKmaXWGB4zsmPjCC8uMSeGYRfys5kheHgpcuZQXj3GXs4XnDhIQscP7oGx\/ll7xlguPCSLrM1cx1L\/+bXjBYbk1k0uaWYg753MQcD8Ub3TWD8MGIuGIPsBNkBAA=="
        }
    }
}
```

You can omit the `to` query parameter to get all formats:

```json
{
    "success": true,
    "meta": {
        "format": "json"
    },
    "data": {
        "formats": {
            "omega": "0+a6LjWfEYbv\/L\/MAMIXps0AY4kjoiww\/PbQdlYYFuz7zgDDKmaXWGB4zsmPjCC8uMSeGYRfys5kheHgpcuZQXj3GXs4XnDhIQscP7oGx\/ll7xlguPCSLrM1cx1L\/+bXjBYbk1k0uaWYg753MQcD8Ub3TWD8MGIuGIPsBNkBAA==",
            "ydke": "ydke:\/\/1xqfAdcanwHXGp8B3P\/TANz\/0wDQlpgA0JaYABjEFQQYxBUEGMQVBO3CtwXtwrcF7cK3BRGO9wARjvcAEY73ACQ20gQkNtIEJDbSBJzJ8QGcyfEBo3Q\/A6N0PwPpHZkF6R2ZBekdmQVTpacDU6WnA7vMPwO7zD8Du8w\/A6DQ4QSg0OEEoNDhBKDi1gSg4tYEoOLWBG927wBvdu8Ab3bvAA==!cdItAzsDfgSPs+sB!OLFjBCkLGgNS94oDU\/eKA7FHsgOxR7ID4VidA+FYnQOjdD8DU6WnAw==!",
            "ydk": "#main\n27204311\n27204311\n27204311\n13893596\n13893596\n10000080\n10000080\n68535320\n68535320\n68535320\n95929069\n95929069\n95929069\n16223761\n16223761\n16223761\n80885284\n80885284\n80885284\n32623004\n32623004\n54490275\n54490275\n93920745\n93920745\n93920745\n61318483\n61318483\n54512827\n54512827\n54512827\n81907872\n81907872\n81907872\n81191584\n81191584\n81191584\n15693423\n15693423\n15693423\n#extra\n53334641\n75367227\n32224143\n!side\n73642296\n52038441\n59438930\n59438931\n62015409\n62015409\n60643553\n60643553\n54490275\n61318483",
            "names": "3 Nibiru, the Primal Being\n2 Exodius the Ultimate Forbidden Lord\n2 The Winged Dragon of Ra - Sphere Mode\n3 Fire Hand\n3 Ice Hand\n3 Thunder Hand\n3 Ghostrick Jiangshi\n2 Nopenguin\n2 Ghostrick Yuki-onna\n3 Penguin Soldier\n2 Ghostrick Jackfrost\n3 Ghostrick Lantern\n3 Ghostrick Specter\n3 Recurring Nightmare\n3 Evenly Matched\n\n1 Ghostrick Angel of Mischief\n1 Ghostrick Alucard\n1 Ghostrick Socuteboss\n\n\n1 Ghost Belle & Haunted Mansion\n1 Ghost Mourner & Moonlit Chill\n1 Ghost Ogre & Snow Rabbit\n1 Ghost Ogre & Snow Rabbit\n2 Ghost Reaper & Winter Cherries\n2 Ghost Sister & Spooky Dogwood\n1 Ghostrick Yuki-onna\n1 Ghostrick Jackfrost",
            "json": "{\"main\":[27204311,27204311,27204311,13893596,13893596,10000080,10000080,68535320,68535320,68535320,95929069,95929069,95929069,16223761,16223761,16223761,80885284,80885284,80885284,32623004,32623004,54490275,54490275,93920745,93920745,93920745,61318483,61318483,54512827,54512827,54512827,81907872,81907872,81907872,81191584,81191584,81191584,15693423,15693423,15693423],\"extra\":[53334641,75367227,32224143],\"side\":[73642296,52038441,59438930,59438931,62015409,62015409,60643553,60643553,54490275,61318483]}"
        }
    }
}
```
