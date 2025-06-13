# 💰 Application de Gestion de Budget - Symfony

Une application web moderne pour gérer votre budget personnel, développée avec Symfony et Docker.

## 🚀 Fonctionnalités

### ✅ Fonctionnalités actuellement disponibles
- **Gestion des périodes** : Créer, modifier, supprimer et consulter vos périodes de budget
- **Tableau de bord** : Vue d'ensemble de votre situation financière
- **Authentification** : Système de connexion sécurisé
- **Interface moderne** : Design responsive avec Bootstrap 5
- **Validation des données** : Vérification automatique de la cohérence des dates
- **Statistiques** : Calculs automatiques des totaux et soldes

### 🔄 Fonctionnalités en développement
- Gestion des revenus et dépenses
- Système de catégories personnalisées
- Graphiques et analyses avancées
- Export des données
- Notifications et rappels

## 📋 Prérequis

- **Docker** et **Docker Compose**
- **Git**

## 🛠️ Installation

### Installation en 2 étapes simples !

1. **Cloner le projet**
```bash
git clone <votre-repo>
cd gestion-budget
```

2. **Lancer l'application**
```bash
docker-compose up
```

**C'est tout !** 🎉 L'application se configure automatiquement :
- ✅ Installation des dépendances
- ✅ Création de la base de données
- ✅ Exécution des migrations
- ✅ Configuration automatique

**Première utilisation ?** Attendez que vous voyiez le message :
```
🎉 Application prête ! Démarrage du serveur sur 0.0.0.0:8000
```

## 🌐 Accès à l'application

- **Application principale** : http://localhost:8000
- **PhpMyAdmin** : http://localhost:8080
  - Utilisateur : `budget_user`
  - Mot de passe : `budget_password`

## 📊 Architecture du projet

### Entités principales

```
USER (Utilisateur)
├── id, email, password, created_at
├── periods[] (Périodes)
└── categories[] (Catégories)

PERIOD (Période)
├── id, name, start_date, end_date
├── user (Propriétaire)
├── incomes[] (Revenus)
└── expenses[] (Dépenses)

CATEGORY (Catégorie)
├── id, name, type (INCOME/EXPENSE)
├── user (Propriétaire)
├── incomes[] (Revenus associés)
└── expenses[] (Dépenses associées)

INCOME (Revenu)
├── id, description, amount, date
├── period (Période)
└── category (Catégorie optionnelle)

EXPENSE (Dépense)
├── id, description, amount, date
├── period (Période)
└── category (Catégorie optionnelle)
```

### Structure des dossiers
```
├── src/
│   ├── Controller/          # Contrôleurs Symfony
│   ├── Entity/              # Entités Doctrine
│   ├── Repository/          # Repositories Doctrine
│   ├── Form/                # Formulaires Symfony
│   └── Enum/                # Énumérations
├── templates/               # Templates Twig
├── docker-compose.yml       # Configuration Docker
├── Dockerfile              # Image Docker personnalisée
└── init-project.sh         # Script d'installation
```

## 🔧 Utilisation

### Créer votre première période

1. **Connectez-vous** à l'application
2. **Cliquez sur "Nouvelle période"** dans le tableau de bord
3. **Remplissez le formulaire** :
   - Nom : ex. "Janvier 2024"
   - Date de début : 01/01/2024
   - Date de fin : 31/01/2024
4. **Validez** la création

### Consulter vos statistiques

- Le **tableau de bord** affiche un résumé global
- Cliquez sur une **période** pour voir les détails
- Les **totaux** sont calculés automatiquement

## 🛡️ Sécurité

- **Mots de passe hashés** avec l'algorithme bcrypt
- **Tokens CSRF** pour toutes les actions sensibles
- **Validation côté serveur** pour toutes les données
- **Isolation des données** par utilisateur

## 📱 Responsive Design

L'application est entièrement responsive et s'adapte à tous les écrans :
- **Desktop** : Interface complète avec sidebar
- **Tablet** : Navigation adaptée
- **Mobile** : Interface optimisée

## 🔄 Développement

### Développement automatisé

L'application se reconfigure automatiquement à chaque redémarrage ! 

**Commandes utiles :**
```bash
# Redémarrer l'application (applique les changements automatiquement)
docker-compose restart app

# Voir les logs en temps réel
docker-compose logs -f app

# Accéder au conteneur pour les commandes Symfony
docker-compose exec app bash
```

### Ajouter une nouvelle fonctionnalité

1. **Créer l'entité** (si nécessaire)
```bash
docker-compose exec app php bin/console make:entity
```

2. **Générer la migration**
```bash
docker-compose exec app php bin/console make:migration
# Les migrations s'appliquent automatiquement au redémarrage !
docker-compose restart app
```

3. **Créer le contrôleur**
```bash
docker-compose exec app php bin/console make:controller
```

4. **Créer le formulaire**
```bash
docker-compose exec app php bin/console make:form
```

### Tests

```bash
# Exécuter les tests
docker-compose exec app php bin/phpunit

# Tests avec couverture
docker-compose exec app php bin/phpunit --coverage-html coverage
```

## 🤝 Contribution

1. **Fork** le projet
2. **Créer une branche** feature (`git checkout -b feature/nouvelle-fonctionnalite`)
3. **Commit** vos changements (`git commit -am 'Ajout nouvelle fonctionnalité'`)
4. **Push** vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. **Ouvrir une Pull Request**

## 📝 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 👨‍💻 Développeur

Développé avec ❤️ pour la gestion de budget personnel.

---

### 📞 Support

En cas de problème, consultez la documentation ou ouvrez une issue sur GitHub. 