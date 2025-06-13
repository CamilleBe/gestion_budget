#!/bin/bash

echo "🚀 Initialisation du projet Symfony - Gestion de Budget"

# Créer le projet Symfony
if [ ! -f "composer.json" ]; then
    echo "📦 Création du projet Symfony..."
    symfony new . --webapp --no-git
    
    # Installer les dépendances supplémentaires
    echo "📦 Installation des dépendances..."
    composer require doctrine/annotations
    composer require symfony/form
    composer require symfony/validator
    composer require symfony/security-bundle
    composer require symfony/twig-bundle
    composer require --dev symfony/maker-bundle
    composer require --dev doctrine/doctrine-fixtures-bundle
fi

# Créer le fichier .env.local
if [ ! -f ".env.local" ]; then
    echo "⚙️ Configuration de l'environnement..."
    cat > .env.local << 'EOF'
# Configuration locale
APP_ENV=dev
APP_SECRET=your-secret-key-here
DATABASE_URL="mysql://budget_user:budget_password@database:3306/budget_db"
EOF
fi

echo "✅ Projet initialisé avec succès !"
echo "🔧 Prochaines étapes :"
echo "1. docker-compose up -d"
echo "2. docker-compose exec app php bin/console doctrine:database:create"
echo "3. docker-compose exec app php bin/console doctrine:migrations:migrate"
echo "4. Accéder à l'application : http://localhost:8000" 