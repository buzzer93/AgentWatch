# AGENT.md — Consignes pour agent de code

## Rôle de l'agent

Tu es un agent de développement chargé d'aider Nicolas Rodriguez à construire une webapp de veille technologique personnelle basée sur Symfony, PostgreSQL, Docker, n8n et IA.

Tu dois produire du code propre, simple, maintenable et adapté à un développeur PHP/Symfony indépendant.

## Objectif du projet

Construire une application permettant de :

1. Collecter jusqu'à 50 flux RSS via n8n.
2. Stocker les articles dans PostgreSQL.
3. Analyser les articles avec une IA locale ou cloud.
4. Afficher un Top 5 quotidien dans une webapp Symfony.
5. Permettre à l'utilisateur de donner du feedback.
6. Utiliser ce feedback pour améliorer progressivement le scoring.
7. Préparer plus tard une veille hebdomadaire exploitable pour LinkedIn.

## Profil du développeur

Nicolas Rodriguez est développeur PHP/Symfony autodidacte.

Stack habituelle :

- PHP
- Symfony
- Doctrine ORM
- Twig
- Bootstrap ou Tailwind
- AssetMapper
- MySQL / PostgreSQL / SQLite
- Docker
- VPS Ubuntu
- Caddy
- GitHub
- n8n
- IA / agents / automatisation

L'agent doit privilégier les solutions compréhensibles, progressives et réalistes.

## Style de code attendu

Respecter les règles suivantes :

- PHP strict types.
- PSR-12.
- Type hints obligatoires.
- Retour explicite des méthodes.
- Pas de logique métier lourde dans les contrôleurs.
- Utiliser des services pour la logique métier.
- Utiliser des repositories pour les requêtes spécifiques.
- Utiliser les attributs PHP pour les routes Symfony.
- Garder les entités Doctrine claires et simples.
- Éviter les abstractions prématurées.
- Ne pas générer de migrations sans demande explicite.
- Ne pas ajouter de dépendance inutile.
- Ne pas utiliser `dd()`, `dump()`, `var_dump()`, `die()` dans le code final.

## Architecture Symfony recommandée

Structure métier suggérée :

```txt
src/
├── Controller/
│   ├── DashboardController.php
│   ├── RssSourceController.php
│   ├── RssItemController.php
│   └── FeedbackController.php
├── Entity/
│   ├── RssSource.php
│   ├── RssItem.php
│   ├── AiAnalysis.php
│   ├── UserFeedback.php
│   ├── DailySelection.php
│   └── DailySelectionItem.php
├── Enum/
│   ├── FeedbackType.php
│   ├── SourceCategory.php
│   └── ArticleCategory.php
├── Repository/
├── Service/
│   ├── Dashboard/
│   ├── Feedback/
│   ├── Scoring/
│   └── Import/
├── DTO/
└── Form/
```

## Entités principales

### RssSource

Source RSS suivie.

Doit contenir :

- name
- feedUrl
- websiteUrl
- category
- priority
- isActive
- createdAt
- updatedAt

### RssItem

Article collecté.

Doit contenir :

- source
- title
- url
- author
- publishedAt
- importedAt
- rawExcerpt
- rawContent
- language
- hash
- isProcessed
- createdAt
- updatedAt

### AiAnalysis

Analyse IA d'un article.

Doit contenir :

- rssItem
- summary
- translatedTitle
- relevanceScore
- businessScore
- learningScore
- contentScore
- finalScore
- userRelevanceScore
- userBusinessScore
- userLearningScore
- userContentScore
- userFinalScore
- userScoresUpdatedAt
- reasoning
- modelUsed
- createdAt

### UserFeedback

Feedback utilisateur.

Doit contenir :

- rssItem
- feedbackType
- comment
- createdAt

### DailySelection

Sélection quotidienne.

Doit contenir :

- selectionDate
- title
- globalSummary
- createdAt

### DailySelectionItem

Article retenu dans une sélection quotidienne.

Doit contenir :

- dailySelection
- rssItem
- rank
- selectionReason
- createdAt

## Enums recommandés

### FeedbackType

Valeurs suggérées :

- BEST_OF_DAY
- USEFUL
- NOT_USEFUL
- BUSINESS_OPPORTUNITY
- CONTENT_IDEA
- READ_LATER
- SAVED

### ArticleCategory

Valeurs suggérées :

- SYMFONY_PHP
- AI_FOR_DEVELOPERS
- AI_AGENTS
- DOCKER_DEVOPS
- N8N_AUTOMATION
- FREELANCE_BUSINESS
- SAAS_OPPORTUNITY
- SECURITY
- CRYPTO_MACRO
- OPEN_SOURCE
- OTHER

## Pages MVP

### Dashboard

Route suggérée :

```txt
/dashboard
```

Afficher :

- Top 5 du jour.
- Résumé global.
- Boutons de feedback.
- Articles récents à fort score.

### Liste des articles

Route suggérée :

```txt
/articles
```

Fonctions :

- pagination ;
- filtre par catégorie ;
- filtre par source ;
- filtre par score ;
- filtre par feedback ;
- recherche texte simple.

### Détail article

Route suggérée :

```txt
/articles/{id}
```

Afficher :

- titre ;
- source ;
- URL externe ;
- date ;
- résumé IA ;
- scores ;
- tags ;
- raisonnement IA ;
- feedbacks disponibles.

### Sources RSS

Route suggérée :

```txt
/sources
```

Fonctions :

- ajouter une source ;
- activer / désactiver ;
- modifier priorité ;
- modifier catégorie.

## Intégration n8n

n8n est responsable de :

- lire les flux RSS ;
- normaliser les articles ;
- appeler l'IA ;
- écrire en base ;
- générer la sélection quotidienne ;
- envoyer une notification mail.

Symfony est responsable de :

- afficher les données ;
- gérer les sources ;
- gérer le feedback ;
- afficher l'historique ;
- préparer plus tard les brouillons LinkedIn.

## API interne Symfony pour n8n

Prévoir plus tard des endpoints internes protégés par token.

Exemples :

```txt
POST /internal/rss-items
POST /internal/ai-analysis
POST /internal/daily-selection
```

Sécurité minimale :

- token API dans header ;
- variable d'environnement ;
- pas d'accès public sans authentification.

## Prompts IA

Les prompts doivent être stockés dans :

```txt
n8n/prompts/
```

Prompts recommandés :

```txt
article-analysis.prompt.md
daily-selection.prompt.md
weekly-linkedin.prompt.md
feedback-learning.prompt.md
```

## Règles de scoring

L'IA doit favoriser les contenus qui :

- aident Nicolas à progresser en Symfony/PHP ;
- améliorent ses compétences Docker/VPS/DevOps ;
- parlent d'IA appliquée au développement ;
- parlent d'agents IA, MCP, n8n ou automatisation ;
- peuvent devenir un service client ;
- peuvent devenir un post LinkedIn ;
- sont actionnables rapidement ;
- aident à détecter une tendance.

L'IA doit défavoriser les contenus :

- trop généralistes ;
- trop marketing ;
- trop orientés gadget ;
- non actionnables ;
- sans lien avec le profil de Nicolas.

## Workflow recommandé pour l'agent

Toujours travailler par petites étapes.

Avant de coder une grosse fonctionnalité :

1. Lire README.md.
2. Lire TODO.md.
3. Identifier la phase en cours.
4. Proposer une implémentation simple.
5. Modifier uniquement ce qui est nécessaire.
6. Mettre à jour TODO, README et la documentation si besoin.

## Priorités

Priorité 1 :

- projet fonctionnel ;
- architecture simple ;
- code lisible ;
- feedback utilisable.

Priorité 2 :

- automatisation n8n ;
- scoring IA ;
- dashboard propre.

Priorité 3 :

- génération LinkedIn ;
- mémoire long terme ;
- recherche sémantique ;
- Qdrant ou embeddings.

## Ce qu'il ne faut pas faire

Ne pas ajouter au début :

- Kubernetes ;
- RabbitMQ ;
- Kafka ;
- microservices inutiles ;
- Qdrant avant validation du MVP ;
- système multi-agent complexe ;
- authentification trop avancée ;
- surcouche DDD lourde ;
- dashboard trop complexe ;
- fonctionnalités LinkedIn avant d'avoir un Top 5 fiable.

## Définition de terminé pour le MVP

Le MVP est terminé quand :

1. Les sources RSS peuvent être configurées.
2. Les articles sont importés automatiquement.
3. Les articles sont analysés par IA.
4. Le Top 5 quotidien est visible dans Symfony.
5. Nicolas peut donner un feedback.
6. Le feedback est sauvegardé.
7. Une notification mail peut pointer vers le dashboard.

## Ton attendu

L'agent doit expliquer ses choix brièvement, puis fournir du code directement exploitable.

Les réponses doivent être pratiques, orientées implémentation, et éviter les grands discours théoriques.
