<!-- resources/views/payment/index.blade.php -->
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
<style>
   

    /* Reset complet du modal */
    .vbox-content .venoframe {
        width: 360px !important;
        height: 640px !important;
        left: 50% !important;
        top: 50% !important;
        transform: translate(-50%, -50%) !important;
        margin: 0 !important;
        padding: 0 !important;
        border: none !important;
        zoom: 0.8 !important;
        /* Réduire le zoom à 80% */
        -moz-transform: translate(-50%, -50%) scale(1.0) !important;
        -webkit-transform: translate(-50%, -50%) scale(1.0) !important;
    }

    /* Forcer la désactivation du zoom sur le conteneur */
    .vbox-container {
        zoom: 1 !important;
        -webkit-transform: none !important;
        transform: none !important;
        overflow: hidden !important;
    }

    /* Réinitialiser les styles de l'overlay */
    .vbox-overlay {
        background: rgba(0, 0, 0, 0.7) !important;
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        overflow: hidden !important;
    }

    /* Forcer le viewport à la bonne taille */
    @viewport {
        zoom: 1.0;
        width: device-width;
    }
</style>

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
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            document.getElementById('paymentButton').addEventListener('click', initiatePayment);
            
            async function initiatePayment() {
                try {
                    const paymentParams = {
                        item_name: "Iphone 7", // Nom du produit
                        item_price: "56000", // Prix du produit
                        currency: "XOF", // Devise
                        ref_command: "YEUJEJEJEJE",  // Référence de la commande
                        command_name: "Paiement Iphone 7 Gold via PayTech", // Nom de la commande
                        ipn_url: "", // URL de notification IPN (CallBack) en https  doit etre une URL api valide et public
                        success_url: "http://127.0.0.1:8000/payment-success/", // URL de redirection après paiement en https
                        cancel_url: "http://127.0.0.1:8000/payment-failed", // URL de redirection en cas d'annulation en https
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
                    
                    const data = await response.json();
                    console.log("Full Response:", data);
                    
                    // Extraction de l'URL de paiement
                    const paymentUrl =
                        data?.redirect_url ||
                        data?.redirectUrl ||
                        data?.data?.redirect_url ||
                        data?.data?.redirectUrl ||
                        data?.data?.token;
                    
                    if (!paymentUrl) {
                        throw new Error("URL de paiement manquante dans la réponse");
                    }
                    
                    // Initialisation du paiement PayTech
                    if (window.PayTech) {
                        new window.PayTech({})
                            .withOption({
                                tokenUrl: paymentUrl,
                                prensentationMode: window.PayTech.OPEN_IN_POPUP,
                            })
                            .send();
                    } else {
                        console.error("SDK PayTech non chargé");
                    }
                } catch (error) {
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