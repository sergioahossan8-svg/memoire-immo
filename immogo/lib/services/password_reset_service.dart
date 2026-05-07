// lib/services/password_reset_service.dart
import 'package:dio/dio.dart';
import 'api_service.dart';
import '../core/constants/api_constants.dart';

class PasswordResetService {
  final _dio = ApiService().dio;

  /// Envoie un lien de réinitialisation à l'email donné.
  /// Retourne le message de succès ou lance une exception avec le message d'erreur.
  Future<String> sendResetLink(String email) async {
    try {
      final response = await _dio.post(
        ApiConstants.forgotPassword,
        data: {'email': email},
      );
      return response.data['message'] as String;
    } on DioException catch (e) {
      final msg = e.response?.data?['message']
          ?? e.response?.data?['errors']?['email']?.first
          ?? 'Une erreur est survenue. Réessayez.';
      throw Exception(msg);
    }
  }

  /// Réinitialise le mot de passe avec le token reçu par email.
  Future<String> resetPassword({
    required String token,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      final response = await _dio.post(
        ApiConstants.resetPassword,
        data: {
          'token': token,
          'email': email,
          'password': password,
          'password_confirmation': passwordConfirmation,
        },
      );
      return response.data['message'] as String;
    } on DioException catch (e) {
      final msg = e.response?.data?['message']
          ?? 'Une erreur est survenue. Réessayez.';
      throw Exception(msg);
    }
  }
}
