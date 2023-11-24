<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Timeish\Timeish;

class TimeishTest extends TestCase
{
    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_規定の値でインスタンス化できること($hour, $minute)
    {
        $timeish = new Timeish($hour, $minute);

        $this->assertInstanceOf(Timeish::class, $timeish);
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::invalidData
     */
    public function test_規定の値でインスタンス化できないこと($hour, $minute)
    {
        $this->expectException(LogicException::class);

        new Timeish($hour, $minute);
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::invalidTypeData
     */
    public function test_規定の型違いの値でインスタンス化できないこと($hour, $minute)
    {
        $this->expectException(TypeError::class);

        new Timeish($hour, $minute);
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_時が取得できること($hour, $minute)
    {
        $timeish = new Timeish($hour, $minute);
        $this->assertSame($hour, $timeish->hour);
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_分が取得できること($hour, $minute)
    {
        $timeish = new Timeish($hour, $minute);
        $this->assertSame($minute, $timeish->minute);
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_時をセットできること($hour, $minute)
    {
        $timeish = new Timeish(0, 0);

        $timeish->setHour($hour);
        $this->assertSame($hour, $timeish->hour);
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_分をセットできること($hour, $minute)
    {
        $timeish = new Timeish(0, 0);

        $timeish->setMinute($minute);
        $this->assertSame($minute, $timeish->minute);
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_文字列に変換できること($hour, $minute)
    {
        $timeish = (new Timeish($hour, $minute))->toString();
        $this->assertSame($this->print($hour, $minute), $timeish);
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_文字列として呼び出せること($hour, $minute)
    {
        $timeish = (string) (new Timeish($hour, $minute));
        $this->assertSame($this->print($hour, $minute), $timeish);
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_任意の時間を時と分に分割できること($hour, $minute)
    {
        $actual  = ['hour' => $hour, 'minute' => $minute];
        $timeish = Timeish::explodeAny(':', $this->print($hour, $minute));
        $this->assertSame($timeish, $actual);
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_クローンできること($hour, $minute)
    {
        $timeish = new Timeish($hour, $minute);
        $cloned  = $timeish->clone();
        $this->assertInstanceOf(Timeish::class, $cloned);
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_クローンを変更してもクローン元に影響しないこと($hour, $minute)
    {
        $timeish = new Timeish($hour, $minute);
        $cloned  = $timeish->clone();
        $this->assertInstanceOf(Timeish::class, $cloned);

        $cloned->addHour();
        $this->assertSame($hour, $timeish->hour);
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_DateTimeに変換可能なこと($hour, $minute)
    {
        $timeish  = new Timeish($hour, $minute);
        $datetime = $timeish->toDateTime();

        $this->assertInstanceOf(DateTime::class, $datetime);
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_フォーマットの値が期待する値と等しいこと($hour, $minute)
    {
        $timeish = new Timeish($hour, $minute);

        $expected_hour = $hour < 24 ? $hour : (int) floor($hour % 24);
        $timeish->setHour($hour);
        $this->assertSame($this->print($expected_hour, $minute), $timeish->datetimeFormat('H:i'));
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::separators
     */
    public function test_指定したセパレーターでフォーマットできること($separator)
    {
        $timeish = new Timeish(0, 0);
        $this->assertSame($this->print(0, 0, $separator), $timeish->format($separator));
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_分割できること($hour, $minute)
    {
        $actual  = ['hour' => $hour, 'minute' => $minute];
        $timeish = (new Timeish($hour, $minute))->explode();

        $this->assertSame($actual, $timeish);
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_1時間足せること($hour, $minute)
    {
        $timeish = new Timeish($hour, $minute);
        $timeish->addHour();

        $this->assertSame($this->print($hour + 1, $minute), $timeish->toString());
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_2時間以上足せること($hour, $minute)
    {
        $timeish = new Timeish($hour, $minute);
        $timeish->addHours(2);

        $this->assertSame($this->print($hour + 2, $minute), $timeish->toString());
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_1分足せること($hour, $minute)
    {
        $timeish = new Timeish($hour, $minute);
        $timeish->addMinute();

        if ($minute + 1 === 60) {
            $hour   += 1;
            $minute  = -1; // 59分 + 1分 => 0分のため、+1 した時に0になるように調整
        }

        $this->assertSame($this->print($hour, $minute + 1), $timeish->toString());
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_2分以上足せること($hour, $minute)
    {
        $timeish = new Timeish($hour, $minute);
        $timeish->addMinutes(2);

        if ($minute + 2 >= 60) {
            $hour   += 1;
            $minute  = 60 - (60 + (60 - $minute));
        }

        $this->assertSame($this->print($hour, $minute + 2), $timeish->toString());
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_マイナス時間にならない場合は1時間引けてマイナスの場合はエラーになること($hour, $minute)
    {
        $timeish = new Timeish($hour, $minute);
        if ($hour >= 1) {
            $timeish->subHour();

            $this->assertSame($this->print($hour - 1, $minute), $timeish->toString());
        } else {
            $this->expectException(LogicException::class);

            $timeish->subHour();
        }
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_マイナス時間にならない場合は2時間以上引けてマイナスの場合はエラーになること($hour, $minute)
    {
        $timeish = new Timeish($hour, $minute);
        if ($hour >= 2) {
            $timeish->subHours(2);

            $this->assertSame($this->print($hour - 2, $minute), $timeish->toString());
        } else {
            $this->expectException(LogicException::class);

            $timeish->subHour();
        }
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_時がマイナスにならない場合は1分引けてマイナスの場合はエラーになること($hour, $minute)
    {
        $timeish = new Timeish($hour, $minute);
        if ($hour >= 1 || $minute >= 1) {
            $timeish->subMinute();

            if ($minute === 0) {
                $hour   -= 1;
                $minute  = 60;
            }

            $this->assertSame($this->print($hour, $minute - 1), $timeish->toString());
        } else {
            $this->expectException(LogicException::class);

            $timeish->subMinute();
        }
    }

    /**
     * @dataProvider \Tests\DataProviders\TimeishDataProvider::validData
     */
    public function test_時がマイナスにならない場合は2分以上引けてマイナスの場合はエラーになること($hour, $minute)
    {
        $timeish = new Timeish($hour, $minute);
        if ($hour >= 1 || $minute >= 2) {
            $timeish->subMinutes(2);

            if ($minute === 0) {
                $hour   -= 1;
                $minute  = 60;
            }

            $this->assertSame($this->print($hour, $minute - 2), $timeish->toString());
        } else {
            $this->expectException(LogicException::class);

            $timeish->subMinutes(2);
        }
    }

    public function test_規定の値より小さいかどうかが判別できること()
    {
        $timeish_small = new Timeish(10, 0);
        $timeish_big   = new Timeish(10, 1);

        $this->assertSame(true, $timeish_small->isLessThan($timeish_big));
    }

    public function test_規定の値より大きいかどうかが判別できること()
    {
        $timeish_small = new Timeish(10, 0);
        $timeish_big   = new Timeish(10, 1);

        $this->assertSame(true, $timeish_big->isGreaterThan($timeish_small));
    }

    public function test_規定の値より以下かどうかが判別できること()
    {
        $timeish_small = new Timeish(10, 0);
        $timeish_big   = new Timeish(10, 0);

        $this->assertSame(true, $timeish_small->isLessThanEqual($timeish_big));

        $timeish_big   = new Timeish(10, 1);
        $this->assertSame(true, $timeish_small->isLessThanEqual($timeish_big));
    }

    public function test_規定の値より以上かどうかが判別できること()
    {
        $timeish_small = new Timeish(10, 0);
        $timeish_big   = new Timeish(10, 0);

        $this->assertSame(true, $timeish_big->isGreaterThanEqual($timeish_small));

        $timeish_big   = new Timeish(10, 1);
        $this->assertSame(true, $timeish_big->isGreaterThanEqual($timeish_small));
    }

    public function print($hour, $minute, $separator = ':'): string
    {
        return sprintf('%02d' . $separator . '%02d', $hour, $minute);
    }
}
