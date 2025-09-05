camagru/
├── docker-compose.yml
├── .env.example
├── .env
├── .gitignore
├── README.md
│
├── app/                          # Aplicación principal
│   ├── public/                   # Punto de entrada web
│   │   ├── index.php
│   │   ├── assets/
│   │   │   ├── css/
│   │   │   │   ├── main.css
│   │   │   │   ├── responsive.css
│   │   │   │   └── components/
│   │   │   ├── js/
│   │   │   │   ├── main.js
│   │   │   │   ├── camera.js
│   │   │   │   ├── ajax.js
│   │   │   │   └── gallery.js
│   │   │   ├── images/
│   │   │   │   ├── overlays/     # Imágenes superpuestas
│   │   │   │   └── uploads/      # Fotos de usuarios
│   │   │   └── icons/
│   │   └── .htaccess
│   │
│   ├── src/                      # Código fuente PHP
│   │   ├── Config/
│   │   │   ├── Database.php
│   │   │   └── Config.php
│   │   │
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── UserController.php
│   │   │   ├── GalleryController.php
│   │   │   ├── PhotoController.php
│   │   │   └── BaseController.php
│   │   │
│   │   ├── Models/
│   │   │   ├── User.php
│   │   │   ├── Photo.php
│   │   │   ├── Comment.php
│   │   │   ├── Like.php
│   │   │   └── BaseModel.php
│   │   │
│   │   ├── Views/
│   │   │   ├── layouts/
│   │   │   │   ├── header.php
│   │   │   │   ├── footer.php
│   │   │   │   └── main.php
│   │   │   ├── auth/
│   │   │   │   ├── login.php
│   │   │   │   ├── register.php
│   │   │   │   └── verify.php
│   │   │   ├── user/
│   │   │   │   ├── profile.php
│   │   │   │   └── settings.php
│   │   │   ├── gallery/
│   │   │   │   └── index.php
│   │   │   └── editor/
│   │   │       └── index.php
│   │   │
│   │   ├── Services/
│   │   │   ├── AuthService.php
│   │   │   ├── EmailService.php
│   │   │   ├── ImageService.php
│   │   │   └── ValidationService.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── AuthMiddleware.php
│   │   │   └── CSRFMiddleware.php
│   │   │
│   │   ├── Utils/
│   │   │   ├── Router.php
│   │   │   ├── Security.php
│   │   │   └── Helpers.php
│   │   │
│   │   └── Database/
│   │       ├── migrations/
│   │       │   ├── 001_create_users_table.sql
│   │       │   ├── 002_create_photos_table.sql
│   │       │   ├── 003_create_comments_table.sql
│   │       │   └── 004_create_likes_table.sql
│   │       └── seeds/
│   │           └── sample_data.sql
│   │
│   ├── vendor/                   # Dependencias si usas Composer
│   └── composer.json
│
├── docker/                       # Configuración Docker
│   ├── nginx/
│   │   ├── Dockerfile
│   │   └── nginx.conf
│   ├── php/
│   │   ├── Dockerfile
│   │   └── php.ini
│   └── mysql/
│       └── init.sql
│
└── docs/                        # Documentación
    ├── API.md
    ├── SETUP.md
    └── screenshots/