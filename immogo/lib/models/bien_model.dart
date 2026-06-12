// lib/models/bien_model.dart
class BienModel {
  final int id;
  final String titre;
  final double prix;
  final String prixFormate;
  final double? superficie;
  final String localisation;
  final String ville;
  final int? chambres;
  final int? sallesBain;
  final String transaction;
  final String statut;
  final String? photo;
  final String? typeBien;
  final String? agence;
  // Détail uniquement
  final String? description;
  final List<BienPhotoModel>? photos;
  final AgenceDetailModel? agenceDetail;
  // Conditions de location (exposées pour les biens en location)
  final int? avanceMois;
  final double? cautionEau;
  final double? cautionElectricite;
  final double? montantTotalLocation;

  const BienModel({
    required this.id,
    required this.titre,
    required this.prix,
    required this.prixFormate,
    this.superficie,
    required this.localisation,
    required this.ville,
    this.chambres,
    this.sallesBain,
    required this.transaction,
    required this.statut,
    this.photo,
    this.typeBien,
    this.agence,
    this.description,
    this.photos,
    this.agenceDetail,
    this.avanceMois,
    this.cautionEau,
    this.cautionElectricite,
    this.montantTotalLocation,
  });

  /// Montant total à payer pour une location
  /// (prix × avance_mois) + caution_eau + caution_electricite
  /// Pour une vente, retourne simplement le prix
  double get montantTotal {
    if (transaction == 'location') {
      return montantTotalLocation ?? prix;
    }
    return prix;
  }

  factory BienModel.fromJson(Map<String, dynamic> json) => BienModel(
        id: json['id'],
        titre: json['titre'] ?? '',
        prix: (json['prix'] as num?)?.toDouble() ?? 0,
        prixFormate: json['prix_formate'] ?? '',
        superficie: (json['superficie'] as num?)?.toDouble(),
        localisation: json['localisation'] ?? '',
        ville: json['ville'] ?? '',
        chambres: json['chambres'],
        sallesBain: json['salles_bain'],
        transaction: json['transaction'] ?? '',
        statut: json['statut'] ?? '',
        photo: json['photo'],
        typeBien: json['type_bien'],
        agence: json['agence'],
        description: json['description'],
        photos: json['photos'] != null
            ? (json['photos'] as List)
                .map((p) => BienPhotoModel.fromJson(p))
                .toList()
            : null,
        agenceDetail: json['agence_detail'] != null
            ? AgenceDetailModel.fromJson(json['agence_detail'])
            : null,
        avanceMois: json['avance_mois'] as int?,
        cautionEau: (json['caution_eau'] as num?)?.toDouble(),
        cautionElectricite: (json['caution_electricite'] as num?)?.toDouble(),
        montantTotalLocation:
            (json['montant_total_location'] as num?)?.toDouble(),
      );
}

class BienPhotoModel {
  final int id;
  final String url;
  final bool isPrincipale;

  const BienPhotoModel(
      {required this.id, required this.url, required this.isPrincipale});

  factory BienPhotoModel.fromJson(Map<String, dynamic> json) => BienPhotoModel(
        id: json['id'],
        url: json['url'] ?? '',
        isPrincipale: json['is_principale'] == true,
      );
}

class AgenceDetailModel {
  final int id;
  final String nom;
  final String? ville;
  final String? secteur;
  final String? logo;
  final String? banqueNom;
  final String? banqueTitulaire;
  final String? banqueIban;
  final String? banqueSwift;
  final bool hasBanque;

  const AgenceDetailModel({
    required this.id,
    required this.nom,
    this.ville,
    this.secteur,
    this.logo,
    this.banqueNom,
    this.banqueTitulaire,
    this.banqueIban,
    this.banqueSwift,
    this.hasBanque = false,
  });

  factory AgenceDetailModel.fromJson(Map<String, dynamic> json) =>
      AgenceDetailModel(
        id: json['id'],
        nom: json['nom'] ?? '',
        ville: json['ville'],
        secteur: json['secteur'],
        logo: json['logo'],
        banqueNom: json['banque_nom'],
        banqueTitulaire: json['banque_titulaire'],
        banqueIban: json['banque_iban'],
        banqueSwift: json['banque_swift'],
        hasBanque: json['has_banque'] == true,
      );
}

class TypeBienModel {
  final int id;
  final String libelle;

  const TypeBienModel({required this.id, required this.libelle});

  factory TypeBienModel.fromJson(Map<String, dynamic> json) =>
      TypeBienModel(id: json['id'], libelle: json['libelle'] ?? '');
}
