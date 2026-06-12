// lib/screens/biens/biens_list_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/theme/app_theme.dart';
import '../../models/bien_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/bien_provider.dart';
import '../../providers/favori_provider.dart';
import '../../providers/notification_provider.dart';
import '../../widgets/bien/bien_card.dart';
import '../../widgets/common/loading_widget.dart';

// ── Catégories affichées sur l'accueil ──────────────────────────────────────
class _Categorie {
  final String label;
  final IconData icon;
  final String keyword;
  const _Categorie(this.label, this.icon, this.keyword);
}

const _categories = [
  _Categorie('Maisons',         Icons.house_rounded,           'maison'),
  _Categorie('Appartements',    Icons.apartment_rounded,       'appartement'),
  _Categorie('Parcelles',       Icons.landscape_rounded,       'parcelle'),
  _Categorie('Locaux\ncommerciaux', Icons.store_rounded,       'local'),
  _Categorie('Villas',          Icons.villa_rounded,           'villa'),
  _Categorie('Studios',         Icons.meeting_room_rounded,    'studio'),
  _Categorie('Bureaux',         Icons.business_center_rounded, 'bureau'),
  _Categorie('Terrains',        Icons.terrain_rounded,         'terrain'),
];

// ── Écran principal ──────────────────────────────────────────────────────────
class BiensListScreen extends ConsumerStatefulWidget {
  const BiensListScreen({super.key});

  @override
  ConsumerState<BiensListScreen> createState() => _BiensListScreenState();
}

class _BiensListScreenState extends ConsumerState<BiensListScreen>
    with WidgetsBindingObserver {
  final _scrollCtrl  = ScrollController();
  final _searchCtrl  = TextEditingController();
  String? _selectedTransaction;
  String? _selectedVille;
  int?    _selectedTypeId;
  String? _activeCategoryLabel;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(bienListProvider.notifier).refresh();
      final authState = ref.read(authProvider);
      final isAuth = authState.status == AuthStatus.authenticated;
      final role   = authState.user?.role ?? '';
      if (isAuth && (role == 'client' || role == '')) {
        ref.read(favoriProvider.notifier).load();
      }
      if (isAuth) {
        ref.read(notificationProvider.notifier).load();
      }
    });
    _scrollCtrl.addListener(_onScroll);
  }

  // Rafraîchit automatiquement quand l'app revient au premier plan
  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      ref.read(bienListProvider.notifier).refresh();
    }
  }

  void _onScroll() {
    if (_scrollCtrl.position.pixels >=
        _scrollCtrl.position.maxScrollExtent - 200) {
      ref.read(bienListProvider.notifier).loadMore();
    }
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _scrollCtrl.dispose();
    _searchCtrl.dispose();
    super.dispose();
  }

  void _applyFiltres() {
    ref.read(bienListProvider.notifier).applyFiltres(BienFiltres(
      transaction: _selectedTransaction,
      typeBienId:  _selectedTypeId,
      ville:       _selectedVille,
      search:      _searchCtrl.text.isNotEmpty ? _searchCtrl.text : null,
    ));
  }

  void _onCategoryTap(_Categorie cat, List<TypeBienModel> types) {
    if (_activeCategoryLabel == cat.label) {
      setState(() {
        _activeCategoryLabel = null;
        _selectedTypeId      = null;
      });
      _applyFiltres();
      return;
    }
    final matched = types.firstWhere(
      (t) => t.libelle.toLowerCase().contains(cat.keyword.toLowerCase()),
      orElse: () => TypeBienModel(id: -1, libelle: ''),
    );
    setState(() {
      _activeCategoryLabel = cat.label;
      _selectedTypeId = matched.id > 0 ? matched.id : null;
    });
    _applyFiltres();
  }

  void _resetAll() {
    setState(() {
      _activeCategoryLabel = null;
      _selectedTypeId      = null;
      _selectedTransaction = null;
      _selectedVille       = null;
    });
    _searchCtrl.clear();
    ref.read(bienListProvider.notifier).applyFiltres(const BienFiltres());
  }

  void _showFiltresSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      useRootNavigator: true,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => _FiltresSheet(
        selectedTransaction: _selectedTransaction,
        selectedVille:       _selectedVille,
        selectedTypeId:      _selectedTypeId,
        onApply: (transaction, ville, typeId) {
          setState(() {
            _selectedTransaction = transaction;
            _selectedVille       = ville;
            _selectedTypeId      = typeId;
            _activeCategoryLabel = null;
          });
          _applyFiltres();
          Navigator.of(context, rootNavigator: true).pop();
        },
        onReset: () {
          _resetAll();
          Navigator.of(context, rootNavigator: true).pop();
        },
      ),
    );
  }

  // Scroll vers la section liste (après hero + recherche + catégories)
  void _scrollToList() {
    _scrollCtrl.animateTo(
      500,
      duration: const Duration(milliseconds: 600),
      curve: Curves.easeInOut,
    );
  }

  @override
  Widget build(BuildContext context) {
    final state      = ref.watch(bienListProvider);
    final authState  = ref.watch(authProvider);
    final isAuth     = authState.status == AuthStatus.authenticated;
    final userRole   = authState.user?.role ?? '';
    final isClient   = userRole == 'client' || userRole == '';
    final canFavori  = isAuth && isClient;
    final typesAsync = ref.watch(typesBiensProvider);
    final unread     = ref.watch(notificationProvider).unreadCount;

    return Scaffold(
      backgroundColor: AppColors.background,
      body: RefreshIndicator(
        onRefresh: () => ref.read(bienListProvider.notifier).refresh(),
        color: AppColors.primary,
        child: CustomScrollView(
          controller: _scrollCtrl,
          slivers: [
          // ── Hero header ─────────────────────────────────────────────
          SliverToBoxAdapter(
            child: _buildHero(context, isAuth, unread),
          ),

          // ── Barre de recherche ───────────────────────────────────────
          SliverToBoxAdapter(child: _buildSearchBar()),

          // ── Catégories ───────────────────────────────────────────────
          SliverToBoxAdapter(
            child: typesAsync.when(
              data:    (types) => _buildCategories(types),
              loading: () => _buildCategories([]),
              error:   (_, __) => _buildCategories([]),
            ),
          ),

          // ── Section "Biens recommandés" (horizontal) ─────────────────
          if (state.biens.isNotEmpty && _activeCategoryLabel == null &&
              _selectedTransaction == null && _selectedVille == null &&
              (_searchCtrl.text.isEmpty)) ...[
            SliverToBoxAdapter(
              child: _buildRecommendedHeader(),
            ),
            SliverToBoxAdapter(
              child: _buildRecommendedList(
                state.biens.take(5).toList(), canFavori, isAuth),
            ),
          ],

          // ── En-tête section liste complète ───────────────────────────
          SliverToBoxAdapter(child: _buildSectionHeader()),

          // ── Liste des biens ──────────────────────────────────────────
          if (state.biens.isEmpty && state.isLoading)
            const SliverFillRemaining(
              child: LoadingWidget(message: 'Chargement des biens...'),
            )
          else if (state.biens.isEmpty && !state.isLoading)
            SliverFillRemaining(
              child: EmptyWidget(
                message: 'Aucun bien trouvé\npour ces critères.',
                icon: Icons.home_work_outlined,
              ),
            )
          else ...[
            SliverPadding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              sliver: SliverList(
                delegate: SliverChildBuilderDelegate(
                  (context, index) {
                    if (index >= state.biens.length) {
                      return const Padding(
                        padding: EdgeInsets.all(16),
                        child: Center(child: CircularProgressIndicator()),
                      );
                    }
                    final bien         = state.biens[index];
                    final favorisState = ref.watch(favoriProvider);
                    final isFavori     = canFavori
                        ? (favorisState.valueOrNull
                                ?.any((b) => b.id == bien.id) ??
                            false)
                        : false;
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 16),
                      child: BienCard(
                        bien: bien,
                        isFavori: isFavori,
                        showFavoriButton: true,
                        onTap: () => context.push('/biens/${bien.id}'),
                        onFavoriTap: () {
                          if (!isAuth) {
                            _showLoginRequired(context);
                            return;
                          }
                          if (canFavori) {
                            ref
                                .read(favoriProvider.notifier)
                                .toggle(bien.id);
                          }
                        },
                      ),
                    );
                  },
                  childCount:
                      state.biens.length + (state.hasMore ? 1 : 0),
                ),
              ),
            ),
          ],

          // ── Bannière sécurité ────────────────────────────────────────
          SliverToBoxAdapter(
              child: _buildSecurityBanner(context, isAuth)),
          const SliverToBoxAdapter(child: SizedBox(height: 24)),
        ],
      ),
      ),
    );
  }

  // ── Hero ─────────────────────────────────────────────────────────────────
  Widget _buildHero(BuildContext context, bool isAuth, int unread) {
    final topPadding = MediaQuery.of(context).padding.top;
    final heroHeight = 240.0 + topPadding;

    return SizedBox(
      width: double.infinity,
      height: heroHeight,
      child: Stack(
        fit: StackFit.expand,
        children: [
          // ── Photo de maison en fond ──────────────────────────────────
          Positioned.fill(
            child: Image.asset(
              'assets/hero_house.jpg',
              fit: BoxFit.cover,
              errorBuilder: (_, __, ___) => Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [Color(0xFF0A5F5F), Color(0xFF0A9E9E)],
                  ),
                ),
              ),
            ),
          ),
          // ── Overlay sombre ───────────────────────────────────────────
          Positioned.fill(
            child: DecoratedBox(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.centerLeft,
                  end: Alignment.centerRight,
                  colors: [Color(0xCC000000), Color(0x44000000)],
                ),
              ),
            ),
          ),
          // ── AppBar row (en haut) ─────────────────────────────────────
          Positioned(
            top: topPadding + 10,
            left: 20, right: 20,
            child: Row(
              children: [
                Container(
                  width: 36, height: 36,
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(Icons.menu_rounded, color: Colors.white, size: 20),
                ),
                const SizedBox(width: 10),
                Container(
                  padding: const EdgeInsets.all(5),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.20),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Icon(Icons.home_work_rounded, color: Colors.white, size: 16),
                ),
                const SizedBox(width: 6),
                const Text('ImmoGo',
                    style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                        fontSize: 20,
                        letterSpacing: 0.5)),
                const Spacer(),
                GestureDetector(
                  onTap: isAuth ? () => context.push('/notifications') : null,
                  child: Stack(
                    clipBehavior: Clip.none,
                    children: [
                      Container(
                        width: 36, height: 36,
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Icon(Icons.notifications_outlined,
                            color: Colors.white, size: 20),
                      ),
                      if (isAuth && unread > 0)
                        Positioned(
                          top: -4, right: -4,
                          child: Container(
                            padding: const EdgeInsets.all(4),
                            decoration: const BoxDecoration(
                              color: Color(0xFF4DD9C0),
                              shape: BoxShape.circle,
                            ),
                            child: Text('$unread',
                                style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 9,
                                    fontWeight: FontWeight.bold)),
                          ),
                        ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          // ── Texte + bouton (en bas) ──────────────────────────────────
          Positioned(
            bottom: 18,
            left: 20, right: 20,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                RichText(
                  text: const TextSpan(
                    style: TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.bold,
                        height: 1.2,
                        color: Colors.white),
                    children: [
                      TextSpan(text: 'Trouvez '),
                      TextSpan(
                          text: 'le bien',
                          style: TextStyle(color: Color(0xFF4DD9C0))),
                      TextSpan(text: '\nqui vous ressemble'),
                    ],
                  ),
                ),
                const SizedBox(height: 4),
                const Text(
                  'Maisons, appartements, parcelles\net plus encore partout au Bénin.',
                  style: TextStyle(
                      color: Colors.white70, fontSize: 11, height: 1.4),
                ),
                const SizedBox(height: 10),
                GestureDetector(
                  onTap: _scrollToList,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 18, vertical: 9),
                    decoration: BoxDecoration(
                      color: const Color(0xFF4DD9C0),
                      borderRadius: BorderRadius.circular(24),
                      boxShadow: [
                        BoxShadow(
                            color: Colors.black.withValues(alpha: 0.18),
                            blurRadius: 8,
                            offset: const Offset(0, 3)),
                      ],
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text('Explorer maintenant',
                            style: TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.w700,
                                fontSize: 13)),
                        SizedBox(width: 6),
                        Icon(Icons.arrow_forward_ios_rounded,
                            color: Colors.white, size: 12),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ── Barre de recherche ────────────────────────────────────────────────────
  Widget _buildSearchBar() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.fromLTRB(16, 14, 16, 10),
      child: Row(
        children: [
          Expanded(
            child: Container(
              decoration: BoxDecoration(
                color: AppColors.background,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: AppColors.divider),
              ),
              child: TextField(
                controller: _searchCtrl,
                onSubmitted: (_) => _applyFiltres(),
                onChanged: (_) => setState(() {}),
                decoration: InputDecoration(
                  hintText: 'Rechercher une maison, parcelle, appartement...',
                  hintStyle: const TextStyle(
                      fontSize: 13, color: AppColors.textSecondary),
                  prefixIcon: const Icon(Icons.search,
                      color: AppColors.textSecondary, size: 20),
                  border: InputBorder.none,
                  contentPadding: const EdgeInsets.symmetric(vertical: 12),
                  suffixIcon: _searchCtrl.text.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear, size: 18),
                          onPressed: () {
                            _searchCtrl.clear();
                            setState(() {});
                            _applyFiltres();
                          })
                      : null,
                ),
              ),
            ),
          ),
          const SizedBox(width: 10),
          GestureDetector(
            onTap: _showFiltresSheet,
            child: Container(
              padding: const EdgeInsets.all(11),
              decoration: BoxDecoration(
                color: AppColors.primary,
                borderRadius: BorderRadius.circular(14),
              ),
              child: const Icon(Icons.tune_rounded,
                  color: Colors.white, size: 20),
            ),
          ),
        ],
      ),
    );
  }

  // ── Catégories ─────────────────────────────────────────────────────────────
  Widget _buildCategories(List<TypeBienModel> types) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 14),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: _categories.map((cat) {
            final isActive = _activeCategoryLabel == cat.label;
            return GestureDetector(
              onTap: () => _onCategoryTap(cat, types),
              child: Container(
                margin: const EdgeInsets.only(right: 12),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      width: 56,
                      height: 56,
                      decoration: BoxDecoration(
                        color: isActive
                            ? AppColors.primary
                            : AppColors.background,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: isActive
                              ? AppColors.primary
                              : AppColors.divider,
                          width: isActive ? 2 : 1,
                        ),
                        boxShadow: isActive
                            ? [
                                BoxShadow(
                                  color: AppColors.primary
                                      .withValues(alpha: 0.25),
                                  blurRadius: 8,
                                  offset: const Offset(0, 4),
                                )
                              ]
                            : null,
                      ),
                      child: Icon(
                        cat.icon,
                        color: isActive
                            ? Colors.white
                            : AppColors.primary,
                        size: 26,
                      ),
                    ),
                    const SizedBox(height: 6),
                    SizedBox(
                      width: 62,
                      child: Text(
                        cat.label,
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: isActive
                              ? FontWeight.w700
                              : FontWeight.w500,
                          color: isActive
                              ? AppColors.primary
                              : AppColors.textSecondary,
                        ),
                        textAlign: TextAlign.center,
                        maxLines: 2,
                      ),
                    ),
                  ],
                ),
              ),
            );
          }).toList(),
        ),
      ),
    );
  }

  // ── Section "Biens recommandés" ─────────────────────────────────────────
  Widget _buildRecommendedHeader() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 20, 16, 10),
      child: Row(
        children: [
          Text('Biens recommandés',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.bold,
                  )),
          const Spacer(),
          GestureDetector(
            onTap: _resetAll,
            child: const Text(
              'Voir tout  ›',
              style: TextStyle(
                  fontSize: 13,
                  color: AppColors.secondary,
                  fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildRecommendedList(
      List<BienModel> biens, bool canFavori, bool isAuth) {
    return SizedBox(
      height: 230,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        itemCount: biens.length,
        itemBuilder: (context, index) {
          final bien         = biens[index];
          final favorisState = ref.watch(favoriProvider);
          final isFavori     = canFavori
              ? (favorisState.valueOrNull
                      ?.any((b) => b.id == bien.id) ??
                  false)
              : false;
          return _RecommendedCard(
            bien: bien,
            isFavori: isFavori,
            showFavoriButton: true,
            onTap: () => context.push('/biens/${bien.id}'),
            onFavoriTap: () {
              if (!isAuth) {
                _showLoginRequired(context);
                return;
              }
              if (canFavori) {
                ref.read(favoriProvider.notifier).toggle(bien.id);
              }
            },
          );
        },
      ),
    );
  }

  // ── En-tête section liste ─────────────────────────────────────────────────
  Widget _buildSectionHeader() {
    final hasFilter = _activeCategoryLabel != null ||
        _selectedTransaction != null ||
        _selectedVille != null ||
        _searchCtrl.text.isNotEmpty;
    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 8),
      child: Row(
        children: [
          Text(
            _activeCategoryLabel != null
                ? _activeCategoryLabel!.replaceAll('\n', ' ')
                : 'Biens disponibles',
            style: Theme.of(context)
                .textTheme
                .titleMedium
                ?.copyWith(fontWeight: FontWeight.bold),
          ),
          const Spacer(),
          if (hasFilter)
            GestureDetector(
              onTap: _resetAll,
              child: const Text(
                'Tout voir  ›',
                style: TextStyle(
                    fontSize: 13,
                    color: AppColors.secondary,
                    fontWeight: FontWeight.w600),
              ),
            ),
        ],
      ),
    );
  }

  // ── Bannière sécurité ─────────────────────────────────────────────────────
  Widget _buildSecurityBanner(BuildContext context, bool isAuth) {
    return Container(
      margin: const EdgeInsets.fromLTRB(16, 8, 16, 0),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.primary,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.15),
              shape: BoxShape.circle,
            ),
            child: const Icon(Icons.verified_user_rounded,
                color: Colors.white, size: 22),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: const [
                Text(
                  'Transaction sécurisée',
                  style: TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                      fontSize: 13),
                ),
                SizedBox(height: 2),
                Text(
                  'Achetez, louez et réservez en toute\nsécurité avec ImmoGo.',
                  style: TextStyle(
                      color: Colors.white70, fontSize: 11, height: 1.4),
                ),
              ],
            ),
          ),
          GestureDetector(
            onTap: () => _showEnSavoirPlus(context),
            child: Container(
              padding: const EdgeInsets.symmetric(
                  horizontal: 14, vertical: 8),
              decoration: BoxDecoration(
                color: AppColors.secondary,
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Text(
                'En savoir\nplus  ›',
                style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                    fontSize: 11),
                textAlign: TextAlign.center,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Modal "En savoir plus" ────────────────────────────────────────────────
  void _showEnSavoirPlus(BuildContext context) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      useRootNavigator: true,
      backgroundColor: Colors.transparent,
      builder: (_) => const _EnSavoirPlusSheet(),
    );
  }

  // ── Dialogue connexion requise ────────────────────────────────────────────
  void _showLoginRequired(BuildContext context) {
    showDialog(
      context: context,
      useRootNavigator: true,
      builder: (ctx) => AlertDialog(
        title: const Text('Connexion requise'),
        content: const Text(
            'Connectez-vous pour ajouter ce bien à vos favoris.'),
        actions: [
          TextButton(
            onPressed: () =>
                Navigator.of(ctx, rootNavigator: true).pop(),
            child: const Text('Annuler'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.of(ctx, rootNavigator: true).pop();
              context.go('/login');
            },
            child: const Text('Se connecter'),
          ),
        ],
      ),
    );
  }
}

// ── Carte recommandée (horizontale, plus petite) ─────────────────────────────
class _RecommendedCard extends StatelessWidget {
  final BienModel bien;
  final VoidCallback onTap;
  final VoidCallback? onFavoriTap;
  final bool isFavori;
  final bool showFavoriButton;

  const _RecommendedCard({
    required this.bien,
    required this.onTap,
    this.onFavoriTap,
    this.isFavori = false,
    this.showFavoriButton = true,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 180,
        margin: const EdgeInsets.only(right: 12),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.08),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image
            Stack(
              children: [
                ClipRRect(
                  borderRadius:
                      const BorderRadius.vertical(top: Radius.circular(16)),
                  child: bien.photo != null
                      ? Image.network(
                          bien.photo!,
                          height: 120,
                          width: double.infinity,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) =>
                              _noPhoto(),
                        )
                      : _noPhoto(),
                ),
                // Favori
                if (showFavoriButton)
                  Positioned(
                    top: 8,
                    right: 8,
                    child: Material(
                      color: Colors.white.withValues(alpha: 0.92),
                      shape: const CircleBorder(),
                      elevation: 2,
                      child: InkWell(
                        onTap: onFavoriTap,
                        customBorder: const CircleBorder(),
                        child: Padding(
                          padding: const EdgeInsets.all(6),
                          child: Icon(
                            isFavori
                                ? Icons.favorite
                                : Icons.favorite_border,
                            color:
                                isFavori ? Colors.red : Colors.grey[600],
                            size: 16,
                          ),
                        ),
                      ),
                    ),
                  ),
                // Badge transaction
                Positioned(
                  bottom: 6,
                  left: 6,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 7, vertical: 3),
                    decoration: BoxDecoration(
                      color: bien.transaction == 'vente'
                          ? AppColors.primary
                          : AppColors.secondary,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      bien.transaction == 'vente'
                          ? 'À VENDRE'
                          : 'À LOUER',
                      style: const TextStyle(
                          color: Colors.white,
                          fontSize: 9,
                          fontWeight: FontWeight.bold),
                    ),
                  ),
                ),
                // Ville
                Positioned(
                  bottom: 6,
                  right: 6,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 6, vertical: 3),
                    decoration: BoxDecoration(
                      color: Colors.black.withValues(alpha: 0.5),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Text(
                      bien.ville,
                      style: const TextStyle(
                          color: Colors.white,
                          fontSize: 9,
                          fontWeight: FontWeight.w600),
                    ),
                  ),
                ),
              ],
            ),
            // Infos
            Expanded(
              child: Padding(
                padding: const EdgeInsets.all(10),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      bien.titre,
                      style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: AppColors.textPrimary),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const Spacer(),
                    // Superficie si parcelle, chambres sinon
                    if (bien.superficie != null)
                      Row(
                        children: [
                          const Icon(Icons.square_foot,
                              size: 12,
                              color: AppColors.textSecondary),
                          const SizedBox(width: 3),
                          Text(
                            '${bien.superficie!.toStringAsFixed(0)} m²',
                            style: const TextStyle(
                                fontSize: 11,
                                color: AppColors.textSecondary),
                          ),
                        ],
                      )
                    else if (bien.chambres != null)
                      Row(
                        children: [
                          const Icon(Icons.bed,
                              size: 12,
                              color: AppColors.textSecondary),
                          const SizedBox(width: 3),
                          Text(
                            '${bien.chambres} ch.',
                            style: const TextStyle(
                                fontSize: 11,
                                color: AppColors.textSecondary),
                          ),
                        ],
                      ),
                    const SizedBox(height: 4),
                    Text(
                      bien.prixFormate.isNotEmpty
                          ? bien.prixFormate
                          : '${bien.prix.toStringAsFixed(0)} FCFA',
                      style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: AppColors.accent),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _noPhoto() => Container(
        height: 120,
        width: double.infinity,
        color: Colors.grey[200],
        child: const Icon(Icons.home_work_rounded,
            size: 40, color: Colors.grey),
      );
}

// ── Sheet "En savoir plus" ────────────────────────────────────────────────────
class _EnSavoirPlusSheet extends StatelessWidget {
  const _EnSavoirPlusSheet();

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Barre de tirage
          Container(
            margin: const EdgeInsets.only(top: 12),
            width: 40,
            height: 4,
            decoration: BoxDecoration(
              color: Colors.grey[300],
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          Flexible(
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // ── En-tête ──────────────────────────────────────────
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: const Color(0xFF0A6E6E).withValues(alpha: 0.1),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(Icons.verified_user_rounded,
                            color: Color(0xFF0A6E6E), size: 24),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('ImmoGo — Transactions sécurisées',
                                style: TextStyle(
                                    fontSize: 15,
                                    fontWeight: FontWeight.bold,
                                    color: Color(0xFF1C1C1E))),
                            Text('Votre guide complet',
                                style: TextStyle(
                                    fontSize: 12,
                                    color: Colors.grey[600])),
                          ],
                        ),
                      ),
                      IconButton(
                        onPressed: () => Navigator.of(context, rootNavigator: true).pop(),
                        icon: const Icon(Icons.close, color: Colors.grey),
                      ),
                    ],
                  ),
                  const SizedBox(height: 20),

                  // ── Section : Nos agences partenaires ─────────────────
                  _buildSectionTitle(
                    icon: Icons.business_rounded,
                    title: 'Des agences immobilières de confiance',
                    color: const Color(0xFF0A6E6E),
                  ),
                  const SizedBox(height: 10),
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: const Color(0xFF0A6E6E).withValues(alpha: 0.06),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                          color: const Color(0xFF0A6E6E).withValues(alpha: 0.15)),
                    ),
                    child: Column(
                      children: [
                        _buildAgenceItem(
                          Icons.apartment_rounded,
                          'Plusieurs agences partenaires',
                          'ImmoGo regroupe des agences immobilières sélectionnées et vérifiées au Bénin.',
                        ),
                        const Divider(height: 16),
                        _buildAgenceItem(
                          Icons.location_on_rounded,
                          'Partout au Bénin',
                          'Cotonou, Calavi, Porto-Novo, Parakou et dans toutes les grandes villes.',
                        ),
                        const Divider(height: 16),
                        _buildAgenceItem(
                          Icons.shield_rounded,
                          'Agences vérifiées',
                          'Chaque agence est contrôlée avant d\'être autorisée à publier sur la plateforme.',
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),

                  // ── Section : Étapes de réservation ──────────────────
                  _buildSectionTitle(
                    icon: Icons.event_available_rounded,
                    title: 'Comment réserver un bien ?',
                    color: const Color(0xFF00B894),
                  ),
                  const SizedBox(height: 12),
                  _buildEtape(
                    numero: '1',
                    titre: 'Choisissez votre bien',
                    description: 'Parcourez les annonces, filtrez par type, ville ou budget et sélectionnez le bien qui vous correspond.',
                    icon: Icons.search_rounded,
                    color: const Color(0xFF00B894),
                  ),
                  _buildEtape(
                    numero: '2',
                    titre: 'Effectuez une réservation',
                    description: 'Cliquez sur "Réserver" sur la page du bien. Vous payez un acompte de 10% du prix total pour bloquer le bien.',
                    icon: Icons.bookmark_add_rounded,
                    color: const Color(0xFF00B894),
                    isLast: false,
                  ),
                  _buildEtape(
                    numero: '3',
                    titre: 'Paiement de l\'acompte',
                    description: 'Réglez les 10% d\'acompte via Mobile Money (MTN, Moov) ou carte bancaire. Votre réservation est confirmée instantanément.',
                    icon: Icons.payment_rounded,
                    color: const Color(0xFF00B894),
                  ),
                  _buildEtape(
                    numero: '4',
                    titre: 'Finalisation du contrat',
                    description: 'L\'agence vous contacte pour signer le contrat et finaliser le solde restant (90%) selon les modalités convenues.',
                    icon: Icons.handshake_rounded,
                    color: const Color(0xFF00B894),
                    isLast: true,
                  ),
                  const SizedBox(height: 24),

                  // ── Section : Paiement complet ────────────────────────
                  _buildSectionTitle(
                    icon: Icons.payments_rounded,
                    title: 'Payer la totalité du prix',
                    color: const Color(0xFF0A6E6E),
                  ),
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF0A6E6E), Color(0xFF0A9E9E)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Row(
                          children: [
                            Icon(Icons.star_rounded,
                                color: Color(0xFF4DD9C0), size: 18),
                            SizedBox(width: 6),
                            Text('Option paiement intégral',
                                style: TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 13)),
                          ],
                        ),
                        const SizedBox(height: 8),
                        _buildPaiementItem(
                            '✓', 'Payez 100% du montant en une seule fois'),
                        _buildPaiementItem(
                            '✓', 'Transaction sécurisée et tracée'),
                        _buildPaiementItem(
                            '✓', 'Contrat généré automatiquement'),
                        _buildPaiementItem(
                            '✓', 'Téléchargez votre reçu instantanément'),
                      ],
                    ),
                  ),
                  const SizedBox(height: 24),

                  // ── Section : Conditions de réservation ──────────────
                  _buildSectionTitle(
                    icon: Icons.gavel_rounded,
                    title: 'Conditions de réservation',
                    color: Colors.orange,
                  ),
                  const SizedBox(height: 12),
                  _buildCondition(
                    icon: Icons.percent_rounded,
                    titre: 'Acompte de 10%',
                    texte: 'Tout bien réservé nécessite un acompte de 10% du prix affiché pour bloquer le bien.',
                    color: Colors.orange,
                  ),
                  _buildCondition(
                    icon: Icons.timer_rounded,
                    titre: 'Validité de 15 jours',
                    texte: 'La réservation est valable 15 jours. Passé ce délai, si le solde n\'est pas réglé, la réservation est annulée.',
                    color: Colors.orange,
                  ),
                  _buildCondition(
                    icon: Icons.cancel_rounded,
                    titre: 'Annulation et remboursement',
                    texte: 'En cas d\'annulation de la réservation, l\'équipe de l\'agence procède au remboursement directement. Si votre réservation n\'est pas annulée, les autres clients ne peuvent plus voir le bien pour passer à l\'action — il est donc important d\'annuler rapidement si vous ne souhaitez plus réserver.',
                    color: Colors.red,
                  ),
                  _buildCondition(
                    icon: Icons.account_circle_rounded,
                    titre: 'Compte requis',
                    texte: 'Vous devez être connecté avec un compte ImmoGo vérifié pour réserver un bien.',
                    color: const Color(0xFF0A6E6E),
                  ),
                  const SizedBox(height: 24),

                  // ── Bouton fermer ─────────────────────────────────────
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: () =>
                          Navigator.of(context, rootNavigator: true).pop(),
                      icon: const Icon(Icons.check_circle_outline_rounded),
                      label: const Text('J\'ai compris'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF0A6E6E),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionTitle({
    required IconData icon,
    required String title,
    required Color color,
  }) {
    return Row(
      children: [
        Icon(icon, color: color, size: 20),
        const SizedBox(width: 8),
        Expanded(
          child: Text(title,
              style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                  color: color)),
        ),
      ],
    );
  }

  Widget _buildAgenceItem(IconData icon, String titre, String texte) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, color: const Color(0xFF0A6E6E), size: 18),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(titre,
                  style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF1C1C1E))),
              const SizedBox(height: 2),
              Text(texte,
                  style: const TextStyle(
                      fontSize: 11,
                      color: Color(0xFF6B7280),
                      height: 1.4)),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildEtape({
    required String numero,
    required String titre,
    required String description,
    required IconData icon,
    required Color color,
    bool isLast = false,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Ligne verticale + cercle numéroté
        Column(
          children: [
            Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                color: color,
                shape: BoxShape.circle,
              ),
              child: Center(
                child: Text(numero,
                    style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                        fontSize: 13)),
              ),
            ),
            if (!isLast)
              Container(
                width: 2,
                height: 40,
                color: color.withValues(alpha: 0.25),
              ),
          ],
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(icon, color: color, size: 16),
                    const SizedBox(width: 6),
                    Text(titre,
                        style: const TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                            color: Color(0xFF1C1C1E))),
                  ],
                ),
                const SizedBox(height: 4),
                Text(description,
                    style: const TextStyle(
                        fontSize: 11,
                        color: Color(0xFF6B7280),
                        height: 1.4)),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildPaiementItem(String check, String texte) {
    return Padding(
      padding: const EdgeInsets.only(top: 5),
      child: Row(
        children: [
          Text(check,
              style: const TextStyle(
                  color: Color(0xFF4DD9C0),
                  fontWeight: FontWeight.bold,
                  fontSize: 13)),
          const SizedBox(width: 8),
          Expanded(
            child: Text(texte,
                style: const TextStyle(
                    color: Colors.white70, fontSize: 11, height: 1.3)),
          ),
        ],
      ),
    );
  }

  Widget _buildCondition({
    required IconData icon,
    required String titre,
    required String texte,
    required Color color,
  }) {
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.06),
        borderRadius: BorderRadius.circular(10),
        border:
            Border.all(color: color.withValues(alpha: 0.2)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color, size: 18),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(titre,
                    style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: color)),
                const SizedBox(height: 3),
                Text(texte,
                    style: const TextStyle(
                        fontSize: 11,
                        color: Color(0xFF6B7280),
                        height: 1.4)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ── Feuille de filtres ────────────────────────────────────────────────────────
class _FiltresSheet extends ConsumerStatefulWidget {
  final String? selectedTransaction;
  final String? selectedVille;
  final int? selectedTypeId;
  final void Function(String?, String?, int?) onApply;
  final VoidCallback onReset;

  const _FiltresSheet({
    this.selectedTransaction,
    this.selectedVille,
    this.selectedTypeId,
    required this.onApply,
    required this.onReset,
  });

  @override
  ConsumerState<_FiltresSheet> createState() => _FiltresSheetState();
}

class _FiltresSheetState extends ConsumerState<_FiltresSheet> {
  String? _transaction;
  String? _ville;
  int?    _typeId;

  @override
  void initState() {
    super.initState();
    _transaction = widget.selectedTransaction;
    _ville       = widget.selectedVille;
    _typeId      = widget.selectedTypeId;
  }

  @override
  Widget build(BuildContext context) {
    final typesAsync  = ref.watch(typesBiensProvider);
    final villesAsync = ref.watch(villesProvider);

    return Padding(
      padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom),
      child: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Filtres',
                    style: Theme.of(context).textTheme.titleLarge),
                IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () =>
                        Navigator.of(context, rootNavigator: true).pop()),
              ],
            ),
            const SizedBox(height: 16),
            Text('Transaction',
                style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            Row(children: [
              _chip('Tous', _transaction == null,
                  () => setState(() => _transaction = null)),
              const SizedBox(width: 8),
              _chip('Location', _transaction == 'location',
                  () => setState(() => _transaction = 'location')),
              const SizedBox(width: 8),
              _chip('Vente', _transaction == 'vente',
                  () => setState(() => _transaction = 'vente')),
            ]),
            const SizedBox(height: 16),
            Text('Ville',
                style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            villesAsync.when(
              data: (villes) => DropdownButtonFormField<String>(
                value: _ville,
                isExpanded: true,
                hint: const Text('Toutes les villes'),
                items: [
                  const DropdownMenuItem(
                      value: null, child: Text('Toutes')),
                  ...villes.map((v) =>
                      DropdownMenuItem(value: v, child: Text(v))),
                ],
                onChanged: (v) => setState(() => _ville = v),
                decoration: InputDecoration(
                    border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12))),
              ),
              loading: () => const SizedBox(
                height: 48,
                child: Center(
                    child: CircularProgressIndicator(strokeWidth: 2)),
              ),
              error: (_, __) =>
                  const Text('Impossible de charger les villes'),
            ),
            const SizedBox(height: 16),
            Text('Type de bien',
                style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            typesAsync.when(
              data: (types) => DropdownButtonFormField<int>(
                value: _typeId,
                isExpanded: true,
                hint: const Text('Tous les types'),
                items: [
                  const DropdownMenuItem(
                      value: null, child: Text('Tous')),
                  ...types.map((t) =>
                      DropdownMenuItem(value: t.id, child: Text(t.libelle))),
                ],
                onChanged: (v) => setState(() => _typeId = v),
                decoration: InputDecoration(
                    border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12))),
              ),
              loading: () => const SizedBox(
                height: 48,
                child: Center(
                    child: CircularProgressIndicator(strokeWidth: 2)),
              ),
              error: (_, __) =>
                  const Text('Impossible de charger les types'),
            ),
            const SizedBox(height: 24),
            Row(children: [
              Expanded(
                child: OutlinedButton(
                    onPressed: widget.onReset,
                    child: const Text('Réinitialiser')),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: ElevatedButton(
                    onPressed: () =>
                        widget.onApply(_transaction, _ville, _typeId),
                    child: const Text('Appliquer')),
              ),
            ]),
            const SizedBox(height: 16),
          ],
        ),
      ),
    );
  }

  Widget _chip(String label, bool selected, VoidCallback onTap) =>
      GestureDetector(
        onTap: onTap,
        child: Container(
          padding:
              const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          decoration: BoxDecoration(
            color: selected ? AppColors.primary : Colors.white,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
                color: selected
                    ? AppColors.primary
                    : AppColors.divider),
          ),
          child: Text(
            label,
            style: TextStyle(
                color: selected
                    ? Colors.white
                    : AppColors.textPrimary,
                fontWeight: FontWeight.w500),
          ),
        ),
      );
}
