<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Helpers\PayPeriodHelper;
use Carbon\Carbon;

class PayPeriodHelperTest extends TestCase
{
    /**
     * Test first period detection (28→12)
     */
    public function test_first_period_detection(): void
    {
        // Test dates in first period (28 prev month → 12 current month)
        
        // Jan 5 should be in first period: Dec 28 → Jan 12
        $result = PayPeriodHelper::getCurrentPeriod('2025-01-05');
        $this->assertEquals('2024-12-28', $result['start']->format('Y-m-d'));
        $this->assertEquals('2025-01-12', $result['end']->format('Y-m-d'));
        $this->assertEquals('2025-01-15', $result['release']->format('Y-m-d'));
        $this->assertEquals('2025-01 (28-12)', $result['label']);

        // Feb 12 (last day of first period)
        $result = PayPeriodHelper::getCurrentPeriod('2025-02-12');
        $this->assertEquals('2025-01-28', $result['start']->format('Y-m-d'));
        $this->assertEquals('2025-02-12', $result['end']->format('Y-m-d'));
        $this->assertEquals('2025-02-15', $result['release']->format('Y-m-d'));
        $this->assertEquals('2025-02 (28-12)', $result['label']);
    }

    /**
     * Test second period detection (13→27)
     */
    public function test_second_period_detection(): void
    {
        // Test dates in second period (13 → 27 same month)
        
        // Jan 13 (first day of second period)
        $result = PayPeriodHelper::getCurrentPeriod('2025-01-13');
        $this->assertEquals('2025-01-13', $result['start']->format('Y-m-d'));
        $this->assertEquals('2025-01-27', $result['end']->format('Y-m-d'));
        $this->assertEquals('2025-01-31', $result['release']->format('Y-m-d')); // Jan has 31 days
        $this->assertEquals('2025-01 (13-27)', $result['label']);

        // Feb 20 (middle of second period)
        $result = PayPeriodHelper::getCurrentPeriod('2025-02-20');
        $this->assertEquals('2025-02-13', $result['start']->format('Y-m-d'));
        $this->assertEquals('2025-02-27', $result['end']->format('Y-m-d'));
        $this->assertEquals('2025-02-28', $result['release']->format('Y-m-d')); // Feb has 28 days (2025 not leap)
        $this->assertEquals('2025-02 (13-27)', $result['label']);

        // Apr 27 (last day of second period)
        $result = PayPeriodHelper::getCurrentPeriod('2025-04-27');
        $this->assertEquals('2025-04-13', $result['start']->format('Y-m-d'));
        $this->assertEquals('2025-04-27', $result['end']->format('Y-m-d'));
        $this->assertEquals('2025-04-30', $result['release']->format('Y-m-d')); // Apr has 30 days
        $this->assertEquals('2025-04 (13-27)', $result['label']);
    }

    /**
     * Test edge case: dates 28-31 belong to NEXT month's first period
     */
    public function test_date_28_to_31_belongs_to_next_month(): void
    {
        // Jan 28 belongs to February's first period (Jan 28 → Feb 12)
        $result = PayPeriodHelper::getCurrentPeriod('2025-01-28');
        $this->assertEquals('2025-01-28', $result['start']->format('Y-m-d'));
        $this->assertEquals('2025-02-12', $result['end']->format('Y-m-d'));
        $this->assertEquals('2025-02-15', $result['release']->format('Y-m-d'));
        $this->assertEquals('2025-02 (28-12)', $result['label']);

        // Jan 31 also belongs to February's first period
        $result = PayPeriodHelper::getCurrentPeriod('2025-01-31');
        $this->assertEquals('2025-01-28', $result['start']->format('Y-m-d'));
        $this->assertEquals('2025-02-12', $result['end']->format('Y-m-d'));
        $this->assertEquals('2025-02-15', $result['release']->format('Y-m-d'));
        $this->assertEquals('2025-02 (28-12)', $result['label']);

        // Mar 30 belongs to April's first period
        $result = PayPeriodHelper::getCurrentPeriod('2025-03-30');
        $this->assertEquals('2025-03-28', $result['start']->format('Y-m-d'));
        $this->assertEquals('2025-04-12', $result['end']->format('Y-m-d'));
        $this->assertEquals('2025-04-15', $result['release']->format('Y-m-d'));
        $this->assertEquals('2025-04 (28-12)', $result['label']);
    }

    /**
     * Test release dates for different month lengths
     */
    public function test_release_dates_vary_by_month(): void
    {
        // January (31 days) - second period
        $result = PayPeriodHelper::getCurrentPeriod('2025-01-20');
        $this->assertEquals('2025-01-31', $result['release']->format('Y-m-d'));

        // February (28 days, not leap) - second period
        $result = PayPeriodHelper::getCurrentPeriod('2025-02-20');
        $this->assertEquals('2025-02-28', $result['release']->format('Y-m-d'));

        // April (30 days) - second period
        $result = PayPeriodHelper::getCurrentPeriod('2025-04-20');
        $this->assertEquals('2025-04-30', $result['release']->format('Y-m-d'));

        // First period always releases on 15th
        $result = PayPeriodHelper::getCurrentPeriod('2025-03-05');
        $this->assertEquals('2025-03-15', $result['release']->format('Y-m-d'));
    }

    /**
     * Test leap year February
     */
    public function test_leap_year_february(): void
    {
        // 2024 is a leap year
        $result = PayPeriodHelper::getCurrentPeriod('2024-02-20');
        $this->assertEquals('2024-02-13', $result['start']->format('Y-m-d'));
        $this->assertEquals('2024-02-27', $result['end']->format('Y-m-d'));
        $this->assertEquals('2024-02-29', $result['release']->format('Y-m-d')); // Leap year
        $this->assertEquals('2024-02 (13-27)', $result['label']);
    }

    /**
     * Test formatPeriodLabel static method
     */
    public function test_format_period_label(): void
    {
        // First period (28→12)
        $start = Carbon::parse('2025-01-28');
        $end = Carbon::parse('2025-02-12');
        $label = PayPeriodHelper::formatPeriodLabel($start, $end);
        $this->assertEquals('2025-02 (28-12)', $label);

        // Second period (13→27)
        $start = Carbon::parse('2025-03-13');
        $end = Carbon::parse('2025-03-27');
        $label = PayPeriodHelper::formatPeriodLabel($start, $end);
        $this->assertEquals('2025-03 (13-27)', $label);
    }

    /**
     * Test isDateInPeriod helper
     */
    public function test_is_date_in_period(): void
    {
        $periodStart = Carbon::parse('2025-01-28');
        $periodEnd = Carbon::parse('2025-02-12');

        // Date within period
        $this->assertTrue(PayPeriodHelper::isDateInPeriod('2025-02-05', $periodStart, $periodEnd));

        // Boundary dates
        $this->assertTrue(PayPeriodHelper::isDateInPeriod('2025-01-28', $periodStart, $periodEnd));
        $this->assertTrue(PayPeriodHelper::isDateInPeriod('2025-02-12', $periodStart, $periodEnd));

        // Date outside period
        $this->assertFalse(PayPeriodHelper::isDateInPeriod('2025-02-13', $periodStart, $periodEnd));
        $this->assertFalse(PayPeriodHelper::isDateInPeriod('2025-01-27', $periodStart, $periodEnd));
    }

    /**
     * Test getPeriodForMonth static method
     */
    public function test_get_period_for_month(): void
    {
        // First period of March 2025
        $result = PayPeriodHelper::getPeriodForMonth('28-12', 2025, 3);
        $this->assertEquals('2025-02-28', $result['start']->format('Y-m-d'));
        $this->assertEquals('2025-03-12', $result['end']->format('Y-m-d'));
        $this->assertEquals('2025-03-15', $result['release']->format('Y-m-d'));
        $this->assertEquals('2025-03 (28-12)', $result['label']);

        // Second period of March 2025
        $result = PayPeriodHelper::getPeriodForMonth('13-27', 2025, 3);
        $this->assertEquals('2025-03-13', $result['start']->format('Y-m-d'));
        $this->assertEquals('2025-03-27', $result['end']->format('Y-m-d'));
        $this->assertEquals('2025-03-31', $result['release']->format('Y-m-d'));
        $this->assertEquals('2025-03 (13-27)', $result['label']);
    }
}
