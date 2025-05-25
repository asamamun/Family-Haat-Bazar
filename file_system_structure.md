# File System Structure

```
ecommerce-pos/
├── composer.json
├── composer.lock
├── .env.example
├── .env
├── .gitignore
├── .htaccess
├── index.php
├── README.md
│
├── app/
│   ├── Config/
│   │   ├── Database.php
│   │   ├── App.php
│   │   └── Payment.php
│   │
│   ├── Controllers/
│   │   ├── BaseController.php
│   │   ├── AuthController.php
│   │   ├── ProductController.php
│   │   ├── CategoryController.php
│   │   ├── CartController.php
│   │   ├── OrderController.php
│   │   ├── CouponController.php
│   │   ├── PaymentController.php
│   │   ├── POSController.php
│   │   ├── ReportController.php
│   │   └── DashboardController.php
│   │
│   ├── Models/
│   │   ├── BaseModel.php
│   │   ├── User.php
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── Subcategory.php
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   ├── Cart.php
│   │   ├── Coupon.php
│   │   ├── PaymentTransaction.php
│   │   ├── StockMovement.php
│   │   └── Setting.php
│   │
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── CartService.php
│   │   ├── OrderService.php
│   │   ├── PaymentService.php
│   │   ├── InventoryService.php
│   │   ├── CouponService.php
│   │   ├── ReportService.php
│   │   └── ImageService.php
│   │
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   ├── AdminMiddleware.php
│   │   ├── CashierMiddleware.php
│   │   └── CorsMiddleware.php
│   │
│   ├── Helpers/
│   │   ├── ValidationHelper.php
│   │   ├── ImageHelper.php
│   │   ├── PaginationHelper.php
│   │   ├── SessionHelper.php
│   │   └── ResponseHelper.php
│   │
│   ├── Libraries/
│   │   ├── Router.php
│   │   ├── Database.php
│   │   ├── Session.php
│   │   ├── Validation.php
│   │   └── Pagination.php
│   │
│   └── Exceptions/
│       ├── ValidationException.php
│       ├── AuthException.php
│       └── PaymentException.php
│
├── public/
│   ├── index.php (entry point)
│   ├── .htaccess
│   │
│   ├── assets/
│   │   ├── css/
│   │   │   ├── bootstrap.min.css
│   │   │   ├── admin.css
│   │   │   ├── pos.css
│   │   │   └── frontend.css
│   │   │
│   │   ├── js/
│   │   │   ├── jquery.min.js
│   │   │   ├── bootstrap.min.js
│   │   │   ├── admin.js
│   │   │   ├── pos.js
│   │   │   ├── cart.js
│   │   │   └── payment.js
│   │   │
│   │   ├── images/
│   │   │   ├── logo.png
│   │   │   ├── placeholder.png
│   │   │   └── icons/
│   │   │
│   │   └── fonts/
│   │
│   └── uploads/
│       ├── products/
│       ├── categories/
│       └── temp/
│
├── views/
│   ├── layouts/
│   │   ├── app.php
│   │   ├── admin.php
│   │   ├── pos.php
│   │   └── auth.php
│   │
│   ├── frontend/
│   │   ├── home.php
│   │   ├── products/
│   │   │   ├── index.php
│   │   │   ├── show.php
│   │   │   └── category.php
│   │   ├── cart/
│   │   │   ├── index.php
│   │   │   └── checkout.php
│   │   └── orders/
│   │       ├── index.php
│   │       └── show.php
│   │
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── products/
│   │   │   ├── index.php
│   │   │   ├── create.php
│   │   │   ├── edit.php
│   │   │   └── show.php
│   │   ├── categories/
│   │   │   ├── index.php
│   │   │   ├── create.php
│   │   │   └── edit.php
│   │   ├── orders/
│   │   │   ├── index.php
│   │   │   └── show.php
│   │   ├── coupons/
│   │   │   ├── index.php
│   │   │   ├── create.php
│   │   │   └── edit.php
│   │   ├── reports/
│   │   │   ├── sales.php
│   │   │   ├── inventory.php
│   │   │   └── customers.php
│   │   └── settings/
│   │       └── index.php
│   │
│   ├── pos/
│   │   ├── index.php
│   │   ├── cart.php
│   │   ├── payment.php
│   │   └── receipt.php
│   │
│   ├── auth/
│   │   ├── login.php
│   │   ├── register.php
│   │   └── forgot-password.php
│   │
│   └── components/
│       ├── header.php
│       ├── footer.php
│       ├── sidebar.php
│       ├── pagination.php
│       └── alerts.php
│
├── database/
│   ├── migrations/
│   │   ├── 001_create_categories_table.sql
│   │   ├── 002_create_products_table.sql
│   │   ├── 003_create_users_table.sql
│   │   ├── 004_create_orders_table.sql
│   │   └── 005_create_indexes.sql
│   │
│   ├── seeds/
│   │   ├── categories_seeder.sql
│   │   ├── products_seeder.sql
│   │   └── users_seeder.sql
│   │
│   └── schema.sql
│
├── storage/
│   ├── logs/
│   │   ├── app.log
│   │   ├── error.log
│   │   └── payment.log
│   │
│   ├── cache/
│   │   └── reports/
│   │
│   └── sessions/
│
├── api/
│   ├── v1/
│   │   ├── routes.php
│   │   ├── products.php
│   │   ├── orders.php
│   │   ├── payments.php
│   │   └── pos.php
│   │
│   └── middleware/
│       └── ApiAuthMiddleware.php
│
├── config/
│   ├── app.php
│   ├── database.php
│   ├── payment.php
│   └── routes.php
│
├── vendor/ (Composer packages)
│
└── tests/
    ├── Unit/
    │   ├── Models/
    │   └── Services/
    │
    └── Integration/
        ├── Controllers/
        └── Api/
```

## Key Directories Explanation:

### `/app/`
- **Controllers**: Handle HTTP requests and responses
- **Models**: Database entities and business logic
- **Services**: Business logic layer (cart, payment, inventory management)
- **Middleware**: Request filtering and authentication
- **Helpers**: Utility functions
- **Libraries**: Core framework components

### `/public/`
- Web-accessible directory
- Entry point (`index.php`)
- Static assets (CSS, JS, images)
- File uploads directory

### `/views/`
- Template files organized by section
- Separate layouts for frontend, admin, and POS
- Reusable components

### `/database/`
- SQL migration files
- Database schema
- Seed data for testing

### `/storage/`
- Application logs
- Cache files
- Session storage

### `/api/`
- REST API endpoints
- Separate from web routes
- API-specific middleware

## Recommended Composer Packages:

```json
{
    "require": {
        "php": ">=7.4",
        "vlucas/phpdotenv": "^5.0",
        "monolog/monolog": "^2.0",
        "intervention/image": "^2.7",
        "phpmailer/phpmailer": "^6.5",
        "firebase/php-jwt": "^6.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.0",
        "fakerphp/faker": "^1.15"
    }
}
```

## File Upload Structure:
- `/public/uploads/products/` - Product images
- `/public/uploads/categories/` - Category images  
- `/public/uploads/temp/` - Temporary uploads
- Files organized by year/month for better performance