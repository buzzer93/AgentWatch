# RSS AI Watch — Veille tech personnelle avec n8n, IA et Symfony

## Objectif du projet

Ce projet a pour objectif de créer une petite application de veille technologique personnelle.

L'idée est de collecter jusqu'à 50 flux RSS ciblés, de les analyser automatiquement avec un workflow n8n et une IA, puis d'afficher les meilleurs sujets dans une webapp Symfony.

Le système doit aider Nicolas Rodriguez à :

- apprendre plus efficacement ;
- gagner du temps dans sa veille ;
- détecter des opportunités business ;
- préparer plus tard du contenu LinkedIn ;
- construire une base de connaissance personnelle autour de Symfony, Docker, IA, agents, automatisation, business freelance et tech.

## Vision globale

Le système repose sur cette logique :

```txt
Flux RSS
↓
n8n collecte les articles
↓
n8n normalise et pré-filtre
↓
IA locale ou hybride analyse les contenus
↓
PostgreSQL stocke les articles, scores et analyses
↓
Symfony affiche le Top 5 et l'historique
↓
Nicolas donne du feedback
↓
Le système améliore progressivement son scoring
```

## Stack technique cible

### Backend webapp

- PHP 8.3 ou 8.4
- Symfony 7.4 ou Symfony 8 selon contraintes PHP
- Doctrine ORM
- Twig
- Tailwind CSS ou Bootstrap
- PostgreSQL

### Automatisation

- n8n
- Workflows séparés :
  - collecte RSS ;
  - enrichissement IA ;
  - sélection quotidienne ;
  - feedback ;
  - préparation LinkedIn plus tard.

### IA

Approche recommandée : hybride.

- Ollama en local pour les tâches simples :
  - résumé court ;
  - extraction de tags ;
  - pré-classification.
- OpenAI ou Anthropic pour les tâches critiques :
  - sélection finale ;
  - scoring personnalisé ;
  - génération de synthèses de qualité ;
  - futur contenu LinkedIn.

### Infrastructure

- VPS Ubuntu
- Docker Compose
- Caddy en reverse proxy HTTPS
- PostgreSQL
- n8n
- Webapp Symfony
- Ollama optionnel au début

## Objectif MVP

Le MVP ne doit pas chercher à tout faire.

Objectif du MVP :

1. Ajouter une liste de flux RSS.
2. Collecter les articles automatiquement.
3. Stocker les articles dans PostgreSQL.
4. Analyser chaque article avec une IA.
5. Calculer un score de pertinence personnalisé.
6. Afficher chaque jour un Top 5 dans une webapp Symfony.
7. Permettre à Nicolas de voter :
   - meilleur sujet ;
   - pertinent ;
   - pas utile ;
   - opportunité business ;
   - idée de contenu.
8. Stocker les feedbacks pour améliorer les prochains scores.

## Fonctionnalités principales

### Gestion des sources RSS

- Ajouter une source RSS.
- Activer / désactiver une source.
- Catégoriser une source.
- Définir un niveau de confiance ou priorité.
- Voir les derniers articles importés par source.

### Collecte des articles

- Récupération automatique via n8n.
- Déduplication par URL.
- Normalisation :
  - titre ;
  - URL ;
  - source ;
  - date de publication ;
  - extrait ;
  - contenu si disponible ;
  - langue ;
  - date d'import.

### Analyse IA

Chaque article doit recevoir :

- un résumé court ;
- des tags ;
- une catégorie principale ;
- un score de pertinence ;
- un score business ;
- un score apprentissage ;
- un score contenu LinkedIn ;
- une raison courte expliquant pourquoi l'article est ou non intéressant.

### Dashboard Symfony

La webapp doit afficher :

- le Top 5 du jour ;
- les articles récents ;
- les articles non lus ;
- les articles sauvegardés ;
- les sujets ayant reçu un feedback positif ;
- les sources les plus pertinentes.

### Feedback utilisateur

Chaque article peut recevoir :

- meilleur sujet du jour ;
- utile ;
- pas utile ;
- opportunité business ;
- idée de contenu ;
- à lire plus tard ;
- sauvegardé.

Ce feedback doit être exploitable par n8n et par les prompts IA.

### Digest mail

Le mail n'est plus la sortie principale, mais reste utile comme notification.

Exemple :

```txt
Ton Top 5 de veille est prêt.
Voir le dashboard :
https://veille.example.com/dashboard
```

## Structure de projet suggérée

```txt
rss-ai-watch/
├── README.md
├── AGENT.md
├── TODO.md
├── docker-compose.yml
├── .env.example
├── app/
│   └── symfony/
├── docker/
│   ├── caddy/
│   ├── php/
│   ├── postgres/
│   └── n8n/
├── n8n/
│   ├── workflows/
│   └── prompts/
├── docs/
│   ├── architecture.md
│   ├── database.md
│   ├── prompts.md
│   └── deployment.md
└── backups/
```

## Modèle de données initial

### rss_source

Représente une source RSS.

Champs suggérés :

- id
- name
- feed_url
- website_url
- category
- priority
- is_active
- created_at
- updated_at

### rss_item

Représente un article collecté.

Champs suggérés :

- id
- source_id
- title
- url
- author
- published_at
- imported_at
- raw_excerpt
- raw_content
- language
- hash
- is_processed
- created_at
- updated_at

### ai_analysis

Stocke l'analyse IA d'un article.

Champs suggérés :

- id
- rss_item_id
- summary
- main_category
- tags
- relevance_score
- business_score
- learning_score
- content_score
- final_score
- reasoning
- model_used
- created_at

### user_feedback

Stocke les retours utilisateur.

Champs suggérés :

- id
- rss_item_id
- feedback_type
- comment
- created_at

### daily_selection

Stocke le Top 5 quotidien.

Champs suggérés :

- id
- selection_date
- title
- global_summary
- created_at

### daily_selection_item

Lie une sélection quotidienne aux articles retenus.

Champs suggérés :

- id
- daily_selection_id
- rss_item_id
- rank
- selection_reason
- created_at

## Catégories de veille recommandées

- Symfony / PHP
- IA pour développeurs
- Agents IA / MCP / LLM tooling
- Docker / DevOps / VPS
- n8n / automatisation
- Business freelance
- SaaS / opportunités business
- Cybersécurité utile
- Crypto / macro tech, optionnel
- GitHub / open source

## Critères de scoring personnalisés

Un article est intéressant s'il répond à au moins un de ces critères :

- il peut améliorer la pratique de Nicolas comme développeur Symfony ;
- il concerne Docker, VPS, déploiement, CI/CD ou self-hosting ;
- il concerne l'IA appliquée au développement ;
- il parle d'agents IA, MCP, workflows ou automatisation ;
- il peut générer une idée de service ou d'offre client ;
- il peut devenir un post LinkedIn ;
- il aide à comprendre une tendance importante ;
- il contient une information actionnable.

Un article est moins intéressant s'il est :

- trop généraliste ;
- purement marketing ;
- orienté gadget grand public ;
- trop théorique sans application pratique ;
- trop éloigné de Symfony, web, IA, infra ou business.

## Roadmap rapide

### Phase 1

- Installer l'infrastructure Docker.
- Déployer n8n + PostgreSQL + Symfony.
- Créer les tables principales.
- Importer les premières sources RSS.
- Créer un workflow n8n RSS vers PostgreSQL.

### Phase 2

- Ajouter l'analyse IA.
- Ajouter scoring + tags + résumé.
- Créer le dashboard Symfony.
- Afficher le Top 5.

### Phase 3

- Ajouter feedback utilisateur.
- Réinjecter les feedbacks dans les prompts.
- Ajouter une notification mail quotidienne.

### Phase 4

- Préparer un résumé hebdomadaire.
- Générer des idées de posts LinkedIn.
- Ajouter un espace de brouillons de contenu.

## Priorité projet

Le projet doit rester simple, maintenable et utile.

Ne pas surarchitecturer au départ.

Le but est d'avoir rapidement une boucle fonctionnelle :

```txt
Collecter → Analyser → Afficher → Choisir → Apprendre
```
