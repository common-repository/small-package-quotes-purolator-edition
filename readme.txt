=== Small Package Quotes - Purolator Edition ===
Contributors: enituretechnology
Tags: eniture. Purolator,parcel rates, parcel quotes, shipping estimates 
Requires at least: 6.4
Tested up to: 6.6.2
Stable tag: 3.6.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Real-time small package (parcel) shipping rates from Purolator. Fifteen day free trial.

== Description ==

Purolator is headquartered in Mississauga, Ontario and is Canada’s premier shipping company. If you don’t have a Purolator account number, contact them at 888-744-7123, or register online( https://eshiponline.purolator.com/ShipOnline/SecurePages/Public/Register.aspx ).

**Key Features**

* Includes negotiated shipping rates in the shopping cart and on the checkout page.
* Ability to control which Purolator services to display
* Support for variable products.
* Define multiple warehouses and drop ship locations
* Option to include residential delivery surcharge
* Option to mark up shipping rates by a set dollar amount or by a percentage.

**Requirements**

* WooCommerce 6.4 or newer.
* A Purolator billing account number.
* A Purolator Registered Account Number.
* A Purolator Registered Address.
* A Purolator Production Key.
* A Purolator Production Key Password.
* An API key from Eniture Technology.

== Installation ==

**Installation Overview**

Before installing this plugin you should have the following information handy:

* Your Purolator billing and registered account number.
* Your Purolator production key.
* Your username and password to Purolator.

If you need assistance obtaining any of the above information, contact your local Purolator
or call [Purolator](http://purolator.com/) 1-888-744-7123

A more extensive and graphically illustrated set of instructions can be found on the *Documentation* tab at
[eniture.com](https://eniture.com/woocommerce-purolator-small-package-plugin/).

**1. Install and activate the plugin**
In your WordPress dashboard, go to Plugins => Add New. Search for "eniture small package quotes", and click Install Now on Small Package Quotes - Purolator Edition.
After the installation process completes, click the Activate Plugin link to activate the plugin.

**2. Get an API key from Eniture Technology**
Go to [Eniture Technology](https://eniture.com/woocommerce-purolator-small-package-plugin/) and pick a
subscription package. When you complete the registration process you will receive an email containing your API key and
your login to eniture.com. Save your login information in a safe place. You will need it to access your customer dashboard
where you can manage your API keys and subscriptions. A credit card is not required for the free trial. If you opt for the free
trial you will need to login to your [Eniture Technology](http://eniture.com) dashboard before the trial period expires to purchase
a subscription to the API key. Without a paid subscription, the plugin will stop working once the trial period expires.

**3. Establish the connection**
Go to WooCommerce => Settings => Purolator. Use the *Connection* link to create a connection to your Purolator
account; and the *Setting* link to configure the plugin according to your preferences.

**4. Enable the plugin**
Go to WooCommerce => Settings => Shipping. Click on the link for Purolator and enable the plugin.

== Frequently Asked Questions ==

= How do I get a Purolator account number? =

Visit the customer support section of purolator.com for FAQs, e-mail inquiries or telephone support. We also post verifications of insurance as a courtesy to our customers.

For additional information, call our customer service number: 1-888-744-7123.

= Where do I find my Purolator username and password? =

Usernames and passwords to Purolator.com.
Visit the customer support section of purolator.com for FAQs, e-mail inquiries or telephone support. We also post verifications of insurance as a courtesy to our customers.

For additional information, call our customer service number: 1-888-744-7123.



= How do I get an API key for my plugin? =

You must register your installation of the plugin, regardless of whether you are taking advantage of the trial period or 
purchased an API key outright. At the conclusion of the registration process an email will be sent to you that will include 
the API key. You can also login to eniture.com using the username and password you created during the registration process 
and retrieve the API key from the My API keys tab.

= How do I change my plugin API key from the trail version to one of the paid subscriptions? =

Login to eniture.com and navigate to the My API keys tab. There you will be able to manage the licensing of all of your Eniture Technology plugins.

= How do I install the plugin on another website? =

The plugin has a single site API key. To use it on another website you will need to purchase an additional API key. If you want 
to change the website with which the plugin is registered, login to eniture.com and navigate to the My API keys tab. There you will 
be able to change the domain name that is associated with the API key.

= Do I have to purchase a second API key for my staging or development site? =

No. Each API key allows you to identify one domain for your production environment and one domain for your staging or 
development environment. The rate estimates returned in the staging environment will have the word “Sandbox” appended to them.

= Why isn’t the plugin working on my other website? =

If you can successfully test your credentials from the Connection page (WooCommerce > Settings > Purolator > Connections) 
then you have one or more of the following licensing issue(s): 1) You are using the API key key on more than one domain. 
The API keys are for single sites. You will need to purchase an additional API key. 2) Your trial period has expired. 
3) Your current API key has expired and we have been unable to process your form of payment to renew it. Login to eniture.com and 
go to the My API keys tab to resolve any of these issues.

= Why were the shipment charges I received on the invoice from Purolator different than what was quoted by the plugin? =

Common reasons include one of the shipment parameters (weight, dimensions) is different, or additional services (such as residential 
delivery) were required. Compare the details of the invoice to the shipping settings on the products included in the shipment. 
Consider making changes as needed. Remember that the weight of the packing materials is included in the billable weight for the shipment. 
If you are unable to reconcile the differences call your local Worldwide Express office for assistance.

= Why do I sometimes get a message that a shipping rate estimate couldn’t be provided? =

There are several possibilities:

* Purolator has restrictions on a shipment’s maximum weight, length and girth which your shipment may have exceeded.
* There wasn’t enough information about the weight or dimensions for the products in the shopping cart to retrieve a shipping rate estimate.
* The purolator.com isn’t operational.
* Your Purolator account has been suspended or cancelled.
* Your Eniture Technology API key for this plugin has expired.

== Screenshots ==

1. Plugin options page
2. Connection settings page
3. Quotes returned to cart

== Changelog ==

= 3.6.4 =
* Fix: Resolved UI compatibility issue with WooCommerce versions later than 9.0.0

= 3.6.3 =
* Update: Updated connection tab according to wordpress requirements 

= 3.6.2 =
* Update: Compatibility with WordPress version 6.5.2
* Update: Compatibility with PHP version 8.2.0
* Update: Introduced an additional option to packaging method when standard boxes is not in use

= 3.6.1 =
* Fix: Fixed the product ID and product title in the metadata required for freightdesk.online.

= 3.6.0 =
* Update: Display "Free Shipping" at checkout when handling fee in the quote settings is  -100% .
* Update: Introduced the Shipping Logs feature.
* Update: Introduced “product level markup” and “origin level markup”.

= 3.5.3 =
* Update: Changed required plan from standard to basic for delivery estimate options.

= 3.5.2 =
* Update: Compatibility with WooCommerce HPOS(High-Performance Order Storage)

= 3.5.1 =
* Update: Fixed grammatical mistakes in "Ground transit time restrictions" admin settings.

= 3.5.0 =
* Update: Introduced optimizing space utilization.
* Update: Modified expected delivery message at front-end from “Estimated number of days until delivery” to “Expected delivery by”.
* Fix: Inherent Flat Rate value of parent to variations

= 3.4.6 =
* Update: Introduced a settings on product page to Exempt ground Transit Time restrictions.

= 3.4.5 =
* Update: Compatibility with WordPress version 6.1
* Update: Compatibility with WooCommerce version 7.0.1

= 3.4.4 =
* Fix:Correction in font color of standard plan message. 

= 3.4.3 =
* Update: Introduced connectivity from the plugin to FreightDesk.Online using Company ID

= 3.4.2 =
* Update: Compatibility with WordPress multisite network
* Fix: Fixed support link. 

= 3.4.1 =
* Update: Introduced debug logs tab.

= 3.4.0 =
* Fix: In case of multiply shipment, wil show rates if all shipments will return rates. 

= 3.3.2 =
* Update: Introduced new features:
* Compatibility with WordPress 5.7
* Order detail widget for draft orders
* Improved order detail widget for Freightdesk.online
* Compatibly with Shippable add-on
* Compatibly with Account Details(ET) add-don(Capturing account number on checkout page).


= 3.3.1 =
* Fix: Action dropdown on order detail page on admin.

= 3.3.0 =
* Update: Compatibility with WordPress 5.6

= 3.2.4 =
* Update: Compatibility with WordPress 5.5

= 3.2.3 =
* Fix: Fixed UI of quote settings tab.

= 3.2.2 =
* Update: Compatibility with WordPress 5.4

= 3.2.1 =
* Update: Change place of field on quote settings tab.

= 3.2.0 =
* Update: Introduced new features 1) Estimated delivery options. 2) Cut off time & offset days. 3) An option to control shipment days of the week.

= 3.1.1 =
* Fix: Fixed UI of quote settings tab.

= 3.1.0 =
* Update: Introduced an ability to mark up individual services.

= 3.0.4 =
* Fix: Fixed compatibility issue with Eniture Technology LTL Freight Quotes plugins.

= 3.0.3 =
* Fix: Fixed a issue in ground transit days. 

= 3.0.2 =
* Update: Compatibility with WordPress 5.1

= 3.0.1 =
* Fix: Identify one warehouse and multiple drop ship locations in basic plan. 

= 3.0.0 =
* Update: Introduced new features and Basic, Standard and Advanced plans.

= 2.0.2 =
* Update: Compatibility with WordPress 5.0

= 2.0.1 =
* Update: Compatibility with WooCommerce 3.4.2 and PHP 7.1.

= 2.0.0 =
* Update: Introduction of Standard Box Sizes feature which are enabled though the installation of plugin add on.

= 1.1.1 =
* Fix: Fixed issue with new reserved word in PHP 7.1.

= 1.1.0 =
* Update: Compatibility with WooCommerce 4.9.
 
= 1.0.0 =
* Initial release.

== Upgrade Notice ==
