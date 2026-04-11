// lib/services/favori_service.dart
import 'package:dio/dio.dart';
import '../core/constants/api_constants.dart';
import '../models/bien_model.dart';
import 'api_service.dart';

class FavoriService {
  final Dio _dio = ApiService().dio;

  Future<List<BienModel>> getFavoris() async {
    final response = await _dio.get(ApiConstants.favoris);
    return (response.data['favoris'] as List)
        .map((b) => BienModel.fromJson(b as Map<String, dynamic>))
        .toList();
  }

  Future<bool> toggle(int bienId) async {
    final response =
        await _dio.post(ApiConstants.toggleFavori(bienId));
    return response.data['added'] == true;
  }
}
