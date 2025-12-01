[![Home page](https://img.shields.io/badge/Home%20page-🏠-555?style=plastic)](https://www.flatpress.org "Home page")
[![Support forum](https://img.shields.io/badge/Support%20forum-💬-555?style=plastic)](https://forum.flatpress.org "Support forum")
[![Wiki](https://img.shields.io/badge/Wiki-📖-555?style=plastic)](https://wiki.flatpress.org "Wiki")
[![Change log](https://img.shields.io/badge/Change%20log-📜-555?style=plastic)](./CHANGELOG.md "Change log")
[![Security policy](https://img.shields.io/badge/Security%20policy-⚡-555?style=plastic)](./SECURITY.md "Security policy")
[![Contributors](https://img.shields.io/badge/Contributors-😎-555?style=plastic)](./CONTRIBUTORS.md "Contributors")
[![Wiki](https://img.shields.io/badge/Donate-💛-555?style=plastic&logo=paypal)](https://www.flatpress.org/donate "Send us a little Thank You")
<a href="https://fosstodon.org/@flatpress" title="Follow on Mastodon"><img src="https://img.shields.io/mastodon/follow/326815?domain=https%3A%2F%2Ffosstodon.org&style=social" alt="Follow on Mastodon"></a>

[![Releases](https://img.shields.io/github/release/flatpressblog/flatpress.svg?label=Latest%20release&style=plastic)](https://github.com/flatpressblog/flatpress/releases "See all releases")
[![License](https://img.shields.io/github/license/flatpressblog/flatpress.svg?style=plastic)](./LICENSE.md "License")
[![Open issues](https://img.shields.io/github/issues-raw/flatpressblog/flatpress?style=plastic)](https://github.com/flatpressblog/flatpress/issues "See open issues")
[![Last commit](https://img.shields.io/github/last-commit/flatpressblog/flatpress?style=plastic)](https://github.com/flatpressblog/flatpress/commits/ "Last commit")
[![PHPStan](https://github.com/flatpressblog/flatpress/actions/workflows/phpstan.yml/badge.svg)](https://github.com/flatpressblog/flatpress/actions/workflows/phpstan.yml)
[![CodeQL](https://github.com/flatpressblog/flatpress/actions/workflows/codeql-analysis.yml/badge.svg)](https://github.com/flatpressblog/flatpress/actions/workflows/codeql-analysis.yml)

# Welcome to FlatPress!
FlatPress is a lightweight, easy-to-set-up blogging engine. Plain and simple, just PHP. No database needed!

## Features
- Independent, standard-compliant blog software
- Works on files, __no database__
- Easy to setup, easy to backup
- Powerful __plugin system__ with widget support
- Easy to customize with __themes__, powered by [Smarty](http://www.smarty.net/)
- __Comments__ function with spam protection
- __Free software__ under [GNU GPLv2](LICENSE.md)
- Supported languages:
[<img src="docs/assets/🇨🇿.svg" alt="CZ" height="12" />](## "Čeština")
[<img src="docs/assets/dk.svg" alt="DK" height="12" />](## "Dansk")
[<img src="docs/assets/de.svg" alt="DE" height="12" />](## "Deutsch")
[<img src="docs/assets/us.svg" alt="EN" height="12" />](## "English US")
[<img src="docs/assets/es.svg" alt="ES" height="12" />](## "Español")
[<img src="docs/assets/eu.svg" alt="EU" height="12" />](## "Euskal")
[<img src="docs/assets/fr.svg" alt="FR" height="12" />](## "Français")
[<img src="docs/assets/gr.svg" alt="GR" height="12" />](## "Ελληνικά")
[<img src="docs/assets/it.svg" alt="IT" height="12" />](## "Italiano")
[<img src="docs/assets/jp.svg" alt="JP" height="12" />](## "日本語")
[<img src="docs/assets/nl.svg" alt="NL" height="12" />](## "Nederlands")
[<img src="docs/assets/br.svg" alt="BR" height="12" />](## "Português Brasileiro")
[<img src="docs/assets/ru.svg" alt="RU" height="12" />](## "Русский")
[<img src="docs/assets/si.svg" alt="SI" height="12" />](## "Slovenski")
[<img src="docs/assets/tr.svg" alt="TR" height="12" />](## "Türkçe") - (easy [to add](https://wiki.flatpress.org/doc:lang:packs:guidelines) yours!)


## Getting started
Installing and running FlatPress is really easy:
- [Download FlatPress](https://www.flatpress.org/download), unzip, upload
- Browse to your web server, run simple FlatPress installer
- Enjoy blogging with FlatPress!

## Demo
You can view live demo here:

https://softaculous.com/demos/flatpress

## Help and support
Visit our [wiki](https://wiki.flatpress.org) to learn everything about blogging with FlatPress, how to work with themes and plugins and where to find them. The wiki also has the [General FAQ](https://wiki.flatpress.org/doc:faq) and the [Tech FAQ](https://wiki.flatpress.org/doc:techfaq).

Ask your questions, show off your FlatPress blog and meet fellow FlatPressers at the [support forum](https://forum.flatpress.org).

## Requirements
FlatPress runs on any web server (e.g. Apache, NGINX or IIS) with PHP 7.1 to PHP 8.4 (more details [on the wiki](https://wiki.flatpress.org/doc:techfaq#what_is_required_to_run_flatpress)). Since all data is stored in files, no database is needed.

## Code quality and security
FlatPress uses automated checks to improve code quality and security:

- [PHPStan](https://phpstan.org/) is executed with every change to detect potential errors, unclean code and violations of best practices at an early stage.
- [CodeQL](https://github.com/github/codeql) analyzes the source code for known security risks and vulnerabilities through semantic code analysis.

These tools run automatically via **GitHub Actions**, both for pull requests and regularly in the background.

## Credits
There are many people who contributed to FlatPress over the years. [See them here.](./CONTRIBUTORS.md)
