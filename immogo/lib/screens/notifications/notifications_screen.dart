// lib/screens/notifications/notifications_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/theme/app_theme.dart';
import '../../models/notification_model.dart';
import '../../providers/notification_provider.dart';
import '../../widgets/common/loading_widget.dart';

class NotificationsScreen extends ConsumerStatefulWidget {
  const NotificationsScreen({super.key});

  @override
  ConsumerState<NotificationsScreen> createState() =>
      _NotificationsScreenState();
}

class _NotificationsScreenState extends ConsumerState<NotificationsScreen> {
  @override
  void initState() {
    super.initState();
    Future.microtask(() => ref.read(notificationProvider.notifier).load());
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(notificationProvider);

    return Scaffold(
      appBar: AppBar(
        title: Row(
          children: [
            const Text('Notifications'),
            if (state.unreadCount > 0) ...[
              const SizedBox(width: 8),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: AppColors.error,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  '${state.unreadCount}',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ],
        ),
        actions: [
          if (state.unreadCount > 0)
            TextButton.icon(
              onPressed: () =>
                  ref.read(notificationProvider.notifier).marquerLues(),
              icon: const Icon(Icons.done_all, size: 18),
              label: const Text('Tout lire'),
              style: TextButton.styleFrom(
                foregroundColor: AppColors.primary,
              ),
            ),
        ],
      ),
      body: state.isLoading
          ? const LoadingWidget(message: 'Chargement des notifications...')
          : state.error != null
              ? AppErrorWidget(
                  message: state.error!,
                  onRetry: () =>
                      ref.read(notificationProvider.notifier).load(),
                )
              : state.notifications.isEmpty
                  ? const EmptyWidget(
                      icon: Icons.notifications_off_outlined,
                      message: 'Aucune notification pour l\'instant.',
                    )
                  : RefreshIndicator(
                      onRefresh: () =>
                          ref.read(notificationProvider.notifier).load(),
                      color: AppColors.primary,
                      child: ListView.builder(
                        padding: const EdgeInsets.symmetric(
                            vertical: 8, horizontal: 12),
                        itemCount: state.notifications.length,
                        itemBuilder: (context, index) => _NotificationTile(
                          notification: state.notifications[index],
                          onTap: () => _handleTap(
                              context, state.notifications[index]),
                        ),
                      ),
                    ),
    );
  }

  void _handleTap(BuildContext context, NotificationModel notif) {
    // Naviguer vers le lien si disponible
    if (notif.lien != null && notif.lien!.isNotEmpty) {
      // Tentative de navigation interne basée sur le lien
      final lien = notif.lien!;
      if (lien.contains('contrat')) {
        // Extraire un id potentiel
        final parts = lien.split('/');
        final id = int.tryParse(parts.last);
        if (id != null) {
          context.push('/contrats/$id');
          return;
        }
        context.push('/contrats');
      } else if (lien.contains('bien')) {
        final parts = lien.split('/');
        final id = int.tryParse(parts.last);
        if (id != null) {
          context.push('/biens/$id');
        }
      }
    }
  }
}

class _NotificationTile extends StatelessWidget {
  final NotificationModel notification;
  final VoidCallback? onTap;

  const _NotificationTile({required this.notification, this.onTap});

  @override
  Widget build(BuildContext context) {
    final isUnread = !notification.lu;

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: BoxDecoration(
        color: isUnread
            ? AppColors.primary.withOpacity(0.05)
            : Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isUnread
              ? AppColors.primary.withOpacity(0.25)
              : AppColors.divider,
        ),
        boxShadow: isUnread
            ? [
                BoxShadow(
                  color: AppColors.primary.withOpacity(0.08),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                )
              ]
            : null,
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Icône
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: isUnread
                      ? AppColors.primary.withOpacity(0.12)
                      : Colors.grey[100],
                  borderRadius: BorderRadius.circular(22),
                ),
                child: Icon(
                  _iconForNotif(notification.titre),
                  color: isUnread ? AppColors.primary : AppColors.textSecondary,
                  size: 22,
                ),
              ),
              const SizedBox(width: 12),

              // Contenu
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            notification.titre,
                            style: Theme.of(context)
                                .textTheme
                                .bodyLarge
                                ?.copyWith(
                                  fontWeight: isUnread
                                      ? FontWeight.bold
                                      : FontWeight.w500,
                                  color: isUnread
                                      ? AppColors.textPrimary
                                      : AppColors.textSecondary,
                                ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        if (isUnread)
                          Container(
                            width: 8,
                            height: 8,
                            margin: const EdgeInsets.only(left: 6),
                            decoration: const BoxDecoration(
                              color: AppColors.primary,
                              shape: BoxShape.circle,
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      notification.message,
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                            color: AppColors.textSecondary,
                            height: 1.4,
                          ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 6),
                    Row(
                      children: [
                        const Icon(Icons.access_time,
                            size: 12, color: AppColors.textSecondary),
                        const SizedBox(width: 4),
                        Text(
                          _formatDate(notification.createdAt),
                          style: Theme.of(context)
                              .textTheme
                              .labelSmall
                              ?.copyWith(color: AppColors.textSecondary),
                        ),
                        if (notification.lien != null &&
                            notification.lien!.isNotEmpty) ...[
                          const Spacer(),
                          const Icon(Icons.arrow_forward_ios,
                              size: 12, color: AppColors.primary),
                        ],
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  IconData _iconForNotif(String titre) {
    final t = titre.toLowerCase();
    if (t.contains('contrat') || t.contains('réservation') || t.contains('reservation')) {
      return Icons.handshake_outlined;
    } else if (t.contains('paiement') || t.contains('payer')) {
      return Icons.payment_outlined;
    } else if (t.contains('bien') || t.contains('propriété')) {
      return Icons.home_outlined;
    } else if (t.contains('approuv') || t.contains('confirm')) {
      return Icons.check_circle_outline;
    } else if (t.contains('annul')) {
      return Icons.cancel_outlined;
    }
    return Icons.notifications_outlined;
  }

  String _formatDate(String dateStr) {
    try {
      final date = DateTime.parse(dateStr);
      final now = DateTime.now();
      final diff = now.difference(date);
      if (diff.inMinutes < 1) return 'À l\'instant';
      if (diff.inMinutes < 60) return 'Il y a ${diff.inMinutes} min';
      if (diff.inHours < 24) return 'Il y a ${diff.inHours}h';
      if (diff.inDays == 1) return 'Hier';
      if (diff.inDays < 7) return 'Il y a ${diff.inDays} jours';
      return '${date.day.toString().padLeft(2, '0')}/${date.month.toString().padLeft(2, '0')}/${date.year}';
    } catch (_) {
      return dateStr;
    }
  }
}
