# TW3 - Documentation technique

## Mise en route

L'application a besoin d'une version de PHP >= 8, de composer et npm.
La base de données doit être sous MySQL.
***

## Installer les dépendances du projet :

Le fichier .env.local n'est pas inclus dans le dépôt GitHub pour des raisons de sécurité.
Assurez-vous de configurer vos variables d'environnement. <br>
Pensez également à configurer votre serveur SMTP dans le fichier.

- Les commandes suivante sont à exécuter depuis un terminal à la racine du projet :
```
composer install
php bin/console importmap:install
```

- Pour charger le css :
Commande pour générer un fichier css en fonction du tailwinds utilisé dans les fichiers twig :
```
.\bin\tailwindcss.exe -i .\assets\styles\app.css -o .\assets\styles\app.tailwind.css -W
```

# ⚠️ Pour la production uniquement ! 
Pour minimiser le css pour la prod on utilise :
```
.\bin\tailwindcss.exe -i .\assets\styles\app.css -o .\assets\styles\app.tailwind.css -m
```

```
symfony console asset-map:compile
```

***

# Démarrer le serveur web

Pour lancer le serveur web, veuillez exécuter les commandes suivantes :

```
php bin/console assets:install
symfony serve -d
```

# Importation de la base de données MySQL
Pour importer la base de données gérée par Doctrine, suivez les étapes ci-dessous.

## Création de la base de données
Créez une nouvelle base de données nommée `anr` en exécutant la commande suivante.

```
php bin/console doctrine:database:create
```

## Exécution des commandes d'importation
Exécutez ensuite les commandes suivantes pour importer les tables :

```
php bin/console make:migration
php bin/console d:m:m
```

- Importer les statistiques de la base de données Notion 
```
php bin/console app:update-nb-users-registred
```

## ⚠️ Important 
L'application utilise des classes tailwinds css. Si vous souhaitez modifier le front merci d'utiliser les classes tailwind et uniquement les classes tailwind !!

- Commande à exécuter dans un terminal à la racine du projet pour générer un fichier css en fonction du tailwinds utilisé dans les fichiers twig :
`.\bin\tailwindcss.exe -i .\assets\styles\app.css -o .\assets\styles\app.tailwind.css -W`
