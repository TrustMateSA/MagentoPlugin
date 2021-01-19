# Plugin installation:

## 1. Unpack downloaded archive and move contents do app/code directory

## 2. From the CLU run: 
  * php bin/magento module:enable TrustMate_Opinions
  * php bin/magento setup:upgrade
  * php bin/magento setup:di:compile
  * php bin/magento setup:static-content:deploy pl_PL en_US 
## 3. After logging in to Magento panel, choose TrustMate → Settings and configure it following given instructions.

## 4. After proper plugin configuration - clear Magento cache
System → Tools → Cache Management → Flush Magento Cache)
or using CLI:
  * php bin/magento cache:flush
