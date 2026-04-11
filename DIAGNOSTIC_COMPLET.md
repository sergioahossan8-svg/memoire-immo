# 🔍 DIAGNOSTIC COMPLET - ImmoGo Backend ↔ Frontend

## 📋 RÉSUMÉ DE L'ARCHITECTURE

### Backend Laravel (ImmoGoB-nin)
- **Framework**: Laravel 12.0 (PHP 8.2+)
- **Auth**: Laravel Sanctum (tokens API)
- **Base de données**: SQLite (dev) / MySQL (prod)
- **Paiements**: FedaPay
- **Permissions**: Spatie Laravel Permission

### Frontend Flutter (immogo)
- **Framework**: Flutter 3.5+
- **State Management**: Riverpod
- **Navigation**: GoRouter
- **HTTP Client**: Dio
- **Storage sécurisé**: flutter_secure_storage

---

## 🔐 FLUX D'AUTHENTIFICATION

### 1. Inscription (`POST /api/register`)
**Request:**
```json
{
  "name": "Nom",
  "prenom": "Prénom",
  "email": "email@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "ville": "Cotonou",
  "telephone": "+22901234567",
  "adresse": "Adresse optionnelle"
}
```

**Response (201):**
```json
{
  "token": "1|xxxxxxxxxxxxx",
  "user": {
    "id": 1,
    "name": "Nom",
    "prenom": "Prénom",
    "email": "email@example.com",
    "telephone": "+22901234567",
    "ville": "Cotonou",
    "adresse": "Adresse",
    "role": "client",
    "avatar": null
  }
}
```

### 2. Connexion (`POST /api/login`)
**Request:**
```json
{
  "email": "email@example.com",
  "password": "password123"
}
```

**Response (200):**
```json
{
  "token": "2|xxxxxxxxxxxxx",
  "user": { ... }
}
```

### 3. Vérification (`GET /api/me`)
**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response (200):**
```json
{
  "user": { ... }
}
```

**Response (401) - Non authentifié:**
```json
{
  "message": "Unauthenticated."
}
```

---

## 🚨 PROBLÈMES IDENTIFIÉS ET SOLUTIONS

### ❌ Problème 1: Serveur Laravel non démarré
**Symptôme**: L'app Flutter reste bloquée sur le splash screen

**Solution**:
```powershell
cd C:\Memoire\ImmoGoB-nin
php artisan serve --host=0.0.0.0
```

Le serveur doit tourner sur `http://0.0.0.0:8000` pour être accessible depuis l'émulateur/appareil.

---

### ❌ Problème 2: IP locale non autorisée dans network_security_config.xml
**Symptôme**: Erreur de connexion réseau sur appareil physique

**Solution**: ✅ CORRIGÉ
Le fichier `immogo/android/app/src/main/res/xml/network_security_config.xml` a été mis à jour pour inclure `192.168.133.82`.

---

### ❌ Problème 3: Navigation après login/register
**Symptôme**: Redirection vers splash au lieu de la page d'accueil

**Solution**: ✅ CORRIGÉ
Ajout de `ref.listen()` dans `login_screen.dart` et `register_screen.dart` pour écouter les changements d'état et naviguer automatiquement.

---

### ❌ Problème 4: Logs de débogage manquants
**Symptôme**: Impossible de diagnostiquer les erreurs

**Solution**: ✅ CORRIGÉ
Logs ajoutés dans:
- `api_service.dart` (requêtes HTTP)
- `auth_service.dart` (appels API)
- `auth_provider.dart` (changements d'état)
- `splash_screen.dart` (flux d'initialisation)

---

## 📡 ENDPOINTS API DISPONIBLES

### Public (sans authentification)
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/login` | Connexion client |
| POST | `/api/register` | Inscription client |
| GET | `/api/biens` | Liste des biens |
| GET | `/api/biens/{id}` | Détails d'un bien |
| GET | `/api/types-biens` | Types de biens |
| GET | `/api/villes` | Liste des villes |
| POST | `/api/estimer` | Estimation de bien |

### Authentifié (client uniquement)
| Méthode | Endpoint | Description |
|---------|----------|-------------|
| POST | `/api/logout` | Déconnexion |
| GET | `/api/me` | Profil utilisateur |
| GET | `/api/profil` | Détails profil |
| POST | `/api/profil` | Mise à jour profil |
| GET | `/api/favoris` | Liste favoris |
| POST | `/api/favoris/{id}` | Toggle favori |
| GET | `/api/historique` | Historique contrats |
| GET | `/api/contrats/{id}` | Détails contrat |
| POST | `/api/biens/{id}/reserver` | Réserver un bien |
| POST | `/api/biens/{id}/payer-complet` | Paiement complet |
| POST | `/api/contrats/{id}/payer-solde` | Payer solde |
| GET | `/api/notifications` | Liste notifications |
| POST | `/api/notifications/lire` | Marquer comme lues |

---

## 🧪 PROCÉDURE DE TEST

### 1. Démarrer le backend
```powershell
cd C:\Memoire\ImmoGoB-nin
php artisan serve --host=0.0.0.0
```

Vérifier que le serveur affiche:
```
INFO  Server running on [http://0.0.0.0:8000]
```

### 2. Tester l'API manuellement
```powershell
cd C:\Memoire
.\test_api.ps1
```

Ce script teste:
- ✓ Connexion au serveur
- ✓ Inscription d'un nouveau client
- ✓ Vérification du token
- ✓ Connexion avec les identifiants

### 3. Démarrer l'app Flutter
```powershell
cd C:\Memoire\immogo
flutter run
```

### 4. Tester le flux complet
1. L'app démarre sur le splash screen
2. Après 300ms, elle vérifie l'authentification
3. Si non authentifié → Redirection vers `/login`
4. Créer un compte ou se connecter
5. Si succès → Redirection vers `/` (home)

---

## 📝 LOGS À SURVEILLER

### Console Flutter
```
🌐 API_BASE_URL configurée: http://192.168.133.82:8000/api
🚀 Splash: Initialisation...
🔐 Splash: Vérification auth...
🔑 AuthService: Lecture du token...
🔑 AuthService: Token = absent
✅ Splash: checkAuth terminé
🧭 Splash: Navigation vers unauthenticated
📝 Login: Tentative de connexion...
📡 AuthService: Appel API /me...
✅ AuthService: Réponse reçue
✅ Login: Authentification réussie, navigation vers /
```

### Console Laravel
```
[timestamp] POST /api/register ........................... 201
[timestamp] POST /api/login .............................. 200
[timestamp] GET /api/me .................................. 200
```

---

## 🔧 CONFIGURATION REQUISE

### Fichier `.env` Flutter (immogo/.env)
```env
# Appareil physique
API_BASE_URL=http://192.168.133.82:8000/api

# Émulateur Android
# API_BASE_URL=http://10.0.2.2:8000/api
```

### Fichier `.env` Laravel (ImmoGoB-nin/.env)
```env
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite
```

### AndroidManifest.xml
```xml
<uses-permission android:name="android.permission.INTERNET"/>
<application android:usesCleartextTraffic="true">
```

### network_security_config.xml
```xml
<domain includeSubdomains="true">192.168.133.82</domain>
<domain includeSubdomains="true">10.0.2.2</domain>
```

---

## ✅ CHECKLIST DE VÉRIFICATION

- [ ] Serveur Laravel démarré (`php artisan serve --host=0.0.0.0`)
- [ ] Migrations exécutées (`php artisan migrate`)
- [ ] Seeders exécutés (`php artisan db:seed`)
- [ ] CORS configuré (déjà fait)
- [ ] `.env` Flutter configuré avec la bonne IP
- [ ] `network_security_config.xml` inclut l'IP locale
- [ ] App Flutter redémarrée complètement (pas juste hot reload)
- [ ] Logs activés dans `api_service.dart`

---

## 🐛 DÉBOGAGE

### Si l'app reste bloquée sur le splash:
1. Vérifier que le serveur Laravel tourne
2. Vérifier les logs Flutter (emojis 🚀🔐🔑)
3. Tester l'API manuellement: `.\test_api.ps1`

### Si "Impossible de joindre le serveur":
1. Vérifier l'IP dans `.env` Flutter
2. Vérifier `network_security_config.xml`
3. Redémarrer complètement l'app (pas hot reload)
4. Vérifier le firewall Windows

### Si "401 Unauthorized" après login:
1. Vérifier que le token est bien stocké
2. Vérifier les headers `Authorization: Bearer {token}`
3. Vérifier que le rôle est "client"

---

## 📞 COMPTE DE TEST

Après avoir exécuté `.\test_api.ps1`, un compte de test est créé:
- **Email**: Affiché dans la console
- **Mot de passe**: `password123`

Ou utiliser le super admin (backend web uniquement):
- **Email**: `hessoueulogegracien@gmail.com`
- **Mot de passe**: `Euloge55`
- **Rôle**: `super_admin` (ne peut pas se connecter sur mobile)

---

## 🎯 PROCHAINES ÉTAPES

1. ✅ Démarrer le serveur Laravel
2. ✅ Exécuter le script de test: `.\test_api.ps1`
3. ✅ Redémarrer l'app Flutter complètement
4. ✅ Tester l'inscription/connexion
5. ⏳ Implémenter les autres fonctionnalités (biens, favoris, contrats, etc.)

---

**Date**: 2026-04-10
**Auteur**: Kiro AI Assistant
