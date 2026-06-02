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

## Décisions retenues

- Nom du projet : AgentWatch
- Domaine cible : agentwatch.nicolas-rodriguez.fr
- Version applicative : PHP 8.4 avec Symfony 8
- UI MVP : Tailwind, intégré rapidement dans Twig
- IA locale initiale : Ollama avec un modèle qwen
- Mode de développement retenu : Symfony lancé en local, services d'infrastructure en Docker

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

## Démarrage local actuel

Le projet démarre avec Symfony exécuté localement sur Windows et les services suivants en Docker :

- PostgreSQL
- n8n
- Ollama
- Mailpit

Variables d'environnement de départ : voir .env.example.

Le header `X-Internal-Token` des routes `/internal/*` doit correspondre a `APP_INTERNAL_TOKEN`.

Ports locaux utilisés :

- PostgreSQL : 5432
- n8n : 5678
- Ollama : 11434
- Mailpit SMTP : 1025
- Mailpit UI : 8025

## État actuel du bootstrap

Ce qui est en place dans le dépôt :

### Infrastructure & outillage

- stack Docker locale (PostgreSQL, **n8n 2.22.3**, **Ollama avec `qwen3:4b`**, Mailpit) ;
- fichier [.env.example](.env.example) pour cadrer les variables de démarrage ;
- scripts d'environnement Windows / PowerShell : [scripts/install.ps1](scripts/install.ps1) et [scripts/update.ps1](scripts/update.ps1) ;
- `schema_filter` Doctrine pour ignorer les tables n8n partageant la même base PostgreSQL.

### Modèle de données

- entités MVP : `RssSource`, `RssItem`, `AiAnalysis`, `UserFeedback`, `DailySelection`, `DailySelectionItem`, **`Tag`** ;
- **double tagging** : tags de source (manuel) **+** tags d'article (générés par l'IA, vocabulaire fermé) ;
- 4 migrations Doctrine appliquées (`Version20260526101708`, `Version20260526111349`, `Version20260526184243`, `Version20260526202724`) ;
- fixtures de démarrage : 87 tags + 16 sources RSS (`doctrine:fixtures:load`).

### Webapp

- dashboard `/` ou `/dashboard` : top 10 articles analysés par l'IA, classés par score final, **titre traduit en français**, badge "corrigé" si scores ajustés manuellement ;
- page article détail `/articles/{id}` : analyse IA complète + **5 sliders HTML5 pour corriger les scores** + bouton reset ;
- CRUD complet pour les sources (`/sources`) et les tags (`/tags`) avec validation, CSRF, flash messages ;
- formulaire des sources avec multi-select Tags en **autocomplete TomSelect** (via `symfony/ux-autocomplete`).

### API interne n8n

- `GET /internal/rss-sources` — sources actives pour la collecte ;
- `GET /internal/rss-items/unprocessed` — articles à analyser ;
- `GET /internal/tags` — vocabulaire fermé pour le prompt IA ;
- `POST /internal/rss-items` — création d'article (dédup par URL et par hash `title|source|publishedAt`) ;
- `POST /internal/ai-analysis` — analyse IA d'un article (résume, tags, scores, titre traduit) ;
- `POST /internal/daily-selection` — Top 5 quotidien ;
- protection par header `X-Internal-Token`.

### Workflows n8n

- **`RSS Collect`** : trigger horaire → fetch sources → Read RSS → Normalize → Filter 24h → Push avec retry on fail ;
- **`Article Analysis`** : **chain-of-prompts en 3 étapes** (Summary+Catégorie+Titre traduit / Tags / Scoring+Reasoning) avec qwen3:4b en local, retry on fail.

Ce qui n'est pas encore fait :

- sélection quotidienne Top 5 (Phase 9) ;
- système de feedback utilisateur (Phase 8) ;
- Caddy et exposition du domaine cible ;
- gestion `continue on fail` sur les flux RSS indisponibles ;
- liste paginée `/articles` avec filtres.

Un état plus détaillé est disponible dans [docs/current-state.md](docs/current-state.md).

## Lancer le projet en local

### Méthode rapide (recommandée)

Le script [scripts/install.ps1](scripts/install.ps1) fait tout en un coup :

```powershell
.\scripts\install.ps1
```

Il vérifie Docker, démarre les conteneurs, attend PostgreSQL, applique les migrations Doctrine, et affiche les URLs utiles. Lance ensuite Symfony :

```powershell
symfony serve
```

Et ouvre :

- Dashboard : <http://127.0.0.1:8000/dashboard>
- Détail d'un article : <http://127.0.0.1:8000/articles/{id}>
- CRUD sources : <http://127.0.0.1:8000/sources>
- CRUD tags : <http://127.0.0.1:8000/tags>
- n8n : <http://127.0.0.1:5678>
- Mailpit : <http://127.0.0.1:8025>

Pour utiliser l'analyse IA en local, télécharge d'abord le modèle Ollama :

```powershell
docker compose -f compose.yaml -f compose.override.yaml exec ollama ollama pull qwen3:4b
```

### Méthode manuelle (étape par étape)

1. Vérifier que Docker Desktop est lancé.

2. Démarrer les services :

   ```powershell
   docker compose -f compose.yaml -f compose.override.yaml up -d
   ```

3. Vérifier l'état des conteneurs :

   ```powershell
   docker compose -f compose.yaml -f compose.override.yaml ps
   ```

4. Copier `.env.example` en `.env.local` et adapter les secrets si besoin.

5. Appliquer les migrations Doctrine :

   ```powershell
   php bin/console doctrine:migrations:migrate --no-interaction
   ```

6. (Optionnel) Charger les fixtures de démarrage (87 tags + 16 sources) :

   ```powershell
   php bin/console doctrine:fixtures:load --no-interaction
   ```

7. Lancer Symfony en local :

   ```powershell
   symfony serve
   ```

## Mettre à jour les conteneurs

Le script [scripts/update.ps1](scripts/update.ps1) fait tout en un coup : pull, recréation, migrations.

```powershell
.\scripts\update.ps1
```

### En manuel

1. Pull les dernières images :

   ```powershell
   docker compose -f compose.yaml -f compose.override.yaml pull
   ```

2. Recréer les conteneurs :

   ```powershell
   docker compose -f compose.yaml -f compose.override.yaml up -d --remove-orphans
   ```

3. Appliquer les éventuelles nouvelles migrations :

   ```powershell
   php bin/console doctrine:migrations:migrate --no-interaction
   ```

4. En cas de souci, redémarrer proprement :

   ```powershell
   docker compose -f compose.yaml -f compose.override.yaml down
   docker compose -f compose.yaml -f compose.override.yaml up -d
   ```

## Configuration Doctrine particulière

PostgreSQL est partagé avec n8n (qui stocke ses workflows, executions et metadata dans la même base). Pour empêcher Doctrine de tenter de supprimer les tables n8n lors d'un `migrations:diff`, le fichier [config/packages/doctrine.yaml](config/packages/doctrine.yaml) déclare un `schema_filter` qui whitelist uniquement les tables AgentWatch.

À chaque nouvelle entité Symfony, **n'oublie pas d'ajouter sa table au `schema_filter`**, sinon les `migrations:diff` ne la verront pas.

## Workflow n8n RSS Collect

Le workflow [n8n/workflows/rss-collect.workflow.json](n8n/workflows/rss-collect.workflow.json) fait :

1. Trigger toutes les heures
2. GET `/internal/rss-sources` avec le token X-Internal-Token
3. Pour chaque source : Read RSS → Normalize Items → Filter 24h → Push vers `/internal/rss-items`
4. Retry 5x sur le Push pour absorber les bursts qui saturent `symfony serve` mono-worker

## Workflow n8n Article Analysis

Le workflow [n8n/workflows/article-analysis.workflow.json](n8n/workflows/article-analysis.workflow.json) fait l'analyse IA en **3 étapes successives** (chain-of-prompts) pour maximiser la qualité sur le petit modèle local qwen3:4b :

1. Trigger toutes les 30 minutes
2. GET `/internal/tags` et `/internal/rss-items/unprocessed?limit=N`
3. **Étape 1** : Build Summary Prompt → Call Ollama → Parse → obtient `translatedTitle`, `summary`
4. **Étape 2** : Build Tags Prompt (avec summary + allowedTags) → Call Ollama → Parse → obtient `tags`
5. **Étape 3** : Build Scoring Prompt (avec summary + tags + profil Nicolas) → Call Ollama → Parse + Merge → obtient les 5 scores et reasoning
6. POST `/internal/ai-analysis` qui persiste l'analyse et crée la jointure `rss_item_tag`

**Pourquoi 3 étapes** : sur petit modèle (4B paramètres), demander 9 champs d'un coup donne des résultats incohérents (classification erronée, JSON tronqué). Une tâche atomique par appel = qualité bien meilleure.

Pour importer un workflow : dans n8n, Workflows → menu `...` → Import from File → sélectionner le fichier JSON correspondant.

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
- translated_title
- relevance_score
- business_score
- learning_score
- content_score
- final_score
- user_relevance_score
- user_business_score
- user_learning_score
- user_content_score
- user_final_score
- user_scores_updated_at
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
