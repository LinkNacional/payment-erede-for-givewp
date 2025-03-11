=== Payment Gateway E-Rede for GiveWP ===
Contributors: linknacional
Donate link: https://www.linknacional.com.br/wordpress/plugins/
Tags: payment, donation, credit, debit, card
Requires at least: 5.7
Requires PHP: 7.4
Tested up to: 6.7
Stable tag: 2.0.3
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Credit and debit card payment using E-Rede

== Description ==

Introducing the Payment Gateway E-Rede for GiveWP plugin – your seamless solution for securely processing credit and debit card payments within your WordPress-powered fundraising efforts. This robust plugin extends the functionality of GiveWP, the leading donations management plugin, by seamlessly integrating E-Rede API as a trusted payment method.

Key Features:

1. **Effortless Integration**: This plugin effortlessly integrates E-Rede's powerful payment processing capabilities into your GiveWP donation forms, providing your supporters with a convenient and secure payment option.

2. **Secure Transactions**: Ensure peace of mind for your donors with E-Rede's robust security measures, safeguarding every transaction and protecting sensitive cardholder data.

3. **User-Friendly Setup**: Setting up E-Rede as a payment gateway for your GiveWP forms is a breeze. Our intuitive configuration wizard guides you through the process, ensuring quick and hassle-free integration.

4. **Seamless Donation Experience**: Donors can now contribute seamlessly, choosing from a range of payment methods including credit and debit cards. The plugin provides a smooth and familiar checkout experience.

Give your donors the convenience and security they deserve while seamlessly managing your fundraising efforts with the Payment Gateway E-Rede for GiveWP plugin. Elevate your online donations experience and watch your fundraising campaigns thrive.

**Dependencies**

Payment Gateway E-Rede for GiveWP plugin is dependent on [GiveWP plugin](https://wordpress.org/plugins/give/), please make sure GiveWP is installed and properly configured.

This plugin requires an active [E-Rede account](https://developer.userede.com.br/) for payment processing.  
It connects to the E-Rede API for transaction processing:  
- **Production:** `https://api.userede.com.br/erede/v1/transactions`  
- **Sandbox:** `https://sandbox-erede.useredecloud.com.br/v1/transactions`

**Third party APIs usage**
This plugin is a payment method, it uses the [E-Rede account](https://developer.userede.com.br/) API for credit and debit card payment processing. See the [privacy policy](https://www.itau.com.br/privacidade/politica-de-privacidade-e-cookies) and [terms of use](https://developer.userede.com.br/files/termos/politica_privacidade_rede_2024.pdf).

**User instructions**

1. Go to GiveWP settings menu;

2. Search for the 'Payment gateways' tab;

3. Activate the credit or debit card payment method for your donation forms;

4. Save;

5. Go to the E-Rede API - Credit card or E-Rede API - Debit card 3DS tab;

6. Fill the form with your E-Rede credentials;

7. Save.

== Installation ==

1. Upload `payment-erede-for-givewp.zip` to the `/wp-content/plugins/` directory;
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= How can I get my E-Rede production credentials? =

To get your E-Rede production credentials you will need to follow [this guide](https://developer.userede.com.br/e-rede#documentacao-credenciamento).

== Screenshots ==

1. None

== Changelog ==
= 2.0.3 =
**07/03/2025**
* Fix WordPress guidelines.

= 2.0.2 =
**07/01/2025**
* Changed log functionality.

= 2.0.1 =
**10/10/2024**
* Add link to view logs;
* Fix donation status;

= 2.0.0 =
**14/05/2024**
* Added compatibility with GiveWP 3.0.0 template.
* General plugin optimizations.
* Addition of 3DS 2.0 for credit card payments.

= 1.0.2 =
**22/09/2023**
* Fix a bug in translation.

= 1.0.1 =
**20/09/2023**
* Fix a bug with front-end inputs.

= 1.0.0 =
**04/09/2023**
* Plugin launch.

== Upgrade Notice ==

= 1.0.0 =
* Plugin launch.
