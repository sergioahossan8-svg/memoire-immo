// lib/services/profil_service.dart
import 'dart:io';
import 'package:dio/dio.dart';
import '../core/constants/api_constants.dart';
import '../models/user_model.dart';
import 'api_service.dart';

class ProfilService {
  final Dio _dio = ApiService().dio;

  Future<UserModel> getProfil() async {
    final response = await _dio.get(ApiConstants.profil);
    return UserModel.fromJson(
        response.data['user'] as Map<String, dynamic>);
  }

  Future<UserModel> updateProfil({
    required String name,
    required String prenom,
    String? telephone,
    String? ville,
    String? adresse,
    String? password,
    String? passwordConfirmation,
    File? avatar,
  }) async {
    FormData formData = FormData.fromMap({
      'name': name,
      'prenom': prenom,
      if (telephone != null && telephone.isNotEmpty) 'telephone': telephone,
      if (ville != null && ville.isNotEmpty) 'ville': ville,
      if (adresse != null && adresse.isNotEmpty) 'adresse': adresse,
      if (password != null && password.isNotEmpty) 'password': password,
      if (passwordConfirmation != null && passwordConfirmation.isNotEmpty)
        'password_confirmation': passwordConfirmation,
      if (avatar != null)
        'avatar': await MultipartFile.fromFile(avatar.path,
            filename: avatar.path.split('/').last),
    });

    final response = await _dio.post(
      ApiConstants.profil,
      data: formData,
      options: Options(contentType: 'multipart/form-data'),
    );
    return UserModel.fromJson(
        response.data['user'] as Map<String, dynamic>);
  }
}
