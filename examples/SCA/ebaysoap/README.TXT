These samples demonstrate how to call the eBay Soap API using the ebaysoap 
binding.  The first example is eBayClient.php and the second is eBayClient2.php.

The first example demonstrates using the @binding.ebaysoap annotation in an SCA
component.  The second shows the use of SCA::getService(...).

To run these examples you will first need to register with eBay to get your
AuthToken, AppId, DevId and AuthCert.  Once you have these values you should 
rename ebay.ini-dist to ebay.ini and fill in the settings.  Some of the ini
settings already contain values which you may need to change (e.g. version to
match the version of the eBay API you wish to call).

Lastly, you will need to download the eBay Web service WSDL and place it in 
the eBay examples directory (the one containing this readme). You will need to 
name it eBaySvc.wsdl, or rename references to that file in eBayClient2.php and
eBayConsumer.php.