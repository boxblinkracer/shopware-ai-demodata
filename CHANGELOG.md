# Changes in API Demo Data

All notable changes of releases are documented in this file
using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [unreleased]

### Added

- Add brand new option to generate **product variants**. Configure what property group to use, and AI will automatically generate all variants, if appropriate for the product.

### Changed

- Support for Shopware 6.6.9.0

## [1.4.0]

### Changed

- Upgraded Image generation model to **dall-e-3** with better results and higher image size options.
- Changed deprecated model gpt-3.5-turbo to the new **gpt-3.5-turbo-instruct**.

## [1.3.1]

### Fixed

- Fix problem where the default value of the "--count" argument did not work. So it was not possible to skip it.
- Fix problem where the plugin always threw an exception if no API key was provided, even if other commands would have been called.

## [1.3.0]

- Added new option **--saleschannel** to specify the SalesChannel when importing products and assign them to a category. (thx @schliesser)

## [1.2.0]

### Added

- Added new option to define the approximate length of product descriptions.
- Add new plugin configuration hint for OpenAI key that it starts with **sk_**. (thx @jissereitsma)
- Added better error output for OpenAI requests. Sometimes the message is empty, but the error code can still be displayed. (thx @jissereitsma)

## [1.1.0]

### Added

- Add new CLI command to generate media images.
- Added new plugin configurations for default values.
- Add new option to specify the sizes of generated product images.

## [1.0.0]

### Added

- Initial version