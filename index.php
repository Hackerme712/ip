<?php
// Configuration de la base de données
$db_host = 'localhost'; // Hôte MySQL
$db_user = 'root';      // Nom d'utilisateur MySQL
$db_pass = '';          // Mot de passe MySQL
$db_name = 'ip';        // Nom de la base de données

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Si une requête POST est reçue
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    if (isset($_POST['ip'])) {
        $ip = $_POST['ip'];

        try {
            // Insérer l'adresse IP dans la base de données
            $stmt = $pdo->prepare("INSERT INTO ip_addresses (ip_address) VALUES (:ip)");
            $stmt->execute(['ip' => $ip]);

            echo json_encode(['status' => 'success', 'message' => 'IP enregistrée avec succès.', 'ip' => $ip]);
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Erreur lors de l\'insertion dans la base de données : ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Adresse IP manquante.']);
    }

    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stockage d'IP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        #message {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div id="message">Chargement de la page...</div>

    <script>
        // Récupérer l'IP publique via l'API ipify
        fetch('https://api.ipify.org?format=json')
            .then(response => response.json())
            .then(data => {
                console.log("erreur :", data.ip);
                envoyerIP(data.ip); // Envoyer l'IP au script PHP
            })
            .catch(err => {
                console.error("Erreur lors du chargement :", err);
                document.getElementById('message').textContent = "Erreur lors du chargement.";
            });

        // Fonction pour envoyer l'IP au serveur PHP
        function envoyerIP(ip) {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `ip=${encodeURIComponent(ip)}`
            })
            .then(response => response.json())
            .then(data => {
                console.log("Réponse du serveur :", data);
                if (data.status === 'success') {
                    document.getElementById('message').textContent = `Votre IP : ${ip} a été enregistrée avec succès dans la base de données !`;
                } else {
                    document.getElementById('message').textContent = `Erreur : ${data.message}`;
                }
            })
            .catch(error => {
                console.error("Erreur lors de l'envoi de l'IP :", error);
                document.getElementById('message').textContent = "Erreur lors de l'envoi de l'IP.";
            });
        }
    </script>
</body>
</html>
