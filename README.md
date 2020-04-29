# Lengow for Shopware

- **Requires at least:** 4.3
- **Tested up to:** 5.6
- **Requires PHP:** 5.6
- **Stable tag:** 1.5.1
- **License:** GPLv3
- **License URI:** https://www.gnu.org/licenses/gpl-3.0

## Overview

![Lengow logo](https://my.lengow.io/images/pages/launching/channels.gif)

Lengow is the e-commerce automation solution that helps brands and distributors improve their performance, automate their business processes, and grow internationally. The Lengow platform is the key to strong profitability and visibility for products sold by online retailers around the world on all distribution channels: marketplaces, comparison shopping engines, affiliate platforms and display/retargeting platforms. Since 2009, Lengow has integrated more than 1,600 partners into its solution to provide a powerful platform to its 4,600 retailers and brands in 42 countries around the world.

Major features in Lengow include:

- Easily import your product data from your cms
- Use Lengow to target and exclude the right products for the right channels and tools (marketplaces, price comparison engines, product ads, retargeting, affiliation) and automate the process of product diffusion.
- Manipulate your feeds (categories, titles, descriptions, rules…) - no need for technical knowledge.
- Lengow takes care of the centralisation of orders received from marketplaces and synchronises inventory data with your backoffice. Track your demands accurately and set inventory rules to avoid running out of stock.
- Monitor and control your ecommerce activity using detailed, yet easy to understand graphs and statistics. Track clicks, sales, CTR, ROI and tweak your campaigns with automatic rules according to your cost of sales / profitability targets.
- Thanks to our API, Lengow is compatible with many applications so you can access the functionality of all your ecommerce tools on a single platform. There are already more than 40 available applications: marketing platform, translation, customer review, email, merchandise, price watch, web-to-store, product recommendation and many more

The Lengow plugin is free to download and it enables you to export your product catalogs and manage your orders. It is compatible only with the new version of our platform.
A Lengow account is created during the extension installation and you will have free access to our platform for 15 days. To benefit from all the functionalities of Lengow, this requires you to pay for an account on the Lengow platform.

## Plugin installation

Follow the instruction below if you want to install Lengow for Shopware using Git.

1.) Clone the git repository in the Shopware `engine/Shopware/Plugins/Community/Backend` folder using:

    git clone git@github.com:lengow/plugin-shopware.git Lengow

In case you wish to contribute to the plugin, fork the `dev` branch rather than cloning it, and create a pull request via Github. For further information please read the section "Become a contributor" of this document.

3.) Set the correct directory permissions:

    chmod -R 755 engine/Shopware/Plugins/Community/Backend/Lengow

Depending on your server configuration, it might be necessary to set whole write permissions (777) to the files and folders above.
You can also start testing with lower permissions due to security reasons (644 for example) as long as your php process can write to those files.

4.) Activate the plugin through the `Configuration > Plugin Manager` screen in Shopware

5.) Log in with your Lengow credentials and configure the plugin

## Frequently Asked Questions

### Where can I find Lengow documentation and user guides?

For help setting up and configuring Lengow plugin please refer to our [user guide](https://support.lengow.com/hc/en-us/articles/360012203311-Shopware)

### Where can I get support?

To make a support request to Lengow, use [our helpdesk](https://support.lengow.com/hc/en-us/requests/new).


## Become a contributor

Lengow for Shopware is available under license (GPLv3). If you want to contribute code (features or bugfixes), you have to create a pull request via Github and include valid license information.

The `master` branch contains the latest stable version of the plugin. The `dev` branch contains the version under development.
All Pull requests must be made on the `dev` branch and must be validated by reviewers working at Lengow.

By default the plugin is made to work on our pre-production environment (my.lengow.net).
To change this environment, you must modify the two constants present in the file `Lengow/Components/LengowConnector.php`

    const LENGOW_URL = 'lengow.net';
    const LENGOW_API_URL = 'https://api.lengow.net';

### Translation

Translations in the plugin are managed via a key system and associated yaml files

Start by installing Yaml Parser:

    sudo apt-get install php5-dev libyaml-dev
    sudo pecl install yaml
    
To translate the project, use specific key in php code and modify the *.yml files in the directory: `Lengow/Snippets/backend/Lengow/yml`

Once the translations are finished, just run the translation update script in `Lengow/Tools` folder

    php translate.php
    
The plugin is translated into English, German and French.

## Changelog

The changelog and all available commits are located under [CHANGELOG](CHANGELOG).