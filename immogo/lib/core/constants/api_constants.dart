// lib/core/constants/api_constants.dart
import 'package:flutter_dotenv/flutter_dotenv.dart';

class ApiConstants {
  static String get baseUrl =>
      dotenv.env['API_BASE_URL'] ?? 'http://10.0.2.2:8000/api';

  // Auth
  static const String login = '/login';
  static const String register = '/register';
  static const String logout = '/logout';
  static const String me = '/me';

  // Biens
  static const String biens = '/biens';
  static String bienDetail(int id) => '/biens/$id';
  static const String typesBiens = '/types-biens';
  static const String villes = '/villes';

  // Estimation
  static const String estimer = '/estimer';

  // Favoris
  static const String favoris = '/favoris';
  static String toggleFavori(int bienId) => '/favoris/$bienId';

  // Contrats
  static const String historique = '/historique';
  static String contratDetail(int id) => '/contrats/$id';
  static String reserver(int bienId) => '/biens/$bienId/reserver';
  static String payerComplet(int bienId) => '/biens/$bienId/payer-complet';
  static String payerSolde(int contratId) => '/contrats/$contratId/payer-solde';

  // Profil
  static const String profil = '/profil';

  // Notifications
  static const String notifications = '/notifications';
  static const String notificationsLire = '/notifications/lire';
}
