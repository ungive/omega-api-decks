## omega-api-decks

This is a service for converting a deck list to any of the following things:

- another format
- an image of the deck and all its cards
- a `JSON` object containing all information about the deck

It can also be used to simply detect the format of your input.

---

### supported deck formats

|Format|Identifier|
|:-|:-:|
|[**`YDK`**](examples/formats/ydk.txt)|`ydk`|
|[**`YDKE`**](examples/formats/ydke.txt)|`ydke`|
|[**`Omega code`**](examples/formats/omega.txt)|`omega`|
|[**`List of card names`**](examples/formats/names.txt)|`names`|
|[**`JSON object`**](examples/formats/json.json)|`json`|

### common query parameters

All endpoints have the following set of query parameters in common:  

**`?list=<input>`** — A deck list in any format. This format may be any of the above and is detected on the fly.  

**`?<identifier>=<input>`** — `<identifier>` may be any valid identifier and informs the service about the input format. This way the service does not have to guess based on the input. This is the recommended option in case the input format is known at the time of requesting this endpoint.  

`<input>` resembles the deck list that is to be handled by the request.

All JSON endpoints also have the `?pretty` query parameter which formats JSON nicely.

`NOTE`: Query parameters must be URL encoded (e.g. with `encodeURIComponent()` in JavaScript).

---

### endpoints

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

### examples

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
            "ydk": "#main\n27204311\n27204311\n27204311\n13893596\n13893596\n10000080\n10000080\n68535320\n68535320\n68535320\n95929069\n95929069\n95929069\n16223761\n16223761\n16223761\n80885284\n80885284\n80885284\n32623004\n32623004\n54490275\n54490275\n93920745\n93920745\n93920745\n61318483\n61318483\n54512827\n54512827\n54512827\n81907872\n81907872\n81907872\n81191584\n81191584\n81191584\n15693423\n15693423\n15693423\n#extra\n53334641\n75367227\n32224143\n!side\n73642296\n52038441\n59438930\n59438931\n62015409\n62015409\n60643553\n60643553\n54490275\n61318483"
        }
    }
}
```
