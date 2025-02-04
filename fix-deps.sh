#!/bin/bash

echo "Eliminando node_modules y package-lock.json..."
rm -rf node_modules package-lock.json

echo "Limpiando la caché de npm..."
npm cache clean --force

echo "Instalando dependencias..."
npm install

echo "Proceso completado. Puedes iniciar el servidor con 'npm run dev'."
