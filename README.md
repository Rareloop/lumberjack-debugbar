# Lumberjack Debug Bar

## Installation
```
composer require rareloop/lumberjack-debugbar
```

Once installed, register the Service Provider in config/app.php within your theme:

```php
'providers' => [
    ...

    Rareloop\Lumberjack\DebugBar\DebugBarServiceProvider::class,

    ...
],
```

## Usage
The DebugBar will only register when the `app.debug` config setting is `true`, which means it should never show on production environments.

### Messages
In order to write to the Messages log you can use the `DebugBar` facade:

```php
use Rareloop\Lumberjack\DebugBar\Facades\DebugBar;

DebugBar::info('message');
DebugBar::warning(123);
DebugBar::error(['foo' => 'bar']);
```

### Timeline
In order to write to the Timeline log you can use the `DebugBar` facade:

```php
DebugBar::startMeasure('api-fetch', 'Time for API request');
DebugBar::stopMeasure('api-fetch');
DebugBar::addMeasure('now', microtime(true), microtime(true) + 10000);
DebugBar::measure('My long operation', function() {
    // Do something…
});
```

### Logs
The logs tab will mirror anything that has been output to the Lumberjack logs.
