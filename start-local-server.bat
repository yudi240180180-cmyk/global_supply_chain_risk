@echo off
echo ========================================
echo Global Supply Chain Risk Platform
echo Local Development Server
echo ========================================
echo.

echo [1/4] Checking PHP...
php -v
if errorlevel 1 (
    echo ERROR: PHP not found!
    pause
    exit /b 1
)
echo.

echo [2/4] Clearing Laravel cache...
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo.

echo [3/4] Testing database connection...
php artisan db:show
if errorlevel 1 (
    echo.
    echo ==========================================
    echo ERROR: Cannot connect to MySQL!
    echo Please start MySQL in XAMPP Control Panel
    echo ==========================================
    pause
    exit /b 1
)
echo.

echo [4/4] Starting Laravel development server...
echo.
echo ========================================
echo Server will start at: http://127.0.0.1:8000
echo.
echo To expose with ngrok, open another terminal and run:
echo   ngrok http 8000
echo ========================================
echo.
echo Press Ctrl+C to stop the server
echo.

php artisan serve --host=127.0.0.1 --port=8000
