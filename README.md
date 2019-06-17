[![Build Status](https://scrutinizer-ci.com/g/vmpublishing/psr15-middleware-request-logger/badges/build.png?b=master)](https://scrutinizer-ci.com/g/vmpublishing/psr15-middleware-request-logger/build-status/master)
[![Code Coverage](https://scrutinizer-ci.com/g/vmpublishing/psr15-middleware-request-logger/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/vmpublishing/psr15-middleware-request-logger/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/vmpublishing/psr15-middleware-request-logger/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/vmpublishing/psr15-middleware-request-logger/?branch=master)

**WHAT**

PSR-15 request logging middleware with as low dependencies as possible.

for maintenance reasons we like a slim, configurable logging middleware, that handles all the logging for us.
- every request
- every response
- every error
- every exception

This will rethrow any errors. error handling should be done on layers further out than this middleware.

**INSTALL**

To install simply use
`composer require vmpublishing/psr15-middleware-request-logger:*@stable`

**USE**

This is a fancy wrapper for psr/log. So you will need to setup your own logger in any way you wish.
After that, you can just simply create the middleware and use it

```
use VM\RequestLogger\Services\StandardLogFormatter;
use VM\RequestLogger\Middlewares\RequestLogger;

// given a logger in $logger, build the StandardLogFormatter (or write your own log formatter, using the interface)
$formatter = new StandardLogFormatter($logger);
$middleware = new RequestLogger($formatter);

// and for slim, given $app
$app->add($middleware);

// or just add it on the routes you want it on
```

The default setting is notice, and it won't log anything below that, whatever you set as log level in your logger.
This is for optimization reasons. The formatter won't build any string or do any array magic, before the log level is sufficient.

If you want to log with a different level than the default, just pass that in:

```
use VM\RequestLogger\Services\LogLevel;
use VM\RequestLogger\Services\StandardLogFormatter;

$logLevel = new LogLevel(LogLevel::LEVEL_ERROR);
$formatter = new StandardLogFormatter($logger, $logLevel);
// ...
```

The StandardLogFormatter can be configured as for which log level to send at which message.
just pass in an array to override the defaults. ie:

```
use VM\RequestLogger\Services\StandardLogFormatter;
use VM\RequestLogger\Services\LogLevel

$logLevel = new LogLevel(LogLevel::LEVEL_ERROR);
$logLevelRequestUri = new LogLevel(LogLevel::LEVEL_DEBUG);
$logLevelMappings = [StandardLogFormatter::MESSAGE_REQUEST_URI => $logLevelRequestUri];
$formatter = new StandardLogFormatter($logger, $logLevel, $logLevelMappings);
```

report any issues/feature requests on github.
enjoy!
