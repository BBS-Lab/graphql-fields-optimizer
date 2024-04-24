# Documentation pour GraphQLFieldsOptimizer 🚀

## Introduction
Le trait `GraphQLFieldsOptimizer` est conçu spécifiquement pour relever ce défi en améliorant les performances des requêtes GraphQL dans une application Laravel. Il optimise la sélection des champs et des relations en fonction des besoins réels de la requête, s'assurant que seule la data nécessaire est extraite de la base de données.

## Installation
Intégrez ce trait dans votre projet Laravel en suivant ces étapes :

1. Assurez-vous que votre projet utilise Laravel et GraphQL Lighthouse.
2. Placez le fichier du trait `GraphQLFieldsOptimizer` dans le répertoire `app/Traits` de votre application Laravel.

## Utilisation 

Incorporez le trait `GraphQLFieldsOptimizer` dans vos modèles Eloquent qui sont utilisés comme types GraphQL. Par exemple, pour un modèle `User` :

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use BBSLab\GraphqlFieldsOptimizer\Traits\GraphQLFieldsOptimizer;

class User extends Model
{
    use GraphQLFieldsOptimizer;
}
```

## Exemple de Requête GraphQL
Utilisez la méthode `scopeWithGraphQLFields` dans vos resolveurs GraphQL pour optimiser les requêtes :

```graphql
query {
  users {
    id
    name
    posts {
      id
      title
    }
  }
}
```
```php
use App\Models\User;

public function resolveUsers($root, $args, $context, ResolveInfo $resolveInfo)
{
    return User::withGraphQLFields($resolveInfo)->get();
}
```

## Fonctionnalités Avancées

### Personnalisation des Clés Étrangères

Personnalisez les clés étrangères pour chaque relation en modifiant la méthode `getForeignKeyForRelation` dans vos modèles si nécessaire :

```php
protected function getForeignKeyForRelation(string $relation): string
{
    if ($relation === 'posts') {
        return 'author_id'; // Clé étrangère personnalisée
    }
    return parent::getForeignKeyForRelation($relation);
}
```

### Validation des Noms de Champs
La validation des noms de champs est contrôlée par la configuration pour prévenir les injections SQL et autres vulnérabilités.

Activez la validation des noms de champs pour renforcer la sécurité en ajoutant la configuration suivante dans votre fichier `config/graphql-fields-optimizer.php` :

```php
return [
    'enable_security' => true
];
```

## Tests de performance
Pour chaque requête, nous avons simulé 50 utilisateurs effectuant 8791 requêtes chacun pendant une durée de 4 minutes. Les requêtes ont été envoyées à une application locale hébergeant notre serveur GraphQL.

| Test | Description | Temps de Réponse Moyen (ms) | Taux de Succès |
|------|-------------|-----------------------------|----------------|
| Sans le package | Requête GraphQL standard sans utilisation du package | 29.32                       | 100% |
| Avec le package | Requête GraphQL optimisée avec utilisation du package | 26.88                       | 100% |

## Informations importantes

Pour améliorer les performances de votre application, il est crucial de considérer un ensemble d'éléments qui vont au-delà de l'utilisation de notre package. Voici quelques pratiques recommandées :

### Utilisation d'index :
Nous pouvons ajouter des index sur les colonnes fréquemment utilisées dans les clauses WHERE ou JOIN pour accélérer la recherche et le tri des données.

### Utilisation de clés étrangères :
L'utilisation de clés étrangères permet d'optimiser les jointures entre les tables, ce qui peut réduire le temps d'exécution des requêtes en évitant les balayages complets de table.

### Optimisation des requêtes :
Nous pouvons examiner et ajuster les requêtes SQL pour qu'elles soient plus efficaces, en utilisant des clauses telles que WHERE, GROUP BY, HAVING et ORDER BY de manière appropriée.

### Cache de requêtes :
Pour les requêtes fréquemment exécutées avec des résultats statiques, nous pouvons utiliser des systèmes de mise en cache tels que Redis ou Memcached pour éviter de réexécuter les mêmes requêtes à chaque fois.

### Optimisation du schéma de base de données :
Une conception de base de données bien pensée, avec des relations appropriées et une normalisation adéquate, peut également contribuer à des performances optimales.

En combinant ces pratiques avec l'utilisation de notre package, vous pourrez maximiser les performances de votre application et offrir une expérience utilisateur fluide et réactive.

## Conclusion
Le trait `GraphQLFieldsOptimizer` offre une solution efficace pour optimiser les requêtes GraphQL en ne chargeant que les données nécessaires, améliorant ainsi significativement les performances de vos applications Laravel. Testez toutes les configurations dans un environnement de développement avant de les déployer en production pour garantir la meilleure expérience utilisateur.