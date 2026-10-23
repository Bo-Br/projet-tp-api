@echo off
setlocal EnableExtensions
cd /d "%~dp0"
set "PROJECT_ROOT=%~dp0"
set "PHP_BIN=php"

echo.
echo ========================================
echo       DEVCOFFEE - DEMARRAGE LOCAL
echo ========================================
echo.

where php >nul 2>nul
if errorlevel 1 (
  if exist "C:\xampp\php\php.exe" set "PHP_BIN=C:\xampp\php\php.exe"
  if not exist "C:\xampp\php\php.exe" (
    echo ERREUR : PHP est introuvable dans PATH et dans C:\xampp\php.
    echo Installe PHP 8.1+ ou ajoute son dossier au PATH.
    pause
    exit /b 1
  )
)

if not exist "%PROJECT_ROOT%backend\vendor\autoload.php" (
  echo ERREUR : dependances PHP absentes dans backend\vendor.
  echo Execute composer install dans le dossier backend si les dependances sont absentes.
  pause
  exit /b 1
)

if not exist "%PROJECT_ROOT%frontend\node_modules\vite\bin\vite.js" (
  echo ERREUR : dependances Frontend absentes dans frontend\node_modules.
  echo Reinstalle-les avec Bun ou npm puis relance ce script.
  pause
  exit /b 1
)

echo La base MySQL devcoffee doit deja exister et contenir les tables du fichier backend\schema.sql.
echo Le script ne cree ni ne modifie la base de donnees.
echo.
echo [1/2] Demarrage de l'API PHP sur http://127.0.0.1:8000 ...
start "DevCoffee API" /D "%PROJECT_ROOT%backend" cmd /k ""%PHP_BIN%" -S 127.0.0.1:8000 -t public public/index.php"

echo [2/2] Demarrage de l'interface Vue sur http://127.0.0.1:5173 ...
where bun >nul 2>nul
if not errorlevel 1 (
  start "DevCoffee Frontend" /D "%PROJECT_ROOT%frontend" cmd /k "bun run dev -- --host 127.0.0.1 --port 5173 --strictPort"
) else (
  where node >nul 2>nul
  if errorlevel 1 (
    echo ERREUR : ni Bun ni Node.js ne sont dans PATH.
    echo L'API est demarree, mais le frontend ne peut pas demarrer.
    pause
    exit /b 1
  )
  start "DevCoffee Frontend" /D "%PROJECT_ROOT%frontend" cmd /k "node node_modules/vite/bin/vite.js --host 127.0.0.1 --port 5173 --strictPort"
)

timeout /t 3 /nobreak >nul
echo.
echo DevCoffee a ete lance.
echo Interface : http://127.0.0.1:5173
echo API       : http://127.0.0.1:8000/api/drinks
echo Ferme les fenetres API et Frontend pour arreter les serveurs.
start "" "http://127.0.0.1:5173"
exit /b 0
