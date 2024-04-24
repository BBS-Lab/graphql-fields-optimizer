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

## Conclusion
Le trait `GraphQLFieldsOptimizer` offre une solution efficace pour optimiser les requêtes GraphQL en ne chargeant que les données nécessaires, améliorant ainsi significativement les performances de vos applications Laravel. Testez toutes les configurations dans un environnement de développement avant de les déployer en production pour garantir la meilleure expérience utilisateur.