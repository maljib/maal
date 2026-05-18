#!/bin/bash
mysqldump -h localhost -u maljib_user -p --databases wordlist >../doc/wordlist_$(date +%Y%m%d).sql
