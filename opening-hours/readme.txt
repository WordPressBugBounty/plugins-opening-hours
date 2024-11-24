=== We’re Open! ===

Contributors: designextreme
Donate link: https://paypal.me/designextreme
Tags: opening hours, open hours, business hours, open times, opening times
Requires at least: 5.3
Tested up to: 6.7.1
Stable tag: 2.3
Requires PHP: 5.2.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Opening hours for your business, a joy to manage and highly customizable. Conditional excerpts; conditional/replacement text; Structured Data for SEO.

== Description ==

Control all aspects of your business’ opening hours with this very easy management tool with multiple display options and conditional text while open or closed with renewed content upon status changes. This plugin offers a high degree of customization and can be placed almost anywhere using a shortcode or widget.

= Features: =

*   **Shortcode and Widget** to display your opening hours in a wide variety of formats
*   Responsive interface to quickly set regular opening hours separate from special opening hours for public holidays
*   Set up to 3 groups of opening times per day
*   Set 24 hour opening times
*   Set a date range for a temporary closure of your business
*   [Consolidation of hours](https://demo.designextreme.com/were-open/#consolidation) with options for:
	* Just weekdays,
	* Just the weekend,
	* Separate weekdays and the weekend,
	* Full consolidation
*   Very high level of customization such as:
	* Separator characters,
	* Extensive day and time formatting,
	* Prefix and suffix for each group of times,
	* Local language day names with option to overwrite,
	* Consolidation words,
	* Hiding of closed days,
	* Start the week to any day including the current day,
	* Regular or special opening hours only,
	* Separate day/date formatting for regular and special opening hours,
	* Date labeling and notes for special opening hours,
	* Date ranges, and much more…
*   Option to refresh opening hours or reload the page at the start of each day
*   HTML classes to give high-level of design customization (e.g. *past*, *today*, *tomorrow*, *future*, *special*)
*   Right To Left (RTL) language support
*   [**Conditional shortcode**](https://demo.designextreme.com/were-open/#open-now) to show content only when open, closed or [special opening hours](https://demo.designextreme.com/were-open/#open-special)
	* Automatic data refreshing or page reload occurring with a change of open or closed status
	* Conditionally show HTML containing special opening hours – only when this is available
*   **Conditional text** with variables (e.g. show text or HTML relevant to current open status)
*   **Structured Data** ([Schema.org](http://schema.org)) support to give accurate information about opening hours to search engines and services such as Google My Business and it assists with SEO
*   Populate and synchronize opening hours from Google My Business (Google API Key, Place ID, Google Billing Account are required)
*   A comprehensive and *free* plugin with no upgrades for additional functionality

= Demo: =

We have a comprehensive showcase of the shortcode’s design and functionality on our [Demonstration Website](https://demo.designextreme.com/were-open/).

*   [Basic Shortcode](https://demo.designextreme.com/were-open/)
*   [Table designs](https://demo.designextreme.com/were-open/#table)
*   [Opening hours as text](https://demo.designextreme.com/were-open/#text)
*   [Consolidation](https://demo.designextreme.com/were-open/#consolidation)
*   [Conditional shortcodes](https://demo.designextreme.com/were-open/#open-now)
*   [Conditional special opening hours](https://demo.designextreme.com/were-open/#open-special)
*   [Labels and notes](https://demo.designextreme.com/were-open/#labels-notes)
*   [Replacement text and logic](https://demo.designextreme.com/were-open/#open-text)
*   [Replacement text reference](https://demo.designextreme.com/were-open/#open-text-reference)

= Recommendations: =

*   If populating from Google, I would recommend [setting your business in Google](https://business.google.com) and finding your [Place ID](https://developers.google.com/places/place-id).
*   And if used more than once, create a [Google Billing Account](https://console.cloud.google.com/billing) to receive your substantial free API Request allocation.

*This is just my second public plugin and [all comments](https://designextreme.com/wordpress/we-are-open/) are very welcome. It is a sister plugin to [Reviews and Rating – Google My Business](https://wordpress.org/plugins/g-business-reviews-rating).*

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/opening-hours` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the ‘Plugins’ screen in WordPress
3. Use the **Settings**→**We’re Open!** screen to configure the plugin
4. Insert a shortcode into any page, post or use the widget to place throughout your website


== Frequently Asked Questions ==

= Nothing is showing up, what is wrong? =

You’ll need to go to **Settings**→**We’re Open!** to set some required settings before you can start using this plugin on the front end.

= The current open or closed status is sometimes wrong. How do I fix this? =

This can happen when the time zone in WordPress is not set correctly. It is very important that your time zone is accurate. Go to Settings→General; set your time zone to the nearest city that shares your time zone (rather than GMT +/– hours). Check the current time using the conditional text shortcode: *[open_text]%now%[/open_text]*.

= I use a caching service. How can I ensure my opening hours are current? =

You can refresh content using the shortcode parameter: *update="immediate"* to force an immediate update to the displayed information.

= The time is out by an hour. What should I try to fix? =

A common mistake is to set the time zone in WordPress using hours from GMT when it’s more reliable to use your geographic location (e.g. Europe/Paris). Using the geographic location option considers daylight saving time.

= How can I keep the opening hours with Google My Business current? =

Enable **Structured Data**. It works very well to inform Google My Business of your complete opening hours, including special opening hours and temporary closures.

Alternatively, you may wish to update your opening hours from Google My Business using the Places API (New). This method requires a Google API Key and Place ID (single location) and doesn’t consider special opening hours or temporary closures.

= Can I use my own words for “closed”, “today”, “tomorrow”, “midday”, “midnight” or label the actual holidays? =

Yes, you can set most of these words in the settings or all of these using a shortcode parameter. Labels to replace the day names or dates in special opening hours are available in the Dashboard.

= Can I have my holiday opening hours appear only when relevant? =

Yes, this is possible by using the [`[open_special]`](https://demo.designextreme.com/were-open/#open-special) shortcode. Enclose your special opening times, and any HTML such as a heading, to have this appear only when it’s needed.

= Is this plugin fully GDPR compliant? =

Yes.

= How do I get a Google API Key if I want to populate or synchronize from my Google My Business listing? =

All the details for collecting your Google API Key can be found using our [visual guide](https://designextreme.com/wordpress/we-are-open/#api-key-guide).

Once your *Project* is set, you will need a new *API Key* with access to **Place API (New)**. As a restriction, set your host’s **IP Address** (not your website’s URL). A more detailed guide is available in the Additional tab of Settings→We’re Open!.

The Google API Key is required for this plugin to load the data from the Google Places API (New).

= How do I find my Place ID? =

You can locate your unique *Place ID* using Google’s: [Place ID Finder](https://developers.google.com/places/place-id). Only specific locations are supported; not coverage areas.

= Can I have multiple sets of opening hours? =

Sorry, this is not supported by this plugin with only one set of opening hours available. [Requests](https://designextreme.com/wordpress/we-are-open/#support) to extend this functionality are welcome, but there is no time-line for this development.

== Screenshots ==

1. Various examples of opening hours in a table format, different consolidation of days and conditional text
2. Easy and fast editing of both regular and special opening hours all in one place
3. Comprehensive settings to get your opening hours displayed exactly as you want
4. Customize all separators and text for closed or consolidated days
5. Shortcodes for opening hours, conditional content for open/closed (with content refresh) and replacement codes in text
6. Extensive parameters are available to specify you6r perfect preferences as you desire
7. Set your custom styling and other additional actions
8. Simple Widget to apply your opening hours to a sidebar or footer section

== Changelog ==

= 2.3 =

* Added text replacement logic and variables for notes (Thanks to @jfinkle)
* Fixed _load_textdomain_just_in_time warning
* Tested with WordPress 6.7.1

= 2.2 =

* Fixed handling of single quotes in open_text (Thanks to @bmc38119)
* Updated all API references to have Places API (New)
* Tested with WordPress 6.6.2

= 2.1 =

* Clean up of code and markup indentation

= 2.0 =

* Added support for Place API (New) (Thanks to @bodarax)
* Added Google synchronization support for special opening hours
* Added file selection support for WEBP images
* Update to allow new special opening hours for today
* Improved usability of special opening hours management
* Multiple improvements UX in Settings
* Extended conditional text logic with addition of 1, 2 and 3 sets of opening hours
* Removed support for uploading unsanitized SVG (Thanks to Tiffany Tyson, Wordfence)
* Removed support for older versions of WordPress
* Fixed support for removing old special opening hours
* Fixed issue with saving temporary closure dates for some time zones

= 1.67 =

* Fixed note warning in management page (Thanks to @Nicker_82)

= 1.66 =

* Added the missing parameter for the character used at the end of a sentence (Thanks to @fluidandy)

= 1.65 =

* Fixed AJAX call for administrator notifications

= 1.64 =

* Added administrator notifications
* Added parameters to text replacement variables
* Added text replacement logic and variables for temporary closure (Thanks to Wanja)
* Improved reset functionality with opening hours and notifications
* Fixed keyboard navigation related error

= 1.63 =

* Added a hidden option to include Structured Data on all pages
* Added keyboard navigation to tabs in Settings
* Fixed fwrite warning from a null value
* Tested with WordPress 6.5

= 1.62 =

* Added new type of formatted opening hours as structured list
* Added German, Czech standard date formats
* Added non-breaking space between time and Meridiem Indicator (Thanks to @mikelidbetter)

= 1.61 =

* Added notes button to opening hours management
* Added indicator for midday and midnight text inputs
* Improved clarity and readability of text strings

= 1.60 =

* Improved placeholder text within Separators & Text
* Fixed JavaScript constant error (Thanks to @demediabaron and @havsland)

= 1.59 =

* Fixed JavaScript constant error (Thanks to @jeremybrookes)

= 1.58 =

* Added text replacements for today and tomorrow (Thanks to @mikelidbetter)
* Added notes to special opening hours (Thanks to @ideliver)
* Added support for labels and notes to widget
* Improved bookmark handling within Settings

= 1.57 =

* Added text replacements for times at midday and midnight
* Fixed 24 hour text replacement when intentionally empty

= 1.56 =

* Improved input sanitization (Thanks to @mikelidbetter)
* Improved the banner and tab placement in Settings

= 1.55 =

* Fixed special opening hours when temporary closure is not set (Thanks to @Nicker_82)

= 1.54 =

* Fixed temporary closure array warning (Thanks to @Nicker_82)

= 1.53 =

* Added new shortcode: open_not_special
* Corrected reference to shortcode: open_special in Shortcodes tab
* Improved text strings in Shortcodes tab

= 1.52 =

* Added new shortcode: open_special
* Added temporary closure to set of special opening hours
* Updated information provided in Shortcodes tab
* Fixed ignored temporary closure opening hours when requesting only special opening hours

= 1.51 =

* Added hidden option to set frequency of synchronization from Google My Business (Thanks to Jarrod)
* Daily Synchronization from Google My Business set to after midnight

= 1.50 =

* Corrected sanitization of translation text strings
* Tested with WordPress 6.4

= 1.49 =

* Corrected some time format labels
* Extended open_change method from 7 days to 31 days

= 1.48 =

* Added new parameter for consolidated suffix
* Introducing labels for special opening hours
* Improved handling of day suffixes
* Extended copy and paste functionality
* Tested with WordPress 6.3

= 1.47 =

* Added daily synchronization from Google My Business
* Added additional sanitization to text strings (Thanks to TaeEun Lee)
* Fixed postal address sanitization for Structured Data

= 1.46 =

* Added CSRF check to AJAX calls (Thanks to Rafie)
* Improved mobile experience in Dashboard→We’re Open! management page

= 1.45 =

* Added credential check for Dashboard AJAX calls (Thanks to Rafie)
* Restored Widget enclosing HTML (Thanks to @obewanz)
* Tested with WordPress 6.2	

= 1.44 =

* Fixed missing argument in wp_kses function call (Thanks to @mr1x)

= 1.43 =

* Fixed string type warnings with PHP 8.1+
* Fixed empty day suffix issue
* Improved the server IP retrieval method
* Improved input sanitization with new recursive method
* Tested with WordPress 6.1.1

= 1.42 =

* Added new code to recursively sanitize all inputs

= 1.41 =

* Fixed formatting error in Structured Data
* Fixed sanitization within text inputs in settings
* Fixed missing spaces around parameters within AJAX refresh calls

= 1.40 =

* Sanitized all global inputs set using AJAX
* Improved security within AJAX refresh and reload calls

= 1.39 =

* Sanitized all global inputs set using AJAX
* Escaping HTML for error and warning messages
* Additional security related changes

= 1.38 =

* Sanitized all input string data submitted in Settings (Thanks to Fioravante)

= 1.37 =

* Tested with WordPress 6.1
* Added copy and paste to opening hours management
* Fixed logic for applying 24 hours from Google My Business
* Improved styling in Dashboard

= 1.36 =

* Extended Style Sheet option
* Added JavaScript loading option
* Improved code cleanliness

= 1.35 =

* Tested with WordPress 6.0

= 1.34 =

* Tested with WordPress 5.9
* Added start and end text alignments
* Changed table padding to use inline version

= 1.33 =

* Added support to the ordinal suffix in a date as super text using ^S (circumflex and uppercase S)
* Added relative start and end dates using +/- days from current date (Thanks to Kai)
* Re-established alternative date formatting (Thanks to @aquanox24 and Kai)
* Fixed incorrect days and dates for GMT+12 and higher time zones (Thanks to @ideliver)
* Fixed update parameter to avoid altering special opening hours option (Thanks to Kai)

= 1.32 =

* Fixed variable reference for current timestamp

= 1.31 =

* Added uppercase Ante meridiem and Post meridiem to time formats
* Fixed a shift in timestamps when future dates differ in daylight saving time status

= 1.30 =

* Added Right To Left (RTL) language support
* Improved small screen support in Dashboard

= 1.29 =

* Tested with WordPress 5.8
* Increased change in status time period to two weeks (Thanks to @breyo)
* Added new conditional text for type of opening days (Thanks to @breyo)

= 1.28 =

* Fixed timestamps and dates for time zone offsets that differ from the current time zone offset
* Fixed modified date in updates to opening hours
* Fixed some incorrect instances of dates showing for Temporary Closure

= 1.27 =

* Tested with PHP 8.0
* Fixed modification check on opening hours update
* Settings button appears for administrators only

= 1.26 =

* Added missing support for Temporary Closure to open_now and closed_now shortcodes

= 1.25 =

* Added replacement text for days to status change and hours divisor
* Added replacement code for else condition
* Removed remaining upgrade check that may result in change of regular opening hours

= 1.24 =

* Tested with WordPress 5.7
* Fixed missing content for subsequent open_text updates

= 1.23 =

* Added update functionality to open_text shortcode
* Updated open_text related parameters in the Shortcode tab
* Removed upgrade check that may result in change of regular opening hours

= 1.22 =

* Fixed missing inner tags in the response for HTML of the updated opening hours

= 1.21 =

* Fixed upgrade version check issue
* Improved appearance of Dashboard notices

= 1.20 =

* Improved handling of larger date ranges for special opening hours

= 1.19 =

* Fixed possible variable type warning for special opening hours and closure range
* Fixed dates in parameters: start and end
* Improved behaviour of parameters: regular and special
* Improved behaviour of date ranges when dates don’t align with week start
* Extended day range to a year minus a week for special opening hours

= 1.18 =

* Added shortcode support in shortcode content
* Added normal font weight description for day names

= 1.17 =

* Fixed empty values for parameters: weekdays_text, weekend_text, everyday_text and day_end
* Fixed first parameter as an alias of type
* Improved functionality of tag parameter
* Added instant update to refresh opening hours, useful for cached pages

= 1.16 =

* Fixed upgrade method
* Checking/setting of Custom Style on upgrade
* Set image selection on Media Library selection; not close
* Improved user interface for Separators & Text with clearer consolidated day words
* Improved appearance of some notices on settings page load
* Added parameters for consolidated day words

= 1.15 =

* Altered Dashboard notification styling to affect just the plugin

= 1.14 =

* Fixed open_now and closed_now shortcodes without any parameters
* Fixed conditional text logic: open later, not open later
* Added new conditional text logic for 24 hour opening times
* Added new conditional text for next opening or closing time

= 1.13 =

* Fixed day value in open_change method
* Replacement text now accepts zero value within the returned string
* Improvement to empty values in Structured Data
* Improved handling for multiple instances of opening hours and conditional shortcodes
* Added support for ID attribute as a parameter to open shortcode
* Added HTML classes: open-now and closed-now when update parameter is true

= 1.12 =

* Structured Data now accepts a page ID value as an alternative to the front page
* Moved Google Places API credentials from Setup tab to Additional tab
* Resolved issue with day timestamps for negative time zones (GMT-1, GMT-2, …)

= 1.11 =

* Added check for temporary closure to conditional text

= 1.10 =

* Using date_i18n as date formatting function fall-back for older WordPress installations
* Improved sentence case formatting
* Added new day formats with month appearing before day number and no ordinal suffixes
* Added groupings to day formats in Settings
* Added temporary closure date range to Structured Data
* Fixed setting to hide closed days in Widget when differs from default
* Applied temporary closure to Widget
* Clarified Structured Data business types

= 1.9 =

* Fixed day names for non-English languages again
* Added sentence case to capitalize day names in some languages
* Improved application of day range suffix character

= 1.8 =

* Improvements to initial setup of plugin with no user requirements (Thanks to @tomtom3000)
* Fixed setting to hide closed days in Widget

= 1.7 =

* Fixed week day allocation for negative time zones (GMT-1, GMT-2, …) in Widget

= 1.6 =

* Altered names of price ranges in Structured Data
* Improved retrieval of icon and logo image URLs
* Improved styling of temporary closure section in Dashboard management
* Improved styling for small screens
* Improved logic for timestamps
* Reduced day format options and moved suffixes to settings and shortcode parameters
* Added week start day: yesterday
* Added new character for conditional text: percent symbol
* Fixed week day allocation for negative time zones (GMT-1, GMT-2, …) (Thanks to @compking)
* Fixed setting to hide closed days (Thanks to @eule1)
* Structured Data price range data set to use only dollar symbol

= 1.5 =

* General improvements for Google Places API data
* Removed language as an option in Google Credentials
* Added new conditional text logic: open later, not open later (Thanks to @erwinteering)
* Added new words for conditional text: today’s name and tomorrow’s name

= 1.4 =

* Temporary closure data accepted without requiring special opening hours
* Improved styling in Dashboard

= 1.3 =

* Clarified source error messages for Google Places API
* Fixed Structured Data appearances
* Improved information provided in Shortcodes tab
* Improved handing of 24 hour and 12 hour time input display
* Improved initial 24 hour or 12 hour time format
* Removed Google Places API notices from Dashboard→We’re Open! management page

= 1.2 =

* Covid-19 relevant functionality: you can now set a temporary closure date range
* Improvements to 12 hour/24 hour selection in Settings
* Added a list of pre-defined HTML classes to Shortcode tab

= 1.1 =

* Improved day separation with consolidated days in continuous text version
* Added new styles to shortcode and widget
* Added shortcode parameter value for yesterday as a starting day

= 1.0 =

* Launch version
* Fixed consolidation when separate and all days have the same opening hours
* Added consolidation logic to Widget
* Added new table layouts to Widget
* Improved non-English language support for Widget
* Improved user interface to clearly show leading and trailing spaces in separator text

= 0.98 =

* Extending update and page reload functionality to main shortcode
* Fixed existing value for Custom Style that did not appear
* Improved open_change method to only check for specific number of days
* Added new replacement codes for today_start and tomorrow_end
* Added wrap, no-wrap classes

= 0.97 =

* Fixed some discrepancies of times with replacement codes
* Improved Structured Data formatting

= 0.96 =

* Fixed replacement day names (Thanks to @frepho)

= 0.95 =

* Improving reliability of web server’s current IP address
* Replacement code references corrected
* Restored IP address guide in the Google Credentials section

= 0.94 =

* Fixed dates assigned to each day
* Fixed day names for non-English languages
* Added shortcode parameter for day names
* Added shortcode parameter for time group prefix
* Added shortcode parameter for time group suffix
* Corrected some spelling errors

= 0.93 =

* Improved internationalization
* Added day formatting for Special Days
* Fixed accepted day format regular expression

= 0.92 =

* Improved storage of new setting input data
* Added style for dark background
* Added style for past days
* Added style for line separating past and today

= 0.91 =

* Improved conditional open or closed shortcode

= 0.9 =

* Pre-launch version

== Upgrade Notice ==



== Getting started with Google Places API ==

In order to retrieve your opening hours from Google My Business, you will need a Google API Key, locate your Place ID and set your billing information. With the Billing details, you’ll receive a substantial *free* allocation, ample enough to use this feature for free.

* [Google API Key Guidelines](https://developers.google.com/maps/documentation/javascript/get-api-key)
* [Place ID Finder](https://developers.google.com/maps/documentation/javascript/examples/places-placeid-finder)
* [Google Cloud Billing Account](https://console.cloud.google.com/billing/enable)

