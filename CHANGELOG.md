# Changes in API Demo Data

All notable changes of releases are documented in this file
using the [Keep a CHANGELOG](https://keepachangelog.com/) principles.

## [unreleased]

### Added

- Add new **batch size of 15** products to speed up initial requests to OpenAI. It takes a lot of time to generate huge amounts of product data.

### Changed

- Changed transparent background to white background because these are JPG images without transparency.
- Improved handling of products that do not have the full data. These will be skipped with a better exception.

### Fixed

- Fix broken default configuration for text models
- Fix broken default Image Styles on initial installation

## [1.5.0]

### Added

- Add brand new option to generate **product variants**. Configure what property group to use, and AI will automatically generate all variants, if appropriate for the product.
- Add option to select what OpenAI text and image models should be used for generation
- Add support fort GPT 4 models.
- Add new product image styles. Open the plugin configuration an select what styles to use for the product images.
- Add new output of **estimated price** for the generated data.
- Add 2 files in the cache directory for the generated prompt and response of product generation requests.

### Changed

- Support for Shopware 6.6.9.0
- Product Numbers are now generated with a length of 24 instead of 16 to avoid conflicts with existing products.

### Fixed

- Fix problems when product meta description was sometimes longer than 255 characters. This is now automatically trimmed to avoid exceptions.

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