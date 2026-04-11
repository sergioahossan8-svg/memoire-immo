// lib/services/estimation_service.dart
import 'package:dio/dio.dart';
import '../core/constants/api_constants.dart';
import '../models/notification_model.dart';
import 'api_service.dart';

class EstimationService {
  final Dio _dio = ApiService().dio;

  Future<Map<String, dynamic>> estimer({
    required int typeBienId,
    required String ville,
    required double superficie,
    required String transaction,
    int? chambres,
  }) async {
    final response = await _dio.post(ApiConstants.estimer, data: {
      'type_bien_id': typeBienId,
      'ville': ville,
      'superficie': superficie,
      'transaction': transaction,
      if (chambres != null) 'chambres': chambres,
    });
    final data = response.data as Map<String, dynamic>;
    return {
      'estimation': data['estimation'] != null
          ? EstimationModel.fromJson(
              data['estimation'] as Map<String, dynamic>)
          : null,
      'message': data['message'] ?? '',
    };
  }
}
