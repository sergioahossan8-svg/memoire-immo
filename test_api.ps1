# Script de test de l'API ImmoGo
Write-Host "=== TEST API IMMOGO ===" -ForegroundColor Cyan

# Test 1: Vérifier que le serveur répond
Write-Host "`n1. Test de connexion au serveur..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://192.168.133.82:8000/api/biens" -Method GET -Headers @{"Accept"="application/json"} -TimeoutSec 5
    Write-Host "✓ Serveur accessible (Status: $($response.StatusCode))" -ForegroundColor Green
    Write-Host "Réponse: $($response.Content.Substring(0, [Math]::Min(200, $response.Content.Length)))..." -ForegroundColor Gray
} catch {
    Write-Host "✗ Serveur inaccessible: $_" -ForegroundColor Red
    Write-Host "Vérifiez que 'php artisan serve --host=0.0.0.0' est lancé" -ForegroundColor Yellow
    exit 1
}

# Test 2: Inscription d'un nouveau client
Write-Host "`n2. Test d'inscription..." -ForegroundColor Yellow
$registerData = @{
    name = "Test"
    prenom = "User"
    email = "test$(Get-Random)@example.com"
    password = "password123"
    password_confirmation = "password123"
    ville = "Cotonou"
    telephone = "+22901234567"
} | ConvertTo-Json

try {
    $response = Invoke-WebRequest -Uri "http://192.168.133.82:8000/api/register" -Method POST -Body $registerData -ContentType "application/json" -Headers @{"Accept"="application/json"}
    $data = $response.Content | ConvertFrom-Json
    $token = $data.token
    Write-Host "✓ Inscription réussie" -ForegroundColor Green
    Write-Host "Token: $($token.Substring(0, 20))..." -ForegroundColor Gray
    Write-Host "User: $($data.user.prenom) $($data.user.name) ($($data.user.email))" -ForegroundColor Gray
} catch {
    Write-Host "✗ Erreur d'inscription: $_" -ForegroundColor Red
    $errorResponse = $_.ErrorDetails.Message
    Write-Host "Détails: $errorResponse" -ForegroundColor Red
    exit 1
}

# Test 3: Vérifier le token avec /me
Write-Host "`n3. Test de vérification du token (/me)..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://192.168.133.82:8000/api/me" -Method GET -Headers @{"Accept"="application/json"; "Authorization"="Bearer $token"}
    $data = $response.Content | ConvertFrom-Json
    Write-Host "✓ Token valide" -ForegroundColor Green
    Write-Host "User connecté: $($data.user.prenom) $($data.user.name)" -ForegroundColor Gray
} catch {
    Write-Host "✗ Token invalide: $_" -ForegroundColor Red
    exit 1
}

# Test 4: Connexion avec les mêmes identifiants
Write-Host "`n4. Test de connexion..." -ForegroundColor Yellow
$loginData = @{
    email = $data.user.email
    password = "password123"
} | ConvertTo-Json

try {
    $response = Invoke-WebRequest -Uri "http://192.168.133.82:8000/api/login" -Method POST -Body $loginData -ContentType "application/json" -Headers @{"Accept"="application/json"}
    $loginResult = $response.Content | ConvertFrom-Json
    Write-Host "✓ Connexion réussie" -ForegroundColor Green
    Write-Host "Nouveau token: $($loginResult.token.Substring(0, 20))..." -ForegroundColor Gray
} catch {
    Write-Host "✗ Erreur de connexion: $_" -ForegroundColor Red
    exit 1
}

Write-Host "`n=== TOUS LES TESTS RÉUSSIS ===" -ForegroundColor Green
Write-Host "`nLe backend fonctionne correctement!" -ForegroundColor Cyan
Write-Host "Email de test: $($data.user.email)" -ForegroundColor Yellow
Write-Host "Mot de passe: password123" -ForegroundColor Yellow
