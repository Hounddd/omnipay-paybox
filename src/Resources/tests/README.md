# How to generate signature with this local RSA keys

openssl dgst -sha1 -binary -sign test_prvkey.pem -out sig.bin data.txt
openssl base64 -in sig.bin -out sig64.txt
rm sig.bin

@see (FR) https://www.paybox.com/espace-integrateur-documentation/la-solution-paybox-system/gestion-de-la-reponse/
