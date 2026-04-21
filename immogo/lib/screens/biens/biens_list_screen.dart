// lib/screens/biens/biens_list_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/theme/app_theme.dart';
import '../../providers/auth_provider.dart';
import '../../providers/bien_provider.dart';
import '../../providers/favori_provider.dart';
import '../../widgets/bien/bien_card.dart';
import '../../widgets/common/loading_widget.dart';
import '../../screens/biens/estimation_screen.dart';

class BiensListScreen extends ConsumerStatefulWidget {
  const BiensListScreen({super.key});

  @override
  ConsumerState<BiensListScreen> createState() => _BiensListScreenState();
}

class _BiensListScreenState extends ConsumerState<BiensListScreen> {
  final _scrollCtrl = ScrollController();
  final _searchCtrl = TextEditingController();
  String? _selectedTransaction;
  String? _selectedVille;
  int? _selectedTypeId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(bienListProvider.notifier).refresh();
      final authState = ref.read(authProvider);
      final isAuth = authState.status == AuthStatus.authenticated;
      final role = authState.user?.role ?? '';
      final isClient = role == 'client' || role == '';
      if (isAuth && isClient) {
        ref.read(favoriProvider.notifier).load();
      }
    });
    _scrollCtrl.addListener(_onScroll);
  }

  void _onScroll() {
    if (_scrollCtrl.position.pixels >=
        _scrollCtrl.position.maxScrollExtent - 200) {
      ref.read(bienListProvider.notifier).loadMore();
    }
  }

  @override
  void dispose() {
    _scrollCtrl.dispose();
    _searchCtrl.dispose();
    super.dispose();
  }

  void _applyFiltres() {
    ref.read(bienListProvider.notifier).applyFiltres(BienFiltres(
          transaction: _selectedTransaction,
          typeBienId: _selectedTypeId,
          ville: _selectedVille,
          search: _searchCtrl.text.isNotEmpty ? _searchCtrl.text : null,
        ));
  }

  void _showFiltresSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => _FiltresSheet(
        selectedTransaction: _selectedTransaction,
        selectedVille: _selectedVille,
        selectedTypeId: _selectedTypeId,
        onApply: (transaction, ville, typeId) {
          setState(() {
            _selectedTransaction = transaction;
            _selectedVille = ville;
            _selectedTypeId = typeId;
          });
          _applyFiltres();
          Navigator.pop(context);
        },
        onReset: () {
          setState(() {
            _selectedTransaction = null;
            _selectedVille = null;
            _selectedTypeId = null;
          });
          ref
              .read(bienListProvider.notifier)
              .applyFiltres(const BienFiltres());
          Navigator.pop(context);
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(bienListProvider);
    final authState = ref.watch(authProvider);
    final isAuth = authState.status == AuthStatus.authenticated;
    // Admins et super-admins ne peuvent pas ajouter en favoris
    final userRole = authState.user?.role ?? '';
    final isClient = userRole == 'client' || userRole == '';
    final canFavori = isAuth && isClient;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('ImmoGo — Biens'),
        actions: [
          IconButton(
            icon: const Icon(Icons.calculate_outlined),
            tooltip: 'Estimation',
            onPressed: () => context.push('/estimation'),
          ),
        ],
      ),
      body: Column(
        children: [
          // Barre de recherche
          Container(
            color: AppColors.primary,
            padding:
                const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _searchCtrl,
                    onSubmitted: (_) => _applyFiltres(),
                    style: const TextStyle(color: Colors.white),
                    decoration: InputDecoration(
                      hintText: 'Rechercher un bien...',
                      hintStyle:
                          const TextStyle(color: Colors.white60),
                      prefixIcon: const Icon(Icons.search,
                          color: Colors.white70),
                      suffixIcon: _searchCtrl.text.isNotEmpty
                          ? IconButton(
                              icon: const Icon(Icons.clear,
                                  color: Colors.white70),
                              onPressed: () {
                                _searchCtrl.clear();
                                _applyFiltres();
                              })
                          : null,
                      filled: true,
                      fillColor: Colors.white.withOpacity(0.15),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide.none,
                      ),
                      contentPadding: const EdgeInsets.symmetric(
                          horizontal: 16, vertical: 12),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.2),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: IconButton(
                    icon: const Icon(Icons.tune, color: Colors.white),
                    onPressed: _showFiltresSheet,
                  ),
                ),
              ],
            ),
          ),
          // Filtres actifs
          if (_selectedTransaction != null ||
              _selectedVille != null ||
              _selectedTypeId != null)
            Container(
              padding: const EdgeInsets.symmetric(
                  horizontal: 16, vertical: 8),
              color: Colors.white,
              child: Row(
                children: [
                  const Icon(Icons.filter_list,
                      size: 16, color: AppColors.secondary),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Wrap(
                      spacing: 6,
                      children: [
                        if (_selectedTransaction != null)
                          _filterChip(_selectedTransaction!
                              .toUpperCase()),
                        if (_selectedVille != null)
                          _filterChip(_selectedVille!),
                      ],
                    ),
                  ),
                  TextButton(
                    onPressed: () {
                      setState(() {
                        _selectedTransaction = null;
                        _selectedVille = null;
                        _selectedTypeId = null;
                      });
                      ref
                          .read(bienListProvider.notifier)
                          .applyFiltres(const BienFiltres());
                    },
                    child: const Text('Effacer'),
                  ),
                ],
              ),
            ),
          // Liste
          Expanded(
            child: state.biens.isEmpty && state.isLoading
                ? const LoadingWidget(message: 'Chargement des biens...')
                : state.biens.isEmpty && !state.isLoading
                    ? EmptyWidget(
                        message:
                            'Aucun bien trouvé\npour ces critères.',
                        icon: Icons.home_work_outlined,
                      )
                    : RefreshIndicator(
                        onRefresh: () async =>
                            ref.read(bienListProvider.notifier).refresh(),
                        child: ListView.builder(
                          controller: _scrollCtrl,
                          padding: const EdgeInsets.all(16),
                          itemCount: state.biens.length +
                              (state.hasMore ? 1 : 0),
                          itemBuilder: (context, index) {
                            if (index >= state.biens.length) {
                              return const Padding(
                                padding: EdgeInsets.all(16),
                                child: Center(
                                    child: CircularProgressIndicator()),
                              );
                            }
                            final bien = state.biens[index];
                            // watch pour être réactif aux changements de favoris (clients seulement)
                            final favorisState = ref.watch(favoriProvider);
                            final isFavori = canFavori
                                ? (favorisState.valueOrNull
                                        ?.any((b) => b.id == bien.id) ??
                                    false)
                                : false;
                            return Padding(
                              padding:
                                  const EdgeInsets.only(bottom: 16),
                              child: BienCard(
                                bien: bien,
                                isFavori: isFavori,
                                showFavoriButton: canFavori,
                                onTap: () => context
                                    .push('/biens/${bien.id}'),
                                onFavoriTap: canFavori
                                    ? () => ref
                                        .read(favoriProvider.notifier)
                                        .toggle(bien.id)
                                    : null,
                              ),
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _filterChip(String label) => Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        decoration: BoxDecoration(
          color: AppColors.secondary.withOpacity(0.1),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: AppColors.secondary),
        ),
        child: Text(label,
            style: const TextStyle(
                fontSize: 11,
                color: AppColors.secondary,
                fontWeight: FontWeight.w600)),
      );
}

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
  int? _typeId;

  @override
  void initState() {
    super.initState();
    _transaction = widget.selectedTransaction;
    _ville = widget.selectedVille;
    _typeId = widget.selectedTypeId;
  }

  @override
  Widget build(BuildContext context) {
    final typesAsync = ref.watch(typesBiensProvider);
    final villesAsync = ref.watch(villesProvider);

    return Padding(
      padding: EdgeInsets.only(
          bottom: MediaQuery.of(context).viewInsets.bottom,
          left: 20,
          right: 20,
          top: 20),
      child: SingleChildScrollView(
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
                    onPressed: () => Navigator.pop(context)),
              ],
            ),
            const SizedBox(height: 16),
            Text('Transaction',
                style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            Row(
              children: [
                _chip('Tous', _transaction == null, () {
                  setState(() => _transaction = null);
                }),
                const SizedBox(width: 8),
                _chip('Location', _transaction == 'location', () {
                  setState(() => _transaction = 'location');
                }),
                const SizedBox(width: 8),
                _chip('Vente', _transaction == 'vente', () {
                  setState(() => _transaction = 'vente');
                }),
              ],
            ),
            const SizedBox(height: 16),
            Text('Ville', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            villesAsync.when(
              data: (villes) => DropdownButtonFormField<String>(
                value: _ville,
                hint: const Text('Toutes les villes'),
                items: [
                  const DropdownMenuItem(value: null, child: Text('Toutes')),
                  ...villes.map((v) =>
                      DropdownMenuItem(value: v, child: Text(v))),
                ],
                onChanged: (v) => setState(() => _ville = v),
                decoration: InputDecoration(
                    border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12))),
              ),
              loading: () => const CircularProgressIndicator(),
              error: (_, __) => const Text('Impossible de charger'),
            ),
            const SizedBox(height: 16),
            Text('Type de bien',
                style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            typesAsync.when(
              data: (types) => DropdownButtonFormField<int>(
                value: _typeId,
                hint: const Text('Tous les types'),
                items: [
                  const DropdownMenuItem(value: null, child: Text('Tous')),
                  ...types.map((t) =>
                      DropdownMenuItem(value: t.id, child: Text(t.libelle))),
                ],
                onChanged: (v) => setState(() => _typeId = v),
                decoration: InputDecoration(
                    border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12))),
              ),
              loading: () => const CircularProgressIndicator(),
              error: (_, __) => const Text('Impossible de charger'),
            ),
            const SizedBox(height: 24),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: widget.onReset,
                    child: const Text('Réinitialiser'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: ElevatedButton(
                    onPressed: () =>
                        widget.onApply(_transaction, _ville, _typeId),
                    child: const Text('Appliquer'),
                  ),
                ),
              ],
            ),
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
                color: selected ? Colors.white : AppColors.textPrimary,
                fontWeight: FontWeight.w500),
          ),
        ),
      );
}
