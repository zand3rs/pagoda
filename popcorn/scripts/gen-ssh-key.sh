#!/bin/bash

#ssh-keygen -t rsa -b 4096 -f popcorn_rsa
#ssh-keygen -e -m PEM -f popcorn_rsa > popcorn_rsa.pem

openssl genrsa -out popcorn_rsa 4096
openssl rsa -in popcorn_rsa -pubout > popcorn_rsa.pem

