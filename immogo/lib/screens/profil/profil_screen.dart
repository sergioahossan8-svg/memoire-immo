// lib/screens/profil/profil_screen.dart
import 'dart:io';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import '../../core/theme/app_theme.dart';
import '../../providers/auth_provider.dart';
import '../../providers/profil_provider.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/custom_text_field.dart';
import '../../widgets/common/loading_widget.dart';

class ProfilScreen extends ConsumerStatefulWidget {
  const ProfilScreen({super.key});

  @override
  ConsumerState<ProfilScreen> createState() => _ProfilScreenState();
}

class _ProfilScreenState extends ConsumerState<ProfilScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  // Contrôleurs formulaire infos
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _prenomCtrl = TextEditingController();
  final _telephoneCtrl = TextEditingController();
  final _villeCtrl = TextEditingController();
  final _adresseCtrl = TextEditingController();

  // Contrôleurs formulaire mot de passe
  final _pwFormKey = GlobalKey<FormState>();
  final _pwCtrl = TextEditingController();
  final _pwConfirmCtrl = TextEditingController();
  bool _obscurePw = true;
  bool _obscurePwConfirm = true;

  File? _avatarFile;
  bool _isSaving = false;
  bool _isSavingPw = false;
  bool _loaded = false;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    // Charger le profil au démarrage
    Future.microtask(() => ref.read(profilProvider.notifier).load());
  }

  @override
  void dispose() {
    _tabController.dispose();
    _nameCtrl.dispose();
    _prenomCtrl.dispose();
    _telephoneCtrl.dispose();
    _villeCtrl.dispose();
    _adresseCtrl.dispose();
    _pwCtrl.dispose();
    _pwConfirmCtrl.dispose();
    super.dispose();
  }

  void _populateFields(user) {
    if (_loaded) return;
    _loaded = true;
    _nameCtrl.text = user.name;
    _prenomCtrl.text = user.prenom;
    _telephoneCtrl.text = user.telephone ?? '';
    _villeCtrl.text = user.ville ?? '';
    _adresseCtrl.text = user.adresse ?? '';
  }

  Future<void> _pickAvatar() async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(
      source: ImageSource.gallery,
      imageQuality: 80,
      maxWidth: 800,
    );
    if (picked != null) {
      setState(() => _avatarFile = File(picked.path));
    }
  }

  Future<void> _saveInfos() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isSaving = true);

    final error = await ref.read(profilProvider.notifier).update(
          name: _nameCtrl.text.trim(),
          prenom: _prenomCtrl.text.trim(),
          telephone: _telephoneCtrl.text.trim(),
          ville: _villeCtrl.text.trim(),
          adresse: _adresseCtrl.text.trim(),
          avatar: _avatarFile,
        );

    setState(() {
      _isSaving = false;
      if (_avatarFile != null) _avatarFile = null;
    });

    if (!mounted) return;
    if (error == null) {
      // Rafraîchir aussi le provider auth pour mettre à jour l'avatar dans la nav
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Profil mis à jour avec succès !'),
          backgroundColor: AppColors.success,
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(error), backgroundColor: AppColors.error),
      );
    }
  }

  Future<void> _savePw() async {
    if (!_pwFormKey.currentState!.validate()) return;
    setState(() => _isSavingPw = true);

    final error = await ref.read(profilProvider.notifier).update(
          name: _nameCtrl.text.trim(),
          prenom: _prenomCtrl.text.trim(),
          password: _pwCtrl.text,
          passwordConfirmation: _pwConfirmCtrl.text,
        );

    setState(() => _isSavingPw = false);
    if (!mounted) return;

    if (error == null) {
      _pwCtrl.clear();
      _pwConfirmCtrl.clear();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Mot de passe modifié avec succès !'),
          backgroundColor: AppColors.success,
        ),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(error), backgroundColor: AppColors.error),
      );
    }
  }

  Future<void> _logout() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Déconnexion'),
        content: const Text('Êtes-vous sûr de vouloir vous déconnecter ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child:
                const Text('Déconnecter', style: TextStyle(color: AppColors.error)),
          ),
        ],
      ),
    );
    if (confirm == true) {
      await ref.read(authProvider.notifier).logout();
      if (mounted) context.go('/login');
    }
  }

  @override
  Widget build(BuildContext context) {
    final profilAsync = ref.watch(profilProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Mon profil'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout, color: AppColors.error),
            tooltip: 'Déconnexion',
            onPressed: _logout,
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(icon: Icon(Icons.person_outline), text: 'Informations'),
            Tab(icon: Icon(Icons.lock_outline), text: 'Mot de passe'),
          ],
        ),
      ),
      body: profilAsync.when(
        loading: () => const LoadingWidget(message: 'Chargement du profil...'),
        error: (e, _) => AppErrorWidget(
          message: 'Impossible de charger votre profil.',
          onRetry: () {
            _loaded = false;
            ref.read(profilProvider.notifier).load();
          },
        ),
        data: (user) {
          _populateFields(user);
          return TabBarView(
            controller: _tabController,
            children: [
              _buildInfoTab(user),
              _buildPasswordTab(),
            ],
          );
        },
      ),
    );
  }

  Widget _buildInfoTab(user) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            // Avatar
            _buildAvatarSection(user),
            const SizedBox(height: 28),

            // Badge rôle
            Container(
              padding:
                  const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
              decoration: BoxDecoration(
                color: AppColors.primary.withOpacity(0.1),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.primary.withOpacity(0.3)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.verified_user_outlined,
                      size: 16, color: AppColors.primary),
                  const SizedBox(width: 6),
                  Text(
                    user.role.toUpperCase(),
                    style: const TextStyle(
                      color: AppColors.primary,
                      fontWeight: FontWeight.w600,
                      fontSize: 12,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 28),

            // Champs infos
            Row(children: [
              Expanded(
                child: CustomTextField(
                  controller: _prenomCtrl,
                  label: 'Prénom',
                  prefixIcon: const Icon(Icons.person_outline,
                      color: AppColors.primary),
                  validator: (v) =>
                      v == null || v.isEmpty ? 'Champ requis' : null,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: CustomTextField(
                  controller: _nameCtrl,
                  label: 'Nom',
                  prefixIcon: const Icon(Icons.person_outline,
                      color: AppColors.primary),
                  validator: (v) =>
                      v == null || v.isEmpty ? 'Champ requis' : null,
                ),
              ),
            ]),
            const SizedBox(height: 16),
            CustomTextField(
              controller: TextEditingController(text: user.email),
              label: 'Email',
              prefixIcon: const Icon(Icons.email_outlined,
                  color: AppColors.primary),
              readOnly: true,
            ),
            const SizedBox(height: 16),
            CustomTextField(
              controller: _telephoneCtrl,
              label: 'Téléphone',
              prefixIcon: const Icon(Icons.phone_outlined,
                  color: AppColors.primary),
              keyboardType: TextInputType.phone,
            ),
            const SizedBox(height: 16),
            CustomTextField(
              controller: _villeCtrl,
              label: 'Ville',
              prefixIcon: const Icon(Icons.location_city_outlined,
                  color: AppColors.primary),
            ),
            const SizedBox(height: 16),
            CustomTextField(
              controller: _adresseCtrl,
              label: 'Adresse',
              prefixIcon: const Icon(Icons.home_outlined,
                  color: AppColors.primary),
            ),
            const SizedBox(height: 28),

            CustomButton(
              label: 'Enregistrer les modifications',
              icon: Icons.save_outlined,
              isLoading: _isSaving,
              onPressed: _isSaving ? null : _saveInfos,
            ),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }

  Widget _buildAvatarSection(user) {
    final avatarUrl = user.avatar as String?;

    return Stack(
      alignment: Alignment.bottomRight,
      children: [
        Container(
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            border: Border.all(color: AppColors.primary, width: 3),
            boxShadow: [
              BoxShadow(
                color: AppColors.primary.withOpacity(0.2),
                blurRadius: 16,
                offset: const Offset(0, 6),
              ),
            ],
          ),
          child: ClipOval(
            child: SizedBox(
              width: 110,
              height: 110,
              child: _avatarFile != null
                  ? Image.file(_avatarFile!, fit: BoxFit.cover)
                  : (avatarUrl != null && avatarUrl.isNotEmpty
                      ? CachedNetworkImage(
                          imageUrl: avatarUrl,
                          fit: BoxFit.cover,
                          placeholder: (_, __) => Container(
                            color: AppColors.primary.withOpacity(0.1),
                            child: const CircularProgressIndicator(strokeWidth: 2),
                          ),
                          errorWidget: (_, __, ___) => _defaultAvatar(user),
                        )
                      : _defaultAvatar(user)),
            ),
          ),
        ),
        GestureDetector(
          onTap: _pickAvatar,
          child: Container(
            padding: const EdgeInsets.all(8),
            decoration: const BoxDecoration(
              color: AppColors.accent,
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.camera_alt, size: 18, color: Colors.white),
          ),
        ),
      ],
    );
  }

  Widget _defaultAvatar(user) {
    final initials = '${user.prenom.isNotEmpty ? user.prenom[0] : ''}${user.name.isNotEmpty ? user.name[0] : ''}'
        .toUpperCase();
    return Container(
      color: AppColors.primary.withOpacity(0.1),
      child: Center(
        child: Text(
          initials,
          style: const TextStyle(
            fontSize: 36,
            fontWeight: FontWeight.bold,
            color: AppColors.primary,
          ),
        ),
      ),
    );
  }

  Widget _buildPasswordTab() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Form(
        key: _pwFormKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const SizedBox(height: 16),
            // Info card
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppColors.primary.withOpacity(0.06),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppColors.primary.withOpacity(0.2)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.info_outline,
                      color: AppColors.primary, size: 22),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Laissez vide si vous ne souhaitez pas changer votre mot de passe.',
                      style: Theme.of(context)
                          .textTheme
                          .bodyMedium
                          ?.copyWith(color: AppColors.textSecondary),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 28),

            TextFormField(
              controller: _pwCtrl,
              obscureText: _obscurePw,
              decoration: InputDecoration(
                labelText: 'Nouveau mot de passe',
                prefixIcon:
                    const Icon(Icons.lock_outline, color: AppColors.primary),
                suffixIcon: IconButton(
                  icon: Icon(
                    _obscurePw ? Icons.visibility_off : Icons.visibility,
                    color: AppColors.textSecondary,
                  ),
                  onPressed: () =>
                      setState(() => _obscurePw = !_obscurePw),
                ),
              ),
              validator: (v) {
                if (v == null || v.isEmpty) return null;
                if (v.length < 8) return 'Minimum 8 caractères';
                return null;
              },
            ),
            const SizedBox(height: 16),

            TextFormField(
              controller: _pwConfirmCtrl,
              obscureText: _obscurePwConfirm,
              decoration: InputDecoration(
                labelText: 'Confirmer le mot de passe',
                prefixIcon: const Icon(Icons.lock_outline,
                    color: AppColors.primary),
                suffixIcon: IconButton(
                  icon: Icon(
                    _obscurePwConfirm
                        ? Icons.visibility_off
                        : Icons.visibility,
                    color: AppColors.textSecondary,
                  ),
                  onPressed: () => setState(
                      () => _obscurePwConfirm = !_obscurePwConfirm),
                ),
              ),
              validator: (v) {
                if (_pwCtrl.text.isEmpty) return null;
                if (v != _pwCtrl.text) return 'Les mots de passe ne correspondent pas';
                return null;
              },
            ),
            const SizedBox(height: 32),

            CustomButton(
              label: 'Changer le mot de passe',
              icon: Icons.lock_reset,
              isLoading: _isSavingPw,
              onPressed: _isSavingPw ? null : _savePw,
            ),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }
}
