// lib/providers/profil_provider.dart
import 'dart:io';
import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/user_model.dart';
import '../services/profil_service.dart';
import '../services/api_service.dart';

class ProfilNotifier extends StateNotifier<AsyncValue<UserModel>> {
  final ProfilService _service;

  ProfilNotifier(this._service) : super(const AsyncValue.loading());

  Future<void> load() async {
    state = const AsyncValue.loading();
    try {
      final user = await _service.getProfil();
      state = AsyncValue.data(user);
    } catch (e, s) {
      state = AsyncValue.error(e, s);
    }
  }

  Future<String?> update({
    required String name,
    required String prenom,
    String? telephone,
    String? ville,
    String? adresse,
    String? password,
    String? passwordConfirmation,
    File? avatar,
  }) async {
    try {
      final user = await _service.updateProfil(
        name: name,
        prenom: prenom,
        telephone: telephone,
        ville: ville,
        adresse: adresse,
        password: password,
        passwordConfirmation: passwordConfirmation,
        avatar: avatar,
      );
      state = AsyncValue.data(user);
      return null;
    } on DioException catch (e) {
      return extractErrorMessage(e);
    }
  }
}

final profilServiceProvider =
    Provider<ProfilService>((ref) => ProfilService());

final profilProvider =
    StateNotifierProvider<ProfilNotifier, AsyncValue<UserModel>>(
        (ref) {
  return ProfilNotifier(ref.read(profilServiceProvider));
});
