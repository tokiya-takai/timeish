<?php

declare(strict_types=1);

namespace Timeish;

use DateTime;
use LogicException;

/**
 * @property int $hour
 * @property int $minute
 */
final class Timeish
{
    /**
     * @var int Minimum hour.
     */
    public const MIN_HOUR = 0;

    /**
     * @var int Minimum minute.
     */
    public const MIN_MINUTE = 0;

    /**
     * @var int Maximum minute.
     */
    public const MAX_MINUTE = 59;

    /**
     * @var string Hour and minute separator.
     */
    protected $separator;

    /**
     * @var string Format for print function.
     */
    protected $print_format;

    protected array $attributes = [
        'hour'   => 0,
        'minute' => 0,
    ];

    public function __construct(int $hour, int $minute, string $separator = ':')
    {
        $this->validate($hour, $minute);

        $this->initSeparator($separator);
        $this->initPrintFormat($separator);
        $this->setHour($hour);
        $this->setMinute($minute);
    }

    public function __get(string $name)
    {
        return $this->attributes[$name];
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function setHour(int $hour): void
    {
        $this->attributes['hour'] = $hour;
    }

    public function setMinute(int $minute): void
    {
        $this->attributes['minute'] = $minute;
    }

    /**
     * Split any given time into hours and minutes.
     *
     * @var string
     *
     * @return array{
     *   hour:   int
     *   minute: int
     * }
     *
     * @param string $time
     */
    public static function explodeAny(string $separator, string $time): array
    {
        $parsed_time = array_combine(
            ['hour', 'minute'],
            explode($separator, $time)
        );

        return [
            'hour'   => (int) $parsed_time['hour'],
            'minute' => (int) $parsed_time['minute'],
        ];
    }

    /**
     * Convert to string.
     *
     * @return string
     */
    public function toString(): string
    {
        return sprintf($this->print_format, $this->hour, $this->minute);
    }

    /**
     * Clone oneself.
     *
     * @return self
     */
    public function clone(): self
    {
        return clone $this;
    }

    /**
     * Convert to DateTime object.
     *
     * @return DateTime
     */
    public function toDateTime(): DateTime
    {
        $date_time = new DateTime();

        return $date_time->setTime($this->hour, $this->minute);
    }

    /**
     * Format by DateTime.
     *
     * @param string $format
     *
     * @return string
     */
    public function datetimeFormat($format): string
    {
        $date_time = new DateTime();
        $date_time->setTime($this->hour, $this->minute);

        return $date_time->format($format);
    }

    /**
     * Format with the specified separator.
     *
     * @param ?string $separator
     *
     * @return string
     */
    public function format(?string $separator = null): string
    {
        if ($separator === null) {
            $separator = $this->separator;
        }

        return sprintf('%02d' . $separator . '%02d', $this->hour, $this->minute);
    }

    /**
     * Split into hours and minutes.
     *
     * @return array{
     *   hour:   int
     *   minute: int
     * }
     */
    public function explode(): array
    {
        return self::explodeAny($this->separator, $this->toString());
    }

    /**
     * Get the minimum time.
     *
     * @return string
     */
    public function getMinTime(): string
    {
        return sprintf($this->print_format, self::MIN_HOUR, self::MIN_MINUTE);
    }

    /**
     * Get the minimum hours. (Always 0)
     *
     * @return int
     */
    public function getMinHour(): int
    {
        return self::MIN_HOUR;
    }

    /**
     * Get the minimum minutes. (Always 0)
     *
     * @return int
     */
    public function getMinMinute(): int
    {
        return self::MIN_MINUTE;
    }

    /**
     * Get the maximum minutes.
     *
     * @return int
     */
    public function getMaxMinute(): int
    {
        return self::MAX_MINUTE;
    }

    /**
     * Add a hour. If the maximum hour is exceeded, it becomes 0.
     *
     * @return self
     */
    public function addHour(): self
    {
        $this->addHours(1);

        return $this;
    }

    /**
     * Add hours.
     *
     * @param int $hours
     *
     * @return self
     */
    public function addHours(int $hours): self
    {
        if ($hours < 0) {
            return $this->subHours(abs($hours));
        }

        $self_hour = $this->hour;
        $this->setHour($self_hour + $hours);

        return $this;
    }

    /**
     * Add a minute.
     *
     * @return self
     */
    public function addMinute(): self
    {
        $this->addMinutes(1);

        return $this;
    }

    /**
     * Add minutes.
     *
     * @param int $minutes
     *
     * @return self
     */
    public function addMinutes(int $minutes): self
    {
        if ($minutes < 0) {
            return $this->subMinutes(abs($minutes));
        }

        $self_minute = $this->minute;
        if ($this->isOverMaxMinute($this->minute + $minutes)) {
            $additional_hours = (int) floor($minutes / 60);
            $this->addHours($additional_hours);

            $additional_minutes = $minutes % 60;
            if ($this->isOverMaxMinute($self_minute + $additional_minutes)) {
                $over_hours = (int) floor(($self_minute + $additional_minutes) / 60);
                $this->addHours($over_hours);
                $self_minute = ($self_minute + $additional_minutes) % 60;
            } else {
                $self_minute += $additional_minutes;
            }
        } else {
            $self_minute = $self_minute + $minutes;
        }

        $this->setMinute($self_minute);

        return $this;
    }

    /**
     * Sub a hour.
     *
     * @return self
     */
    public function subHour(): self
    {
        $this->subHours(1);

        return $this;
    }

    /**
     * Sub hours.
     *
     * @param int $hours
     *
     * @throws LogicException
     *
     * @return self
     */
    public function subHours(int $hours): self
    {
        if ($hours < 0) {
            return $this->addHours(abs($hours));
        }

        $self_hour = $this->hour;
        if ($this->isBelowMinHour($self_hour - $hours)) {
            throw new LogicException('Invalid Argument Error: Hour cannot be less than ' . self::MIN_HOUR . '.');
        }
        $this->setHour($self_hour - $hours);

        return $this;
    }

    /**
     * Sub a minute.
     *
     * @return self
     */
    public function subMinute(): self
    {
        $this->subMinutes(1);

        return $this;
    }

    /**
     * Sub minutes.
     *
     * @param int $minutes
     *
     * @return self
     */
    public function subMinutes(int $minutes): self
    {
        if ($minutes < 0) {
            return $this->addMinutes(abs($minutes));
        }

        $self_minute = $this->minute;
        if ($this->isBelowMinMinute($self_minute - $minutes)) {
            $subtraction_hours = (int) floor($minutes / 60);
            $this->subHours($subtraction_hours);

            $subtraction_minutes = $minutes % 60;
            if ($this->isBelowMinMinute($self_minute - $subtraction_minutes)) {
                $below_hours = abs((int) floor(($self_minute - $subtraction_minutes) / 60));
                $this->subHours($below_hours);
                $self_minute = (self::MAX_MINUTE + 1) - (abs($self_minute - $subtraction_minutes) % 60);
            } else {
                $self_minute -= $subtraction_minutes;
            }
        } else {
            $self_minute -= $minutes;
        }

        $this->setMinute($self_minute);

        return $this;
    }

    /**
     * Is the hour below the minimum hour?
     *
     * @param int $hour
     *
     * @return bool
     */
    public function isBelowMinHour(int $hour): bool
    {
        return $hour < self::MIN_HOUR;
    }

    /**
     * Is the minute below the minimum minute?
     *
     * @param int $minute
     *
     * @return bool
     */
    public function isBelowMinMinute(int $minute): bool
    {
        return $minute < self::MIN_MINUTE;
    }

    /**
     * Is the minute over the minimum minute?
     *
     * @param int $minute
     *
     * @return bool
     */
    public function isOverMaxMinute(int $minute): bool
    {
        return $minute > self::MAX_MINUTE;
    }

    /**
     * Earlier than argument time?
     *
     * @param Timeish $comparison_time
     *
     * @return bool
     */
    public function isLessThan(self $comparison_time): bool
    {
        return $this->datetimeFormat($this->getFormat()) < $comparison_time->datetimeFormat($this->getFormat());
    }

    /**
     * Is it before the time of the argument?
     *
     * @param Timeish $comparison_time
     *
     * @return bool
     */
    public function isLessThanEqual(self $comparison_time): bool
    {
        return $this->datetimeFormat($this->getFormat()) <= $comparison_time->datetimeFormat($this->getFormat());
    }

    /**
     * Late than argument time?
     *
     * @param Timeish $comparison_time
     *
     * @return bool
     */
    public function isGreaterThan(self $comparison_time): bool
    {
        return $this->datetimeFormat($this->getFormat()) > $comparison_time->datetimeFormat($this->getFormat());
    }

    /**
     * Is it after the time of the argument?
     *
     * @param Timeish $comparison_time
     *
     * @return bool
     */
    public function isGreaterThanEqual(self $comparison_time): bool
    {
        return $this->datetimeFormat($this->getFormat()) >= $comparison_time->datetimeFormat($this->getFormat());
    }

    /**
     * Validate construct arguments.
     *
     * @param int $hour
     * @param int $minute
     *
     * @return void
     */
    protected function validate(int $hour, int $minute): void
    {
        if (self::MIN_HOUR > $hour) {
            throw new LogicException('Invalid Argument Error: Hour must be at least ' . self::MIN_HOUR . '.');
        }
        if ((self::MAX_MINUTE < $minute) || (self::MIN_MINUTE > $minute)) {
            throw new LogicException('Invalid Argument Error: Minutes must be between ' . self::MIN_MINUTE . ' and ' . self::MAX_MINUTE . '.');
        }
    }

    /**
     * Initialize separator.
     *
     * @param string $separator
     *
     * @return void
     */
    protected function initSeparator(string $separator): void
    {
        $this->separator = $separator;
    }

    /**
     * Initialize print format.
     *
     * @return void
     */
    protected function initPrintFormat(string $separator): void
    {
        $this->print_format = '%02d' . $separator . '%02d';
    }

    /**
     * Get Hour and minute format.
     *
     * @return string
     */
    protected function getFormat(): string
    {
        return 'H' . $this->separator . 'i';
    }
}
