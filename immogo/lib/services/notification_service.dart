// lib/services/notification_service.dart
import 'package:dio/dio.dart';
import '../core/constants/api_constants.dart';
import '../models/notification_model.dart';
import 'api_service.dart';

class NotificationService {
  final Dio _dio = ApiService().dio;

  Future<Map<String, dynamic>> getNotifications({int page = 1}) async {
    final response = await _dio.get(ApiConstants.notifications,
        queryParameters: {'page': page});
    final data = response.data as Map<String, dynamic>;
    return {
      'notifications': (data['notifications'] as List)
          .map((n) => NotificationModel.fromJson(n as Map<String, dynamic>))
          .toList(),
      'unread_count': data['unread_count'] ?? 0,
      'pagination': data['pagination'],
    };
  }

  Future<void> marquerLues() async {
    await _dio.post(ApiConstants.notificationsLire);
  }
}
