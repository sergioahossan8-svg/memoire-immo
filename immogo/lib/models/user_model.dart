// lib/models/user_model.dart
class UserModel {
  final int id;
  final String name;
  final String prenom;
  final String email;
  final String? telephone;
  final String? ville;
  final String? adresse;
  final String role;
  final String? avatar;

  const UserModel({
    required this.id,
    required this.name,
    required this.prenom,
    required this.email,
    this.telephone,
    this.ville,
    this.adresse,
    required this.role,
    this.avatar,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) => UserModel(
        id: json['id'],
        name: json['name'] ?? '',
        prenom: json['prenom'] ?? '',
        email: json['email'] ?? '',
        telephone: json['telephone'],
        ville: json['ville'],
        adresse: json['adresse'],
        role: json['role'] ?? 'client',
        avatar: json['avatar'],
      );

  String get fullName => '$prenom $name';
}
