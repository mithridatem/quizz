# quizz
1 cloner le projet
```bash
git clone https://github.com/mithridatem/quizz.git quizzbackend
cd quizz backend
```
2 créer un fichier env.local
3 copier le contenu du fichier .env -> env.local
4 créer les entrées suivantes
```
###> symfony/framework-bundle ###
APP_ENV=dev
APP_SECRET=413ea7f0d64ec92f629b62be1f249a8a
###< symfony/framework-bundle ###
DATABASE_URL="mysql://login:!changeme!@127.0.0.1:3306/app?serverVersion=10.4.32-MariaDB&charset=utf8mb4"
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
###> lexik/jwt-authentication-bundle ###
JWT_SECRET_KEY='%kernel.project_dir%/config/jwt/private.pem'
JWT_PUBLIC_KEY='%kernel.project_dir%/config/jwt/public.pem'
JWT_PASSPHRASE=
###< lexik/jwt-authentication-bundle ###
```
installer le projet
```bash
composer install
symfony console doctrine:database:create
symonfy console doctrine:migrations:migrate
```
5 créer vos clés ssl
```bash
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
```
6 copier votre passphrase dans env.local->JWT_PASSPHRASE
