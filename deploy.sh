#!/bin/bash

# Abortar el script si ocurre algún error
set -e

echo "----------------------------------------------------"
echo "🚀 Iniciando proceso de despliegue en Producción..."
echo "----------------------------------------------------"

# 1. Obtener los últimos cambios de GitHub
echo "⏬ Paso 1: Sincronizando repositorio con GitHub..."
git fetch origin main
git reset --hard origin/main

# 2. Reconstruir si hay cambios en el Dockerfile (opcional pero recomendado)
echo "🐳 Paso 2: Verificando cambios en la imagen Docker..."
docker compose up -d --build

# 3. Instalar dependencias de PHP (solo si faltan o cambiaron)
echo "📦 Paso 3: Instalando dependencias de Composer..."
docker compose exec -T app composer install --optimize-autoloader --no-dev

# 4. Ejecutar migraciones
echo "🗄️ Paso 4: Ejecutando migraciones de base de datos..."
docker compose exec -T app php artisan migrate --force

# 4.1 Sincronizar roles y permisos (Es seguro, no duplica información)
echo "🌱 Paso 4.1: Sincronizando roles y permisos..."
docker compose exec -T app php artisan db:seed --class=RoleSeeder --force

# 5. Instalar dependencias de Node y compilar assets (Inertia/Vue)
echo "🎨 Paso 5: Compilando assets del Frontend (Vite)..."
docker compose exec -T app npm install
docker compose exec -T app npm run build

# 6. Limpiar y generar caché de Laravel para mejor rendimiento
echo "⚡ Paso 6: Optimizando caché de Laravel..."
docker compose exec -T app php artisan optimize
docker compose exec -T app php artisan view:cache

# 7. Reiniciar Nginx para aplicar cambios en configuración (ej. app.conf)
echo "🔄 Paso 7: Recargando configuración de servidor web..."
docker compose exec -T web nginx -s reload || docker compose restart web

echo "----------------------------------------------------"
echo "✅ ¡Despliegue completado con éxito!"
echo "📍 Tu aplicación está lista en el servidor de producción."
echo "----------------------------------------------------"
