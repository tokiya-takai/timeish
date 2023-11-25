# As if it were time.

Timeish is a library that can handle values beyond 24 as if they were time.

## Example
```php
$timeish = new Timeish(24, 0);

echo $timeish->toString(); // 24:00
```

The concept of time is not forgotten.
```php
$timeish = new Timeish(0, 59);
$timeish->addMinute();

echo $timeish->toString(); // 01:00
```

Timeish will make you happy.
