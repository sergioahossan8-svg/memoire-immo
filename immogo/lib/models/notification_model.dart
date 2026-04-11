// lib/models/notification_model.dart
class NotificationModel {
  final int id;
  final String titre;
  final String message;
  final String? lien;
  final bool lu;
  final String createdAt;

  const NotificationModel({
    required this.id,
    required this.titre,
    required this.message,
    this.lien,
    required this.lu,
    required this.createdAt,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) =>
      NotificationModel(
        id: json['id'],
        titre: json['titre'] ?? '',
        message: json['message'] ?? '',
        lien: json['lien'],
        lu: json['lu'] == true,
        createdAt: json['created_at'] ?? '',
      );
}

class EstimationModel {
  final double min;
  final double max;
  final double moyen;
  final double prixM2;
  final int nbBiens;
  final String ville;
  final String type;
  final double superficie;
  final String transaction;

  const EstimationModel({
    required this.min,
    required this.max,
    required this.moyen,
    required this.prixM2,
    required this.nbBiens,
    required this.ville,
    required this.type,
    required this.superficie,
    required this.transaction,
  });

  factory EstimationModel.fromJson(Map<String, dynamic> json) =>
      EstimationModel(
        min: (json['min'] as num?)?.toDouble() ?? 0,
        max: (json['max'] as num?)?.toDouble() ?? 0,
        moyen: (json['moyen'] as num?)?.toDouble() ?? 0,
        prixM2: (json['prix_m2'] as num?)?.toDouble() ?? 0,
        nbBiens: json['nb_biens'] ?? 0,
        ville: json['ville'] ?? '',
        type: json['type'] ?? '',
        superficie: (json['superficie'] as num?)?.toDouble() ?? 0,
        transaction: json['transaction'] ?? '',
      );
}
