# Plugin installation

## 1. Install or update using composer

`composer require trustmate/magento-extension`

`composer update trustmate/magento-extension`

## 2. From command line run
  * php bin/magento module:enable TrustMate_Opinions
  * php bin/magento setup:upgrade
  * php bin/magento setup:di:compile
  * php bin/magento setup:static-content:deploy pl_PL en_US (languages of your choice)

## 3. Configure plugin in Magento admin panel in TrustMate → Settings

## 4. After configuration clear the cache

In System → Tools → Cache management → Flush Magento Cache or from command line:
  * php bin/magento cache:flush

# Important notice about displaying reviews using this module

Version 3.0.x is a last version that will allow presentqing TrustMate reviews using this module.
This feature will be removed in version 3.1.x. Using TrustMate widget or creating custom integration
using our APIs is recommended.

# Upgrading

## To version 3.0.0 from version 1.2.0+ if you render reviews using module

BEFORE UPDATE:

Due to changed review storage structure, reviews from TrustMate need to be re-downloaded. Remove everything from `trustmate_product_opinions` table along with references:

```
DELETE FROM review WHERE trustmate_review_id IN (SELECT id FROM trustmate_product_opinions);
DELETE FROM trustmate_product_opinions;
```

If you're using older version, please remove all TrustMate reviews using review title for example.

AFTER UPDATE:

No action is required, reviews will download periodically (1000 in single job run). If you want to speed up this process - temporarly change cron job in module to be run more often, avoiding overlapping or download reviews manually using:


```
php bin/magento trustmate:import:opinions

```

## To version 2.1.0

* Due to bug in some older versions, if your products are identified in TrustMate using database identifier instead of SKU - please use new configuration option to keep this behaviour. Without it you may notice that new orders create brand new products on TrustMate side ignoring all existing ones.

## To version 1.2.0

* If you were using agreements from module please turn it off after upgrade. Use own agreement if necessary.

#### Change log

##### 3.0.6 (2023-08-29)
- Fixed issues on PHP 7
- User agreement related code cleanup

##### 3.0.5 (2023-08-08)
- Fixed issue with average rating in GraphQL
- Fixed issue with the same ID in magento and TrustMate review

##### 3.0.4 (2023-07-27)
- Fixed query issue on version 2.4.5-p2

##### 3.0.3 (2023-07-26)
- Fixed issue with GraphQL

##### 3.0.2 (2023-07-24)
- Fixed issues with Magento and TrustMate review overlapping
- Fixed issue with product ratings

##### 3.0.1 (2023-06-21)
- Fixed issues with pagination
- Fixed issues with average grades and review count per product

##### 3.0.0 (2023-04-27)

- [BREAKING CHANGE] Internal review storage method was redesigned and TrustMate reviews are now decoupled from native Magento reviews. They are still rendering the same way.
- Abandoned matching Store Views by language, single Store View should be connected to single TrustMate account using separate API key.
- Added better support for "After create shipment" status - now also works when shipment is created by external tool.
- Better support for review sharing (TrustMate feature)

##### 2.1.6 (2023-02-15)
- Reviews which became unpublished are now soft-deleted on Magento side
- Fixed issue with multiple stores with same language
- Fixed managing reviews with empty body

##### 2.1.1 (2022-11-15)
- Added support for PHP 8.1

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
- Reviews can now be updated if changed in TrustMate
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




