# How to generate signature with this local RSA keys

To generate an RSA private key test_prvkey.pem and extract the public key test_pubkey.pem

```bash
openssl genrsa -out test_prvkey.pem 1024
openssl rsa -in test_prvkey.pem -pubout -out test_pubkey.pem
```

Signing data contained in the data.txt file


```bash 
openssl dgst -sha1 -binary -sign test_prvkey.pem -out sig.bin data.txt
openssl base64 -in sig.bin -out sig64.txt
rm sig.bin
```

Signature verification using the test_pubkey.pem public key

```bash
openssl base64 -d -in sig64.txt -out sig.bin
openssl dgst -sha1 -binary -verify test_pubkey.pem -signature sig.bin data.txt
rm sig.bin
```


@see (FR) https://www.paybox.com/espace-integrateur-documentation/la-solution-paybox-system/gestion-de-la-reponse/
