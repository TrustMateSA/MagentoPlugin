# Plugin installation:

## 1. Install or update using composer

`composer require trustmate/magento-extension`

`composer update trustmate/magento-extension`

## 2. From command line run:
  * php bin/magento module:enable TrustMate_Opinions
  * php bin/magento setup:upgrade
  * php bin/magento setup:di:compile
  * php bin/magento setup:static-content:deploy pl_PL en_US

## 3. You can now configure plugin in Magento admin panel in TrustMate → Settings

## 4. After configuration clear the cache

In System → Tools → Cache management → Flush Magento Cache or from command line:
  * php bin/magento cache:flush


# Upgrading


## To version 2.1.0

* Due to bug in some older versions, if your products are identified in TrustMate using database identifier instead of SKU - please use new configuration option to keep this behaviour. Without it you may notice that new orders create brand new products on TrustMate side ignoring all existing ones.

## To version 1.2.0

* If you were using agreements from module please turn it off after upgrade. Use own agreement if necessary.

#### Change log

##### 2.1.0 (2022-11-08)
- Added support for GTIN/EAN/MPN synchronization for Google integration
- Customer surnames are no longer passed to review invitations
- Order identifier are saved along with reviews
- Product identifier can be changed (required for backward compatibility is some rare cases)
- Fixed translation synchronization for multiple stores in same language

##### 2.0.0 (2022-07-25)
- Switched to GitHub releases
- Functionally the same as 1.2.0

##### 1.2.0 (2022-04-20)
- Reviews can now being updated if changed in TrustMate
- Reviews can be updated retrospectively using command line
- Added support for review translations
- Added support for product thumbnails
- Removed old widget
- Removed built-in agreements
- Fixed dead links
- Added sandbox mode

##### 1.1.0 (2020-09-29)
- Saving invitation consent in sales_order
- Added possibility to change sending invitation trigger (After order / After creating shipment)
- Moved invitation creation logic

##### 1.0.5 (2020-09-02)
- Removed surname from customer_name

##### 1.0.4 (2020-08-05)
- Handle invitation metadata
- Changed order_id to increment_id




