# Generic module for multiple contact forms 

This module can be used for multiple contact forms. By default it inserts a contact form on product page tabs and it can be used as a replacement for magentos native contact form.

## Installation
```
composer require azra/genericform
```

## Usage 
Insert the following block on any cms page

{{block class="Azra\GenericForm\Block\ContactForm"  template="Azra_GenericForm::forms/contact_additional.phtml" recaptcha_for="custom_contact" }}

### Todo:
	1. Configurable contact fields

### Supported 
Magento CE 2.4