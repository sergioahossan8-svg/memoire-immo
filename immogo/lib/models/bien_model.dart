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
  final bool isPremium;
  final String? photo;
  final String? typeBien;
  final String? agence;
  // Détail uniquement
  final String? description;
  final List<BienPhotoModel>? photos;
  final AgenceDetailModel? agenceDetail;

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
    required this.isPremium,
    this.photo,
    this.typeBien,
    this.agence,
    this.description,
    this.photos,
    this.agenceDetail,
  });

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
        isPremium: json['is_premium'] == true,
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

  const AgenceDetailModel(
      {required this.id,
      required this.nom,
      this.ville,
      this.secteur,
      this.logo});

  factory AgenceDetailModel.fromJson(Map<String, dynamic> json) =>
      AgenceDetailModel(
        id: json['id'],
        nom: json['nom'] ?? '',
        ville: json['ville'],
        secteur: json['secteur'],
        logo: json['logo'],
      );
}

class TypeBienModel {
  final int id;
  final String libelle;

  const TypeBienModel({required this.id, required this.libelle});

  factory TypeBienModel.fromJson(Map<String, dynamic> json) =>
      TypeBienModel(id: json['id'], libelle: json['libelle'] ?? '');
}
