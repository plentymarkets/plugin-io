# Release Notes for IO

## v4.0.0 (2019-05-02) <a href="https://github.com/plentymarkets/plugin-io/compare/3.2.0...4.0.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### TODO

- It is now possible for registered customers to change their email address in the My Account area. To enable this, you need to carry out settings in the **System » System settings » Client » Select client » Email** menu. Create a new template under **Templates**. This template needs to include the variable "$NewCustomerEmail", which contains a confirmation link for the change of the email address. You need to link this template to the **Customer wants to change email address** event under **Automatic**.
- In order to make it possible for customers to change their email address in the My Account area, you need to activate the route "/change-mail" in the settings of the IO plugin.

### Added

- The shipping profile now displays the maximum delivery time. The maximum delivery time is the sum of the availability with the longest delivery time among items in the shopping cart and the period of delivery specified in the shipping profile.
- "Mx." is now available as an option in the "Form of address" drop-down list in the registration and the address selection. This option serves to provide a form of address for the gender option "Diverse".

### Changed

- The input field "Contact person" for B2B customers is no longer a required field.
- The code that ensures that no address containing shipping to a Packstation/post office can be selected if the selected shipping profile does not support this option has been relocated from Ceres to IO.
- When saving an address that includes shipping to a Packstation/post office, the value for the post number is now taken from the field "postNumber" instead of "address3".
- All classes of the namespace "IO\Services\ItemLoader" have been removed. The classes of the namespace "IO\Services\ItemSearch" are used as an alternative.


### Fixed

- Due to an error, the cross-selling item list in the single item view was not loaded when the page was accessed the first time. This has been fixed.
- Due to an error, the sorting value for the category also affected the sorting of variations of individual items, as long as the option "dynamically" was selected for the setting **Show variations by type**. As of now, the variation with the lowest price is displayed first.
- The ShopBuilder checkout and My Account can now be displayed correctly, even if the setting "Category routes" in IO is inactive.
- We fixed an error that occurred in the context of checking already existing email addresses in the newsletter registration.



## v3.2.0 (2019-03-25) <a href="https://github.com/plentymarkets/plugin-io/compare/3.1.2...3.2.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- The TWIG filter "addressOptionType" has been added in order to access data pertaining to the address type of an address.
- We added a new TWIG filter, which makes it possible to remove tabulator spaces from character strings.
- Ceres now considers the visibility of order statuses as specified in the back end.

### Changed

- It is now required to enter the old password in order to change the password in the MyAccount area.
- The loading of the category tree has been refactored, resulting in a significantly better performance.

### Fixed

- You can now edit categories in the ShopBuilder, even if their routes are deactivated in the IO settings.
- Under certain circumstances, grouped attributes were displayed incorrectly in item lists. This has been fixed.
- Errors in the calculation of shipping costs that stem from restrictions from payment methods and shipping profiles are now intercepted and the shipping costs are displayed correctly.
- The category option "Visible: After login" is now applicable in Ceres online stores. Categories for which this option is active are only displayed in the navigation after a login. Directly accessing the URL redirects to the login page.

## v3.1.2 (2019-03-20) <a href="https://github.com/plentymarkets/plugin-io/compare/3.1.1...3.1.2" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- The checkout and shopping cart can now correctly display and process items with measurement inputs for length and width.

## v3.1.1 (2019-03-11) <a href="https://github.com/plentymarkets/plugin-io/compare/3.1.0...3.1.1" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- Due to an error it was possible that 404 pages weren't displayed correctly. This has been fixed.

## v3.1.0 (2019-02-25) <a href="https://github.com/plentymarkets/plugin-io/compare/3.0.1...3.1.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- We integrated an interface that serves to register users for one or more newsletters when placing an order.
- It is now possible to query all items of a manufacturer via ElasticSearch

### Changed

- Items in the shopping cart now contain additional data about variation property groups.
- The creation of a return now creates a new order property. This order property serves to execute an event procedure.
- Prior to the sending of the contact form, the Google reCAPTCHA is verified via the secret site key.

### Fixed

- Due to an error, the plugin was not successfully built under certain circumstances. This has been fixed.
- Due to an error, item-dependent coupons were not removed if the corresponding item was removed from the shopping cart. This has been fixed.
- The pagination of category pages was not working properly for additional clients. This behaviour has been fixed.
- Due to an error, the currency was not updated correctly when the language was changed in the online store. This has been fixed.

## v3.0.1 (2019-02-07) <a href="https://github.com/plentymarkets/plugin-io/compare/3.0.0...3.0.1" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- Due to an error, it was possible that items were oversold in the online store. This has been fixed.
- Under specific circumstances the configuration of the minimum and maximum number of items and variations could lead to errors when changes were made to items in the shopping cart. This behaviour has been fixed.
- Due to an error, it was possible that full memory utilisation was reached. This has been fixed.

## v3.0.0 (2019-01-21) <a href="https://github.com/plentymarkets/plugin-io/compare/2.17.1...3.0.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- In the preview mode, you can now access items in the online store that would normally be invisible due to their settings (e.g. no price for the online store).
- The route **io/facet** has been added to enable the reloading of filters on the category and search pages.

### Changed

- The validators for storing addresses have been updated to account for the changes in Ceres that enable the selection of a contact person for B2B addresses.
- When loading multiple items, the number of items is no longer limited to 10.
- Instead of referencing Ceres, **Middleware.php** now references the template name saved in the configuration of IO for available currencies. Thanks to <a href="https://github.com/davidisaak" target="_blank" rel="noopener"><b>@davidisaak</b></a> for this change.

### Fixed

- Due to an error, guest accounts could create addresses without specifying an email address. This has been fixed.
- The validity of the checkout URL can now be specified in the Ceres configuration.
- The results of `ItemService::getVariations()` will now be returned in the specified order.
- The automatic sending of emails was not working properly for guest accounts. This has been fixed.
- The plugin build process showed a missing method declaration even though the method exists. This has been fixed.
- Due to an error, the correct order status could not be set when using a coupon with a larger value or the exact value of the order. This has been fixed.
- If more than 10 items were listed in the order confirmation page, not all item images were displayed. This has been fixed.
- The default client was always saved for newly registered customers. This behaviour has been fixed.

## v2.17.1 (2018-11-29) <a href="https://github.com/plentymarkets/plugin-io/compare/2.17.0...2.17.1" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- Due to an error, item categories without items were displayed in the navigation tree. This has been fixed.

## v2.17.0 (2018-11-27) <a href="https://github.com/plentymarkets/plugin-io/compare/2.16.1...2.17.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- We expanded the `CategoryItemResource` in order to output category description 2 as well.

### Changed

- The currency can now be set using the URL parameter "Currency" in order to conform to existing Callisto URLs.
- The country of delivery can now be set using the URL parameter "ShipToCountry" in order to conform to existing Callisto URLs.

### Fixed

- The status of orders with an invoice amount of 0,00€ was not updated correctly. This behaviour has been fixed.
- The settings for activating the newsletter routes were not considered in the registration confirmation and the cancellation of the newsletter. This has been fixed.

## v2.16.1 (2018-11-15) <a href="https://github.com/plentymarkets/plugin-io/compare/2.16.0...2.16.1" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- Due to an error, categories linked to a client were displayed as filter options in the item search. This has been fixed.
- Under certain circumstances, the single item view could not be displayed due to invalid sorting options. This has been fixed.
- Due to an error, the customer class was erroneously reset when a B2B company address was added or edited. This has been fixed.

## v2.16.0 (2018-10-22) <a href="https://github.com/plentymarkets/plugin-io/compare/2.15.0...2.16.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- We added a new function `getShippingCountryId`. This functions serves to retrieve the ID of the country of delivery in the checkout.
- We added the ShopBuilder newsletter widget in Ceres.

### Changed

- The contact form now sends e-mails that include a response address.
- When creating an order or a return, the customer note is now saved prior to the creation. This way, the information is already included in the confirmation e-mail.
- The LocalizedOrder now includes the ShippingProfileId.
- The file structures for category navigation have been minimised in order to improve loading times.
- The function `getHierarchy()` in the CategoryService now returns all categories and not only those that are displayed in the navigation.
- In IO it is now possible to read the current template during a REST call.

### Fixed

- Due to an error, the link from the order confirmation forwarded to a 404 page. This has been fixed.
- Due to an error, the category descriptions of the main client was used for additional clients as well. This has been fixed.
- Due to an error, the variation drop-down list in the single item view also listed variations for which no valid sales price had been set for the online store. This has been fixed.
- Due to an error, all shipping profiles were displayed in the checkout of guest orders. This has been fixed.
- Due to an error, page calls via the HEAD method always returned 404 status codes. This has been fixed.
- We made several SEO-relevant adjustments.
- Due to an error, not all relevant items were included in the **Last seen** item list. This has been fixed.
- We fixed an error, due to which a selection of variations in the single item view was not possible if 2 or more variations consisted of the same combination of attributes or if the variations had to attributes at all. In these cases, the selection is now possible via the content drop-down list.
- In rare cases, the shipping costs were displayed incorrectly on the order confirmation page. This behaviour has been fixed.

## v2.15.0 (2018-09-12) <a href="https://github.com/plentymarkets/plugin-io/compare/2.14.0...2.15.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- The login URL stored at the level of the customer now also works in Ceres.
- Due to an error the option **Domestic and EU only** of the setting **Show VAT for the shipping costs on the invoice** was not interpreted correctly. This has been fixed.

## v2.14.0 (2018-08-28) <a href="https://github.com/plentymarkets/plugin-io/compare/2.13.0...2.14.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- Categories have been added as filter options for search results.
- The URL of the Callisto search now opens the Ceres search.
- The Callisto /tag/-route now redirects to the Ceres search page.
- Item lists and search results can now be sorted randomly.
- A new event hook has been added, which reacts to the building of plugins. This enables the invalidation of the content cache.

### Changed

- The live search in the header and the search page have been synchronised so that they provide the same search results.

### Fixed

- Adding items to the shopping cart led to errors if item bundles were replaced by basic items as a result. This behaviour has been fixed.
- Due to an error users were able to select currencies that were not enabled in the Ceres configuration. This has been fixed.
- An error has been fixed that prevented the display of a conclusive error notification when items for which no stock was available had been moved to the shopping cart.
- Due to an error, items with more than one configured price were not correctly displayed if the minimum order quantity had been set for the items. This has been fixed.
- Orders that included items with live shopping prices led to errors in Callisto stores with an installed Ceres checkout. This behaviour has been fixed.

## v2.13.0 (2018-07-30) <a href="https://github.com/plentymarkets/plugin-io/compare/2.12.0...2.13.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- The "Forgot password" e-mail template stored in Ceres can now be sent from the backend.
- The OrderTotalsService now provides the coupon code.

### Changed

- The unit price was not displayed if the number of units was 1. This has ben fixed. The display of the unit price is now exclusively dependent on the setting stored at the variation level.

### Fixed

- The navigation always displayed all categories. This behaviour has been adjusted, so that different navigations can be displayed, depending on different customer classes.
- The CDN URLs for item images are now loaded client-dependently in the correct manner.
- Due to an error, the wrong language was stored when a new user was created. This has been fixed.
- Due to an error, the prices of item bundles were displayed as 0 Euro on the order confirmation page. This has been fixed.

## v2.12.0 (2018-07-10) <a href="https://github.com/plentymarkets/plugin-io/compare/2.11.0...2.12.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- Rendered contents are cached in order to improve loading times of the online store. An additional module in the plentymarkets tariff is necessary to use this functionality.
- Item bundles can now be displayed in the online store.

### Changed

- User-specific data, such as the shopping cart, login information and wish list, is now loaded later.
- The list of last seen items is now loaded later.
- The route "/rest/io/customer" no longer returns customer addresses. The route "io/customer/address" is now used for this purpose.

### Fixed

- Due to an error, the links to the homepage were not working properly. This has been fixed.
- Variations in item lists of the type **tags** were not grouped in accordance with the plugin settings. This has been fixed.
- If no URL route had been stored for an item while the option **Trailing slash after URLs** was active at the same time, the URL route was not generated properly. This behaviour has been fixed.
- Forwarding to absolute URLs led to errors. This behaviour has been fixed.
- Various faulty links have been fixed.
- The existence of multiple plugin sets sometimes led to the sitemap not being generated in accordance with the Ceres pattern. This behaviour has been fixed.
- Under certain circumstances, categories were displayed even if they had not been linked to a client. This behaviour has been fixed.
- If a content category was saved for all languages, the issue could arise that the online store displayed a 404 error page.

## v2.11.0 (2018-06-26) <a href="https://github.com/plentymarkets/plugin-io/compare/2.10.0...2.11.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- The country code has been removed from the URLs of the standard language. The country code is part of the URLs for all other languages.
- Due to an error, the VAT was not calculated in the shopping cart, if net prices were displayed because of a customer class. This has been fixed.

## v2.10.0 (2018-06-12) <a href="https://github.com/plentymarkets/plugin-io/compare/2.9.1...2.10.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- The IO configuration has been translated into German.
- The event `AfterBasketChanged` has been expanded by the field `showNetPrices`. This field determines whether gross or net sums are highlighted in the checkout and the shopping cart.
- The order data on the order confirmation page has been extended by the field `highlightNetPrices`. This field determines whether net or gross sums are highlighted.

### Changed

- The interfaces for issuing (error-)messages have been improved.

### Fixed

- Due to an error, the link in the order confirmation e-mail forwarded to a 404 page if the option "Forward to login page after clicking the link in order confirmation" was active. This has been fixed.
- Due to an error, the shipping costs were not converted to the selected currency. This has been fixed.
- Due to an error, delivery countries in the address form were always displayed in German. This has been fixed.
- Due to an error, surcharges for order properties were not converted when the currency was changed. This has been fixed.
- Due to an error, surcharges for order properties were always displayed as gross prices. This has been fixed.

## v2.9.1 (2018-06-05) <a href="https://github.com/plentymarkets/plugin-io/compare/2.9.0...2.9.1" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- References to categories that no longer exist led to errors in the display of the online store. This has been fixed.
- Due to an error, problems could occur if a coupon with a minimum order value was redeemed while an item was removed from the shopping cart at the same time, thereby lowering the price below the minimum order value. This has been fixed.
- Redeeming a coupon for special offers could lead to a faulty display of sums if items with different VAT rates were present in the shopping cart. This has been fixed.

## v2.9.0 (2018-05-24) <a href="https://github.com/plentymarkets/plugin-io/compare/2.8.1...2.9.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- The method **getURLById**, which returns the URL of a category, has been added to the CategoryService.
- The route **io/order/additional_information** has been added in order to add and edit additional order information.

### Fixed

- The canonical URLs stored on the category level were not properly utilised. This has been fixed.
- Due to an error, the function ItemService.getVariationImage() did not return image URLs. This has been fixed.

## v2.8.1 (2018-05-16) <a href="https://github.com/plentymarkets/plugin-io/compare/2.8.0...2.8.1" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- Due to an error, addresses could not be created or edited, if no federal states were available for the selected country of delivery. This has been fixed.

## v2.8.0 (2018-05-08) <a href="https://github.com/plentymarkets/plugin-io/compare/2.7.0...2.8.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- A new service (TagService) has been added in order to retrieve the name of a tag via its ID.
- The facet type **price** has been added.
- The option to include trailing slashes is now considered when generating URLs.

### Fixed

- Due to an error, data from the Ceres GlobalContext could not be loaded if accessed via a route of another plugin. This has been fixed.
- When using Ceres and IO as a client that is not the main client, category details of the main client were loaded under certain circumstances. This has been fixed.

## v2.7.0 (2018-04-13) <a href="https://github.com/plentymarkets/plugin-io/compare/2.6.0...2.7.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- For items without image, the preconfigured placeholder image is now displayed in the online store.
- Order properties of the type **file** can now be processed.

### Fixed

- Due to an error the shopping cart did not display graduated prices. This has been fixed.
- Returns did not adopt the lock state from the original order. This has been fixed.
- Due to an error the data was not validated by the server when address data was saved or edited. This has been fixed.
- Due to an error, the order confirmation always displayed the order status, the shipping service provider and the payment method in the system language. This has been fixed.
- Due to an error customer class-dependent graduated rebates on gross item value were not considered in the order creation. This has been fixed.
- Due to an error, a failed login did not issue a notification. This has been fixed.

## v2.6.0 (2018-04-03) <a href="https://github.com/plentymarkets/plugin-io/compare/2.5.2...2.6.0" target="_blank"><b>Overview of all changes</b></a>

### Added

- IO is now able to react to the generation of the sitemap and can provide its own patterns for the creation of URLs.

### Fixed

- Due to an error graduated prices were not displayed in the shopping cart. This has been fixed.
- The default country of delivery is now selected as the active country of delivery after logging out.
- After a guest purchase the email address is now deleted from the session, so that it has to be entered again for a new order process.
- Under certain circumstances the button for changing payment methods was not displayed on the order confirmation page. This has been fixed.
- Due to an error a purchase via Paypal redirected to a 404 page instead of the order confirmation page. This has been fixed.

## v2.5.2 (2018-03-26) <a href="https://github.com/plentymarkets/plugin-io/compare/2.5.1...2.5.2" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Due to an error items could not be correctly sorted by name. This has been fixed.

## v2.5.1 (2018-03-21) <a href="https://github.com/plentymarkets/plugin-io/compare/2.5.0...2.5.1" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Due to an error the shopping cart could not be refreshed if changes had been made to it. This has been fixed.

## v2.5.0 (2018-03-19) <a href="https://github.com/plentymarkets/plugin-io/compare/2.4.0...2.5.0" target="_blank"><b>Overview of all changes</b></a>

- Context classes, which provide data to related Twig templates, have been added.
- New service classes have been added in order to facilitate the use of ElasticSearch.

## v2.4.0 (2018-03-06) <a href="https://github.com/plentymarkets/plugin-io/compare/2.3.2...2.4.0" target="_blank"><b>Overview of all changes</b></a>

### Added

- A new helper class has been added in order to facilitate the access to plugin configurations.

### Changed

- In order to improve the performance, global services in Twig are only instantiated when they are required.

### Fixed

- Due to an error filters yielded empty facets and the configuration **Minimum number of hits** was not considered. This has been fixed.

## v2.3.2 (2018-02-28) <a href="https://github.com/plentymarkets/plugin-io/compare/2.3.1...2.3.2" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- The subject of the "Forgot password" email is now received via the REST call parameter "subject" and is sent as a translated version if the parameter is a valid translation key.

## v2.3.1 (2018-02-26) <a href="https://github.com/plentymarkets/plugin-io/compare/2.3.0...2.3.1" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Coupon discounts are now displayed on the order confirmation page and in the order details in the My account section.
- After the creation of a return, the return confirmation page will now be displayed again. (The route in IO config has to be active.)
- The page for the creation of returns now only displays items that can be returned. (No shipping costs, coupon positions, etc.)
- Due to an error particular attributes were not displayed in the variation selection. This has been fixed.
- Due to an error the display of gross/net prices for shipping costs was not refreshed correctly. This has been fixed.
- Errors in the shipping cost calculation didn't yield error messages. This has been fixed.
- The last seen list no longer displays random items if no item has been previously viewed in the store.

## v2.3.0 (2018-02-19) <a href="https://github.com/plentymarkets/plugin-io/compare/2.2.2...2.3.0" target="_blank"><b>Overview of all changes</b></a>

### Changed

- The filter `itemName` is now able to display the variation name or a combination of item name and variation name in accordance with the Ceres configuration.

### Fixed

- Due to an error item URLs weren't generated correctly. This has been fixed.

## v2.2.2 (2018-02-12) <a href="https://github.com/plentymarkets/plugin-io/compare/2.2.1...2.2.2" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Due to an error, the item view occasionally displayed a 404 page if the URL was entered without Variation ID. This has been fixed by taking the configuration value **Show variations by type** into account in the item view as well.

## v2.2.1 (2018-02-07) <a href="https://github.com/plentymarkets/plugin-io/compare/2.2.0...2.2.1" target="_blank"><b>Overview of all changes</b></a>

### Changed

- The sorting order of search results has been improved.
- The list of active languages will no longer be loaded from the `WebstoreConfigurationRepositoryContract`. This list will now be loaded from the configuration of the respective template plugin instead.

### Fixed

- Due to an error the prices of cross selling items weren't calculated correctly. This has been fixed.

## v2.2.0 (2018-02-05) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.5...2.2.0" target="_blank"><b>Overview of all changes</b></a>

### Added

- `IO.Resources.Import` can now receive parameters. For example, when generating and integrating a script own values saved in the plugin configuration can now be transferred and taken into account when rendering the script.
- The content of **.properties** files can now be loaded.

### Fixed

- Due to an error the error page was transmitted with a 200 status code. This has been fixed.
- Due to an error the relevance of an item wasn't correctly taken into account when searching for items and sorting items by relevance. This has been fixed.

## v2.1.5 (2018-02-02) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.4...2.1.5" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Due to an error the pagination wasn't displayed correctly when using the setting Show varations by type: Dynamically. This has been fixed.
- Due to an error item data was not displayed in a consistent way. This has been fixed.
- Due to an error surcharges for order properties weren't calculated correctly. This has been fixed.

## v2.1.4 (2018-01-29) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.3...2.1.4" target="_blank"><b>Overview of all changes</b></a>

- Due to an error URLs without the **Variation ID** parameter weren't displayed correctly. This has been fixed.

## v2.1.3 (2018-01-23) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.2...2.1.3" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Due to an error the 404 page wasn't displayed correctly. This has been fixed.
- Due to an error unneccessary item requests were executed. This has been fixed.

## v2.1.2 (2018-01-22) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.1...2.1.2" target="_blank"><b>Overview of all changes</b></a>

### Added

- A security prompt has been added which prevents customers from returning items multiple times.

### Fixed

- Due to an error too many items have been displayed in the wish list. This has been fixed.

## v2.1.1 (2018-01-09) <a href="https://github.com/plentymarkets/plugin-io/compare/2.1.0...2.1.1" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- When ordering as a guest, the address will now be removed from the session after placing the order.
- Due to an error, wrong item URLs have been generated when only one language has been activated for the online store. This has been fixed.

## v2.1.0 (2018-01-04) <a href="https://github.com/plentymarkets/plugin-io/compare/2.0.3...2.1.0" target="_blank"><b>Overview of all changes</b></a>

### Added

- URLs for items and categories can now be generated in the respective language.

### Fixed

- Due to an error, readable URLs for new items could not be generated. This has been fixed.

## v2.0.3 (2017-12-21) <a href="https://github.com/plentymarkets/plugin-io/compare/2.0.2...2.0.3" target="_blank"><b>Overview of all changes</b></a>

### Added

- Translatable error message for registration in case the email address already exists.

### Fixed

- Delivery address can now be set back to "Delivery address equals invoice address".
- Fixed error for item visibility in spite of link to customer class.

## v2.0.2 (2017-12-13) <a href="https://github.com/plentymarkets/plugin-io/compare/2.0.1...2.0.2" target="_blank"><b>Overview of all changes</b></a>

### Added

- The additional flag `isSelectable` is sent when loading payment methods.

### Fixed

- Order referrers will now be taken into consideration when loading items or calculating prices.
- Various errors concerning the handling of coupon codes have been fixed.

## v2.0.1 (2017-12-06) <a href="https://github.com/plentymarkets/plugin-io/compare/2.0.0...2.0.1" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Due to an error the default homepage wasn't displayed correctly. This has been fixed.

## v2.0.0 (2017-11-30) <a href="https://github.com/plentymarkets/plugin-io/compare/1.7.2...2.0.0" target="_blank"><b>Overview of all changes</b></a>

### Added

- The Twig functions `get_additional_styles()` und `get_additional_scripts()` allow external plugins get styles and scripts and output them at the respective location.
- A new REST route `io/checkout/paymentId` for setting the payment method has been added.
- A new REST route `io/checkout/shippingId` for seeting the shipping method has been added.
- An **Account** will be created in plentymarkets when a B2B customer signs up in the online store.
- A middleware has been added for reacting to changes of the currency in the online store.
- Prices will now be converted when the currency is changed.
- The logic for calculating order sums has been added (previously this logic was contained in a Twig macro in Ceres).
- A customer that ordered as a guest may now change the payment method on the order confirmation page if enabled.
- A customer that ordered as a guest can now pay an order subsequently, e.g. when the payment method changes.
- An error message has been added that will be displayed when an error occurs during adding items to the shopping cart.

### Fixed

- Due to an error the **My Account** area could not be loaded when loading the orders of a customer.
- Due to an error the route `/wishlist` for the wish list hasn't been active even though it has been activated in the configuration. This has been fixed.
- Due to an error prices with different VAT rated haven't been displayed correctly. This has been fixed.
- Multiple events are now triggered after loggint out of the online store for, e.g. updating the shopping cart.
- An order for which returns are not allowed cannot be accessed directly using the `/returns` route anymore.

## v1.7.2 (2017-11-22) <a href="https://github.com/plentymarkets/plugin-io/compare/1.7.1...1.7.2" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Due to an error, shipping costs weren't displayed correctly on the order detail page and on the order confirmation page. This has been fixed.
- Due to an error, additional item data wasn't displayed in the shopping cart when having more than 10 items in the shopping cart. This has been fixed.

## v1.7.1 (2017-11-17) <a href="https://github.com/plentymarkets/plugin-io/compare/1.7.0...1.7.1" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- The position of a sales price is now taken into account in the front end to ensure the correct display of prices in the online store.
- The minimum order quantity saved for a customer class is now also taken into account.
- Variations that are not linked to the current customer class of the customer, will not be displayed in the variation selection of the single item view.

## v1.7.0 (2017-11-08) <a href="https://github.com/plentymarkets/plugin-io/compare/1.6.2...1.7.0" target="_blank"><b>Overview of all changes</b></a>

### Added

- Customer classes are now taken into consideration when displaying item data in the online store.
- Plugins can now add new values to extend the item sorting in the online store. For further information about this, refer to <a href="https://developers.plentymarkets.com/dev-doc/cookbook#item-sorting" target="_blank">plentyDevelopers</a>.

### Fixed

- The variation setting for unite prices **Show unit price** is now taken into account. When deactivating this setting, the unit price is not displayed in the online store.

## v1.6.2 (2017-10-25) <a href="https://github.com/plentymarkets/plugin-io/compare/1.6.1...1.6.2" target="_blank"><b>Overview of all changes</b></a>

### Added

- Addresses can be saved as a "DHL Packstation" or post office.
- In the Customer Service, the function `hasReturns` was added to show if the customer has any returns.

## v1.6.1 (2017-10-19) <a href="https://github.com/plentymarkets/plugin-io/compare/1.6.0...1.6.1" target="_blank"><b>Overview of all changes</b></a>


### Changed

- The setting **Allow returns** is now carried out in the configuration of the plugin **Ceres**.

### Fixed

- Due to an error, the order overview could not be loaded when an order with an old shipping profile was saved. This has been fixed.

## v1.6.0 (2017-10-16) <a href="https://github.com/plentymarkets/plugin-io/compare/1.5.1...1.6.0" target="_blank"><b>Overview of all changes</b></a>

### Added

- Graduated prices have been integrated.

#### Fixed

- Due to an error the wrong payment method has been saved for an order when paying the order with a payment method using the express checkout. This has been fixed.
- When updating an address, the `FrontendCustomerAddressChanged`event is triggered.
- When creating a return, a new date will be created instead of using the order date for the return.

## v1.5.1 (2017-10-05) <a href="https://github.com/plentymarkets/plugin-io/compare/1.5.0...1.5.1" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- The contact card route is now always correctly available once activated in the IO configuration.

## v1.5.0 (2017-09-28) <a href="https://github.com/plentymarkets/plugin-io/compare/1.4.7...1.5.0" target="_blank"><b>Overview of all changes</b></a>

### Added

- The logic for returning items of an order has been added.
- A method has been added in the `RegisterController`. This allows the use of the Ceres checkout with the old **order process** and the **individual shopping cart** of Callisto.

### Fixed

- Due to an error, the order overview could not be loaded when an order with an old payment method was saved. This has been fixed.
- Due to a randomly occurring error, the checkout could not be opened when ordering as a guest. This has been fixed.

## v1.4.7 (2017-09-20) <a href="https://github.com/plentymarkets/plugin-io/compare/1.4.6...1.4.7" target="_blank"><b>Overview of all changes</b></a>

### Fixed

- Due to an error the unit price wasn’t displayed correctly. This has been fixed.
- Due to an error the payment method wasn’t always selected correctly in the checkout. This has been fixed.
- Due to an error the addresses weren’t always selected correctly in the checkout. This has been fixed.

## v1.4.6 (2017-09-13) <a href="https://github.com/plentymarkets/plugin-io/compare/1.4.5...1.4.6" target="_blank"><b>Overview of all changes</b></a>

### Added

- The search by variation number has been implemented.
- Due to an error the online store wasn’t displayed correctly when the data base table for the wish list was missing. This has been fixed.

## v1.4.5 (2017-09-06)

### Fixed

- Due to an error, the number of items wasn’t displayed correctly in the shopping cart preview. This has been fixed.

## v1.4.4 (2017-08-30)

### Added

- A method has been implemented for sending an email as soon as a customer wants to reset the password.
- A new password for the customer can be saved.

### Fixed

- The variation selection dropdown in the single item view now also displays the attributes of the main variation.

### TODO

- The `password-reset` route must be activated in IO in order to use the **Forgot your password?** feature in Ceres.

## v1.4.3 (2017-08-25)

### Removed

- The unused route `/guest` and `GuestController` have been removed.

## v1.4.2 (2017-08-23)

### Fixed

- When accessing the order overview page with an expired session, a 404 page is shown instead of a twig error.

## v1.4.1 (2017-08-11)

### Added

- The order confirmation link in the order overview of the back end can now also be interpreted.
- `ContactMailService` now accepts a parameter to submit a copy of the contact form to the sender.

### Fixed

- Due to an error, prices of cross-selling items were not displayed. This has been fixed.
- In case of an invalid order confirmation link, a 404 page will be displayed instead of a Twig error.

## v1.4.0 (2017-08-09)

### Added

- The logic and the route `/wish-list` has been added to display a wish list in the online store. **Note:** In order for the migration of the data base table to run correctly, the standard client must be activated and the plugin deployed. After deployment the standard client can be deactivated.
- The logic and the route `/contact` has been added to display a contact page in the online store.
- The `ContactMailService` has been added to process the sending of customer requests via the contact page of the online store.
- A method has been added in the `BasketService` to get the quantity of items in the shopping cart.
- The `NotificationService` has been extended to correctly display error messages in the front-end.
- The link in the order confirmation email now forwards to the order confirmation page of Ceres.

### Fixed

- The language selection in the header of the online store displays languages again.

### Removed

- The logic for item stock has been removed from the `ItemController`. This information is now contained in the `result fields` of ElasticSearch.

## v1.3.2 (2017-07-26)

### Added

- The phone number can now be saved in the `CustomerService`.

### Fixed

- The performance of the order confirmation page has been improved.
- The item images on the order confirmation page are now displayed correctly.

## v1.3.1 (2017-07-21)

### Added

- Order properties of the **Text** type are now processed in the `BasketService` and the `OrderItemBuilder`.
- The route `io/localization/language` has been added. This route can be used to set the language of the online store.

## v1.3.0 (2017-07-13)

### Added

- IO now provides data concerning cross-selling and tags for item lists.
- Templates can now be cached.
- The academic title can now be saved in the `CustomerService`.
- A new event `LocalizationChanged` has been added.
- Multiple conditions for changing the payment method in the **My account** area have been added. The **Allow customer to change the payment method** setting must be activated in the Ceres configuration. Additionally, the order must not be paid yet. The order status must be less than 3.4, or when the order was created the same day the order status must be 5 or less than 3.4.

### Changed

- The online store search will now use the **AND** operator. This replaces the **OR** search that was previously used.
- Editing additional address fields has been optimised in the `CustomerService`.

### Fixed

- Only those item images activated for a client will be displayed in the respective online store.

## v1.2.10 (2017-07-05)

### Added

- The `getCheckoutPaymentDataList` method was added in the `CheckoutService`, to return the `sourceUrl` of a payment plugin.
- It is now possible to set up complex item sorting for the category view and the search by using the recommended sorting options.
- The result of a requested item also contains the formatted item price.

### Changed

- Address fields that are deactivated in the configuration of Ceres but for which validation is activated, will not be validated in the online store anymore.

## v1.2.9 (2017-06-30)

### Fixed

- The translation in the list of payment methods wasn't displayed, when clicking on **Change payment method** in the checkout. This has been fixed.
- In the `TemplateService` the method `isCurrentTemplate` has been added to dynamically request the current template.

## v1.2.8 (2017-06-29)

### Added

- A payment method can be changed subsequently for an order in the **My account** area if this feature is enabled in the payment method.

### Changed

- Variations that are out of stock cannot be added to the shopping cart anymore.
- When selecting a variation that is out of stock the customer will be forwarded to the next variation with stock.

### Fixed

- Due to an error, a deleted address was not removed from the address list. This has been fixed.
- Due to an error the address could not be edited when ordering as a guest. This has been fixed.

## v.1.2.7 (2017-06-21)

### Fixed

- During registration, when the customer enters an invoice address, the entered address is not automatically saved as the delivery address.

## v1.2.6 (2017-06-14)

### Fixed

- Due to an error, the validator for invoice and delivery addresses for the country of delivery **United kingdom** did not work properly. This has been fixed.

## v1.2.5 (2017-06-08)

### Added

- Countries of delivery and online store settings are now loaded from the cache to improve the overall performance.

### Fixed

- Due to an error the default country of delivery has not been set. This has been fixed.

## v1.2.4 (2017-06-02)

### Added

- A Twig filter for sorting an object by a given key has been added.
- Validation of the address form for the delivery country **United Kingdom**

## v1.2.3 (2017-05-19)

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
