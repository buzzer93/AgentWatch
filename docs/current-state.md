# État actuel du projet

Date de mise à jour : 26 mai 2026.

## Ce qui a été implémenté

### Infrastructure & outillage

- bootstrap Symfony 8 sur PHP 8.4 ;
- stack Docker locale avec PostgreSQL, **n8n 2.22.3** (bumpé depuis 1.95.3), **Ollama 0.9 avec modèle `qwen3:4b`** (2.5 GB) et Mailpit ;
- variables d'environnement de départ via [.env.example](../.env.example) ;
- scripts PowerShell d'environnement : [scripts/install.ps1](../scripts/install.ps1) et [scripts/update.ps1](../scripts/update.ps1) ;
- `schema_filter` Doctrine dans [config/packages/doctrine.yaml](../config/packages/doctrine.yaml) pour ignorer les tables n8n partageant la base PostgreSQL.

### Modèle de données

- entités MVP : `RssSource`, `RssItem`, `AiAnalysis`, `UserFeedback`, `DailySelection`, `DailySelectionItem` ;
- entité `Tag` partagée entre `RssSource` (manuel) et `RssItem` (IA) en ManyToMany ;
- `AiAnalysis` enrichie de :
  - `translatedTitle` (titre traduit en français par l'IA) ;
  - 5 colonnes `userXxxScore` + `userScoresUpdatedAt` pour les corrections manuelles ;
- repositories spécialisés (`findLatest`, `findLatestUnprocessed`, `findRecentTopScored`, `findAllOrderedByPriority`, `findOneBySlug`...) ;
- contraintes de validation (NotBlank, Url, Length, Range, Regex slug, UniqueEntity) ;
- migrations appliquées :
  - `Version20260526101708` — schéma initial MVP ;
  - `Version20260526111349` — entité `Tag`, jointure `rss_source_tag` ;
  - `Version20260526184243` — jointure `rss_item_tag`, suppression colonne JSON `tags` ;
  - `Version20260526202724` — colonnes `translated_title`, `user_xxx_score` × 5, `user_scores_updated_at`.

### Interface webapp

- layout Twig de base avec Tailwind via CDN (design tokens : `sand`, `ink`, `ember`, `moss`) ;
- dashboard sur `/` et `/dashboard` :
  - liste des **articles analysés par l'IA** (pas les rss_item bruts), triée par score final décroissant ;
  - **titres traduits en français** (fallback titre original si pas de traduction) ;
  - badge "corrigé" sur les articles dont les scores ont été ajustés manuellement ;
  - score final effectif (user si présent, IA sinon) ;
- page détail `/articles/{id}` :
  - titre traduit en gros, titre original en italique petit ;
  - analyse IA complète : résumé, tags, reasoning, modèle utilisé ;
  - **5 sliders HTML5 (0-100)** pour corriger Pertinence / Business / Apprentissage / Contenu / Score final ;
  - affichage côte à côte des scores IA originaux et des valeurs courantes ;
  - bouton "Réinitialiser aux scores IA" si déjà corrigé (avec confirm JS) ;
- **CRUD sources RSS** complet sur `/sources` (index, new, edit, toggle, delete) ;
- **CRUD tags** complet sur `/tags` (génération auto du slug, validation regex) ;
- formulaire des sources avec multi-select Tags en **autocomplete TomSelect** ;
- form theme Tailwind dédié dans [templates/rss_source/\_form_theme.html.twig](../templates/rss_source/_form_theme.html.twig).

### API interne pour n8n

- garde de token via `InternalApiTokenGuard` lisant `APP_INTERNAL_TOKEN` ;
- routes :
  - `GET /internal/rss-sources` — sources actives ;
  - `GET /internal/rss-items/unprocessed?limit=N` — articles à analyser ;
  - `GET /internal/tags` — vocabulaire fermé pour le prompt IA ;
  - `POST /internal/rss-items` — création d'article (dédup double) ;
  - `POST /internal/ai-analysis` — analyse IA (résume, tags, scores, titre traduit) ;
  - `POST /internal/daily-selection` — Top 5 quotidien ;
- `RssItemImporter` avec déduplication double :
  - par URL (UniqueConstraint en DB + check applicatif) ;
  - par hash `sha256(title | sourceId | publishedAt[Y-m-d])`, fallback `'no-date'` si publication date manquante.
- `AiAnalysisImporter` :
  - résout les tags slugs en entités `Tag`, attache au `RssItem` ;
  - log les slugs inconnus retournés par l'IA (tolérant) ;
  - persiste `translatedTitle` ;
  - marque l'article `isProcessed = true`.

### Workflows n8n

#### `RSS Collect` ([rss-collect.workflow.json](../n8n/workflows/rss-collect.workflow.json))

- trigger horaire → fetch sources → boucle Read RSS par source → Normalize → **Filter 24h** (nœud Filter natif) → Push ;
- nœud Push configuré en mode `bodyParameters` (keypair) pour fiabilité de la sérialisation JSON ;
- **Retry on Fail** : 5 essais avec 2s d'attente pour absorber les bursts qui saturent `symfony serve` mono-worker.

#### `Article Analysis` ([article-analysis.workflow.json](../n8n/workflows/article-analysis.workflow.json))

- trigger toutes les 30 min → fetch tags + items unprocessed → expansion ;
- **Chain-of-prompts en 3 étapes** :
  1. Summary : `summary` (FR), `translatedTitle` (FR) ;
  2. Tags : `tags` (array de 2-6 slugs picked dans `allowedTags`) ;
  3. Scoring : 5 scores 0-100 + reasoning (FR) ;
- modèle Ollama local `qwen3:4b` avec `think: false` (qwen3 default thinking désactivé) ;
- `format: 'json'` côté Ollama pour forcer un JSON valide ;
- `num_predict` ajusté par étape (500 / 200 / 400) ;
- timeout HTTP 600s par appel, retry 3x ;
- Push final avec retry 5x.

### Fixtures (dev)

- `TagFixtures` — 87 tags pré-définis (Symfony/PHP, IA/LLM/Agents, Infra/DevOps, Business, Architecture, Sécurité, méta-tags) ;
- `RssSourceFixtures` — 16 sources RSS (10 validées le 2026-05-26 + 6 ajoutées ensuite), avec leurs tags associés et priorités initiales ;
- 11 URLs cassées documentées en commentaire et 4 sources retirées par expérience (InfoQ Architecture pour 406 n8n, Hacker News pour bruit, SaaStr pour superficialité, GitHub Engineering pour long-form trop théorique).

## Décisions techniques notables

- **Symfony reste exécuté en local** pendant le développement (`symfony serve`), seuls l'infra et n8n sont en Docker. Le saut vers Symfony dans Docker est reporté à la phase de déploiement VPS.
- **Tags partagés** entre sources (manuel) et articles (IA) plutôt que deux entités séparées : permet le filtrage transversal et évite la divergence de nommage.
- **Vocabulaire fermé pour les tags IA** : le prompt reçoit la liste des 87 tags existants, l'IA pioche dedans, les slugs inconnus sont loggés et ignorés.
- **Chain-of-prompts plutôt que monolithique** pour l'analyse IA : qualité bien meilleure sur petit modèle (qwen3:4b).
- **Corrections manuelles persistées séparément** des scores IA : garde l'historique pour la future phase d'apprentissage (Phase 11).
- **`N8N_BLOCK_ENV_ACCESS_IN_NODE=false`** dans `compose.yaml` pour autoriser `{{$env.APP_INTERNAL_TOKEN}}` dans les nœuds n8n (changement de default en n8n 2.0).
- **CASCADE ON DELETE** sur `rss_item.source_id → rss_source.id`, `ai_analysis.rss_item_id → rss_item.id`, `rss_item_tag.*` → la suppression d'une source nettoie tout proprement.

## Vérifications déjà passées

- migrations Doctrine s'appliquent sans toucher aux tables n8n ;
- routes CRUD et article enregistrées (`debug:router` confirme `app_rss_source_*`, `app_tag_*`, `app_article_*`) ;
- `cache:clear` réussit après chaque modification d'entité ou de form ;
- workflow `RSS Collect` exécute le cycle complet bout en bout (sources → items dédupliqués en base) ;
- workflow `Article Analysis` 3 étapes validé manuellement sur articles réels ;
- test `InternalApiTokenGuardTest` passe (3 tests).

## Ce qui reste immédiatement à faire

- workflow de **sélection quotidienne Top 5** (Phase 9) ;
- **système de feedback utilisateur** sur les articles (Phase 8) ;
- liste paginée `/articles` avec filtres (catégorie, source, score, tag) ;
- `continue on fail` sur le nœud Read RSS pour ne pas casser tout le run en cas de feed indisponible ;
- logging des imports (table dédiée ou fichier).

## Hypothèses de travail retenues

- Symfony reste exécuté en local pendant le développement ;
- les services externes tournent dans Docker ;
- l'authentification interne n8n passe par un token simple en header ;
- la base PostgreSQL est partagée entre Symfony et n8n, isolation via `schema_filter` côté Doctrine ;
- les fixtures sont rechargeables à volonté (purge automatique) tant qu'on est en dev ;
- analyse IA en local avec qwen3:4b ; passage cloud (OpenAI/Anthropic) prévu pour la sélection quotidienne Top 5 (tâche critique).
