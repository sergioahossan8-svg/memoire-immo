// lib/screens/paiement/paiement_confirmation_screen.dart
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../core/theme/app_theme.dart';
import '../../widgets/common/custom_button.dart';

class PaiementConfirmationScreen extends StatelessWidget {
  final String? reference;
  final bool success;

  const PaiementConfirmationScreen({
    super.key,
    this.reference,
    required this.success,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: Padding(
            padding: const EdgeInsets.all(32),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  width: 100,
                  height: 100,
                  decoration: BoxDecoration(
                    color: success
                        ? AppColors.success.withOpacity(0.1)
                        : AppColors.error.withOpacity(0.1),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(
                    success ? Icons.check_circle : Icons.cancel,
                    size: 60,
                    color: success ? AppColors.success : AppColors.error,
                  ),
                ),
                const SizedBox(height: 24),
                Text(
                  success ? 'Paiement confirmé !' : 'Paiement échoué',
                  style: Theme.of(context).textTheme.headlineMedium,
                ),
                const SizedBox(height: 12),
                Text(
                  success
                      ? 'Votre transaction a été traitée avec succès. Retrouvez votre contrat dans l\'historique.'
                      : 'Votre paiement a été refusé ou annulé. Aucune réservation n\'a été créée.',
                  textAlign: TextAlign.center,
                  style: Theme.of(context).textTheme.bodyLarge,
                ),
                if (reference != null) ...[
                  const SizedBox(height: 16),
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 16, vertical: 10),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withOpacity(0.07),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      'Référence : $reference',
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          fontWeight: FontWeight.w600,
                          color: AppColors.primary),
                    ),
                  ),
                ],
                const SizedBox(height: 40),
                CustomButton(
                  label: success
                      ? 'Voir mes contrats'
                      : 'Retour à l\'accueil',
                  onPressed: () => context.go('/'),
                ),
                if (success) ...[
                  const SizedBox(height: 12),
                  CustomButton(
                    label: 'Voir mes contrats',
                    outlined: true,
                    onPressed: () => context.go('/'),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}
