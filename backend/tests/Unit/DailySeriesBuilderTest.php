<?php

namespace Tests\Unit;

use App\Helpers\DailySeriesBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DailySeriesBuilderTest extends TestCase
{
    #[Test]
    public function it_zero_fills_days_with_no_recorded_activity(): void
    {
        Carbon::setTestNow('2026-08-05 12:00:00');

        $counts = new Collection([
            '2026-08-03' => 5,
            '2026-08-05' => 2,
        ]);

        $series = DailySeriesBuilder::fill($counts, 5);

        $this->assertCount(5, $series);
        $this->assertSame('2026-08-01', $series[0]['date']);
        $this->assertSame(0, $series[0]['views']);
        $this->assertSame('2026-08-03', $series[2]['date']);
        $this->assertSame(5, $series[2]['views']);
        $this->assertSame('2026-08-05', $series[4]['date']);
        $this->assertSame(2, $series[4]['views']);

        Carbon::setTestNow();
    }

    #[Test]
    public function it_returns_all_zeros_for_an_empty_collection(): void
    {
        Carbon::setTestNow('2026-08-05 12:00:00');

        $series = DailySeriesBuilder::fill(new Collection, 3);

        $this->assertSame([0, 0, 0], array_column($series, 'views'));

        Carbon::setTestNow();
    }
}
