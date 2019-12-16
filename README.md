Instalacja wtyczki:
=
1. Rozpakuj pobrane archiwum i jego zawartość przenieś do katalogu app/code
==
2. Z poziomu konsoli uruchom kolejno polecenia: 
==
  * php bin/magento module:enable TrustMate_Opinions
  * php bin/magento setup:upgrade
  * php bin/magento setup:static-content:deploy pl_PL en_US 
3. Po zalogowaniu do panelu Magento wybierz opcję TrustMate->Ustawienia gdzie możesz skonfigurować wtyczkę wg zawartej tam instrukcji
==
4. Po skonfigurowaniu wtyczki wyczyść cache magento (System->Narzędzia->Pamięć podręczna->Wyczyść pamięć podręczną Magento) lub poprzez polecenie w konsoli
==
  * php bin/magento cache:flush