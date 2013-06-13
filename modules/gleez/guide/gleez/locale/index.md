# About Gleez Locale

## Introduction

<span class="classname">Gleez_Locale</span> is the answer to the question, "How can the same application be used around the whole world?". Most people will say, "That's easy. Let's translate all our output to several languages." However, using simple translation tables to map phrases from one language to another is not sufficient. Different regions will have different conventions for first names, surnames, formatting of numbers, dates, times, currencies, etc.

We need [Localization and complementary Internationalization](http://en.wikipedia.org/wiki/Internationalization_and_localization). Both are often abbreviated to *L10n and I18n*. Internationalization refers more to support for use of systems, regardless of special needs unique to groups of users related by language, region, number format conventions, financial conventions, time and date conventions, etc. Localization involves adding explicit support to systems for special needs of these unique groups, such as language translation, and support for local customs or conventions for communicating plurals, dates, times, currencies, names, symbols, sorting and ordering, etc. *L10n and I18n* compliment each other.

Gleez CMS provides support for these through a combination of components, including <span class="classname">Gleez_Locale</span>.

[!!] <span class="classname">Gleez_Locale</span> partly borrowed and adapted from [Zend Framework](http://framework.zend.com/) 1.12. Please see [Zend license](http://framework.zend.com/license/new-bsd)

## How are Locales Represented?

Locale identifiers consist of information about the user's language and preferred/primary geographic region (e.g. state or province of home or workplace). The locale identifier strings used in Gleez CMS are internationally defined standard abbreviations of language and region, written as *language_REGION*. Both the language and region parts are abbreviated to alphabetic, ASCII characters.

<blockquote>Note: Be aware that there exist not only locales with 2 characters as most people think. Also there are languages and regions which are not only abbreviated with 2 characters. Therefor you should NOT strip the region and language yourself, but use <strong class="classname">Gleez_Locale</strong> when you want to strip language or region from a locale string. Otherwise you could have unexpected behaviour within your code when you do this yourself.</blockquote>

A user from USA would expect the language English and the region **USA**, yielding the locale identifier "en_US". A user in Germany would expect the language German and the region Germany, yielding the locale identifier "de_DE". See the [list of pre-defined locale and region combinations](http://unicode.org/repos/cldr-tmp/trunk/diff/supplemental/language_territory_information.html), if you need to select a specific locale within Gleez CMS.
