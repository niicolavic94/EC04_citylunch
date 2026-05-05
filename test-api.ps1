# Test API CityLunch
$base = "http://localhost:8000"

Write-Host "`n=== 1. Créer un produit ===" -ForegroundColor Cyan
$produit = @{ nom = "Pizza Margherita"; prix = 12.99; description = "Pizza classique" } | ConvertTo-Json
try {
    $r1 = Invoke-RestMethod -Uri "$base/api/produits" -Method POST -ContentType "application/json" -Body $produit
    $r1 | ConvertTo-Json
} catch { Write-Host $_.ErrorDetails.Message -ForegroundColor Red }

Write-Host "`n=== 2. Créer un livreur ===" -ForegroundColor Cyan
$livreur = @{ nom = "Jean Dupont"; email = "jean@example.com" } | ConvertTo-Json
try {
    $r2 = Invoke-RestMethod -Uri "$base/api/livreurs" -Method POST -ContentType "application/json" -Body $livreur
    $r2 | ConvertTo-Json
    Write-Host "Livreur créé ! Vérifie ton email (Mailtrap) pour le mot de passe." -ForegroundColor Yellow
} catch { Write-Host $_.ErrorDetails.Message -ForegroundColor Red }

# ============================================================
# Remplace MOT_DE_PASSE par le mot de passe reçu par email
$motDePasse = "06bc6eec6a2de9"
# ============================================================

if ($motDePasse -eq "MOT_DE_PASSE") {
    Write-Host "`nModifie la variable `$motDePasse dans le script avec le mot de passe reçu par email, puis relance." -ForegroundColor Yellow
    exit
}

Write-Host "`n=== 3. Login ===" -ForegroundColor Cyan
$login = @{ email = "jean@example.com"; password = $motDePasse } | ConvertTo-Json
try {
    $r3 = Invoke-RestMethod -Uri "$base/api/login" -Method POST -ContentType "application/json" -Body $login
    $token = $r3.token
    Write-Host "Token JWT: $token" -ForegroundColor Green
} catch { Write-Host $_.ErrorDetails.Message -ForegroundColor Red; exit }

Write-Host "`n=== 4. Ajouter produit au sac ===" -ForegroundColor Cyan
$sac = @{ produitId = 1; quantite = 2 } | ConvertTo-Json
try {
    $r4 = Invoke-RestMethod -Uri "$base/api/sac/ajouter" -Method POST -ContentType "application/json" -Headers @{ Authorization = "Bearer $token" } -Body $sac
    $r4 | ConvertTo-Json
} catch { Write-Host $_.ErrorDetails.Message -ForegroundColor Red }

Write-Host "`n=== 5. Consulter le sac ===" -ForegroundColor Cyan
try {
    $r5 = Invoke-RestMethod -Uri "$base/api/sac" -Method GET -Headers @{ Authorization = "Bearer $token" }
    $r5 | ConvertTo-Json
} catch { Write-Host $_.ErrorDetails.Message -ForegroundColor Red }
