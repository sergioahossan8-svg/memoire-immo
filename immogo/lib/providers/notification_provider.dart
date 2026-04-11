// lib/providers/notification_provider.dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/notification_model.dart';
import '../services/notification_service.dart';

class NotificationState {
  final List<NotificationModel> notifications;
  final int unreadCount;
  final bool isLoading;
  final String? error;

  const NotificationState({
    this.notifications = const [],
    this.unreadCount = 0,
    this.isLoading = false,
    this.error,
  });

  NotificationState copyWith({
    List<NotificationModel>? notifications,
    int? unreadCount,
    bool? isLoading,
    String? error,
  }) {
    return NotificationState(
      notifications: notifications ?? this.notifications,
      unreadCount: unreadCount ?? this.unreadCount,
      isLoading: isLoading ?? this.isLoading,
      error: error,
    );
  }
}

class NotificationNotifier extends StateNotifier<NotificationState> {
  final NotificationService _service;

  NotificationNotifier(this._service) : super(const NotificationState());

  Future<void> load() async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final result = await _service.getNotifications();
      state = state.copyWith(
        notifications:
            result['notifications'] as List<NotificationModel>,
        unreadCount: result['unread_count'] as int,
        isLoading: false,
      );
    } catch (e) {
      state = state.copyWith(
          isLoading: false, error: 'Erreur de chargement.');
    }
  }

  Future<void> marquerLues() async {
    try {
      await _service.marquerLues();
      state = state.copyWith(
        unreadCount: 0,
        notifications: state.notifications
            .map((n) => NotificationModel(
                id: n.id,
                titre: n.titre,
                message: n.message,
                lien: n.lien,
                lu: true,
                createdAt: n.createdAt))
            .toList(),
      );
    } catch (_) {}
  }
}

final notificationServiceProvider =
    Provider<NotificationService>((ref) => NotificationService());

final notificationProvider =
    StateNotifierProvider<NotificationNotifier, NotificationState>(
        (ref) {
  return NotificationNotifier(ref.read(notificationServiceProvider));
});
