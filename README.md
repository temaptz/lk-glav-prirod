# Личный кабинет клиента по экологии

**MVP системы управления экологическим соответствием** для компании "ГлавПриродБюро"

Система автоматизирует контроль экологических требований, управление документами, финансами и отчётностью для организаций различных категорий НВОС (Негативное Воздействие на Окружающую Среду).

---

## 🚀 Технологический стек

### Backend
- **PHP 8.3** + **Yii2 Framework 2.0.53** - REST API
- **PostgreSQL 13** - реляционная БД с RLS (Row-Level Security)
- **JWT (HS256)** - безопасная аутентификация без сессий
- **MinIO** - S3-совместимое хранилище документов

### Frontend  
- **Angular 19** - современный SPA фреймворк
- **Signals API** - реактивное управление состоянием
- **TypeScript** - типобезопасность
- **Standalone Components** - модульная архитектура без NgModule

### Инфраструктура
- **Docker Compose** - контейнеризация всех сервисов
- **Nginx** - reverse proxy для backend API
- **4 миграции** - чистая структура БД (таблицы → RLS → справочники → демо-данные)

---

## 🏗️ Архитектура системы

```
┌─────────────────────────────────────────────────────────────┐
│                    FRONTEND (Angular 19)                     │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐   │
│  │ Клиенты  │  │Требования│  │Документы │  │ Финансы  │   │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘  └────┬─────┘   │
│       │             │              │             │          │
│       └─────────────┴──────────────┴─────────────┘          │
│                         │ HTTP/JWT                           │
└─────────────────────────┼────────────────────────────────────┘
                          │
         ┌────────────────┴────────────────┐
         │       Nginx Reverse Proxy       │
         └────────────────┬────────────────┘
                          │
┌─────────────────────────┼────────────────────────────────────┐
│              BACKEND (Yii2 REST API)                         │
│  ┌──────────────────────┴──────────────────────┐            │
│  │         Controllers Layer                    │            │
│  │  Organization │ Requirements │ Artifacts │...│            │
│  └──────────────────────┬──────────────────────┘            │
│                         │                                     │
│  ┌──────────────────────┴──────────────────────┐            │
│  │         Business Logic Layer                 │            │
│  │  • RequirementBuilder - автогенерация        │            │
│  │  • AuditBehavior - логирование 152-ФЗ       │            │
│  │  • OrgFilterTrait - фильтрация по доступу   │            │
│  └──────────────────────┬──────────────────────┘            │
│                         │                                     │
│  ┌──────────────────────┴──────────────────────┐            │
│  │         Data Access Layer (Models)           │            │
│  │  Organization │ User │ Contract │ Invoice │..│            │
│  └──────────────────────┬──────────────────────┘            │
└─────────────────────────┼────────────────────────────────────┘
                          │
         ┌────────────────┴────────────────┐
         │                                  │
┌────────┴────────┐              ┌─────────┴────────┐
│  PostgreSQL 13  │              │   MinIO (S3)     │
│                 │              │                  │
│  4 schemas:     │              │  Bucket:         │
│  • auth         │              │  • artifacts     │
│  • compliance   │              │                  │
│  • finance      │              │  Документы:      │
│  • audit        │              │  - Отчёты        │
│                 │              │  - Лицензии      │
│  RLS enabled    │              │  - Акты          │
└─────────────────┘              └──────────────────┘
```

### Ключевые архитектурные решения

1. **Multi-tenant через RLS**
   - Каждый пользователь видит только свои организации
   - Фильтрация на уровне приложения через JOIN с `auth.users_orgs`
   - Безопасность: клиент физически не может получить чужие данные

2. **Автоматическая генерация требований**
   - При создании организации система подбирает требования из справочника
   - Учитывается: категория НВОС (I-IV), наличие воды, побочные продукты
   - Битовые маски для эффективной фильтрации

3. **S3-совместимое хранилище**
   - MinIO для локальной разработки
   - Простой переход на AWS S3 / Yandex Object Storage в production

4. **Audit Trail для 152-ФЗ**
   - Логирование всех изменений в `audit.logs`
   - AuditBehavior автоматически записывает: кто, когда, что изменил

---

## 👥 DEMO USERS

```
Email                   Пароль       Роль         Доступ
─────────────────────────────────────────────────────────────────────
admin@example.com       admin        Админ        Все 5 организаций
                                                  + управление пользователями

manager@example.com     manager      Менеджер     4 организации (категории 1-3)
                                                  Управление клиентами

specialist@example.com  specialist   Специалист   3 организации (категории 1-2)
                                                  Работа с требованиями

client@example.com      client       Клиент       1 организация (ООО "Демо Клиент")
                                                  Только свои данные
```

### Демо-организации

1. **ООО "Демо Клиент"** - Категория II, скважина
   - 🔒 Доступ: client, admin

2. **ООО "ЭкоПром"** - Категория I, река, побочные продукты  
   - 👥 Доступ: admin, manager, specialist

3. **АО "Природа Плюс"** - Категория II, скважина
   - 👥 Доступ: admin, manager, specialist

4. **ООО "ГринТех"** - Категория III, река, побочные продукты
   - 👥 Доступ: admin, manager

5. **ООО "Эко Решения"** - Категория II, скважина, побочные продукты
   - 👥 Доступ: admin, manager, specialist

Для каждой организации созданы:
- ✅ Требования по экологии (на основе категории и профиля)
- ✅ Договоры, счета, акты
- ✅ События календаря отчётности

---

## 📊 Структура БД

```sql
auth         -- Пользователи и доступ
├── users              (id, email, password_hash, role)
└── users_orgs         (user_id, org_id) -- многие-ко-многим

compliance   -- Экологическое соответствие
├── organizations      (id, name, inn, category, water_source...)
├── requirements       (id, code, title, category_mask, need_water...)
├── client_requirements(id, org_id, requirement_id, status, deadline)
├── artifacts          (id, org_id, path, filename, requirement_id...)
├── calendar_events    (id, org_id, title, event_date, requirement_id)
└── risks              (id, code, title, severity)

finance      -- Финансовые документы
├── contracts          (id, org_id, number, signed_at, status)
├── invoices           (id, contract_id, amount, paid_at, status)
└── acts               (id, contract_id, accepted_at, status)

audit        -- Логирование для 152-ФЗ
└── logs               (id, user_id, action, model, old/new_value, ip)
```

---

## 🎯 Основной функционал

### Для клиента
- 📋 Просмотр карты экологических требований
- 📄 Загрузка/скачивание документов (лицензии, отчёты)
- 📅 Календарь дедлайнов отчётности
- 💰 Договоры, счета, акты
- ⚠️ Информация о рисках и штрафах

### Для персонала (manager, specialist)
- 👥 Управление несколькими клиентами
- 📊 Просмотр статистики по всем организациям
- 📝 Отслеживание выполнения требований
- 📁 Централизованное хранилище документов

### Для администратора
- 👤 Управление пользователями (CRUD)
- 📚 Управление справочниками (требования, риски)
- 🔍 Полный доступ ко всем данным
- 📊 Аудит действий пользователей

---

## ⚡ Quick Start

### Требования
- **Docker** и **Docker Compose v2**

### Запуск

```bash
# 1. Клонировать репозиторий
git clone <repo-url> && cd lk-glav-prirod

# 2. Запустить все сервисы
docker compose up -d --build

# 3. Применить миграции (создаст БД и демо-данные)
docker compose exec backend php yii migrate --interactive=0

# 4. Открыть приложение
open http://localhost:4200
```

### Доступ к сервисам

| Сервис | URL | Логин/Пароль |
|--------|-----|--------------|
| **Frontend** | http://localhost:4200 | См. DEMO USERS выше |
| **Backend API** | http://localhost:8080 | JWT токен |
| **MinIO Console** | http://localhost:9001 | minioadmin / minioadmin |
| **PostgreSQL** | localhost:5432 | gpuser / gppass |

---

## 🔄 Сброс БД

Если нужно пересоздать БД с нуля:

```bash
# Остановить и удалить данные
docker compose down --volumes

# Запустить заново
docker compose up -d --build

# Применить миграции
docker compose exec backend php yii migrate --interactive=0
```

---

## 🛠️ Полезные команды

```bash
# Просмотр логов
docker compose logs -f backend
docker compose logs -f frontend

# Консоль Yii2
docker compose exec backend php yii

# Список миграций
docker compose exec backend php yii migrate/history

# Подключение к БД
docker compose exec postgresql psql -U gpuser -d glavprirod

# Пересборка frontend
docker compose up -d --build frontend
```

---

## 📂 Структура проекта

```
lk-glav-prirod/
├── backend/                    # Yii2 REST API
│   ├── config/                # Конфигурация
│   ├── migrations/            # 4 миграции БД
│   ├── src/
│   │   ├── controllers/       # REST контроллеры
│   │   ├── models/            # ActiveRecord модели
│   │   ├── components/        # JWT, RLS, CORS
│   │   ├── services/          # Бизнес-логика
│   │   └── traits/            # OrgFilterTrait
│   └── composer.json
│
├── frontend/                   # Angular 19 SPA
│   ├── src/
│   │   ├── app/
│   │   │   ├── features/      # Страницы (requirements, finance...)
│   │   │   ├── services/      # HTTP сервисы
│   │   │   └── guards/        # Route guards (auth, role)
│   │   └── styles.css
│   └── package.json
│
├── docker/                     # Nginx конфиги
├── docker-compose.yml          # Оркестрация сервисов
└── .env                        # Переменные окружения
```

---

## 🚢 Production Deploy

1. Настроить `.env` для production:
   ```bash
   POSTGRES_PASSWORD=<strong-password>
   JWT_SECRET=<random-256-bit-key>
   MINIO_ROOT_PASSWORD=<strong-password>
   ```

2. Использовать внешний PostgreSQL и S3:
   ```bash
   # В backend/config/db.php изменить dsn
   # В backend/config/web.php изменить minio endpoint
   ```

3. Настроить HTTPS через Nginx/Traefik

4. Запустить:
   ```bash
   docker compose up -d --build
   ```

---

## 📖 Дополнительная документация

- **Техническое задание:** `technical_assignment/ТЗ_Личный_кабинет_клиента_по_экологии_MVP.md`
- **Структура миграций:** `backend/migrations/README.md`
- **Код ревью:** `CODE_REVIEW_FIXES.md`

---

## 📄 Лицензия

Proprietary - ГлавПриродБюро © 2025