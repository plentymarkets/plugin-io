# Release Notes for IO

## v5.0.56 (2022-XX-XX) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.55...5.0.56" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- On the "Forgot password" page, an error message is only displayed if there is a problem with dispatching the e-mail.

## v5.0.55 (2022-09-22) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.54...5.0.55" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- The preview of the order confirmation page was not displayed in the ShopBuilder due to an error related to sample prices. This has been fixed.

## v5.0.54 (2022-08-08) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.53...5.0.54" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### TODO

- If you are using the plentyShop contact form (either the standard template or via a ShopBuilder content), please make sure that the route `/contact-mail-api` is activated in the IO plugin. To check this, open the IO setting in your plugin set. Open the tab **Configuration**. In the setting **Activate routes**, activate the route `/contact-mail-api` and save your changes. If you do *not* use a plentyShop contact form, please make sure that the route `/contact-mail-api` is deactivated.

### Added

- The route `/contact-mail-api` has been added. You can use it to activate and deactivate the sending of mail via the contact form independently of the `/contact` route.

### Fixed

- For multilingual shops, errors could occurr in regards to the shopping cart URL. This has been fixed.
- If pages were called with parameters that were excluded from ShopBooster, faulty markup could be generated. This has been fixed.
- On mobile devices, the combination of language selection and ShopBooster could result in the mobile navigation being displayed in the previously selected language. This behaviour has been fixed.
- Item sets with set components that contained order characteristics could cause an incorrect display of the value of goods. This has been fixed.

## v5.0.53 (2022-07-04) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.52...5.0.53" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Behoben

- The setting **Forward to login page after clicking link in order confirmation** has been added back to plentyShop LTS settings and plentyShop wizard. These settings were removed in version 5.0.52, which resulted in the order confirmation of manually created orders not being accessible. We have therefore reverted this change.

## v5.0.52 (2022-06-29) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.50...5.0.52" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- The setting **Forward to login page after clicking link in order confirmation** has been removed from the plentyShop LTS settings and the plentyShop assistant. Now, the default behaviour is that customers are always forwarded to the login page. 

### Fixed

- Category filters were not displayed in the ShopBuilder. This has been fixed.
- We fixed an error concerning the generation of the backlink when users were forwarded from the order confirmation to the login page.

## v5.0.50 (2022-05-04) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.49...5.0.50" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### TODO for external developers

- The loading of the shopping cart has been removed from the `BasketController` because the shopping cart is already loaded in the `GlobalContext`. External developers who overwrite the `GlobalContext` in their theme and have removed the shopping cart call must add this call again. Otherwise errors may occur when loading the shopping cart.

### Changed

- The logic for checking the validity of an order confirmation page was transferred from the plugin to the core.
- The amount already paid is now displayed on the order confirmation page.
- The order confirmation page can now display multiple redeemed coupon values.
- The IO plugin is now compatible with PHP 8.
- Order properties and characteristics configured as additional costs are now shown as separate items in the totals.
- For order properties and characteristics, it is now displayed in the single item view, in the shopping cart, and in the order confirmation whether the costs are inclusive or additional.
- Required, pre-selected order properties that have been configured as additional costs are now displayed without a checkbox below the item price on the single item view.

### Fixed

- Shipping costs could be displayed incorrectly in the shopping cart if a sales coupon affected both the value of goods and the shipping costs. This has been fixed.
- For addresses that contained a postcode with white spaces, incorrect links could occur for the shipment tracking function. This has been fixed.

## v5.0.49 (2022-04-11) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.48...5.0.49" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- An error with inheritance from outer to inner sorting was fixed.

## v5.0.48 (2022-03-21) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.46...5.0.48" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- Due to an error, categories were unintentionally not visible. This has been fixed.
- A fix in version 5.0.46 led to another error. This has been fixed.

## v5.0.46 (2022-02-24) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.45...5.0.46" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- In the newsletter registration, first and last names are now checked for invalid characters in order to prevent spam.

### Fixed

- For the route `/place-order`, the VAT identification number is now checked for length before it is validated.
- The setting **Show categories as filter options for search results** has been renamed and an error that occurred during saving was fixed.
- The URL of the domain is now added to the sitemap.
- Due to an error, selectable values of order properties were incorrectly displayed in the **My Account** area and on the order confirmation page.
- In the shopping cart, coupons were always displayed with gross values, even if the order was an export delivery. This has been fixed.
- Due to an error, single item pages were not always detected by the plugin. This has been fixed.
- When a user changes their password, the affected account is now logged out of all linked devices.

## v5.0.45 (2022-01-18) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.44...5.0.45" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- Under certain circumstances the wish list did not display all relevant items for customers who were logged in. This has been fixed.
- Executing the session REST call now also detects the language in order to ensure that ShopBooster works properly for multilingual shops.  

## v5.0.44 (2021-12-27) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.43...5.0.44" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- Under certain circumstances, the shopping cart was not displayed correctly. This has been fixed.
- Under certain circumstances, the variable `CategoryController::$LANGUAGE_FROM_URL` was filled with the wrong values. This has been fixed.

## v5.0.43 (2021-11-30) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.42...5.0.43" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- We fixed an error in the CategoryService.

## v5.0.42 (2021-11-15) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.41...5.0.42" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- The meta data for files in the webspace are now collected from the plentymarkets core, which leads to an improved TTFB.
- Additional category data is now loaded via the lazyloader, in order to reduce database traffic.
- The performance of data procurement for linked variations in ShopBuilder has been improved. 

### Fixed

- Invalid items were not removed from the shopping cart when a user changed the country of delivery. This has been fixed.
- The data of properties is now correctly displayed for items in **Last seen** item lists.
- If errors occur during the initialisation of the context classes, the corresponding page is no longer cached in ShopBooster.

## v5.0.41 (2021-10-20) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.40...5.0.41" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

* Package components are now added to the shopping cart with the correct quantity if the option **Replace item package with basic item** is active.
* When adding set items to the shopping cart, the set components were not included the first time. This has been fixed.
* Due to an error, no value was displayed for order properties of the type **Selection**. This has been fixed.
* When a value was selected from the category facet, it was not displayed as selected.

## v5.0.40 (2021-10-05) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.39...5.0.40" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### ToDo

* In order to provide the redirect upon detected diverging browser langage to your customers, you need to implement the ShopBuilder widget **Language detection**.

### Changed

* plentyShop is now able to react to a future order setting with which prefixes for item bundles and components can be customised. Note that changing these prefixes may lead to a faulty display of older orders.
* The redirect for automatic browser language detection has been deactivated.

### Fixed

* Tag result pages were not always treated as search result pages. This behaviour has been fixed.

## v5.0.39 (2021-09-13) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.38...5.0.39" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- It's no longer possible to send the contact form if reCaptcha is active and the corresponding cookie was not accepted by the user.

## v5.0.38 (2021-08-31) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.37...5.0.38" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- For the changing of payment methods, the `accessKey` for the order is now also passed. 

## v5.0.37 (2021-08-17) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.36...5.0.37" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- The pagination on the results page of a tag route was partially faulty. This has been fixed.
- The pagination on the search results page was partially faulty. This has been fixed.

## v5.0.36 (2021-08-05) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.35...5.0.36" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- Category data is now loaded via new interfaces in order to improve performance.
- The loading of customer data, the shopping cart, and shopping cart items are now subsumed under a single query.

### Fixed

- The correct canonical URL is now generated for the homepage category.

## v5.0.35 (2021-07-12) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.34...5.0.35" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- It was not possible to save an empty string in the input field **Thousands separator** if the option **No** had been selected for the setting **Use customer-specific price format** in then number formats section of the IO settings. This has been fixed.
- An incorrect canonical URL was used for the search results page. This has been fixed.

## v5.0.34 (2021-06-28) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.33...5.0.34" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- When creating and updating addresses, the field **Email** can now be used to influence address options.

## v5.0.33 (2021-06-014) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.32...5.0.33" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added 

- The method `getVariations` in the ItemService can now overwrite the result fields of the search result via a parameter. 

### Fixed

- Components of item sets now contain values in order characteristics of the type selection.
- Due to an error, shipping costs were displayed incorrectly on the order confirmation page. This has been fixed.
- An order's date of creation was not updated on the order confirmation page and in the My Account area if it had been changed in the backend. This has been fixed. 
 
## v5.0.32 (2021-06-01) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.30...5.0.32" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- We added a Twig helper function which allows filtering of item variations.

### Fixed

- In the translations of the subject of the contact form, not all entered data could be accessed. This has been fixed.

## v5.0.30 (2021-05-14) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.29...5.0.30" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- The shop performance regarding the loading of the number of items in the shopping cart has been improved.

## v5.0.29 (2021-05-11) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.28...5.0.29" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- ShopBooster now also caches pages with query parameters.

### Changed 

- The performance of the detection of the current page type has been improved.

## v5.0.28 (2021-04-20) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.26...5.0.28" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

- The routing for 404 pages returned the wrong HTTP status code. This behaviour has been fixed.
- Payment method dependent surcharges and rebates are now taken into account if the payment method of an already existing order is changed.  
- Under certain circumstances, the currency was displayed incorrectly in the order overview in the My Account section. This behaviour has been fixed.

### Changed

- The country-specific prefix of the VAT identification number is now validated.

## v5.0.26 (2021-04-06) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.25...5.0.26" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- The event `AfterBasketItemUpdate` now also contains the updated base price.

## v5.0.25 (2021-03-22) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.24...5.0.25" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- For export deliveries, shipping costs were sometimes displayed incorrectly on the order confirmation page. This has been fixed.

## v5.0.24 (2021-03-08) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.23...5.0.24" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- In the checkout, the existing shopping cart is no longer emptied, but replaced by a new empty one instead. This speeds up order creation especially for bigger shopping carts.

### Fixed 

- In case the routes /login and /register were not linked to a category, they were not listed in the sitemap. This has been fixed.
- When linking a category, the 404 route wasn't identified correctly. This has been fixed.
- Search suggestions partially showed different results. This depended on the usage of upper and lower case letters.

## v5.0.23 (2021-02-22) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.22...5.0.23" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added 

- The item list widget now contains the option to create a list that contains items from the entire range of products.

### Changed

- The sorting of countries of delivery is now rendered server-side.

### Fixed 

- Login pages that were not created with the ShopBuilder did not have a canonical tag. This behaviour has been fixed.

## v5.0.22 (2021-02-11) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.21...5.0.22" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- Under certain circumstances the order confirmation displayed the wrong order status. This behaviour has been fixed.

## v5.0.21 (2021-02-09) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.20...5.0.21" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### TODO

- If your theme uses the function `getVariationList($itemId, $withPrimary)` from the `ItemService`, you need to check whether the second parameter `$withPrimary` is interpreted as expected.

### Added

- The email attachment widget was added to the ShopBuilder. The widget makes it possible to attach files to emails that are sent via the contact form.

### Fixed 

- Opening single item views in categories without items could lead to errors in the ShopBuilder. This behaviour has been fixed.
- Due to an error, empty values could not be saved for individual input fields of existing addresses. This behaviour has been fixed.
- The function `getVariationList($itemId, $withPrimary)` in the `ItemService` misinterpreted the second parameter. As a result, `$withPrimary == true` returned only sub-variations and `$withPrimary == false` also returned the main variation. This has been fixed.

## v5.0.20 (2021-01-19) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.19...5.0.20" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed 

- The information text for export deliveries has not been displayed correctly since 01 January 2021. This behaviour has been fixed.
- The returns history displayed shipping costs as items. This behaviour has been fixed.

## v5.0.19 (2021-01-05) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.18...5.0.19" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- In order to improve webshop performance, the caching time of the shop URLs that are linked in the ShopBuilder settings, which was introduced in version 5.0.7, has been increased from 5 minutes to 10 minutes. 

## v5.0.18 (2021-01-04) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.17...5.0.18" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added 

- You can now determine the search operator of the online shop search in the Ceres assistant. You can either select an **And** or an **Or** operator.

### Changed

- The order sums now include the additional costs of characteristics.

## v5.0.17 (2020-12-21) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.16...5.0.17" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- Code documentation as been updated in a number of placed by adding viable input types.

## v5.0.16 (2020-12-01) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.15...5.0.16" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- The newsletter widget now uses Google reCAPTCHA.

### Changed

- Under certain circumstances, the order confirmation could contain rounding errors. This behaviour has been fixed.
- For a default country of delivery that supports export deliveries, gross prices were displayed when the online shop was accessed the first time. This behaviour has been fixed.
- Under certain circumstances, the language selection could lead to errors in combination with URLs. This has been fixed.
- If the currency settings in the Ceres plugin had never been saved, it was possible to set an invalid currency via a currency parameter. This behaviour has been fixed.

## v5.0.15 (2020-11-09) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.14...5.0.15" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- Rounding prices with more than 2 decimal places could lead to errors. This has been fixed. 
- The properties of the initially displayed variations of item sets were not loaded correctly. This has been fixed.
- Under certain circumstances, accessing the order confirmation page could lead to error notifications in the log. This has been fixed.
- The ShopBuilder did not display all sample data of an item, if this data had the value 0. This has been fixed.

## v5.0.14 (2020-10-20) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.13...5.0.14" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- The performance of events for additional sorting has been improved.

### Fixed

- Several places in the online store did not consider currency icons for displaying currencies.
- Additional URL parameters were missing when redirecting to the ShopBuilder search results page. This has been fixed.
- Users who changed the language in the checkout or my account area were redirected to the homepage. This has been fixed. From now on, users who change the language are redirected to the corresponding page as long as this page has been translated for the language.
- If the shopping cart contained an inactive item, incorrect totals were displayed. This behaviour has been fixed.
- Under certain circumstances, company addresses were not correctly validated, even though all input fields had been filled out correctly. This behaviour has been fixed.
- The recognition of the template type in the ShopBuilder has been corrected.

## v5.0.13 (2020-09-28) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.12...5.0.13" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added 

- The class `ShopUrls` now includes the function `isLegalPage`. This function states whether the current page is a legal page, such as the cancellation rights or the terms and conditions.

### Fixed

- The step-by-step navigation calculated incorrect values. As a result, the "Load more" button was not always displayed. This has been fixed.
- The selected sorting was not working properly in the live shopping widget. This behaviour has been fixed.
- Due to an error, gross prices were displayed for export deliveries upon initial access of the online store. This has been fixed.
- When accessing the route `/tag/tagname`, **tagName** was not added as a query parameter for the ShopBuilder search results page. This behaviour has been fixed.

## v5.0.12 (2020-09-14) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.11...5.0.12" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- The Twig function `query_string()` now only considers parameters from the URL query.
- During the creation of an order, the customer wish was not passed on correctly if the customer had aborted the PayPal payment procedure. This behaviour has been fixed.
- The order history displayed the wrong value of goods if an order had been created that included a customer class rebate. This has been fixed.
- A notification is now displayed when an invalid gift card is removed from the shopping cart.
- Under certain circumstances, the SingleItem template could not be detected, which could lead to display errors. This has been fixed.
- Certain combinations of settings could lead to errors concerning the splitting of item bundles for returns. This has been fixed.
- In the registration process, it was possible that a contact was created, even if a contradictory error message had been displayed. This behaviour has been fixed.

## v5.0.11 (2020-09-01) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.10...5.0.11" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- Blog entries of the Callisto blog were not correctly displayed if the route "Page not found" had been activated. This has been fixed.

## v5.0.10 (2020-08-27) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.9...5.0.10" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- URLs with more than 6 segments are now correctly recognized and displayed as 404 pages.

## v5.0.9 (2020-08-25) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.8...5.0.9" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- The meta title for the item view can now be set to one of the 3 item names using a new setting in the Ceres assistant. This setting also controls which of the item names is used when generating the item URL.
- The setting **pass through /blog to Callisto** was added to still display the blog from the old CMS despite the active category route.

### Fixed

- Filters were not displayed in the tag search and the sorting did not work correctly. This has been fixed.
- Due to an error not all data was displayed in the **Order return** widget. This has been fixed.
- If the setting to split item bundles is active, no error was displayed on the item page if an attempt was made to put more stock of an item into the basket than was available. This behavior has been fixed.
- On the order confirmation page, it could happen that the wrong sales tax was displayed when paying with a foreign currency. This behavior has been fixed.
- In the list of shipping profiles, under a certain constellation, it could happen that wrong prices were displayed. This behavior has been fixed.

## v5.0.8 (2020-08-05) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.7...5.0.8" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- The tag route was only registered for the activated default search. If a category was linked to the search, the route was not registered. This behaviour has been fixed.
- The widget step by step navigation no longer renders placeholders if no child categories exist for the current category.
- In version 5.0.7 we added caching for online store URLs. This could lead to problems for systems with multiple clients. The caching behaviour has been adjusted.
- If the referrer is changed in the frontend, all items, for which no sales price has been stored for the new referrer, are now removed from the shopping cart.

## v5.0.7 (2020-07-28) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.6...5.0.7" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- We've made a change that improves the online store performance. Due to this change, the link between category ID and ShopBuilder page, which is set up in the IO or ShopBuilder settings, will be cached for 5 minutes. Because of this, changes to the link or to the URLs of linked pages can be displayed in the online store with a delay.

### Fixed

- If a header or footer content was linked to all categories of the type **Item category**, the header or footer was not displayed. This has been fixed.
- Due to an error, the quick search could lead to the ShopBuilder not loading. This behaviour has been fixed.
- When a user changed the country of delivery, net and gross prices were displayed incorrectly in some cases. This has been fixed.
- Static pages of second clients used the meta description and meta keywords of the main client if the category routes were deactivated. This has been fixed.

## v5.0.6 (2020-07-21) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.5...5.0.6" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed 

- Search suggestions also included categories that were not visible for the current client. This behaviour has been fixed.
- URL redirections in the online store are now executed with the HTTP status code 301. Priorly, redirections had been executed with the HTTP status code 302.

## v5.0.5 (2020-06-30) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.4...5.0.5" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- The URLs for search suggestions for items and categories were generated without taking the currently selected online store language into account. This has been fixed.
- Under certain circumstances, the customer class of a contact could reset to the default customer class. This has been fixed.
- Under certain circumstances, contact data could be overwritten by address data. This has been fixed.
- Static pages were included in the sitemap even if these pages had already been linked to a ShopBuilder category. This behaviour has been fixed.

## v5.0.4 (2020-06-08) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.3...5.0.4" target="_blank" rel="noopener"><b>Overview of all changes</b></a>


### Fixed

- The additional costs for deliveries outside of the European Union Customs Union were displayed incorrectly. This behaviour has been fixed.

## v5.0.3 (2020-06-02) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.2...5.0.3" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- After changing the email address in the My Account area, it was possible that orders were created with the former email address. This behaviour has been fixed.
- The type of the current template is now also recognised for URLs with a trailing slash.
- Under certain circumstances, not all layout containers were filled by payment plugins on order confirmation pages that had not been created via the ShopBuilder. This has been fixed.
- The link to the order confirmation page in the My Account area was incorrect if the order confirmation page had not been created via the ShopBuilder. This has been fixed.

## v5.0.2 (2020-05-12) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.1...5.0.2" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- We improved performance during the completion of orders.

### Fixed 

- When a customer accessed a page for changing their email address or password that had been created with the ShopBuilder, the customer was not automatically logged out. As a result, input fields were not visible. This has been fixed.
- If a user was already logged in, additional attempts to log into the online store could, under certain circumstances, lead to the deletion of addresses. This behaviour has been fixed.
- Surcharges were not taken into account for the value of items on the order confirmation page. This behaviour has been fixed.

## v5.0.1 (2020-04-27) <a href="https://github.com/plentymarkets/plugin-io/compare/5.0.0...5.0.1" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- The deprecated route **/io/facets** and the corresponding resource **FacetResource** have been removed from the plugin.

### Fixed 

- Returns pages that are created via the ShopBuilder are now correctly displayed, even if the setting **Category routes** in IO is inactive.
- Pages which display success, error, or warning notifications are no longer written into the cache by the ShoBooster.
- Due to an error, a 404 page was displayed for multilingual homepages if a category without a URL had been linked in the ShopBuilder. This behaviour has been fixed.
- Due to an error, items were removed from the shopping cart when a customer logged out of the online store. This behaviour has been fixed.
- Upon a failed attempt to login, a notification regarding shipping cost calculation was displayed. This has been fixed.

## v5.0.0 (2020-04-14) <a href="https://github.com/plentymarkets/plugin-io/compare/4.6.4...5.0.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- An additional search is now carried out for misspelled search terms. The search results page now provides an alternative search term in the "Did you mean...?" message.

### Changed

- The behaviour of the canonical tag and the robots information on category and search result pages has been revised.
- Variation properties are now included in the data of the order confirmation page.

### Fixed

- Changing the currency could lead to errors in the sum calculation of the shopping cart if no price had been stored for the item in the corresponding currency. These items are now removed from the shopping cart and a corresponding message is displayed.
- The browser language is set as the online store language upon the initial loading of the online store. If the browser language differed from the default online store language and a URL without a language abbreviation affix (e.g. /de or /en) was opened, the corresponding page could not be found. This behaviour has been fixed.

## v4.6.4 (2020-02-27) <a href="https://github.com/plentymarkets/plugin-io/compare/4.6.3...4.6.4" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- The registration and contact forms could not be sent if Google reCAPTCHA and the Ceres setting **Block unaccepted cookies** were active and the user had not yet accepted the reCAPTCHA cookie. This behaviour has been fixed. 

## v4.6.3 (2020-02-24) <a href="https://github.com/plentymarkets/plugin-io/compare/4.6.2...4.6.3" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed 

- Properties are now included again in the item data for all view. Property data had previously been removed from all views except the single item view.

## v4.6.2 (2020-02-19) <a href="https://github.com/plentymarkets/plugin-io/compare/4.6.1...4.6.2" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- Due to an error pertaining to the category filter, the pagination was not working as intended. This has been fixed.

## v4.6.1 (2020-02-18) <a href="https://github.com/plentymarkets/plugin-io/compare/4.6.0...4.6.1" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- Due to an error, search results were not output correctly if no page for search results had been linked via the ShopBuilder. This has been fixed.
- Due to an error pertaining to delivery addresses, customers with guest accounts were unable to place orders in the checkout. This has been fixed.

## v4.6.0 (2020-02-17) <a href="https://github.com/plentymarkets/plugin-io/compare/4.5.1...4.6.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### TODO

- Google reCAPTCHA is now only carried out after the online store user accepts the corresponding cookies. Forms that are subject to reCAPTCHA, such as the contact form or the customer registration, can therefore only be sent after the user's consent.

### Added 

- You can now add a reference numeral for customers when creating orders.
- The assistant now contains a setting via which you can activate the VAT number verification for the checkout, the creation of new addresses and changes to existing addresses.

### Changed

- The OrderReturnController.php now passes the category on to the frontend if a category has been linked to the returns page in the ShopBuilder.
- Properties are no longer output in the data set for item lists in order to reduce the quantity of data.
- The transmitted data for items with order characteristics has been optimised.
- The ItemImageFilter now also outputs the alternative text and name of the image.
- The live shopping widget now displays an offer as ended if the corresponding item's stock is limited to net stock and the stock is depleted.
- If a Google reCAPTCHA-related error occurs, a corresponding error notification is displayed.

### Fixed 

- A permanent dependency to the Ceres plugin has been removed.
- Updating the number of items in the shopping cart did not remove redeemed coupons with minimum order value. This behaviour has been fixed.
- The settings for paddings did not affect list elements of the navigation tree widget that were loaded at a later time. This has been fixed.
- The navigation bar no longer displays categories if no category type has been activated in the Ceres setting **Type of categories rendered in the navigation**.
- Under certain circumstances, a redeemed coupon code was not included in the order in the frontend. This has been fixed.
- Orders of the type warranty are now displayed and can be returned in the my account area.
- When a user was redirected to a login page that had been created with the ShopBuilder, the user was not redirected to the checkout after they have logged in. This has been fixed.


## v4.5.1 (2020-01-28) <a href="https://github.com/plentymarkets/plugin-io/compare/4.5.0...4.5.1" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed 

- Order documents of guest account orders can now be accessed again via the link in the order confirmation.

## v4.5.0 (2019-12-19) <a href="https://github.com/plentymarkets/plugin-io/compare/4.4.3...4.4.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added 

- The Ceres assistant now includes settings for the variation selection, with which the "Please select" option can be added and preselected.
- The route `/rest/io/categorytree/children` has been added. It serves to return subcategories of a category.
- The route `/rest/io/categorytree/template_for_children` has been added. It serves to return rendered markup of the navigation tree widget.

### Changed

- The route `io/facet` has been marked as `deprecated`.
- Facet values are now returned in a sorted fashion.

### Fixed

- No address was preselected in the address selection. This behaviour has been fixed.
- Under certain circumstances, item URLs were generated incorrectly. This behaviour has been fixed.
- Routing errors could occur if a category, for which no translations had been stored, was linked via the ShopBuilder. This behaviour has been fixed.
- The shopping cart was displayed incorrectly if changing the language also entailed a change of currency and country of delivery. This behaviour has been fixed.

## v4.4.3 (2019-11-29) <a href="https://github.com/plentymarkets/plugin-io/compare/4.4.2...4.4.3" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed 

- Due to an error, ShopBooster was unable to write item category pages to the cache. This has been fixed.

## v4.4.2 (2019-11-28) <a href="https://github.com/plentymarkets/plugin-io/compare/4.4.1...4.4.2" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed 

- Due to an error, ShopBooster was unable to write item category pages to the cache. This has been fixed.

## v4.4.1 (2019-11-19) <a href="https://github.com/plentymarkets/plugin-io/compare/4.4.0...4.4.1" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed 

- Due to an error, order characteristics were not displayed in the shopping cart and the checkout and were not included in the order. This behaviour has been fixed.

## v4.4.0 (2019-11-14) <a href="https://github.com/plentymarkets/plugin-io/compare/4.3.4...4.4.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added 

- The ShopBuilder now enables the display of properties of the type **file** in the single item view.

### Changed 

- During the completion of the order, the required item data is now loaded from the database in order to reduce susceptibility to errors.
- The event **AfterBasketChanged** now also contains basketItems.

### Fixed 

- If an order was split via an event procedure, an incorrect order was displayed in the order confirmation. This has been fixed.
- Items for which the setting **Promotional coupon/POS discount: Purchasable with coupon only** is active can no longer be bought if no promotional coupon has been redeemed.
- If the trailing slash option for URLs was active, the order confirmation page could lead to a redirection error. This has been fixed.
- Due to an error, the mobile navigation did not display categories if other categories of the same branch were not included in the link list. Now, all categories of the first level are displayed.
- Characteristics for which the setting "Display as additional costs" (deposit) is active are now correctly included in the order items and are added to the totals of the order.
- If an error occurs while loading item data, the ShopBooster no longer writes the corresponding template into the cache.
- Metadata is now output correctly.
- Under certain circumstances, shipping costs were displayed incorrectly. This behaviour has been fixed.
- Due to an error, trailing slashes were appended to URLs that contained query parameters. This behaviour has been fixed.
- Under certain circumstances, orders were created twice. This behaviour has been fixed.
- For Callisto stores that use the Ceres checkout, the order confirmation could not be displayed if it had been created with the ShopBuilder. This has been fixed.
- Using a ShopBuilder category as the homepage could sometimes lead to the loading of a wrong header. This has been fixed.

## v4.3.4 (2019-10-30) <a href="https://github.com/plentymarkets/plugin-io/compare/4.3.3...4.3.4" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- In the case of two consecutive orders in the online store, the list of payment methods was not loaded correctly when accessing the checkout. This behaviour has been fixed.

## v4.3.3 (2019-10-17) <a href="https://github.com/plentymarkets/plugin-io/compare/4.3.2...4.3.3" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### TODO

- In order to use IO 4.3.3, you need to update the plugin Ceres to its current version 4.3.4.

### Fixed 

- The ShopBuilder displayed a 404 page if a category was linked as the returns page. This has been fixed.
- Due to an error, the order confirmation page was not displayed if a ShopBuilder category with the URL slug "/confirmation" was created and linked.
- Due to an error, the link on the returns page was not working if the link was accessed without additional parameters. This has been fixed.
- Guest accounts could not access order documents. This has been fixed.
- Checkboxes that were configured via the assistant could not be interpreted correctly. This has been fixed.
- Under certain circumstances, the number of log entries could increase significantly. This behaviour has been fixed.
- The components of item bundles were displayed incorrectly. This has been fixed.

## v4.3.2 (2019-10-02) <a href="https://github.com/plentymarkets/plugin-io/compare/4.3.1...4.3.2" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- Due to an error, the registration page could not be opened via the header of the online store. This has been fixed.

## v4.3.1 (2019-10-01) <a href="https://github.com/plentymarkets/plugin-io/compare/4.3.0...4.3.1" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- Due to an error, language-specific URLs could not be determined correctly. This has been fixed.
- Due to an error, the mobile navigation could not be loaded correctly. This has been fixed.

## v4.3.0 (2019-09-26) <a href="https://github.com/plentymarkets/plugin-io/compare/4.2.0...4.3.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added 

- Guest accounts can now return orders.
- We added the link button widget to the ShopBuilder. It serves to provide buttons that link to returns and shipment tracking.
- We added a REST route for unsubscribing from individual newsletters.

### Changed

- The data of the class **LocalizedOrder** now also contain the attributes of the variation.
- The amount of data transferred during changes to the shopping cart has been reduced in order to improve the performance of the online store.
- Objects created via **pluginApp** are now stored in their variables before they are processed further. The direct use of new instances could, under certain circumstances, lead to errors during the publishing of plugins.
- The function `getBasketForTemplate` now adds the field "isExportDelivery" to the shopping cart. This contains the information whether the combination of shop location and selected country of delivery constitutes an export delivery.
- The REST route `io/itemWishList` now returns the entire item data of variations in the wish list instead of the variation IDs.
- The shopping cart data now contains the IDs of variations in the wish list.
- The ShopBooster now also caches the wish list view.
- If a customer unsubscribes from a newsletter, they now only unsubscribe from one newsletter, depending on the link they followed. Previously, a customer unsubscribed from all newsletters at once. 

### Fixed 

- The dynamic grouping of variations now considers the result fields correctly and no longer loads unnecessary data.
- The amount of properties per group that can be used as placeholders in the ShopBuilder is no longer limited to 50.
- Due to an error, duplicate values were removed from URL parameters. This has been fixed.
- Cross-selling item lists displayed variations for which the setting "invisible in item lists" was active. This behaviour has been fixed.
- Due to an error, facet values were displayed even though they did not meet the minimum number of hits. This behaviour has been fixed.
- Internal links now always redirect to the secure HTTPS domain, if it is available.
- During loading of the mobile navigation, categories without names are filtered out of the result.
- Due to an error, some attributes could not be selected in the single item view. This has been fixed.

## v4.2.2 (2019-08-21) <a href="https://github.com/plentymarkets/plugin-io/compare/4.1.2...4.2.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- The static pages for cancellation form, cancellation rights, general terms and conditions, privacy policy and legal disclosure can now be created and edited with the ShopBuilder.

### Fixed

- Under certain circumstances, the display of base prices could lead to errors. This behaviour has been fixed.
- Changing the item quantity in the shopping cart in connection with a coupon could cause problems if the minimum order value was not reached. This has been fixed.
- For some languages, a space character was missing between the ISO code of the currency and the amount in the shopping cart. This has been fixed.
- Removing items could lead to errors if a coupon had already been redeemed. This behaviour has been fixed.
- Due to an error in the address validation, entering a company address could under certain circumstances lead to the issuing of an error notification, in which case the address was not saved. This has been fixed.
- Shipping methods were not refreshed when the country of delivery was changed. This has been fixed.

## v4.1.2 (2019-07-16) <a href="https://github.com/plentymarkets/plugin-io/compare/4.1.1...4.1.2" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- We added the new REST route 'rest/io/categorytree' for loading categories of the mobile navigation.

### Changed

- Attributes of variations without stock are now displayed if the options "Available automatically if net stock is positive" and "Not available automatically if net stock is 0 or negative" are deactivated.

## v4.1.1 (2019-07-10) <a href="https://github.com/plentymarkets/plugin-io/compare/4.1.0...4.1.1" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Fixed

- Due to an error, the IO route “Page not found” in the **Routing** section was also activated for Callisto online stores that use the Ceres checkout. This behaviour has been fixed.

## v4.1.0 (2019-07-08) <a href="https://github.com/plentymarkets/plugin-io/compare/4.0.1...4.1.0" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Added

- Plugins can now specify data fields that are always loaded, independent from the Result Fields template.
- Meta information of files stored in the new Webspace can now be read within the template via the Twig function `cdn_metadata()`.
- We added the TWIG filter 'propertySelectionValueName'. This returns the name of a value of an order property of the type **selection**.
- You can now determine whether the IO 404 page should be displayed in the **routing** tab of the IO configuration.

### Changed

- The variation selection in the single item view has been remodeled on the basis of ElasticSearch technology in order to increase performance.
- The "Change password" function in the My Account area now validates the password on the side of the server according to our specifications.
- The error notification that is displayed when the minimum order value is not met has been adjusted and now contains the minimum order value.
- The setting "Enable selection of variations without stock in variation dropdown" in the Ceres configuration has been marked as "deprecated" and is no longer considered by the variation selection widget.

### Fixed

- The input field for dates is now correctly validated on the server side.
- Due to an error, clicking the "Order process" button in the order history did not open a link if the homepage was deactivated. This has been fixed.
- The wish list's quantity indication also counted inactive items. This behaviour has been fixed.
- Item list widgets for which the option **manufacturer** was active ignored the sorting options. This behaviour has been fixed.
- Due to an error, URLs of categories were not generated correctly if the category's name began with the same letters as the language code of the currently selected language.
- The error "Resource not found" could occur in the order overview if the shipping profile specified in the order was no longer available in the system. This behaviour has been fixed.
- The error "Resource not found" could occur in the order confirmation if the order's status was no system status. This behaviour has been fixed.
- The labels of order properties are now displayed in the correct language.
- The calculation of graduated prices for variations with order characteristics could lead to errors. This behaviour has been fixed.
- The display of gross and net prices in combination with the invoice address's VAT identification number could lead to errors. This behaviour has been fixed.
- If an error occurs during payment after an order has been completed, the order can only be finalised after a waiting period of 30 seconds. This prevents the creation of duplicate orders that would thereby be invalid.
- Accessing categories that ended in the URL slug a-XXX would sometimes redirect to a 404 page or a single item view. This behaviour has been fixed.
- We fixed an error due to which the routes /checkout and /my-account did not redirect to the corresponding ShopBuilder content.
- The language of emails sent via the online store now corresponds with the currently selected language in the online store.
- Due to an error, pages would not load if the TWIG function `queryString` was called with an invalid parameter. This has been fixed.

## v4.0.1 (2019-05-14) <a href="https://github.com/plentymarkets/plugin-io/compare/4.0.0...4.0.1" target="_blank" rel="noopener"><b>Overview of all changes</b></a>

### Changed

- The method `createContact()` in the class CustomerService was modified to include the possibility of adding a customer language in the transmitted data if the language that is currently selected in the online store should not be used.

### Fixed

- For orders placed by guest customers, changing the shipping profile could lead to errors. This behaviour has been fixed.
- Redirects in the online store now correctly consider the language.

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
