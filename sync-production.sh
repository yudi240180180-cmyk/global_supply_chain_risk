#!/bin/bash
# Production Sync Script - Run this in Railway Console after deployment

set -e

echo "=========================================="
echo "🚀 Starting Full Production Sync"
echo "=========================================="

# Clear all caches
echo ""
echo "==> Clearing caches..."
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Seed users
echo ""
echo "==> Seeding users..."
php artisan db:seed --class=AdminUserSeeder --force || echo "AdminUserSeeder already run or failed"
php artisan db:seed --class=ManagerUserSeeder --force || echo "ManagerUserSeeder already run or failed"

# Check user count
USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null | tail -1 || echo "0")
echo "✅ Users in database: $USER_COUNT"

# Sync all data
echo ""
echo "==> Syncing countries (250 expected)..."
php artisan sync:countries
COUNTRY_COUNT=$(php artisan tinker --execute="echo \App\Models\Country::count();" 2>/dev/null | tail -1 || echo "0")
echo "✅ Countries: $COUNTRY_COUNT"

echo ""
echo "==> Syncing economics data..."
php artisan sync:economics
echo "✅ Economics synced"

echo ""
echo "==> Syncing exchange rates..."
php artisan sync:rates
echo "✅ Exchange rates synced"

echo ""
echo "==> Syncing news articles..."
php artisan sync:news
NEWS_COUNT=$(php artisan tinker --execute="echo \App\Models\NewsArticle::count();" 2>/dev/null | tail -1 || echo "0")
echo "✅ News articles: $NEWS_COUNT"

echo ""
echo "==> Syncing weather data..."
php artisan sync:weather
echo "✅ Weather synced"

echo ""
echo "==> Calculating risk scores..."
php artisan calculate:risk
RISK_COUNT=$(php artisan tinker --execute="echo DB::table('risk_scores')->count();" 2>/dev/null | tail -1 || echo "0")
echo "✅ Risk scores: $RISK_COUNT"

echo ""
echo "==> Syncing ports..."
php artisan sync:all
PORT_COUNT=$(php artisan tinker --execute="echo \App\Models\Port::count();" 2>/dev/null | tail -1 || echo "0")
echo "✅ Ports: $PORT_COUNT"

# Final summary
echo ""
echo "=========================================="
echo "🎉 SYNC COMPLETED!"
echo "=========================================="
echo "Users: $USER_COUNT"
echo "Countries: $COUNTRY_COUNT"
echo "Ports: $PORT_COUNT"
echo "News: $NEWS_COUNT"
echo "Risk Scores: $RISK_COUNT"
echo ""
echo "Dashboard: https://global-supply-chain-risk-production-a7da.up.railway.app"
echo "=========================================="
