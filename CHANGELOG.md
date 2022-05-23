Version 2.0
===

Differences to DeepLy version 1.x are:
- Text length check has been removed (because the limit is now about 120000 letters which should be enough in most cases)
- Texts can no longer be split into sentences, as the API does not seem to support this
- `proposeTranslations()` method has been removed
- Guzzle implementation removed (you can still write your own though)
- `HttpClientInterface` has been modified
- JSON RPC protocol support has been removed
- All the bag classes have been removed
- No longer uses the unofficial API, but uses official v2 API
- API key has been introduced
- Updated API error handling, `CallException` now contains API HTTP error code
- Glossary support has been introduced
- Document support has been introduced
- Usage method has been introduced
- Support for new languages added
- The `translateFile()` method is now deprecated, please use `translateTextFile()` instead!
