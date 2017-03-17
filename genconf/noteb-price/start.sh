#!/bin/bash
cd /var/www/vault/genconf/noteb-price
source venv/bin/activate 
python web_service.py &
