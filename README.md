## omega-api-decks

This is a service for converting a deck list to any of the following things:

- another format ***`TODO`***
- an image of the deck and all its cards
- a `JSON` object containing all information about the deck ***`TODO`***

---

### supported deck formats

[**`YDK`**](examples/formats/ydk.txt),
[**`YDKE`**](examples/formats/ydke.txt),
an [**`Omega code`**](examples/formats/omega.txt)
and a [**`list of card names`**](examples/formats/names.txt)

***`TODO`*** implement `JSON` as a top-level format

### format identifiers

Each format is identified by a specific lowercase string of characters:

|Identifier|Format|
|:-:|:-|
|`ydk`|**`YDK`**|
|`ydke`|**`YDKE`**|
|`omega`|**`Omega code`**|
|`names`|**`list of card names`**|

### common query parameters

All endpoints have the following set of query parameters in common:  

**`?list=<input>`** — A deck list in any format. This format may be any of the above and is detected on the fly.  
**`?<identifier>=<input>`** — `<identifier>` may be any valid identifier and informs the service about the input format. This way the service does not have to guess based on the input. This is the recommended option in case the input format is known at the time of requesting this endpoint.  

`<input>` resembles the deck list that is to be handled by the request.

---

### endpoints

##### `/parse`
Parses any format and outputs information as a `JSON` object ***`TODO`***

##### `/imageify`
Yields an image of the deck list.

##### `/convert`
Converts a deck list from one format to another ***`TODO`***  
Additional query paramters: ...

***`TODO`*** add an endpoint for just `detect`ing the input format

---

### examples

...

e.g. **`?ydke=ydke%3A%2F%2F…`** passes a deck list and tells the service that it is encoded as **`YDKE`**.
