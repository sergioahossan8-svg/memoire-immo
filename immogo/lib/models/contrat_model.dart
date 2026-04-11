// lib/models/contrat_model.dart
import 'bien_model.dart';
import 'paiement_model.dart';

class ContratModel {
  final int id;
  final String typeContrat;
  final String statutContrat;
  final String? dateContrat;
  final double montantTotal;
  final double montantAcompte;
  final double montantPaye;
  final double soldeRestant;
  final ContratBienModel? bien;
  final List<PaiementModel>? paiements;

  const ContratModel({
    required this.id,
    required this.typeContrat,
    required this.statutContrat,
    this.dateContrat,
    required this.montantTotal,
    required this.montantAcompte,
    required this.montantPaye,
    required this.soldeRestant,
    this.bien,
    this.paiements,
  });

  factory ContratModel.fromJson(Map<String, dynamic> json) => ContratModel(
        id: json['id'],
        typeContrat: json['type_contrat'] ?? '',
        statutContrat: json['statut_contrat'] ?? '',
        dateContrat: json['date_contrat'],
        montantTotal: (json['montant_total'] as num?)?.toDouble() ?? 0,
        montantAcompte: (json['montant_acompte'] as num?)?.toDouble() ?? 0,
        montantPaye: (json['montant_paye'] as num?)?.toDouble() ?? 0,
        soldeRestant: (json['solde_restant'] as num?)?.toDouble() ?? 0,
        bien: json['bien'] != null
            ? ContratBienModel.fromJson(json['bien'])
            : null,
        paiements: json['paiements'] != null
            ? (json['paiements'] as List)
                .map((p) => PaiementModel.fromJson(p))
                .toList()
            : null,
      );
}

class ContratBienModel {
  final int id;
  final String titre;
  final String localisation;
  final String ville;
  final String transaction;
  final double prix;
  final String? photo;
  final String? agence;

  const ContratBienModel({
    required this.id,
    required this.titre,
    required this.localisation,
    required this.ville,
    required this.transaction,
    required this.prix,
    this.photo,
    this.agence,
  });

  factory ContratBienModel.fromJson(Map<String, dynamic> json) =>
      ContratBienModel(
        id: json['id'],
        titre: json['titre'] ?? '',
        localisation: json['localisation'] ?? '',
        ville: json['ville'] ?? '',
        transaction: json['transaction'] ?? '',
        prix: (json['prix'] as num?)?.toDouble() ?? 0,
        photo: json['photo'],
        agence: json['agence'],
      );
}
