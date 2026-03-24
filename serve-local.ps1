$php = "C:\xampp\php\php.exe"

if (-not (Test-Path $php)) {
    Write-Error "PHP was not found at $php. Update serve-local.ps1 with the correct path."
    exit 1
}

Write-Host "Starting local preview at http://localhost:8000"
& $php -S localhost:8000 -t public router.php
