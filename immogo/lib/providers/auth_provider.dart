// lib/providers/auth_provider.dart
import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/user_model.dart';
import '../services/auth_service.dart';
import '../services/api_service.dart';

enum AuthStatus { loading, authenticated, unauthenticated, error }

class AuthState {
  final AuthStatus status;
  final UserModel? user;
  final String? errorMessage;

  const AuthState({
    required this.status,
    this.user,
    this.errorMessage,
  });

  AuthState copyWith(
      {AuthStatus? status, UserModel? user, String? errorMessage}) {
    return AuthState(
      status: status ?? this.status,
      user: user ?? this.user,
      errorMessage: errorMessage,
    );
  }
}

class AuthNotifier extends StateNotifier<AuthState> {
  final AuthService _authService;

  AuthNotifier(this._authService)
      : super(const AuthState(status: AuthStatus.loading));

  Future<void> checkAuth() async {
    try {
      final user = await _authService.me();
      state = user != null
          ? AuthState(status: AuthStatus.authenticated, user: user)
          : const AuthState(status: AuthStatus.unauthenticated);
    } catch (e) {
      state = const AuthState(status: AuthStatus.unauthenticated);
    }
  }

  Future<void> login(String email, String password) async {
    state = state.copyWith(status: AuthStatus.loading);
    try {
      final data = await _authService.login(email, password);
      final user = UserModel.fromJson(data['user'] as Map<String, dynamic>);
      state = AuthState(status: AuthStatus.authenticated, user: user);
    } on DioException catch (e) {
      final msg = extractErrorMessage(e);
      state = AuthState(
          status: AuthStatus.error, errorMessage: msg, user: state.user);
    } catch (e) {
      state = AuthState(
          status: AuthStatus.error,
          errorMessage: 'Une erreur est survenue. Réessayez.',
          user: state.user);
    }
  }

  Future<void> register({
    required String name,
    required String prenom,
    required String email,
    required String password,
    required String passwordConfirmation,
    required String ville,
    String? telephone,
    String? adresse,
  }) async {
    state = state.copyWith(status: AuthStatus.loading);
    try {
      final data = await _authService.register(
        name: name,
        prenom: prenom,
        email: email,
        password: password,
        passwordConfirmation: passwordConfirmation,
        ville: ville,
        telephone: telephone,
        adresse: adresse,
      );
      final user = UserModel.fromJson(data['user'] as Map<String, dynamic>);
      state = AuthState(status: AuthStatus.authenticated, user: user);
    } on DioException catch (e) {
      final msg = extractErrorMessage(e);
      state = AuthState(
          status: AuthStatus.error, errorMessage: msg, user: state.user);
    } catch (e) {
      state = AuthState(
          status: AuthStatus.error,
          errorMessage: 'Une erreur est survenue. Réessayez.',
          user: state.user);
    }
  }

  Future<void> logout() async {
    await _authService.logout();
    state = const AuthState(status: AuthStatus.unauthenticated);
  }

  void updateUser(UserModel user) {
    state = state.copyWith(status: AuthStatus.authenticated, user: user);
  }

  void setUnauthenticated() {
    state = const AuthState(status: AuthStatus.unauthenticated);
  }

  void setAuthenticated() {
    state = AuthState(status: AuthStatus.authenticated, user: state.user);
  }
}

final authServiceProvider = Provider<AuthService>((ref) => AuthService());

final authProvider =
    StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  return AuthNotifier(ref.read(authServiceProvider));
});
