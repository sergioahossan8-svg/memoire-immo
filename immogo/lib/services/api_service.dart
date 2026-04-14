// lib/services/api_service.dart
import 'package:dio/dio.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class ApiService {
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;

  final _storage = const FlutterSecureStorage();

  late final Dio dio = Dio(
    BaseOptions(
      baseUrl: dotenv.env['API_BASE_URL'] ?? 'http://10.0.2.2:8000/api',
      connectTimeout: const Duration(seconds: 60),
      receiveTimeout: const Duration(seconds: 60),
      sendTimeout: const Duration(seconds: 60),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ),
  )..interceptors.addAll([
      _AuthInterceptor(_storage),
      // Log seulement en debug
      LogInterceptor(
        requestBody: false,
        responseBody: false,
        logPrint: (obj) => print(obj),
      ),
    ]);

  ApiService._internal() {
    final baseUrl = dotenv.env['API_BASE_URL'] ?? 'http://10.0.2.2:8000/api';
    print('🌐 API_BASE_URL: $baseUrl');
  }
}

class _AuthInterceptor extends Interceptor {
  final FlutterSecureStorage _storage;
  _AuthInterceptor(this._storage);

  @override
  void onRequest(
      RequestOptions options, RequestInterceptorHandler handler) async {
    final token = await _storage.read(key: 'auth_token');
    if (token != null && token.isNotEmpty) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    handler.next(options);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    if (err.response?.statusCode == 401) {
      _storage.delete(key: 'auth_token');
    }
    handler.next(err);
  }
}

/// Extrait un message d'erreur lisible depuis une DioException
String extractErrorMessage(DioException e) {
  try {
    final data = e.response?.data;
    if (data is Map) {
      if (data['message'] != null) return data['message'].toString();
      if (data['errors'] != null) {
        final errors = data['errors'] as Map;
        return errors.values.first is List
            ? (errors.values.first as List).first.toString()
            : errors.values.first.toString();
      }
    }
  } catch (_) {}
  switch (e.response?.statusCode) {
    case 401:
      return 'Identifiants incorrects ou session expirée.';
    case 403:
      return 'Accès non autorisé.';
    case 404:
      return 'Ressource introuvable.';
    case 422:
      return 'Données invalides. Vérifiez les champs.';
    case 500:
      return 'Erreur serveur. Réessayez plus tard.';
    default:
      if (e.type == DioExceptionType.connectionTimeout ||
          e.type == DioExceptionType.receiveTimeout ||
          e.type == DioExceptionType.sendTimeout) {
        return 'Délai de connexion dépassé. Vérifiez votre réseau.';
      }
      if (e.type == DioExceptionType.connectionError) {
        return 'Impossible de joindre le serveur. Vérifiez votre connexion.';
      }
      return 'Une erreur est survenue. Réessayez.';
  }
}
