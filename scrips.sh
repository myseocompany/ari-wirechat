ssh forge-miami

mysqldump -u forge -p ariwirechat $(cat mqe_tables.csv) > copia_sin_fechas.sql

