// lib/models/paiement_model.dart
class PaiementModel {
  final int id;
  final double montant;
  final String? datePaiement;
  final String typePaiement;
  final String? modePaiement;
  final String? reference;
  final String statut;

  const PaiementModel({
    required this.id,
    required this.montant,
    this.datePaiement,
    required this.typePaiement,
    this.modePaiement,
    this.reference,
    required this.statut,
  });

  factory PaiementModel.fromJson(Map<String, dynamic> json) => PaiementModel(
        id: json['id'],
        montant: (json['montant'] as num?)?.toDouble() ?? 0,
        datePaiement: json['date_paiement'],
        typePaiement: json['type_paiement'] ?? '',
        modePaiement: json['mode_paiement'],
        reference: json['reference'],
        statut: json['statut'] ?? '',
      );
}
