<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement Annulé | {{ config('app.name') }}</title>
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --danger-color: #ef4444;
            --text-color: #1f2937;
            --background-color: #f9fafb;
            --border-radius: 12px;
            --box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        body {
            background-color: var(--background-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .cancel-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            max-width: 500px;
            width: 90%;
            padding: 2rem;
            text-align: center;
            animation: fadeIn 0.6s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .cancel-icon {
            width: 90px;
            height: 90px;
            background-color: var(--danger-color);
            color: white;
            font-size: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: scaleIn 0.5s ease-in-out 0.3s both;
        }
        
        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }
        
        .cancel-title {
            color: var(--text-color);
            margin-bottom: 1rem;
            font-weight: 600;
        }
        
        .cancel-message {
            color: #6b7280;
            margin-bottom: 2rem;
        }
        
        .btn-retry {
            background-color: var(--primary-color);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: all 0.3s ease;
            margin-right: 10px;
        }
        
        .btn-retry:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }
        
        .btn-home {
            background-color: white;
            border: 1px solid #d1d5db;
            color: var(--text-color);
            padding: 10px 25px;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-home:hover {
            background-color: #f3f4f6;
            transform: translateY(-2px);
        }
        
        .transaction-details {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
            text-align: left;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        
        .detail-label {
            color: #6b7280;
            font-size: 0.9rem;
        }
        
        .detail-value {
            font-weight: 500;
            color: var(--text-color);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="cancel-card">
            <div class="cancel-icon">
                <i class="fas fa-times"></i>
            </div>
            
            <h1 class="cancel-title">Paiement annulé</h1>
            <p class="cancel-message">
                Votre transaction n'a pas pu être complétée. Vous pouvez réessayer ou contacter notre service client pour plus d'informations.
            </p>
            
            <div class="d-flex justify-content-center gap-3">
                <a href="javascript:history.back()" class="btn btn-retry">
                    <i class="fas fa-redo me-2"></i>Réessayer
                </a>
                <a href="{{ url('/') }}" class="btn btn-home">
                    <i class="fas fa-home me-2"></i>Accueil
                </a>
            </div>
            
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