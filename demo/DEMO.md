# Déroulé de la démo (~15 min)

Chaque étape correspond à un point du schéma des slides. `./step.sh N` affiche
le diff de l'étape puis l'applique, ce qui permet de commenter le code avant de
le lancer. Pour revenir au départ : `./step.sh 0`.

La page `https://app.demo.test` sert de fil rouge : une ligne par point, qui
passe du ✘ au ✔ au fil des étapes.

## Avant midi

```bash
cd demo
./step.sh 0
castor docker:doctor        # tout doit être vert
castor docker:build         # chauffe le cache pour la démo
for n in 1 2 3 4 5 6; do ./step.sh $n > /dev/null; done
castor docker:up --build    # chauffe aussi les images des étapes suivantes
./step.sh 0
castor docker:up --build    # remet l'image de départ, sans figlet
castor docker:stop
```

Garde trois onglets ouverts : les slides, `https://app.demo.test` et
`https://adminer.demo.test`.

---

## 0 · Départ : PHP + Postgres, sans contexte

```bash
cat castor.php
castor up
castor about
```

- Ouvrir `https://app.demo.test` : le certificat est valide, la connexion à
  Postgres marche (✔) sans rien configurer, l'application est déjà configurée.
- `castor list` : les tâches `app:*` et `postgres:*` ont été générées.
- `castor postgres:client`, puis `\l` et `\q`.
- `castor app:bash`, puis `env | grep -E 'DATABASE|PROJECT'`.
- Montrer `compose.generated.yaml`, *« on ne l'édite jamais »*.

## 1 · ① Le contexte

```bash
./step.sh 1
castor build
```

- Le contexte apparaît : `root_domain` et `build_parallel`, des options globales
  au projet.
- `castor build` : les services se buildent chacun de leur côté, avec une ligne
  de progression par service.

## 2 · ② L'API fluide

```bash
./step.sh 2
castor up --build
```

- `RedisService`, `addExtension('redis')` et `withPhpIni(['memory_limit' => '1G'])`.
- Recharger la page : `memory_limit` et Redis passent au vert.
- Bonus : `castor about` affiche maintenant Redis Insight.

## 3 · ③ Le Dockerfile Twig

```bash
./step.sh 3
castor up --build
```

- Le `Dockerfile` fait 10 lignes, et on ne surcharge que le bloc `php_base`.
- Le point clé : on ne copie pas le Dockerfile du plugin, on l'étend.
- Recharger la page : la bannière figlet apparaît, ce qui prouve que le paquet
  est dans l'image.

## 4 · ④ Le ComposeBuilder

```bash
./step.sh 4
castor up
castor about
```

- Un conteneur ponctuel sans écrire de service : adminer, routé en HTTPS.
- Ouvrir `https://adminer.demo.test` et se connecter avec serveur `postgres`,
  utilisateur `app`, mot de passe `app`, base `app`.

## 5 · ⑤ DockerComposeWriteEvent

```bash
./step.sh 5
castor up
grep -A3 deploy: compose.generated.yaml
castor stats --no-disk      # le Mem % de postgres est calculé sur 256 Mo (~10 %), pas sur la RAM de la machine
```

- La trappe de secours pour une clé que le builder ne connaît pas.

## 6 · ⑥ compose.override.yaml

```bash
./step.sh 6
castor up
```

- Du YAML classique, jamais touché par le plugin.
- Recharger la page : tout est vert ✔.

## ⑦ Écrire un service (slides seulement)

Pas de live : renvoyer vers `example/castor.php` et la page « Writing your own
service » de la doc.

---

## Si on a du temps

```bash
castor doctor
castor stats
castor docker:tunnel:start                   # URL publique (Cloudflare)
```

Les worktrees ont besoin d'un dépôt git, et `docker-pres/` n'en est pas un.
Pour les montrer, passe dans le dépôt du plugin, dans `example/` :
`castor worktree:create demo-branch --start` crée une deuxième stack avec des
domaines préfixés (`demo-branch.project.test`).

## Plan B

- Si le réseau ou le build lâche : `castor about` et `castor doctor` répondent
  même avec la stack arrêtée, et montrer le diff (`./step.sh N`) avec le
  `compose.generated.yaml` suffit pour les étapes ④ et ⑤.
- Pour tout remettre à zéro : `castor docker:destroy --force && ./step.sh 0`.
