# Maps Rating

A Laravel-based place discovery and rating platform where users can browse places, search by location and category, view detailed place information, submit multi-dimensional reviews, like reviews, bookmark places, and contact the platform administration.

The project demonstrates practical Laravel concepts including Eloquent relationships, custom query scopes, Form Requests, authorization, authentication, reusable Traits, database seeders, bookmarks, review ratings, search, and user interactions.

---

## 📌 Overview

Maps Rating is a web application designed to help users discover and evaluate different places.

Users can:

- Browse popular places
- Search for places by address
- Filter places by category
- View detailed place information
- View reviews and ratings
- Rate places based on multiple criteria
- Like reviews
- Bookmark places
- View bookmarked places
- Submit reports/contact requests
- Manage their account through Laravel Jetstream

The application calculates an overall rating for each place based on several rating dimensions.

---

# ✨ Features

## 🗺️ Place Discovery

The homepage displays popular places based on their view count.

Places are ordered using:

```php
Place::orderBy('view_count', 'desc')->take(3)->get();
```

This allows the application to highlight the most viewed places.

---

## 📍 Place Details

Each place has a dedicated details page.

A place can contain:

- Name
- Slug
- Image
- Overview
- Address
- Category
- Latitude
- Longitude
- View count
- Reviews
- Average ratings

The details page also displays the reviews associated with the place.

---

## ⭐ Multi-Dimensional Rating System

Instead of using only one rating value, the application allows users to evaluate a place across four different dimensions:

- Service
- Quality
- Cleanliness
- Pricing

Each dimension contributes to the final average rating.

The rating calculation is handled through a reusable `RateableTrait`.

---

## 📊 Rating Calculation

The application calculates the average for each rating category:

```text
Service Rating
Quality Rating
Cleanliness Rating
Pricing Rating
```

The overall rating is calculated by averaging these four values.

Conceptually:

```text
Overall Rating =
(Service + Quality + Cleanliness + Pricing) / 4
```

The reusable trait provides:

```php
averageRating($place)
```

which returns:

```php
[
    'total' => $total,
    'service_rating' => $avg->service_rating,
    'quality_rating' => $avg->quality_rating,
    'cleanliness_rating' => $avg->cleanliness_rating,
    'pricing_rating' => $avg->pricing_rating
]
```

---

# 📝 Reviews

Authenticated users can submit reviews for places.

A review contains:

- Review text
- Service rating
- Quality rating
- Cleanliness rating
- Pricing rating
- User
- Place

---

## 🔒 One Review Per User

The application prevents the same user from reviewing the same place more than once.

Before creating a review, the controller checks:

```php
if ($request->user()->reviews()->wherePlace_id($request->place_id)->exists()) {
    return redirect(url()->previous() . '#review-div')
        ->with('fail', 'لقد قييمت المكان من قبل');
}
```

This prevents duplicate reviews from the same user for the same place.

---

# ✅ Review Validation

Reviews are validated using a dedicated Laravel Form Request:

```text
ReviewFormRequest
```

The request requires authentication:

```php
public function authorize(): bool
{
    return auth()->check();
}
```

The review content must also contain at least five characters:

```php
'review' => 'required|min:5'
```

Validation messages are customized in Arabic.

Example:

```text
حقل المراجعة فارغ
```

and:

```text
محتوى المراجعة قصير جدًا
```

---

# ❤️ Review Likes

Users can like reviews.

The application uses a many-to-many relationship between users and reviews through the `likes` pivot table.

Users can toggle their like:

```php
$request->user()->likes()->toggle($request->review_id);
```

After the operation, the application returns the current number of likes for the review.

---

# 🔖 Bookmarks

Users can bookmark places that they want to save for later.

Bookmarking uses a many-to-many relationship between users and places.

The bookmark action uses:

```php
auth()->user()->bookmarks()->toggle($place_id);
```

This means the same endpoint can:

- Add a bookmark
- Remove an existing bookmark

---

## 📚 User Bookmarks

Users can access their saved places through:

```text
/bookmarks
```

The application retrieves the authenticated user's bookmarked places:

```php
$bookmarks = auth()->user()->bookmarks;
```

and displays them through:

```text
user_bookmarks
```

---

# 🔍 Search

The application provides a search system for finding places.

Search supports:

- Address search
- Category filtering

The search logic is implemented through a custom Eloquent query scope.

---

## Search Scope

The `Place` model contains:

```php
public function scopeSearch($query, $request)
{
    if ($request->category) {
        $query->whereCategory_id($request->category);
    }

    if ($request->address) {
        $query->where(
            'address',
            'LIKE',
            '%' . $request->address . '%'
        );
    }

    return $query;
}
```

This allows the controller to simply call:

```php
Place::search($request)->get();
```

---

# ⚡ Address Autocomplete

The application also provides address autocomplete functionality.

The endpoint checks the submitted address and searches places using a partial match:

```php
Place::where(
    'address',
    'LIKE',
    "%$input%"
)->get();
```

The matching addresses are returned as an HTML list.

---

# 🗂️ Categories

Places belong to categories.

The relationship is:

```text
Category
   │
   └── hasMany Places
```

The `Category` model contains:

```php
public function places()
{
    return $this->hasMany(Place::class);
}
```

Users can browse places belonging to a specific category.

Category URLs use the category slug:

```text
/{category:slug}
```

---

# 🧭 Place URLs

Places are displayed using a route containing both the place and its slug:

```text
/{place}/{slug}
```

The place details controller retrieves the requested place and loads its related reviews.

---

# 📍 Geographic Data

Places contain geographic coordinates:

```text
Latitude
Longitude
```

Example seeded data:

```text
Latitude: 21.3924513
Longitude: 39.8226124
```

This makes the project suitable for integrating maps and location-based functionality.

---

# 🖼️ Place Images

Place images are stored and exposed through an Eloquent accessor.

The `Place` model contains:

```php
public function getImageAttribute($image)
{
    return asset('storage/images/' . $image);
}
```

This allows the application to automatically convert the stored image filename into a public asset URL.

---

# 👤 User Relationships

Users can interact with multiple application resources.

A user can have:

```text
User
├── Reviews
├── Likes
└── Bookmarks
```

---

## User → Reviews

```php
public function reviews()
{
    return $this->hasMany(Review::class);
}
```

---

## User → Likes

```php
public function likes()
{
    return $this->belongsToMany(Review::class, 'likes');
}
```

---

## User → Bookmarks

```php
public function bookmarks()
{
    return $this->belongsToMany(Place::class, 'bookmarks');
}
```

---

# 🏢 Place Relationships

A place can have:

```text
Place
├── Reviews
├── User
└── Bookmarks
```

The `Place` model contains:

```php
public function reviews()
{
    return $this->hasMany(Review::class);
}
```

and:

```php
public function user()
{
    return $this->belongsTo(User::class);
}
```

Bookmarks are handled through the many-to-many relationship on the user side.

---

# ⭐ Review Relationships

A review belongs to a user:

```php
public function user()
{
    return $this->belongsTo(User::class);
}
```

Reviews are associated with places through:

```text
place_id
```

Reviews also support likes through a pivot relationship.

---

# 🧩 Eloquent Relationships

The main relationships can be represented as:

```text
User
│
├── hasMany Reviews
│
├── belongsToMany Reviews
│   └── Likes
│
└── belongsToMany Places
    └── Bookmarks


Place
│
├── hasMany Reviews
│
└── belongsTo User


Category
│
└── hasMany Places


Review
│
├── belongsTo User
│
└── belongsToMany Reviews
    └── Likes
```

---

# 🔄 Application Workflow

## Browsing Places

```text
User
 ↓
Homepage
 ↓
Popular Places
 ↓
Select Place
 ↓
Place Details
 ↓
Reviews & Ratings
```

---

## Searching

```text
User
 ↓
Search
 ↓
Enter Address
 ↓
Optional Category Filter
 ↓
Place::search()
 ↓
Search Results
```

---

## Reviewing a Place

```text
Authenticated User
        ↓
Open Place
        ↓
Submit Review
        ↓
ReviewFormRequest
        ↓
Validate Review
        ↓
Check Existing Review
        ↓
Create Review
        ↓
Redirect to Place
```

---

## Bookmarking

```text
User
 ↓
Open Place
 ↓
Bookmark
 ↓
Toggle Relationship
 ↓
Saved Place
```

---

## Liking a Review

```text
User
 ↓
Open Review
 ↓
Like Review
 ↓
Authorization Check
 ↓
Toggle Like
 ↓
Return Like Count
```

---

# 🔐 Authentication

The application uses Laravel Jetstream for authentication and account management.

The project includes:

- Registration
- Login
- Logout
- Password management
- Email verification
- Profile management
- Profile photos
- Two-factor authentication
- Session management
- Sanctum authentication

Authenticated dashboard routes are protected using:

```php
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])
```

---

# 🛡️ Authorization

The project uses Laravel authorization to control user actions.

For example, review likes are checked before the operation is performed:

```php
if ($request->user()->can('like-review', $review)) {
    // Like review
}
```

This allows the application to restrict actions according to the defined authorization rules.

---

# 📩 Contact & Reports

Users can submit reports or contact requests through:

```text
/report
```

The application provides:

```text
GET  /report/create
POST /report
```

The submitted data is stored using the `Report` model.

The application then sends the report through a Laravel Mail class:

```php
\Mail::send(new SendReport($data));
```

---

# ✉️ Email

The project uses Laravel Mail functionality for sending reports.

The mail implementation uses:

```text
SendReport
```

This provides a dedicated way of handling contact/report emails.

---

# 🧱 MVC Architecture

The application follows Laravel's MVC architecture.

```text
User Request
      ↓
Route
      ↓
Controller
      ↓
Model / Eloquent
      ↓
Database
      ↓
View
      ↓
Response
```

The project separates responsibilities through:

- Models
- Controllers
- Form Requests
- Traits
- Policies / authorization
- Mail classes
- Views
- Routes

---

# 🧠 Reusable Trait

The project contains a custom trait:

```text
RateableTrait
```

Location:

```text
app/Traits/RateableTrait.php
```

The trait contains the rating calculation logic:

```php
public function averageRating($place)
```

This keeps rating-related business logic reusable and separate from the controller.

---

# 📊 Rating Architecture

The rating system works using four independent values:

```text
service_rating
quality_rating
cleanliness_rating
pricing_rating
```

The system calculates:

```text
Service Average
Quality Average
Cleanliness Average
Pricing Average
        ↓
Overall Average
```

This allows the application to provide a more detailed evaluation of each place.

---

# 🧪 Database Seeding

The project includes database seeders for development and testing.

The main `DatabaseSeeder` calls:

```php
$this->call([
    PlaceSeeder::class,
    CategorySeeder::class,
    UserSeeder::class,
    ReviewSeeder::class,
    RoleSeeder::class
]);
```

This allows the application to be populated with sample data quickly.

---

# 🌱 Seeded Places

The project includes sample places such as:

```text
سوق مكة
مطعم مكة
صيدلية مكة
صيدلية الشهد
```

The seeded places include information such as:

- Name
- Slug
- Image
- Category
- Overview
- Address
- User
- Latitude
- Longitude
- View count

---

# 🌱 Seeded Reviews

Sample reviews demonstrate the multi-dimensional rating system.

Example:

```php
Review::create([
    'review' => 'ممتاز جدًا',
    'service_rating' => 5,
    'quality_rating' => 5,
    'cleanliness_rating' => 5,
    'pricing_rating' => 5,
    'place_id' => 1,
    'user_id' => 1,
]);
```

---

# 🗺️ Example Data Structure

A place can be represented as:

```text
Place
├── ID
├── Name
├── Slug
├── Image
├── Category ID
├── Overview
├── Address
├── User ID
├── Latitude
├── Longitude
└── View Count
```

A review can be represented as:

```text
Review
├── ID
├── Review
├── Service Rating
├── Quality Rating
├── Cleanliness Rating
├── Pricing Rating
├── Place ID
└── User ID
```

---

# 🌐 Routes

## Main Routes

```text
GET  /
GET  /search
POST /search
```

---

## Bookmarks

```text
GET /bookmark/{place_id}
GET /bookmarks
```

---

## Categories

```text
GET /{category:slug}
```

---

## Places

```text
GET /place/create
GET /{place}/{slug}
```

---

## Reviews

```text
POST /review
```

---

## Likes

```text
POST /like
```

---

## Reports

```text
GET  /report/create
POST /report
```

---

## Dashboard

```text
GET /dashboard
```

The dashboard requires:

```text
Authentication
Email Verification
Jetstream Session
```

---

# 🔌 API

The project includes Laravel Sanctum API authentication.

The default authenticated API endpoint is:

```text
GET /api/user
```

It is protected by:

```php
Route::middleware('auth:sanctum')
```

The endpoint returns the authenticated user.

---

# 🎨 Frontend

The project uses Laravel's modern frontend tooling.

Frontend technologies include:

- Blade
- Tailwind CSS
- Alpine.js
- Axios
- Vite
- Laravel Vite Plugin
- Livewire

---

# 🛠️ Tech Stack

## Backend

- PHP 8.1+
- Laravel 10
- Laravel Jetstream
- Laravel Sanctum
- Laravel Livewire
- Laravel Eloquent
- Laravel Form Requests
- Laravel Mail
- Laravel Authentication
- Laravel Authorization

## Frontend

- Blade
- Tailwind CSS
- Alpine.js
- Axios
- Vite

## Database

- MySQL / Compatible relational database
- Laravel Migrations
- Eloquent ORM
- Pivot tables
- Database Seeders

## Development Tools

- Composer
- NPM
- Git
- GitHub
- Laravel Sail
- Laravel Pint
- PHPUnit
- Laravel Debugbar
- VS Code

---

# 📦 Main Dependencies

The project uses the following major packages:

```text
laravel/framework
laravel/jetstream
laravel/sanctum
livewire/livewire
guzzlehttp/guzzle
laravel/tinker
```

Development dependencies include:

```text
barryvdh/laravel-debugbar
fakerphp/faker
laravel/pint
laravel/sail
mockery/mockery
nunomaduro/collision
phpunit/phpunit
spatie/laravel-ignition
```

---

# 🧰 Laravel Debugging

The project includes Laravel Debugbar as a development dependency:

```text
barryvdh/laravel-debugbar
```

This can be used during development to inspect:

- Queries
- Routes
- Requests
- Views
- Performance information

---

# 📄 Form Request Architecture

Review validation is separated into:

```text
app/Http/Requests/ReviewFormRequest.php
```

Instead of placing all validation rules directly inside the controller, the project uses a dedicated Form Request.

This provides:

```text
Controller
    ↓
ReviewFormRequest
    ↓
Authorization
    ↓
Validation
    ↓
Controller Logic
```

---

# 🔒 Data Protection

The `User` model protects sensitive authentication information using hidden attributes:

```php
protected $hidden = [
    'password',
    'remember_token',
    'two_factor_recovery_codes',
    'two_factor_secret',
];
```

This prevents sensitive authentication information from being exposed when the model is serialized.

---

# 🏗️ Project Structure

Important project directories include:

```text
app/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Mail/
├── Models/
├── Policies/
└── Traits/

database/
├── factories/
├── migrations/
└── seeders/

resources/
├── css/
├── js/
└── views/

routes/
├── api.php
├── channels.php
├── console.php
└── web.php

public/
└── ...
```

---

# 🚀 Installation

## 1. Clone the Repository

```bash
git clone https://github.com/MoamenRamy/Maps_Rating.git
```

Navigate into the project:

```bash
cd Maps_Rating
```

---

## 2. Install Composer Dependencies

```bash
composer install
```

---

## 3. Install NPM Dependencies

```bash
npm install
```

---

## 4. Create Environment File

Copy:

```text
.env.example
```

to:

```text
.env
```

On Linux/macOS:

```bash
cp .env.example .env
```

On Windows, copy the file manually or use:

```bash
copy .env.example .env
```

---

## 5. Generate Application Key

```bash
php artisan key:generate
```

---

## 6. Configure Database

Update the database settings inside `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=maps_rating
DB_USERNAME=root
DB_PASSWORD=
```

---

## 7. Run Migrations

```bash
php artisan migrate
```

---

## 8. Seed the Database

```bash
php artisan db:seed
```

Or:

```bash
php artisan migrate --seed
```

---

## 9. Create Storage Link

```bash
php artisan storage:link
```

---

## 10. Start Laravel

```bash
php artisan serve
```

The application will be available at:

```text
http://127.0.0.1:8000
```

---

## 11. Start Vite

During development:

```bash
npm run dev
```

For production assets:

```bash
npm run build
```

---

# ⚙️ Useful Artisan Commands

Start the development server:

```bash
php artisan serve
```

Run migrations:

```bash
php artisan migrate
```

Run migrations and seed the database:

```bash
php artisan migrate --seed
```

Run database seeders:

```bash
php artisan db:seed
```

Create storage link:

```bash
php artisan storage:link
```

Clear Laravel caches:

```bash
php artisan optimize:clear
```

Run tests:

```bash
php artisan test
```

---

# 🧪 Testing

The project includes PHPUnit through Laravel's testing infrastructure.

Run the test suite with:

```bash
php artisan test
```

Testing can be expanded to cover:

- Place discovery
- Search
- Review validation
- Duplicate review prevention
- Bookmark functionality
- Review likes
- Authorization
- Authentication
- Category filtering

---

# 🔄 Example User Journey

A typical user interaction looks like:

```text
Visit Website
      ↓
Browse Popular Places
      ↓
Search / Filter
      ↓
Select Place
      ↓
View Details
      ↓
Read Reviews
      ↓
Bookmark Place
      ↓
Submit Review
      ↓
Rate Service
      ↓
Rate Quality
      ↓
Rate Cleanliness
      ↓
Rate Pricing
      ↓
View Overall Rating
```

---

# 🎯 Project Goals

This project was built to demonstrate practical Laravel backend development skills through a real-world place rating platform.

It demonstrates experience with:

- Laravel MVC
- PHP OOP
- Eloquent ORM
- Model relationships
- Many-to-many relationships
- Pivot tables
- Query scopes
- Form Requests
- Authentication
- Authorization
- Policies
- Traits
- Mail
- Search
- CRUD architecture
- Database seeders
- Validation
- File/image handling
- User interactions
- Bookmarks
- Likes
- Rating systems
- Laravel Sanctum
- Jetstream
- Livewire
- Tailwind CSS
- Vite

---

# 💡 Key Laravel Concepts Demonstrated

## Eloquent ORM

The project uses Eloquent for interacting with places, users, reviews, categories, bookmarks, and likes.

---

## Query Scopes

Custom search logic is encapsulated inside the `Place` model:

```php
Place::search($request)->get();
```

---

## Form Requests

Review validation is handled through:

```text
ReviewFormRequest
```

---

## Traits

Rating calculations are extracted into:

```text
RateableTrait
```

---

## Relationships

The project demonstrates:

```text
One-to-Many
Many-to-Many
```

relationships.

---

## Authentication

Laravel Jetstream and Sanctum are used to handle authenticated users and protected routes.

---

## Authorization

Laravel authorization is used to control review interactions such as liking reviews.

---

# 🚀 Possible Future Improvements

The current application can be extended with:

- Interactive Google Maps integration
- Distance-based place search
- Nearby places
- Advanced map markers
- Location-based filtering
- Sorting by rating
- Sorting by distance
- Rating distribution charts
- Review editing
- Review deletion
- Review pagination
- Review moderation
- Place owner accounts
- Place management dashboard
- Admin dashboard
- Advanced role and permission management
- REST API for places
- API Resources
- API pagination
- Social authentication
- Push notifications
- Advanced search
- Elasticsearch / Laravel Scout
- Redis caching
- Image optimization
- Automated testing
- Docker deployment
- CI/CD
- Production deployment

---

# 🧑‍💻 Author

## Moamen Ramy Rahmo

PHP & Laravel Backend Developer

GitHub:

https://github.com/MoamenRamy

LinkedIn:

https://www.linkedin.com/in/moamen-ramy-492a8b212/

---

# 📄 License

This project is open-sourced software licensed under the MIT License.

---

# ⭐ Support

If you find this project useful or want to explore more Laravel projects, check out my GitHub profile:

https://github.com/MoamenRamy
