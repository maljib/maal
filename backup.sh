#!/bin/bash
mysqldump -h localhost -u scott -p --databases wordlist >../doc/wordlist_$(date +%Y%m%d).sql
