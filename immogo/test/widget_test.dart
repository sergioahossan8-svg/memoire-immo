import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_dotenv/flutter_dotenv.dart';
import 'package:immogo/app.dart';

void main() {
  setUpAll(() async {
    // Charger le fichier .env pour les tests
    await dotenv.load(fileName: '.env');
  });

  testWidgets('ImmoGo app démarre sans crash', (WidgetTester tester) async {
    await tester.pumpWidget(
      const ProviderScope(child: ImmoGoApp()),
    );
    // Vérifier que l'app se lance (au moins un widget est rendu)
    expect(find.byType(ImmoGoApp), findsOneWidget);
  });
}
