# 🔧 SOLUTION - Erreur "Out of Memory" lors du build

## 📊 DIAGNOSTIC

### Erreur rencontrée :
```
../../runtime/vm/zone.cc: 96: error: Out of memory.
Skipped 575 frames! The application may be doing too much work on its main thread.
```

### Cause principale :
- **Trop de mémoire allouée** dans `gradle.properties` (8 Go demandé, téléphone en a 2-3 Go)
- **Build en mode debug** qui consomme énormément de RAM
- **WebView Kkiapay** avec HTML/CSS/JS lourd

---

## ✅ SOLUTIONS APPLIQUÉES

### 1. Réduction mémoire JVM (`android/gradle.properties`)
**Avant :** `-Xmx8G -XX:MaxMetaspaceSize=4G`  
**Après :** `-Xmx2048m -XX:MaxMetaspaceSize=256m`

### 2. Build en mode release (PLUS STABLE)
```powershell
cd C:\Memoire\immogo

# Nettoyage complet
flutter clean

# Rebuild dependencies
flutter pub get

# Build en mode release (optimisé)
flutter build apk --release --split-per-abi
```

**Note :** `--split-per-abi` crée des APKs séparés pour chaque architecture (arm64, armeabi, x86), réduisant la taille de 40-60%.

### 3. Alternative : Build avec moins d'optimisations
```powershell
# Si le build échoue encore, utilisez :
flutter build apk --release --no-tree-shake-icons --no-shrink
```

---

## 🚀 COMMANDES RECOMMANDÉES

### Pour développement (test local) :
```powershell
flutter run --release
```

### Pour distribution (APK final) :
```powershell
flutter build apk --release --split-per-abi
```

Les APKs seront dans : `build\app\outputs\flutter-apk\`
- `app-arm64-v8a-release.apk` (la plupart des téléphones modernes)
- `app-armeabi-v7a-release.apk` (anciens téléphones)
- `app-x86_64-release.apk` (émulateurs)

---

## 📱 INSTALLATION SUR LE TÉLÉPHONE

### Méthode 1 : Transfert direct
1. Copiez `app-arm64-v8a-release.apk` sur le téléphone
2. Installez (autorisez "Sources inconnues" dans Paramètres > Sécurité)

### Méthode 2 : Via Flutter
```powershell
# Connectez le téléphone en USB (débogage USB activé)
flutter run --release
```

---

## ⚠️ SI LE PROBLÈME PERSISTE

### Option A : Réduire encore la mémoire
```properties
# Dans android/gradle.properties
org.gradle.jvmargs=-Xmx1536m -XX:MaxMetaspaceSize=128m
```

### Option B : Activer le swap sur le téléphone
Le téléphone manque de RAM → fermez toutes les autres apps avant d'installer.

### Option C : Utiliser AppBundle au lieu d'APK
```powershell
flutter build appbundle --release
```
Puis utilisez [App Bundle Tool](https://developer.android.com/studio/command-line/bundletool) pour générer des APKs optimisés.

---

## 🔍 AMÉLIORATION 1 : Paiement total via Kkiapay

### Compréhension du besoin :
> "Je veux que quand on choisit un bien et qu'on veut la totalité, ça appelle Kkiapay et que la personne paye le solde total."

### Implémentation existante :
Le code existe déjà dans `reservation_screen.dart` :
- Ligne 66-71 : `payerComplet()` → appelle Kkiapay pour le montant total
- Ligne 84-89 : `initReservation()` → appelle Kkiapay pour l'acompte (10%)

### Vérification :
```dart
// Dans reservation_screen.dart
if (_isComplet) {
  final kkiapayData = await PaiementService().payerComplet(
    bienId: widget.bienId,
    typeContrat: _typeContrat,
  );
  if (mounted) {
    context.push('/paiement/kkiapay', extra: kkiapayData);
  }
}
```

**Cette fonctionnalité est déjà implémentée !** ✅

---

## 📝 CHECKLIST FINALE

- [x] Réduire `gradle.properties` (2 Go au lieu de 8 Go)
- [ ] Exécuter `flutter clean`
- [ ] Exécuter `flutter pub get`
- [ ] Build release : `flutter build apk --release --split-per-abi`
- [ ] Tester l'APK sur le téléphone cible
- [ ] Vérifier que Kkiapay fonctionne

---

**Date :** 2026-04-15  
**Auteur :** Qwen Code
