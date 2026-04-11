// lib/services/bien_service.dart
import 'package:dio/dio.dart';
import '../core/constants/api_constants.dart';
import '../models/bien_model.dart';
import 'api_service.dart';

class BienService {
  final Dio _dio = ApiService().dio;

  Future<Map<String, dynamic>> getBiens({
    String? transaction,
    int? typeBienId,
    String? ville,
    double? prixMin,
    double? prixMax,
    String? search,
    bool? premium,
    int page = 1,
  }) async {
    final params = <String, dynamic>{'page': page};
    if (transaction != null) params['transaction'] = transaction;
    if (typeBienId != null) params['type_bien_id'] = typeBienId;
    if (ville != null && ville.isNotEmpty) params['ville'] = ville;
    if (prixMin != null) params['prix_min'] = prixMin;
    if (prixMax != null) params['prix_max'] = prixMax;
    if (search != null && search.isNotEmpty) params['search'] = search;
    if (premium == true) params['premium'] = 1;

    final response =
        await _dio.get(ApiConstants.biens, queryParameters: params);
    final data = response.data as Map<String, dynamic>;
    final biens = (data['data'] as List)
        .map((b) => BienModel.fromJson(b as Map<String, dynamic>))
        .toList();
    return {
      'biens': biens,
      'pagination': data['pagination'],
    };
  }

  Future<Map<String, dynamic>> getBien(int id) async {
    final response = await _dio.get(ApiConstants.bienDetail(id));
    final data = response.data as Map<String, dynamic>;
    return {
      'bien': BienModel.fromJson(data['bien'] as Map<String, dynamic>),
      'similaires': (data['similaires'] as List)
          .map((b) => BienModel.fromJson(b as Map<String, dynamic>))
          .toList(),
    };
  }

  Future<List<TypeBienModel>> getTypes() async {
    final response = await _dio.get(ApiConstants.typesBiens);
    return (response.data['types'] as List)
        .map((t) => TypeBienModel.fromJson(t as Map<String, dynamic>))
        .toList();
  }

  Future<List<String>> getVilles() async {
    final response = await _dio.get(ApiConstants.villes);
    return (response.data['villes'] as List).map((v) => v.toString()).toList();
  }
}
