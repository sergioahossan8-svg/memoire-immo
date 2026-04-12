// lib/services/auth_service.dart
import 'package:dio/dio.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../core/constants/api_constants.dart';
import '../models/user_model.dart';
import 'api_service.dart';

class AuthService {
  final Dio _dio = ApiService().dio;
  final _storage = const FlutterSecureStorage();

  Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await _dio.post(ApiConstants.login, data: {
      'email': email,
      'password': password,
    });
    final token = response.data['token'] as String;
    await _storage.write(key: 'auth_token', value: token);
    return response.data as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> register({
    required String name,
    required String prenom,
    required String email,
    required String password,
    required String passwordConfirmation,
    required String ville,
    String? telephone,
    String? adresse,
  }) async {
    final response = await _dio.post(ApiConstants.register, data: {
      'name': name,
      'prenom': prenom,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
      'ville': ville,
      if (telephone != null && telephone.isNotEmpty) 'telephone': telephone,
      if (adresse != null && adresse.isNotEmpty) 'adresse': adresse,
    });
    final token = response.data['token'] as String;
    await _storage.write(key: 'auth_token', value: token);
    return response.data as Map<String, dynamic>;
  }

  Future<void> logout() async {
    try {
      await _dio.post(ApiConstants.logout);
    } catch (_) {
      // Même en cas d'erreur, on supprime le token local
    } finally {
      await _storage.delete(key: 'auth_token');
    }
  }

  /// Vérifie si un token est stocké localement (instantané, sans réseau)
  Future<bool> hasLocalToken() async {
    final token = await _storage.read(key: 'auth_token');
    return token != null && token.isNotEmpty;
  }

  /// Supprime le token local (déconnexion locale)
  Future<void> clearToken() async {
    await _storage.delete(key: 'auth_token');
  }

  Future<UserModel?> me() async {
    final token = await _storage.read(key: 'auth_token');
    if (token == null || token.isEmpty) return null;

    try {
      final response = await _dio.get(
        ApiConstants.me,
        options: Options(
          receiveTimeout: const Duration(seconds: 20),
          sendTimeout: const Duration(seconds: 20),
        ),
      );
      return UserModel.fromJson(response.data['user'] as Map<String, dynamic>);
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) {
        // Token expiré → supprimer
        await _storage.delete(key: 'auth_token');
        return null;
      }
      // Erreur réseau/timeout → on considère unauthenticated sans crasher
      return null;
    } catch (_) {
      return null;
    }
  }

  Future<bool> isLoggedIn() async {
    final token = await _storage.read(key: 'auth_token');
    return token != null && token.isNotEmpty;
  }
}
