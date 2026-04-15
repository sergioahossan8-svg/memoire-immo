# 📝 Résumé des modifications — ImmoGo

## Date : 15 avril 2026

---

## 🎯 Objectif

Corriger l'incohérence où le client pouvait **choisir** le type de contrat (location/vente) alors que c'est l'**admin de l'agence** qui le définit lors de la création du bien.

Deux problèmes identifiés et corrigés :

1. **Type de contrat modifiable** : Le client pouvait changer location ↔ vente via un dropdown
2. **Page de réservation avec switch** : Le client pouvait basculer entre "acompte 10%" et "paiement complet" sur la même page

---

## 🔧 Modifications effectuées

### 1. Frontend Flutter

#### `reservation_screen.dart`
| Avant | Après |
|-------|-------|
| Dropdown modifiable (location/vente) | Champ **non modifiable** affichant la transaction du bien |
| Boutons de switch en haut (acompte ↔ complet) | **Un seul mode** déterminé à la navigation, pas de switch |
| `_payerComplet` = variable d'état | `widget.payerComplet` = paramètre **fixe** passé au constructeur |
| Titre : "Réservation" | Titre dynamique : "Réservation" ou "Paiement en totalité" |
| Widget `_modeOption()` (supprimé) | Plus de widget de bascule — page dédiée à un seul mode |

**Nouveau comportement :**
- Si `payerComplet = false` → Page de **réservation** (acompte 10%) avec formulaire (date limite, mode de paiement)
- Si `payerComplet = true` → Page de **paiement en totalité** avec message informatif, pas de formulaire de date

#### `bien_detail_screen.dart`
| Avant | Après |
|-------|-------|
| Dialog popup pour choisir location/vente avant "Payer en totalité" | Le type est pris **directement** depuis `bien.transaction` |
| Navigation avec `type_contrat` en paramètre URL | Navigation simplifiée : `/reservation/{id}?type=complet` |

**Boutons sur la page détail :**
- **"Réserver (acompte 10%)"** → `/reservation/{id}` → Page réservation uniquement
- **"Payer en totalité"** → `/reservation/{id}?type=complet` → Page paiement complet uniquement

#### `app.dart` (routeur)
La route `/reservation/:bienId` lit maintenant le query param `type` :
```dart
GoRoute(
  path: '/reservation/:bienId',
  builder: (_, state) {
    final bienId = int.parse(state.pathParameters['bienId']!);
    final type = state.uri.queryParameters['type'];
    return ReservationScreen(
      bienId: bienId,
      payerComplet: type == 'complet',
    );
  },
),
```

---

### 2. Backend Laravel

#### `ContratApiController.php` (méthode `reserver`)
Ajout d'une **vérification de cohérence** :
```php
if ($data['type_contrat'] !== $bien->transaction) {
    return response()->json([
        'message' => 'Le type de contrat doit correspondre à la transaction du bien.'
    ], 422);
}
```

#### `PaiementApiController.php` (méthode `payerComplet`)
Même vérification ajoutée : le `type_contrat` envoyé par le mobile **doit** correspondre à la `transaction` du bien, sinon erreur 422.

---

## 📊 Flux actuel

### Réservation (acompte 10%)
```
Bien détail → Bouton "Réserver (acompte 10%)"
  → /reservation/{id}
    → Page Réservation (mode = acompte)
      → Type de contrat = bien.transaction (non modifiable)
      → Formulaire : mode de paiement + date limite
      → Bouton "Payer l'acompte via KKiapay"
        → Widget KKiapay → Paiement 10%
        → Callback → Création contrat + notification
```

### Paiement en totalité
```
Bien détail → Bouton "Payer en totalité"
  → /reservation/{id}?type=complet
    → Page Paiement (mode = complet)
      → Type de contrat = bien.transaction (non modifiable)
      → Message : "Vous allez payer la totalité du bien via KKiapay"
      → Bouton "Payer en totalité via KKiapay"
        → Widget KKiapay → Paiement 100%
        → Callback → Création contrat statut "actif" + notification
```

---

## ✅ Résultat final

| Règle | Statut |
|-------|--------|
| Le client **ne peut pas** modifier le type de contrat | ✅ Fait |
| Le type de contrat est **toujours** celui défini par l'admin | ✅ Fait |
| Le client **ne peut pas** basculer entre acompte et complet sur la même page | ✅ Fait |
| Le backend **rejette** toute requête avec un type incohérent | ✅ Fait |
| Chaque bouton ouvre une page **dédiée** à un seul mode | ✅ Fait |

---

## 📁 Fichiers modifiés

| Fichier | Type | Modifications |
|---------|------|---------------|
| `immogo/lib/screens/contrats/reservation_screen.dart` | Flutter | Suppression du switch, paramètre `payerComplet` fixe |
| `immogo/lib/screens/biens/bien_detail_screen.dart` | Flutter | Suppression du dialog de choix du type |
| `immogo/lib/app.dart` | Flutter | Route lit le paramètre `?type=complet` |
| `ImmoGoB-nin/app/Http/Controllers/Api/ContratApiController.php` | Laravel | Vérification type_contrat === bien.transaction |
| `ImmoGoB-nin/app/Http/Controllers/Api/PaiementApiController.php` | Laravel | Vérification type_contrat === bien.transaction |
