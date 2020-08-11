## omega-api-decks

This is a service for converting a deck list to any of the following things:

- another format
- an image of the deck and all its cards
- a `JSON` object containing all information about the deck

It can also be used to simply detect the format of your input.

---

### supported deck formats

[**`YDK`**](examples/formats/ydk.txt),
[**`YDKE`**](examples/formats/ydke.txt),
an [**`Omega code`**](examples/formats/omega.txt),
a [**`list of card names`**](examples/formats/names.txt)
and a [**`JSON object`**](examples/formats/json.json)

### format identifiers

Each format is identified by a specific lowercase string of characters:

|Identifier|Format|
|:-:|:-|
|`ydk`|**`YDK`**|
|`ydke`|**`YDKE`**|
|`omega`|**`Omega code`**|
|`names`|**`list of card names`**|
|`json`|**`JSON object`**|

### common query parameters

All endpoints have the following set of query parameters in common:  

**`?list=<input>`** — A deck list in any format. This format may be any of the above and is detected on the fly.  

**`?<identifier>=<input>`** — `<identifier>` may be any valid identifier and informs the service about the input format. This way the service does not have to guess based on the input. This is the recommended option in case the input format is known at the time of requesting this endpoint.  

`<input>` resembles the deck list that is to be handled by the request.

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

...
