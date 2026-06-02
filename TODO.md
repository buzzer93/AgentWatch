# TODO.md — Roadmap RSS AI Watch

## Objectif

Construire progressivement une webapp Symfony connectée à n8n pour collecter, analyser, scorer et trier des flux RSS tech avec IA.

La priorité est de livrer rapidement une version utile, puis d'ajouter les briques avancées.

---

## Phase 0 — Préparation projet

## Décisions à prendre

- [X] Choisir le nom final du projet    "AgentWatch"
- [X] Choisir le domaine ou sous-domaine. "agentwatch.nicolas-rodriguez.fr"
- [X] Choisir la version PHP. PHP 8.4 si Symfony 8.
- [X] Choisir le framework CSS. Tailwind pour une UI plus moderne.
- [X] Choisir le mode IA initial. Ollama avec le model qwen.

## Fichiers initiaux

- [X] Créer `README.md`.
- [X] Créer `AGENT.md`.
- [X] Créer `TODO.md`.
- [X] Créer `.env.example`.
- [X] Créer `compose.yaml` et `compose.override.yaml`.
- [X] Créer un dossier `docs/`.
- [X] Créer un dossier `n8n/workflows/`.
- [X] Créer un dossier `n8n/prompts/`.

---

## Phase 1 — Infrastructure Docker

## Docker Compose

Services à prévoir :

- [ ] Caddy.
- [ ] PHP/Symfony.
- [X] PostgreSQL.
- [X] n8n.
- [X] Ollama, optionnel au début.

## Réseau

- [X] Créer un réseau Docker commun.
- [ ] Vérifier que Symfony peut accéder à PostgreSQL.
- [X] Vérifier que n8n peut accéder à PostgreSQL.
- [ ] Vérifier que Caddy peut exposer Symfony.
- [ ] Vérifier que Caddy peut exposer n8n si besoin.

## Variables d'environnement

- [X] Configurer `DATABASE_URL`.
- [X] Configurer les accès PostgreSQL.
- [X] Configurer les variables n8n.
- [X] Configurer le token interne API.
- [ ] Configurer les clés IA si cloud.
- [X] Configurer les variables mail.

## Caddy

- [ ] Créer un Caddyfile.
- [ ] Exposer la webapp Symfony.
- [ ] Exposer n8n sur un sous-domaine protégé.
- [ ] Vérifier HTTPS.
- [ ] Vérifier les redirections HTTP vers HTTPS.

---

## Phase 2 — Création application Symfony

## Installation

- [X] Créer le projet Symfony.
- [X] Installer Doctrine ORM.
- [X] Installer Maker Bundle en dev.
- [X] Installer Twig.
- [X] Installer les assets.
- [X] Installer le framework CSS choisi.
- [X] Configurer PostgreSQL (connexion locale + `schema_filter` Doctrine pour ne pas toucher aux tables n8n partageant la base).
- [X] Installer `DoctrineFixturesBundle` (dev).
- [X] Installer `symfony/ux-autocomplete` (search dans les selects).

## Entités

- [X] Créer `RssSource`.
- [X] Créer `RssItem`.
- [X] Créer `AiAnalysis`.
- [X] Créer `UserFeedback`.
- [X] Créer `DailySelection`.
- [X] Créer `DailySelectionItem`.
- [X] Créer `Tag` (ManyToMany avec `RssSource`, prévue pour `RssItem` plus tard).

## Enums

- [X] Créer `FeedbackType`.
- [X] Créer `ArticleCategory`.
- [X] Créer `SourceType` (RSS / HTML_SCRAPE) → préparation Phase 13.
- [~] ~~Créer `SourceCategory`, optionnel~~ → remplacé par l'entité `Tag` (plus flexible).

## Repositories

- [X] Ajouter une méthode pour récupérer les articles récents (`findLatest`).
- [X] Ajouter une méthode pour récupérer les articles non traités (`findLatestUnprocessed`).
- [X] Ajouter une méthode pour récupérer les meilleurs articles du jour (`findRecentTopScored`).
- [ ] Ajouter une méthode pour récupérer les articles par tag.
- [ ] Ajouter une méthode pour récupérer les feedbacks positifs.

## Migrations Doctrine

- [X] `Version20260526101708` — création des tables MVP (rss_source, rss_item, ai_analysis, user_feedback, daily_selection, daily_selection_item).
- [X] `Version20260526111349` — ajout de l'entité `Tag`, table de jointure `rss_source_tag`, suppression de la colonne `category` de `rss_source`.
- [X] `Version20260526184243` — table de jointure `rss_item_tag` (tags d'article via IA), suppression de la colonne JSON `tags` de `ai_analysis`.
- [X] `Version20260526202724` — colonnes `translated_title`, `user_xxx_score` × 5, `user_scores_updated_at` sur `ai_analysis`.
- [X] `Version20260527170000` — colonnes `type` (enum `SourceType`) et `scrape_config` (JSON) sur `rss_source`, préparation du mode HTML scraping (Phase 13).

## Fixtures (dev)

- [X] `TagFixtures` — 87 tags pré-définis (Symfony, IA, DevOps, business, sécurité, meta).
- [X] `RssSourceFixtures` — 16 sources (10 validées + 6 ajoutées ensuite) avec leurs tags associés.

## Scripts d'environnement

- [X] `scripts/install.ps1` — démarrage Docker + migrations.
- [X] `scripts/update.ps1` — pull images + reboot conteneurs + migrations.

---

## Phase 3 — Gestion des sources RSS

## CRUD Sources

- [X] Page liste des sources (`/sources`).
- [X] Formulaire d'ajout (`/sources/new`).
- [X] Formulaire d'édition (`/sources/{id}/edit`).
- [X] Bouton activer / désactiver (action POST dédiée).
- [X] Champ priorité (0-100, contrainte Range).
- [X] Champ tags (multi-select autocomplete TomSelect via ux-autocomplete) → remplace l'idée initiale d'un champ catégorie unique.
- [ ] Affichage du nombre d'articles importés par source.

## CRUD Tags

- [X] Page liste des tags (`/tags`).
- [X] Formulaire d'ajout / édition / suppression.
- [X] Génération automatique du slug si laissé vide (via `SluggerInterface`).
- [X] Validation regex sur le slug (`^[a-z0-9-]+$`).

## Import initial

- [X] Préparer une première liste de 10 flux RSS.
- [X] Tester l'import (workflow `RSS Collect` validé bout en bout).
- [ ] Étendre progressivement jusqu'à 50 flux max.
- [X] Documenter dans la fixture les URLs RSS cassées découvertes (11 URLs en commentaire).

---

## Phase 4 — API interne pour n8n

## Sécurité

- [X] Créer un token API interne.
- [X] Lire le token depuis `.env`.
- [X] Vérifier le token dans les endpoints internes.

## Endpoints

- [X] `POST /internal/rss-items`
  - Crée un article s'il n'existe pas.
  - Ignore les doublons par URL ou hash.

- [X] `POST /internal/ai-analysis`
  - Ajoute ou met à jour l'analyse IA d'un article.

- [X] `POST /internal/daily-selection`
  - Crée la sélection quotidienne.

- [X] `GET /internal/rss-sources`
  - Retourne les sources actives pour n8n.

---

## Phase 5 — Workflow n8n collecte RSS

## Workflow Collecte

- [X] Créer un workflow n8n `RSS Collect`.
- [X] Déclenchement toutes les heures.
- [X] Lire les sources actives.
- [X] Pour chaque source, lire le flux RSS.
- [X] Normaliser les données.
- [X] Envoyer vers Symfony ou écrire directement dans PostgreSQL.
- [X] Filtrer les articles à plus de 24h (nœud Filter natif n8n).
- [X] Retry on Fail sur Push RSS Item (5 essais, 2s d'attente) pour absorber les bursts qui saturent `symfony serve` mono-worker.
- [ ] Gérer les erreurs de flux indisponibles (continue on fail sur Read RSS).
- [ ] Logger les imports (table dédiée ou fichier).

## Déduplication

- [X] Dédupliquer par URL.
- [X] Dédupliquer par hash titre + source + date.
- [X] Ne pas réimporter les anciens articles.

---

## Phase 6 — Analyse IA

## Prompt article-analysis

- [X] Créer `n8n/prompts/article-analysis.prompt.md`.
- [X] Définir le rôle de l'IA.
- [X] Définir le profil de Nicolas.
- [X] Définir les catégories.
- [X] Définir les critères de scoring.
- [X] Demander une sortie JSON stricte.

## Analyse article

- [X] Workflow n8n `Article Analysis` ([n8n/workflows/article-analysis.workflow.json](n8n/workflows/article-analysis.workflow.json)).
- [X] Récupérer les articles non traités via `GET /internal/rss-items/unprocessed`.
- [X] Architecture **chain-of-prompts en 3 étapes** pour améliorer la qualité sur petit modèle :
  - Étape 1 : Summary + **translatedTitle (FR)**.
  - Étape 2 : Tags (à partir du summary + allowedTags).
  - Étape 3 : Scoring + reasoning (à partir du summary + tags + profil Nicolas).
- [X] Envoyer titre + extrait + source + **liste des tags existants** à l'IA (étape 1+2).
- [X] Recevoir résumé en FR + titre traduit + tags choisis + scores.
- [X] Stocker dans `ai_analysis` et lier les tags via la jointure `rss_item_tag`.
- [X] Marquer l'article comme traité (`isProcessed = true`).
- [X] Modèle local Ollama `qwen3:4b`, `think: false` pour éviter le mode raisonnement lent.

## Tagging IA des articles (décisions prises)

- [X] **Vocabulaire fermé** : le prompt reçoit `allowedTags` (slugs des tags existants), l'IA pioche dedans.
- [X] **Stockage M2M `rss_item_tag`** : migration `Version20260526184243` appliquée, relation ManyToMany dans `RssItem` et `Tag`.
- [X] **Deux niveaux de tagging conservés** : tags de source (manuel) + tags d'article (IA).
- [X] Migration `rss_item_tag` créée et appliquée.
- [X] Champ JSON `tags` supprimé de `AiAnalysis`.
- [X] `AiAnalysisImporter` mis à jour : résout les slugs en `Tag`, attache au `RssItem`, log les slugs inconnus.
- [X] `GET /internal/tags` créé pour injection dynamique des slugs autorisés dans le prompt.

## Correction manuelle des scores

- [X] Migration `Version20260526202724` : 5 colonnes `user_xxx_score` (nullable) + `user_scores_updated_at` sur `ai_analysis`.
- [X] Méthodes `applyUserScores()`, `resetUserScores()`, `hasUserScores()`, `getEffectiveFinalScore()` sur l'entité.
- [X] Page article `show` avec 5 sliders HTML5 (0-100) pour ajuster chaque score.
- [X] Affichage côte à côte : score IA original + score corrigé courant.
- [X] Bouton "Réinitialiser aux scores IA" (avec confirm JS) si déjà corrigé.
- [X] CSRF protection sur update et reset.
- [ ] Plus tard : exploiter l'écart IA/humain pour améliorer les prompts (Phase 11).

## Traduction des titres

- [X] Étape Summary du workflow retourne aussi `translatedTitle` (FR, max 250 chars).
- [X] Colonne `translated_title` dans `ai_analysis` (nullable).
- [X] Dashboard affiche le titre traduit, avec fallback sur le titre original.
- [X] Page `show` affiche le titre traduit en gros et le titre original en italique petit.

## Scores

- [X] relevanceScore (clampé 0-100 côté workflow n8n).
- [X] businessScore.
- [X] learningScore.
- [X] contentScore.
- [X] finalScore.
- [X] reasoning.

---

## Phase 7 — Dashboard Symfony

## Page dashboard

- [X] Créer `DashboardController`.
- [X] Afficher les articles analysés par l'IA (top 10 par score final).
- [X] Afficher les titres traduits en français (avec fallback titre original).
- [X] Afficher le score final effectif (corrigé manuellement si présent, sinon IA).
- [X] Badge "corrigé" sur les articles dont les scores ont été ajustés manuellement.
- [ ] Afficher explicitement le Top 5 du jour comme section dédiée.
- [ ] Afficher le résumé global du jour (Phase 9, sélection quotidienne).
- [ ] Afficher les boutons de feedback (Phase 8).

## UI Article Card (dashboard)

- [X] titre (traduit FR) ;
- [X] source ;
- [X] date ;
- [X] résumé IA ;
- [X] tags ;
- [X] score final ;
- [X] catégorie principale ;
- [X] lien vers la page de détail.
- [ ] boutons feedback (Phase 8).

## Détail article

- [X] Page `/articles/{id}` (`ArticleController::show`).
- [X] Afficher contenu brut disponible (rawExcerpt strippé HTML).
- [X] Afficher analyse IA complète : résumé, tags, scores, modèle utilisé.
- [X] **Sliders pour corriger les 5 scores manuellement** (POST `/articles/{id}/scores`).
- [X] **Bouton reset** pour réinitialiser aux scores IA.
- [X] Afficher le titre traduit avec mention du titre original.
- [ ] Afficher feedbacks (Phase 8).
- [ ] Ajouter commentaires utilisateur (Phase 8).

## Liste articles

- [ ] Page `/articles`.
- [ ] Pagination.
- [ ] Filtre par catégorie.
- [ ] Filtre par source.
- [ ] Filtre par score.
- [ ] Filtre par feedback.
- [ ] Recherche texte simple.

---

## Phase 8 — Feedback utilisateur

## Boutons feedback

- [ ] Meilleur sujet du jour.
- [ ] Utile.
- [ ] Pas utile.
- [ ] Opportunité business.
- [ ] Idée de contenu.
- [ ] À lire plus tard.
- [ ] Sauvegardé.

## Backend

- [ ] Créer `FeedbackController`.
- [ ] Créer `FeedbackService`.
- [ ] Empêcher les doublons absurdes.
- [ ] Permettre plusieurs feedbacks différents sur un même article si pertinent.
- [ ] Ajouter un commentaire optionnel.

## Exploitation

- [ ] Créer une page des articles sauvegardés.
- [ ] Créer une page des opportunités business.
- [ ] Créer une page des idées de contenu.

---

## Phase 9 — Sélection quotidienne Top 5

## Workflow n8n Daily Selection

- [ ] Déclenchement tous les jours.
- [ ] Récupérer les articles des dernières 24h.
- [ ] Trier par finalScore.
- [ ] Retirer les doublons thématiques.
- [ ] Envoyer les meilleurs candidats à une IA cloud.
- [ ] Sélectionner 5 sujets maximum.
- [ ] Générer un résumé global.
- [ ] Stocker dans `daily_selection`.
- [ ] Envoyer une notification mail avec lien vers dashboard.

## Prompt daily-selection

- [ ] Créer `n8n/prompts/daily-selection.prompt.md`.
- [ ] Prendre en compte le feedback historique.
- [ ] Favoriser les sujets actionnables.
- [ ] Favoriser les opportunités business.
- [ ] Favoriser les sujets pouvant devenir du contenu.

---

## Phase 10 — Notification mail

## Mail quotidien

- [ ] Configurer SMTP.
- [ ] Créer un template mail simple.
- [ ] Envoyer un lien vers le dashboard.
- [ ] Optionnel : inclure les titres du Top 5.
- [ ] Ne pas faire un mail trop long.

Exemple :

```txt
Ton Top 5 de veille est prêt.

Voir le dashboard :
https://veille.example.com/dashboard
```

---

## Phase 11 — Boucle d'amélioration IA

## Analyse feedback

- [ ] Créer un workflow hebdomadaire `Feedback Learning`.
- [ ] Récupérer les feedbacks positifs et négatifs.
- [ ] Résumer les préférences de Nicolas.
- [ ] Produire un bloc de contexte réutilisable dans les prompts.
- [ ] Stocker ce bloc dans PostgreSQL ou dans un fichier de prompt.

## Préférences à apprendre

- [ ] Sources souvent pertinentes.
- [ ] Catégories préférées.
- [ ] Types de sujets ignorés.
- [ ] Types de sujets transformables en contenu.
- [ ] Types de sujets business.

---

## Phase 12 — Préparation LinkedIn

À faire seulement quand le Top 5 quotidien est fiable.

## Weekly Digest

- [ ] Créer une page `/weekly`.
- [ ] Regrouper les meilleurs sujets de la semaine.
- [ ] Détecter les tendances.
- [ ] Générer 3 angles de posts LinkedIn.
- [ ] Générer un brouillon de post.
- [ ] Ajouter statut :
  - brouillon ;
  - à modifier ;
  - prêt ;
  - publié.

## Prompt LinkedIn

- [ ] Créer `n8n/prompts/weekly-linkedin.prompt.md`.
- [ ] Ton naturel, pas trop corporate.
- [ ] Angle développeur indépendant.
- [ ] Angle apprentissage / retour d'expérience.
- [ ] Pas de contenu trop vendeur.

---

## Phase 13 — Améliorations futures

## Recherche

- [ ] Recherche full-text PostgreSQL.
- [ ] Recherche par tags.
- [ ] Recherche par score.
- [ ] Recherche par source.

## Mémoire long terme

- [ ] Ajouter embeddings plus tard.
- [ ] Évaluer Qdrant.
- [ ] Déduplication sémantique.
- [ ] Recherche sémantique.

## Qualité

- [ ] Ajouter logs d'import.
- [ ] Ajouter page erreurs RSS.
- [ ] Ajouter page monitoring n8n.
- [ ] Ajouter backups PostgreSQL.
- [ ] Ajouter export CSV ou JSON.

## Sources sans flux RSS (HTML scraping)

Cas d'usage : certains blogs intéressants n'exposent aucun flux XML (ex : Grafikart vérifié le 2026-05-27 — `/feed`, `/rss`, `/atom.xml`, `/index.xml`, `/feed.xml` tous 404).

Infra déjà prête côté Symfony (MVP) :

- [X] Enum `App\Enum\SourceType` avec `RSS` (défaut) et `HTML_SCRAPE`.
- [X] Champs `type` (NOT NULL, défaut `RSS`) et `scrape_config` (JSON nullable) sur `RssSource`.
- [X] Migration `Version20260527170000`.
- [X] Formulaire CRUD étendu avec select `type` + textarea JSON `scrapeConfig`.

À faire en Phase 13 (quand 1-2 sources stratégiques le justifient) :

- [ ] Ajouter un filtre `?type=` sur `GET /internal/rss-sources` (ou exposer le champ `type` dans la réponse pour que n8n filtre).
- [ ] Workflow n8n `HTML Scrape Collect` parallèle au `RSS Collect` :
  - `HTTP Request` → récupère la page HTML.
  - `HTML Extract` → applique les sélecteurs CSS de `scrape_config`.
  - Normalisation → `{title, url, publishedAt, rawExcerpt}` (même format que RSS).
  - Push vers `POST /internal/rss-items` (endpoint inchangé, déduplication par URL).
- [ ] Documenter dans `docs/` le format attendu pour `scrape_config` (sélecteurs `itemSelector`, `titleSelector`, `linkSelector`/`linkAttribute`, `dateSelector`/`dateAttribute`, `excerptSelector`).
- [ ] UI : masquer/afficher le champ `scrapeConfig` selon la valeur du select `type` (JS / Stimulus controller).
- [ ] Validation côté Symfony : exiger `scrape_config` non vide si `type = HTML_SCRAPE`.
- [ ] Tester la robustesse : que se passe-t-il si le HTML cible change ? Logger les imports vides comme alerte.

---

## Première liste de flux à préparer

## Catégories

- [X] Symfony / PHP (SymfonyCasts, Tomas Votruba).
- [X] IA développeur (Simon Willison, OpenAI, Hugging Face, Ollama).
- [X] Agents IA / MCP (tags présents, sources à enrichir).
- [X] Docker / DevOps (Docker Blog).
- [X] n8n / automatisation (n8n Blog).
- [ ] Business freelance (SaaStr retiré pour cause de superficialité, à remplacer).
- [ ] Open source (tags présents, sources à ajouter — GitHub Eng retiré).
- [ ] Sécurité (tags présents, sources à ajouter).

## Limite

- [X] Ne pas dépasser 50 flux au début.
- [X] Commencer avec 10 à 15 flux (10 actuellement).
- [ ] Ajouter progressivement selon la qualité des résultats.

---

## Définition du MVP terminé

Le MVP est terminé quand :

- [ ] n8n collecte automatiquement des articles RSS.
- [ ] Les articles sont stockés en base.
- [ ] Une analyse IA est générée.
- [ ] Le Top 5 quotidien apparaît dans Symfony.
- [ ] Nicolas peut donner un feedback.
- [ ] Le feedback est stocké.
- [ ] Un mail quotidien notifie que le dashboard est prêt.

---

## Principe directeur

Toujours privilégier :

```txt
Simple → Fonctionnel → Utile → Améliorable
```

Ne pas chercher à construire la version parfaite dès le début.
