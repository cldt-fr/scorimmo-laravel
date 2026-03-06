# Laravel Scorimmo

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cldt/laravel-scorimmo.svg?style=flat-square)](https://packagist.org/packages/cldt/laravel-scorimmo)
[![Total Downloads](https://img.shields.io/packagist/dt/cldt/laravel-scorimmo.svg?style=flat-square)](https://packagist.org/packages/cldt/laravel-scorimmo)

A Laravel wrapper for the [Scorimmo API](https://pro.scorimmo.com/api/doc) — a lead management platform for real estate professionals.

This package provides a fluent API client and a webhook handler built on top of [spatie/laravel-webhook-client](https://github.com/spatie/laravel-webhook-client).

## Installation

Install the package via Composer:

```bash
composer require cldt/laravel-scorimmo
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag="scorimmo-config"
```

If you plan to use webhooks, publish and run the migration:

```bash
php artisan vendor:publish --tag="scorimmo-migrations"
php artisan migrate
```

## Configuration

Add the following environment variables to your `.env` file:

```env
# API credentials
SCORIMMO_ENDPOINT=https://pro.scorimmo.com/
SCORIMMO_USERNAME=your-username
SCORIMMO_PASSWORD=your-password

# Or provide a token directly (bypasses login)
SCORIMMO_API_TOKEN=your-jwt-token

# Webhooks
SCORIMMO_WEBHOOK_PATH=/webhook/scorimmo
SCORIMMO_WEBHOOK_VERIFY_TOKEN=true
SCORIMMO_WEBHOOK_TOKEN=your-webhook-secret
```

## Usage

### Authentication

Scorimmo uses JWT Bearer tokens. You can either set `SCORIMMO_API_TOKEN` in your `.env`, or authenticate programmatically:

```php
use CLDT\Scorimmo\ScorimmoFacade as Scorimmo;

// Option 1: Token set via SCORIMMO_API_TOKEN env variable — works out of the box
$leads = Scorimmo::lead()->list();

// Option 2: Login to obtain a token
$response = Scorimmo::auth()->login();
$token = $response->getData()['token'];

// Then use it
$scorimmo = Scorimmo::build()->withToken($token);
$leads = $scorimmo->lead()->list();
```

### Leads

#### List all leads

```php
$response = Scorimmo::lead()->list();
$response = Scorimmo::lead()->list([
    'limit' => 50,
    'page' => 2,
    'order' => 'desc',
    'orderby' => 'created_at',
    'search' => 'Dupont',
]);

$leads = $response->getData();

if ($response->isPaginated()) {
    $pagination = $response->getPagination();
    $pagination->getTotalItems();
    $pagination->getTotalPages();
    $pagination->getCurrentPage();
}
```

#### List leads by store

```php
$response = Scorimmo::lead()->listByStore(42, [
    'limit' => 20,
    'page' => 1,
]);
```

#### Get a single lead

```php
$response = Scorimmo::lead()->getOne(123);
$lead = $response->getData();
```

#### Create a lead

```php
$response = Scorimmo::lead()->create([
    'store_id' => 1,
    'properties' => [
        [
            'type' => 'Appartement',
            'price' => 250000,
            'area' => 65,
            'zipcode' => '75011',
            'city' => 'Paris',
        ],
    ],
    'customer' => [
        'last_name' => 'Dupont',
        'first_name' => 'Jean',
        'email' => 'jean.dupont@example.com',
        'phone' => '0612345678',
    ],
    'interest' => 'TRANSACTION',
    'origin' => 'Website',
    'seller' => [
        'email' => 'agent@example.com',
    ],
    'comment' => 'Interested in 2-bedroom apartments',
]);

if ($response->hasError()) {
    echo $response->getMessage();
}
```

#### Update a lead

```php
$response = Scorimmo::lead()->update(123, [
    'customer' => [
        'phone' => '0698765432',
    ],
    'comment' => 'Updated phone number',
]);
```

### Email

```php
$response = Scorimmo::email()->send([
    'to_email' => 'client@example.com',
    'subject' => 'Your property estimate',
    'html' => '<h1>Hello</h1><p>Here is your estimate.</p>',
    'last_name' => 'Dupont',
    'first_name' => 'Jean',
    'reference' => 'REF-123',
]);
```

### Web Callback

```php
$response = Scorimmo::webCallback()->launch([
    'key' => 'your-wcb-user-key',
    'number_to_call' => '0612345678',
]);
```

### Handling errors

Every API method returns a `ScorimmoApiResponse` object:

```php
$response = Scorimmo::lead()->getOne(999);

if ($response->hasError()) {
    $response->getStatusCode(); // 404
    $response->getMessage();    // Error message
    $response->getVerbose();    // Detailed error
} else {
    $response->getData();       // Lead data as array
    $response->count();         // Number of items
    $response->getFirst();      // First item (for lists)
    $response->getLast();       // Last item (for lists)
    $response->toArray();       // Full response as array
    $response->toString();      // JSON string of data
}
```

## Webhooks

The package automatically registers a `POST` route at the configured `webhook_path` (default: `/webhook/scorimmo`).

### Available events

| Event | Description |
|-------|-------------|
| `new_lead` | A new lead is created |
| `update_lead` | A lead is updated |
| `new_comment` | A comment is added to a lead |
| `new_reminder` | A reminder is created |
| `new_rdv` | An appointment is scheduled |
| `closure_lead` | A lead is closed |

### Using Laravel events

Every incoming webhook fires a Laravel event named `scorimmo::{event_name}`:

```php
// In EventServiceProvider
protected $listen = [
    'scorimmo::new_lead' => [
        \App\Listeners\HandleNewLead::class,
    ],
    'scorimmo::closure_lead' => [
        \App\Listeners\HandleLeadClosure::class,
    ],
];
```

```php
// App\Listeners\HandleNewLead.php
use CLDT\Scorimmo\Models\ScorimmoWebhookCall;

class HandleNewLead
{
    public function handle(ScorimmoWebhookCall $webhookCall)
    {
        $leadId = $webhookCall->payload('id');
        $customer = $webhookCall->payload('customer');
        // ...
    }
}
```

### Using jobs

You can also map events to queued jobs in `config/scorimmo.php`:

```php
'webhook_jobs' => [
    'new_lead' => \App\Jobs\Scorimmo\HandleNewLeadJob::class,
    'update_lead' => \App\Jobs\Scorimmo\HandleLeadUpdateJob::class,
    'closure_lead' => \App\Jobs\Scorimmo\HandleLeadClosureJob::class,

    // Use '*' to handle all events with a single job
    // '*' => \App\Jobs\Scorimmo\HandleAllWebhooksJob::class,
],
```

Your job should accept a `ScorimmoWebhookCall` instance:

```php
// App\Jobs\Scorimmo\HandleNewLeadJob.php
use CLDT\Scorimmo\Models\ScorimmoWebhookCall;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class HandleNewLeadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ScorimmoWebhookCall $webhookCall
    ) {}

    public function handle()
    {
        $payload = $this->webhookCall->payload();
        // Process the new lead...
    }
}
```

### Timestamp format

> **Note:** Timestamps in webhook payloads use the format `yyyy-MM-dd HH:mm:ss` (e.g. `2025-01-01 00:00:39`), **not** ISO 8601 with timezone. Keep this in mind when parsing date fields:
>
> ```php
> // Correct
> $date = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $webhookCall->payload('created_at'));
>
> // Incorrect — will fail or produce wrong results
> $date = \Carbon\Carbon::parse($webhookCall->payload('created_at'));
> ```

### Webhook payload examples

#### new_lead / update_lead

```json
{
    "event": "new_lead",
    "id": 123,
    "store_id": "1",
    "customer": {
        "title": "M.",
        "first_name": "Jean",
        "last_name": "Dupont",
        "email": "jean@example.com",
        "phone": "0612345678",
        "other_phone_number": null,
        "zip_code": "75011",
        "city": "Paris"
    },
    "seller": {
        "id": 42,
        "first_name": "Marie",
        "last_name": "Martin",
        "email": "marie@agency.com"
    },
    "properties": [
        {
            "id": 456,
            "type": "Appartement",
            "price": 250000,
            "area": 65,
            "nb_rooms": 3,
            "reference": "REF-789",
            "address": "10 rue de la Paix, 75002 Paris",
            "link": "https://example.com/property/456"
        }
    ],
    "comments": [
        {
            "content": "First contact by phone",
            "created_at": "2025-01-15 10:30:00"
        }
    ],
    "origin": "Website",
    "interest": "TRANSACTION",
    "status": "new",
    "purpose": "Achat",
    "contact_type": "email",
    "created_at": "2025-01-15 10:30:00"
}
```

#### new_comment

```json
{
    "event": "new_comment",
    "lead_id": 123,
    "external_lead_id": "EXT-456",
    "comment": "Client called back, very interested",
    "created_at": "2025-01-15 14:00:00"
}
```

#### new_reminder

```json
{
    "event": "new_reminder",
    "lead_id": 123,
    "external_lead_id": "EXT-456",
    "created_at": "2025-01-15 14:00:00",
    "start_time": "2025-01-20 09:00:00",
    "detail": "recontact",
    "comment": "Follow up on property visit"
}
```

#### new_rdv

```json
{
    "event": "new_rdv",
    "lead_id": 123,
    "external_lead_id": "EXT-456",
    "created_at": "2025-01-15 14:00:00",
    "start_time": "2025-01-20 10:00:00",
    "location": "10 rue de la Paix, 75002 Paris",
    "detail": "Visite",
    "comment": "Apartment visit with client"
}
```

#### closure_lead

```json
{
    "event": "closure_lead",
    "lead_id": 123,
    "external_lead_id": "EXT-456",
    "status": "SUCCESS",
    "close_reason": "Property sold"
}
```

### Pruning old webhook calls

The `ScorimmoWebhookCall` model uses Laravel's `MassPrunable` trait. Old records are automatically deleted based on the `webhook_prune_calls_after_days` config value (default: 30 days).

Make sure the Laravel pruning command is scheduled:

```php
// app/Console/Kernel.php
$schedule->command('model:prune', ['--model' => \CLDT\Scorimmo\Models\ScorimmoWebhookCall::class])->daily();
```

## API Reference

### Available methods

| Method | HTTP | Endpoint | Description |
|--------|------|----------|-------------|
| `auth()->login($username, $password)` | POST | `/api/login_check` | Obtain a JWT token |
| `lead()->list($params)` | GET | `/api/leads` | List leads (paginated) |
| `lead()->getOne($id)` | GET | `/api/lead/{id}` | Get a single lead |
| `lead()->create($data)` | POST | `/api/lead` | Create a lead |
| `lead()->update($id, $data)` | PUT | `/api/lead/{id}` | Update a lead |
| `lead()->listByStore($storeId, $params)` | GET | `/api/stores/{id}/leads` | List leads for a store |
| `email()->send($data)` | POST | `/api/email` | Send an email |
| `webCallback()->launch($data)` | POST | `/api/wcb` | Launch a web callback |

### Search parameters for leads

The `search` parameter supports keyed searches:

```php
// Global search
Scorimmo::lead()->list(['search' => 'Dupont']);

// Keyed searches
Scorimmo::lead()->list(['search' => ['email' => 'jean@example.com']]);
Scorimmo::lead()->list(['search' => ['status' => 'new']]);
Scorimmo::lead()->list(['search' => ['seller_id' => 42]]);
```

Available search keys: `id`, `type`, `customer_firstname`, `customer_lastname`, `email`, `phone`, `origin`, `interest`, `seller_firstname`, `seller_lastname`, `seller_id`, `created_at`, `status`, `closed_date`, `updated_at`, `anonymized_at`, `seller_present_on_creation`, `transfered`, `external_lead_id`, `external_customer_id`, `other_phone_number`, `reference`.

### ScorimmoApiResponse

| Method | Return type | Description |
|--------|-------------|-------------|
| `hasError()` | `bool` | Whether the request failed |
| `getStatusCode()` | `int` | HTTP status code |
| `getMessage()` | `string` | Error message |
| `getVerbose()` | `string` | Detailed error message |
| `getData()` | `array` | Response data |
| `getFirst()` | `?array` | First item in data |
| `getLast()` | `?array` | Last item in data |
| `count()` | `int` | Number of items |
| `isPaginated()` | `bool` | Whether response has pagination |
| `getPagination()` | `ScorimmoApiPagination` | Pagination details |
| `toArray()` | `array` | Full response as array |
| `toString()` | `string` | Data as JSON string |

### ScorimmoApiPagination

| Method | Return type | Description |
|--------|-------------|-------------|
| `getLimit()` | `int` | Items per page |
| `getCurrentPage()` | `int` | Current page number |
| `getTotalItems()` | `int` | Total number of items |
| `getTotalPages()` | `int` | Total number of pages |
| `getCurrentPageResults()` | `int` | Items on current page |
| `getNextPage()` | `?string` | Next page URL |
| `getPreviousPage()` | `?string` | Previous page URL |

## Requirements

- PHP >= 8.0
- Laravel >= 8.77

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
