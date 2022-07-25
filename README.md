# Plugin installation:

## 1. Unpack archive and move to app/code directory

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

## To version 1.2.0

* If you were using agreements from module please turn it off after upgrade. Use own agreement if necessary.

#### Change log

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




