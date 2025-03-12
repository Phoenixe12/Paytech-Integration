# Intégration de PayTech avec Laravel

Une implémentation simple et efficace de la passerelle de paiement PayTech pour les applications Laravel. Cette intégration vous permet d'accepter facilement les paiements via les services de PayTech avec une interface réactive et conviviale.

![PayTech Integration](https://paytech.sn/assets/images/logo.png)

## Fonctionnalités

- 🔐 Traitement sécurisé des paiements avec PayTech
- 🌐 Interface de paiement responsive
- ✅ Gestion des succès et des échecs de paiement
- 📱 Design adapté aux mobiles
- 🔄 Gestion des notifications pour les mises à jour de statut de paiement
- 💾 Enregistrement des transactions avec journalisation détaillée

## Prérequis

- PHP 8.0+
- Laravel 9.0+
- Composer
- Compte marchand PayTech

## Guide d'installation pas à pas

### 1. Création du projet Laravel

```bash
# Créer un nouveau projet Laravel
composer create-project laravel/laravel paytech-integration

# Se déplacer dans le répertoire du projet
cd paytech-integration
```

### 2. Configuration de la base de données

Modifier le fichier `.env` pour configurer la connexion à la base de données :

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=paytech_db
DB_USERNAME=root
DB_PASSWORD=
```

Ajouter les clés d'API PayTech :

```
PAYTECH_API_KEY=votre_cle_api
PAYTECH_API_SECRET=votre_secret_api
```

### 3. Création du modèle et de la migration pour les notifications

```bash
# Créer le modèle et la migration
php artisan make:model Notification -m
```

### 4. Configurer la migration pour la table des notifications

Modifier le fichier de migration créé dans `database/migrations/xxxx_xx_xx_create_notifications_table.php` :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Exécuter les migrations.
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type_event')->nullable();
            $table->string('ref_command')->nullable();
            $table->json('custom_field')->nullable();
            $table->string('item_name')->nullable();
            $table->decimal('item_price', 10, 2)->nullable();
            $table->string('devise')->nullable();
            $table->string('command_name')->nullable();
            $table->string('env')->nullable();
            $table->string('token')->nullable();
            $table->string('api_key_sha256')->nullable();
            $table->string('api_secret_sha256')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Inverser les migrations.
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
```

### 5. Exécuter la migration

```bash
php artisan migrate
```

### 6. Modifier le modèle Notification

Créer ou modifier le fichier `app/Models/Notification.php` :

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * La table associée au modèle.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<string>
     */
    protected $fillable = [
        'type_event',      // Type d'événement (paiement, remboursement, etc.)
        'ref_command',     // Référence de la commande
        'custom_field',    // Champ personnalisé (stocké en JSON)
        'item_name',       // Nom de l'article
        'item_price',      // Prix de l'article
        'devise',          // Devise utilisée
        'command_name',    // Nom de la commande
        'env',             // Environnement (test ou production)
        'token',           // Token de paiement
        'api_key_sha256',  // Clé API hashée
        'api_secret_sha256' // Secret API hashé
    ];

    /**
     * Les attributs qui doivent être convertis.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'custom_field' => 'json',      // Conversion automatique du JSON
        'item_price' => 'decimal:2',   // Conversion en décimal avec 2 décimales
    ];
}
```

### 7. Création du contrôleur PayTech

```bash
php artisan make:controller PaytechController --resource
```

### 8. Configurer le contrôleur PayTech

Modifier le fichier `app/Http/Controllers/PaytechController.php` :

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Notification;

class PaytechController extends Controller
{
    /**
     * Afficher la page principale de paiement.
     */
    public function index()
    {
        // Retourne la vue principale de paiement
        return view('paytech');
    }

    /**
     * Créer une nouvelle demande de paiement avec PayTech.
     */
    public function create(Request $request)
    {
        // Définition des en-têtes pour l'API PayTech
        $headers = [
            'Content-Type' => 'application/json',            // Type de contenu JSON
            'API_KEY' => env('PAYTECH_API_KEY'),             // Clé API depuis les variables d'environnement
            'API_SECRET' => env('PAYTECH_API_SECRET'),       // Secret API depuis les variables d'environnement
            'Accept' => 'application/json',                  // Accepter les réponses en JSON
        ];

        try {
            // Envoi de la requête à l'API PayTech
            $response = Http::withHeaders($headers)
                ->post('https://paytech.sn/api/payment/request-payment', $request->all());

            // Si la requête est réussie
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json()  // Retourne les données de réponse
                ]);
            }

            // En cas d'erreur de l'API
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => 'Erreur de paiement',
                    'status' => $response->status(),  // Code de statut HTTP
                    'data' => $response->json()       // Détails de l'erreur
                ]
            ], $response->status());
        } catch (\Exception $e) {
            // En cas d'exception (erreur serveur, timeout, etc.)
            return response()->json([
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),  // Message d'erreur
                    'status' => 500,                // Erreur serveur interne
                    'data' => null
                ]
            ], 500);
        }
    }

    /**
     * Gérer le retour d'un paiement réussi.
     */
    public function paymentSuccess(Request $request)
    {
        // Ici, vous pourriez ajouter une logique pour vérifier le paiement
        // ou mettre à jour le statut d'une commande

        // Affiche la page de succès
        return view('success');
    }

    /**
     * Gérer le retour d'un paiement échoué ou annulé.
     */
    public function paymentFailed(Request $request)
    {
        // Ici, vous pourriez ajouter une logique pour gérer l'échec
        // ou l'annulation d'un paiement

        // Affiche la page d'annulation
        return view('cancel');
    }

    // Méthodes non utilisées mais générées par --resource
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
```

### 9. Configurer les routes

Modifier le fichier `routes/web.php` :

```php
<?php

use App\Http\Controllers\PaytechController;
use Illuminate\Support\Facades\Route;

// Route principale qui affiche la page de paiement
Route::get('/', function () {
    return view('paytech');
});

// Routes pour les ressources PayTech
Route::resource('/paytech', PaytechController::class);

// Route pour créer un paiement (API)
Route::post('/create-payment', [PaytechController::class, 'create'])->name('payment.create');

// Route pour le retour en cas de succès
Route::get('/payment-success', [PaytechController::class, 'paymentSuccess'])->name('payment.success');

// Route pour le retour en cas d'échec
Route::get('/payment-failed', [PaytechController::class, 'paymentFailed'])->name('payment.failed');
```

### 10. Création des vues

#### Créer le répertoire pour les vues
```bash
mkdir -p resources/views
```

#### Créer la vue principale de paiement (resources/views/paytech.blade.php)

```php
<!-- resources/views/paytech.blade.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Paiement PayTech</title>
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://paytech.sn/cdn/paytech.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Paiement PayTech</div>

                    <div class="card-body">
                        <h3>Payer avec PayTech</h3>
                        <button id="paymentButton" class="btn btn-primary">Effectuer le paiement</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="https://paytech.sn/cdn/paytech.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Récupération du token CSRF pour la sécurité
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Ajout de l'écouteur d'événement sur le bouton de paiement
            document.getElementById('paymentButton').addEventListener('click', initiatePayment);
            
            // Fonction pour initier le paiement
            async function initiatePayment() {
                try {
                    // Paramètres de paiement à envoyer à PayTech
                    const paymentParams = {
                        item_name: "Iphone 7", // Nom du produit
                        item_price: "56000", // Prix du produit
                        currency: "XOF", // Devise
                        ref_command: "YEUJEJEJEJE",  // Référence de la commande
                        command_name: "Paiement Iphone 7 Gold via PayTech", // Nom de la commande
                        ipn_url: "", // URL de notification IPN (CallBack) en https
                        success_url: "http://127.0.0.1:8000/payment-success/", // URL de redirection après paiement
                        cancel_url: "http://127.0.0.1:8000/payment-failed", // URL de redirection en cas d'annulation
                    };
                    
                    // Envoi de la requête via fetch avec CSRF token
                    const response = await fetch('/create-payment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(paymentParams)
                    });
                    
                    // Récupération de la réponse JSON
                    const data = await response.json();
                    console.log("Full Response:", data);
                    
                    // Extraction de l'URL de paiement (plusieurs formats possibles selon la réponse)
                    const paymentUrl =
                        data?.redirect_url ||
                        data?.redirectUrl ||
                        data?.data?.redirect_url ||
                        data?.data?.redirectUrl ||
                        data?.data?.token;
                    
                    // Vérification de la présence de l'URL
                    if (!paymentUrl) {
                        throw new Error("URL de paiement manquante dans la réponse");
                    }
                    
                    // Initialisation du paiement PayTech
                    if (window.PayTech) {
                        new window.PayTech({})
                            .withOption({
                                tokenUrl: paymentUrl,
                                prensentationMode: window.PayTech.OPEN_IN_POPUP, // Ouverture dans une popup
                            })
                            .send();
                    } else {
                        console.error("SDK PayTech non chargé");
                    }
                } catch (error) {
                    // Gestion des erreurs
                    console.error(
                        "Erreur de paiement:",
                        error.response?.data?.error || error.message
                    );
                    alert("Une erreur est survenue lors du paiement. Veuillez réessayer.");
                }
            }
        });
    </script>
</body>
</html>
```

#### Créer la vue pour le succès de paiement (resources/views/success.blade.php)

```php
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Réussi | {{ config('app.name') }}</title>
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
   
</head>

<body>
    <div class="container">
        <div class="success-card">
            <!-- Icône de succès -->
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <!-- Titre et message de succès -->
            <h1 class="success-title">Paiement réussi !</h1>
            <p class="success-message">
                Votre transaction a été traitée avec succès. Merci pour votre confiance !
            </p>
            
            <!-- Bouton retour à l'accueil -->
            <div class="d-flex justify-content-center gap-3">
                <a href="{{ url('/') }}" class="btn btn-success-primary">
                    <i class="fas fa-home me-2"></i>Retour à l'accueil
                </a>
            </div>
            
            <!-- Détails de la transaction -->
            <div class="transaction-details">
                <div class="detail-row">
                    <span class="detail-label">Date</span>
                    <span class="detail-value">{{ date('d/m/Y H:i') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Référence</span>
                    <span class="detail-value">{{ request()->ref ?? 'PAY-'.time() }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Statut</span>
                    <span class="detail-value text-success">Confirmé</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

#### Créer la vue pour l'échec de paiement (resources/views/cancel.blade.php)

```php
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Annulé | {{ config('app.name') }}</title>
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
   
</head>

<body>
    <div class="container">
        <div class="cancel-card">
            <!-- Icône d'annulation -->
            <div class="cancel-icon">
                <i class="fas fa-times"></i>
            </div>
            
            <!-- Titre et message d'annulation -->
            <h1 class="cancel-title">Paiement annulé</h1>
            <p class="cancel-message">
                Votre transaction n'a pas pu être complétée. Vous pouvez réessayer ou contacter notre service client pour plus d'informations.
            </p>
            
            <!-- Boutons d'action -->
            <div class="d-flex justify-content-center gap-3">
                <a href="javascript:history.back()" class="btn btn-retry">
                    <i class="fas fa-redo me-2"></i>Réessayer
                </a>
                <a href="{{ url('/') }}" class="btn btn-home">
                    <i class="fas fa-home me-2"></i>Accueil
                </a>
            </div>
            
            <!-- Détails de la transaction -->
            <div class="transaction-details">
                <div class="detail-row">
                    <span class="detail-label">Date</span>
                    <span class="detail-value">{{ date('d/m/Y H:i') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Référence</span>
                    <span class="detail-value">{{ request()->ref ?? 'PAY-'.time() }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Statut</span>
                    <span class="detail-value text-danger">Annulé</span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

### 11. Lancer l'application

```bash
php artisan serve
```

Votre application sera disponible à l'adresse http://127.0.0.1:8000.

## Fonctionnement technique

### Flux de paiement

1. **Initialisation** : L'utilisateur clique sur le bouton "Effectuer le paiement" sur la page principale.
2. **Préparation** : JavaScript prépare les paramètres de paiement (montant, devise, référence, etc.).
3. **Requête au serveur** : Une requête AJAX est envoyée au endpoint `/create-payment`.
4. **Traitement côté serveur** : Le contrôleur `PaytechController` envoie ces informations à l'API PayTech.
5. **Réponse de PayTech** : L'API PayTech renvoie un token ou une URL de paiement.
6. **Affichage du formulaire** : Le SDK PayTech ouvre une popup avec le formulaire de paiement.
7. **Paiement** : L'utilisateur entre ses informations de paiement et confirme.
8. **Redirection** : Après le paiement, l'utilisateur est redirigé vers :
   - `/payment-success` en cas de succès
   - `/payment-failed` en cas d'échec ou d'annulation

### Sécurité

- **Protection CSRF** : Toutes les requêtes POST incluent un token CSRF.
- **Variables d'environnement** : Les clés d'API sont stockées dans les variables d'environnement.
- **Hachage** : Les clés API peuvent être hashées avant d'être stockées en base de données.
- **HTTPS** : Il est recommandé d'utiliser HTTPS en production.

## Personnalisation

### Modifier les paramètres de paiement

Pour personnaliser les détails du paiement, modifiez l'objet `paymentParams` dans `paytech.blade.php` :

```javascript
const paymentParams = {
    item_name: "Votre produit",             // Nom du produit
    item_price: "10000",                    // Prix du produit
    currency: "XOF",                        // Devise (XOF, EUR, USD, etc.)
    ref_command: "REF-" + Date.now(),       // Référence unique
    command_name: "Achat sur votre site",   // Description
    ipn_url: "https://votre-site.com/ipn",  // URL de notification
    success_url: "https://votre-site.com/payment-success/",
    cancel_url: "https://votre-site.com/payment-failed",
};
```

### Modifier le style

Les pages de succès et d'annulation utilisent des variables CSS personnalisables :

```css
:root {
    --primary-color: #4f46e5;      /* Couleur principale */
    --primary-hover: #4338ca;      /* Couleur au survol */
    --success-color: #10b981;      /* Couleur de succès */
    --danger-color: #ef4444;       /* Couleur d'erreur */
    --text-color: #1f2937;         /* Couleur du texte */
    --background-color: #f9fafb;   /* Couleur de fond */
    --border-radius: 12px;         /* Rayon des bordures */
    --box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}
```

# Configuration de l'IPN PayTech

## Qu'est-ce que l'IPN PayTech ?

L'IPN (Instant Payment Notification) est un mécanisme qui permet à PayTech de notifier votre application lorsqu'un paiement est effectué. Cette notification est envoyée directement depuis les serveurs de PayTech vers une URL publique définie dans votre application.

## Prérequis

- Une URL publique accessible depuis Internet
- La capacité de recevoir des requêtes POST avec des données de formulaire (non JSON)

## Utilisation de webhook.site pour les tests

Pour tester rapidement votre intégration IPN sans avoir à déployer votre application en production, vous pouvez utiliser [webhook.site](https://webhook.site/).

### Étapes de configuration

1. **Obtenir une URL webhook.site**
   - Rendez-vous sur [https://webhook.site/](https://webhook.site/)
   - Une URL unique sera automatiquement générée pour vous (par exemple `https://webhook.site/abcd1234-5678-90ef-ghij-klmnopqrstuv`)
   - Conservez cette page ouverte pour voir les notifications entrantes

2. **Configurer votre contrôleur IPN**

```php
<?php
// Dans PaytechController.php

/**
 * Traite les notifications IPN de PayTech.
 * IMPORTANT: Cette méthode doit accepter les données en format FORM, pas en JSON.
 */
public function handleIpn(Request $request)
{
    // Log de toutes les données reçues (utile pour le débogage)
    \Log::info('IPN PayTech reçue:', $request->all());
    
    // Récupération des données du formulaire (pas en JSON)
    $typeEvent = $request->input('type_event');
    $refCommand = $request->input('ref_command');
    $customField = $request->input('custom_field');
    $itemName = $request->input('item_name');
    $itemPrice = $request->input('item_price');
    $devise = $request->input('devise');
    $commandName = $request->input('command_name');
    $environment = $request->input('env');
    $token = $request->input('token');
    
    // Valider la notification (vérifier si la transaction existe dans votre système)
    if (empty($refCommand)) {
        \Log::error('IPN PayTech invalide: référence manquante');
        return response('Notification invalide', 400);
    }
    
    // Créer un enregistrement dans la table notifications
    try {
        Notification::create([
            'type_event' => $typeEvent,
            'ref_command' => $refCommand,
            'custom_field' => $customField,
            'item_name' => $itemName,
            'item_price' => $itemPrice,
            'devise' => $devise,
            'command_name' => $commandName,
            'env' => $environment,
            'token' => $token,
            'api_key_sha256' => hash('sha256', env('PAYTECH_API_KEY')),
            'api_secret_sha256' => hash('sha256', env('PAYTECH_API_SECRET'))
        ]);
        
        // Mettre à jour le statut de la commande dans votre système
        // Exemple: Order::where('reference', $refCommand)->update(['status' => 'paid']);
        
        // Répondre avec un statut de succès (important pour PayTech)
        return response('IPN reçue avec succès', 200);
    } catch (\Exception $e) {
        \Log::error('Erreur lors du traitement de l\'IPN PayTech: ' . $e->getMessage());
        return response('Erreur lors du traitement de la notification', 500);
    }
}
```

3. **Configurer la route IPN**

```php
// Dans routes/web.php ou routes/api.php
Route::post('/paytech-ipn', [PaytechController::class, 'handleIpn'])->name('paytech.ipn');

// IMPORTANT: Cette route doit être accessible sans vérification CSRF
// Ajoutez l'exception dans app/Http/Middleware/VerifyCsrfToken.php
```

4. **Désactiver la vérification CSRF pour la route IPN**

```php
<?php
// Dans app/Http/Middleware/VerifyCsrfToken.php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        '/paytech-ipn', // Exclure la route IPN de la vérification CSRF
    ];
}
```

5. **Mettre à jour les paramètres de paiement**

```javascript
// Dans paytech.blade.php
const paymentParams = {
    item_name: "Iphone 7",
    item_price: "56000",
    currency: "XOF",
    ref_command: "REF-" + Date.now(), // Référence unique basée sur le timestamp
    command_name: "Paiement Iphone 7 Gold via PayTech",
    ipn_url: "https://webhook.site/votre-url-unique", // URL webhook.site pour les tests
    success_url: "http://127.0.0.1:8000/payment-success/",
    cancel_url: "http://127.0.0.1:8000/payment-failed",
};
```

## Test de l'IPN avec webhook.site

1. Effectuez un paiement test dans votre application
2. Observez la page webhook.site - vous devriez voir une requête entrante avec les détails de la transaction
3. Analysez les données reçues pour vous assurer qu'elles correspondent à votre transaction

Exemple de données reçues sur webhook.site :
```
type_event: payment_successful
ref_command: REF-1647359412345
item_name: Iphone 7
item_price: 56000
devise: XOF
command_name: Paiement Iphone 7 Gold via PayTech
env: test
token: abcdef123456789
```

## Passage en production

Pour passer en production :

1. Remplacez l'URL webhook.site par l'URL publique de votre application :
```javascript
ipn_url: "https://votre-domaine.com/paytech-ipn",
```

2. Assurez-vous que votre serveur est configuré pour accepter les requêtes POST externes

3. Vérifiez les logs de votre application pour confirmer la réception des notifications IPN

4. Implémentez une logique métier appropriée dans la méthode `handleIpn()` pour traiter les paiements confirmés (mise à jour de commandes, envoi d'emails, etc.)

## Dépannage

### Problèmes courants

1. **Notifications IPN non reçues**
   - Vérifiez que l'URL IPN est publique et accessible depuis Internet
   - Assurez-vous que l'URL IPN est correctement configurée dans vos paramètres de paiement
   - Vérifiez que la route n'est pas bloquée par un pare-feu ou une restriction d'accès

2. **Erreurs 419 (CSRF)**
   - Assurez-vous que la route IPN est bien exclue de la vérification CSRF

3. **Erreurs 500 lors du traitement IPN**
   - Vérifiez les logs de votre application pour identifier l'erreur précise
   - Assurez-vous que votre modèle Notification accepte tous les champs nécessaires

## Vérification de sécurité

Pour renforcer la sécurité de vos IPN :

1. Validez l'origine de la requête (vérifiez qu'elle provient bien de PayTech)
2. Vérifiez que la transaction existe dans votre système avant de la traiter
3. Utilisez le token pour valider l'authenticité de la notification
4. Implémentez une vérification de signature si PayTech le supporte



## Déploiement en production

Pour déployer votre intégration en production :

1. Mettez à jour les URL de redirection dans `paytech.blade.php` pour qu'elles pointent vers votre domaine :

```javascript
success_url: "https://votre-domaine.com/payment-success/",
cancel_url: "https://votre-domaine.com/payment-failed",
```

2. Configurez les variables d'environnement de production dans votre `.env` :

```
PAYTECH_API_KEY=votre_cle_api_production
PAYTECH_API_SECRET=votre_secret_api_production
```

3. Activez HTTPS sur votre serveur (obligatoire pour les paiements en ligne).

4. Si nécessaire, configurez un webhook IPN dans votre tableau de bord PayTech.

## Dépannage

### Problèmes courants

1. **Le modal ne s'affiche pas correctement** :
   - Vérifiez que le SDK PayTech est bien chargé
   - Assurez-vous que les styles CSS du modal sont correctement appliqués

2. **Erreur "URL de paiement manquante"** :
   - Vérifiez les clés API dans le fichier `.env`
   - Consultez les logs serveur pour plus de détails sur l'erreur

3. **Pas de redirection après paiement** :
   - Vérifiez que les URL de redirection sont correctes et accessibles
   - Assurez-vous que les routes correspondantes sont bien définies

## Support et contribution

Pour toute question ou suggestion concernant cette intégration, n'hésitez pas à ouvrir une issue sur GitHub ou à contacter notre équipe de support.

Pour contribuer à ce projet :
1. Créez un fork du dépôt
2. Créez une branche pour votre fonctionnalité
3. Soumettez une pull request

