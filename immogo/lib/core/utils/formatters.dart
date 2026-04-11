// lib/core/utils/formatters.dart
import 'package:intl/intl.dart';

class Formatters {
  static String prix(double montant) {
    final f = NumberFormat('#,###', 'fr_FR');
    return '${f.format(montant.toInt())} FCFA';
  }

  static String date(String? dateStr) {
    if (dateStr == null || dateStr.isEmpty) return '-';
    try {
      // Already formatted like "01/01/2025"
      return dateStr;
    } catch (_) {
      return dateStr;
    }
  }

  static String superficie(double? s) {
    if (s == null) return '-';
    return '${s.toStringAsFixed(0)} m²';
  }

  static String statut(String s) {
    const map = {
      'disponible': 'Disponible',
      'reserve': 'Réservé',
      'loue': 'Loué',
      'vendu': 'Vendu',
      'indisponible': 'Indisponible',
      'en_attente': 'En attente',
      'actif': 'Actif',
      'annule': 'Annulé',
    };
    return map[s] ?? s;
  }

  static String transaction(String t) {
    return t == 'vente' ? 'Vente' : 'Location';
  }
}
