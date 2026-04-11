// lib/services/contrat_service.dart
import 'package:dio/dio.dart';
import '../core/constants/api_constants.dart';
import '../models/contrat_model.dart';
import 'api_service.dart';

class ContratService {
  final Dio _dio = ApiService().dio;

  Future<List<ContratModel>> getHistorique() async {
    final response = await _dio.get(ApiConstants.historique);
    return (response.data['contrats'] as List)
        .map((c) => ContratModel.fromJson(c as Map<String, dynamic>))
        .toList();
  }

  Future<ContratModel> getContrat(int id) async {
    final response = await _dio.get(ApiConstants.contratDetail(id));
    return ContratModel.fromJson(
        response.data['contrat'] as Map<String, dynamic>);
  }

  Future<Map<String, dynamic>> reserver({
    required int bienId,
    required String typeContrat,
    required String dateLimite,
    required String modePaiement,
  }) async {
    final response = await _dio.post(ApiConstants.reserver(bienId), data: {
      'type_contrat': typeContrat,
      'date_limite': dateLimite,
      'mode_paiement': modePaiement,
    });
    return response.data as Map<String, dynamic>;
  }
}
