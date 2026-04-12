// lib/services/paiement_service.dart
import 'package:dio/dio.dart';
import '../core/constants/api_constants.dart';
import 'api_service.dart';

class PaiementService {
  final Dio _dio = ApiService().dio;

  Future<Map<String, dynamic>> initReservation({
    required int bienId,
    required String reservationKey,
  }) async {
    final response =
        await _dio.post(ApiConstants.initReservation(bienId), data: {
      'reservation_key': reservationKey,
    });
    return response.data as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> payerComplet({
    required int bienId,
    required String typeContrat,
  }) async {
    final response =
        await _dio.post(ApiConstants.payerComplet(bienId), data: {
      'type_contrat': typeContrat,
    });
    return response.data as Map<String, dynamic>;
  }

  Future<Map<String, dynamic>> payerSolde({
    required int contratId,
    required double montant,
    required String typePaiement,
  }) async {
    final response =
        await _dio.post(ApiConstants.payerSolde(contratId), data: {
      'montant': montant,
      'type_paiement': typePaiement,
    });
    return response.data as Map<String, dynamic>;
  }
}
