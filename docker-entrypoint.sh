#!/bin/bash
set -e

echo "🚀 Démarrage de l'application Gestionnaire de Budget..."

# Fonction pour attendre que MySQL soit prêt
wait_for_mysql() {
    echo "⏳ Attente de la base de données MySQL..."
    
    until php -r "
        try {
            \$pdo = new PDO('mysql:host=database;port=3306', 'budget_user', 'budget_password');
            echo 'MySQL est prêt!' . PHP_EOL;
            exit(0);
        } catch (PDOException \$e) {
            echo 'MySQL pas encore prêt: ' . \$e->getMessage() . PHP_EOL;
            exit(1);
        }
    "; do
        echo "⏳ MySQL n'est pas encore prêt, attente de 2 secondes..."
        sleep 2
    done
}

# Créer le fichier .env.local s'il n'existe pas
if [ ! -f ".env.local" ]; then
    echo "⚙️ Création du fichier .env.local..."
    cat > .env.local << EOF
# Configuration locale générée automatiquement
APP_ENV=dev
APP_SECRET=$(php -r "echo bin2hex(random_bytes(16));")
DATABASE_URL="mysql://budget_user:budget_password@database:3306/budget_db"
EOF
    echo "✅ Fichier .env.local créé avec succès"
fi

# Installer les dépendances si nécessaire
if [ ! -d "vendor" ]; then
    echo "📦 Installation des dépendances Composer..."
    composer install --optimize-autoloader --no-interaction
    echo "✅ Dépendances installées"
fi

# Attendre que MySQL soit prêt
wait_for_mysql

# Créer la base de données si elle n'existe pas
echo "🗄️ Vérification/création de la base de données..."
php bin/console doctrine:database:create --if-not-exists --no-interaction
echo "✅ Base de données prête"

# Exécuter les migrations
echo "🔄 Exécution des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction
echo "✅ Migrations exécutées"

# Vider le cache
echo "🧹 Nettoyage du cache..."
php bin/console cache:clear
echo "✅ Cache nettoyé"

echo "🎉 Application prête ! Démarrage du serveur sur 0.0.0.0:8000"

# Démarrer le serveur PHP
exec php -S 0.0.0.0:8000 -t public 