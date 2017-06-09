# Release Notes for IO

## v.1.2.5 (2017-06-08)

### Added

- Countries of delivery and online store settings are now loaded from the cache to improve the overall performance.

### Fixed

- Due to an error the default country of delivery has not been set. This has been fixed.

## v.1.2.4 (2017-06-02)

### Added

- A Twig filter for sorting an object by a given key has been added.
- Validation of the address form for the delivery country **United Kingdom**

## v.1.2.3 (2017-05-19)

### Added

- The date of birth and the VAT number entered during the address input will now be saved with the address.
- Added a twig filter for variation images.
- A corresponding template plugin can now be specified in the configuration of IO.
- Address validation based on the specified template plugin.

### Fixed

- Items will only be returned when item texts have been saved in the selected store language.

## v1.2.2 (2017-05-11)

## Fixed

- Suggested search results created by the auto-complete feature are now taking into account the grouping of variations.

## v1.2.1 (2017-05-08)

## Fixed

- Minor bug fixes and improvements.

## v1.2.0 (2017-04-28)

### Fixed

- Registrations with an email address for which an account already exists are no longer possible.
- Breadcrumbs are now also working correctly in the single item view.

## v1.1.1 (2017-04-24)

### Added

- Logic for the item list of last seen items

### Fixed

- Grouping of variations in the category item list and on the search result page
- Sorting by item name in the category item list and on the search result page

## v1.1.0 (2017-04-12)

### Added

- TemplateService: `isCategoryView` method added to check if current page is category page.
- Support for new category logic in Ceres.

## v.1.0.4

### Fixed

- An error that occurred when opening the order confirmation page has been fixed

## v1.0.3 (2017-03-24)

### Added

- Filter functionality via facets
- Rendered Twig templates can now be retrieved via REST
- New Twig functions: `trimNewLines` and `formatDateTime` 
- New method in the **CategoryService**: `getChildren()`
to get all subcategories

### Changed

- Routing was updated and extended: Old store URLs can now be processed and displayed in **Ceres**. The URL structure was optimised from `/{itemName}/{itemId}/{variationId}` to `/{category}/{subcategory}/.../{itemName}-{itemId}-{variationId}` 

## v1.0.2 (2017-03-06)

### Fixed

- Fixed an error when accessing the category view and single item view.
- Fixed an error with items showing up in a category which they weren‘t linked with.
- Fixed an error with other plugin routes being overwritten by the 404 route of IO.

## v1.0.1 (2017-02-22)

### Fixed

- Fixed an error that occurred when activating additional store languages. When [adding](https://developers.plentymarkets.com/dev-doc/template-plugins#design-lang) new language files to the `resources/lang` folder and compiling the files with [Gulp](https://developers.plentymarkets.com/dev-doc/template-plugins#gulp-ceres), the template will be displayed in the selected language.

## v1.0.0 (2017-02-20)

### Features
**IO** offers a variety of logic functions for a plentymarkets online store and serves as an interface between plentymarkets and the following online store pages:
- Homepage
- Category view
- Item view
- Shopping cart
- Checkout
- Order confirmation
- Login and registration
- Guest order page
- **My account** page
- static pages (e.g. terms and conditions, legal disclosure etc.)

Furthermore, **IO** allows you to load additional content with the help of template containers.
