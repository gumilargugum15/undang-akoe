<?php

namespace Tests\Unit;

use App\Helpers\UserAgentParser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserAgentParserTest extends TestCase
{
    private const ANDROID_CHROME = 'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36';

    private const IPHONE_SAFARI = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

    private const WINDOWS_CHROME = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

    private const IPAD_SAFARI = 'Mozilla/5.0 (iPad; CPU OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';

    private const WINDOWS_FIREFOX = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0';

    #[Test]
    public function it_detects_device_type(): void
    {
        $this->assertSame('mobile', UserAgentParser::device(self::ANDROID_CHROME));
        $this->assertSame('mobile', UserAgentParser::device(self::IPHONE_SAFARI));
        $this->assertSame('desktop', UserAgentParser::device(self::WINDOWS_CHROME));
        $this->assertSame('tablet', UserAgentParser::device(self::IPAD_SAFARI));
        $this->assertSame('other', UserAgentParser::device(null));
    }

    #[Test]
    public function it_detects_browser_giving_chrome_priority_over_the_safari_substring_it_contains(): void
    {
        $this->assertSame('Chrome', UserAgentParser::browser(self::ANDROID_CHROME));
        $this->assertSame('Chrome', UserAgentParser::browser(self::WINDOWS_CHROME));
        $this->assertSame('Safari', UserAgentParser::browser(self::IPHONE_SAFARI));
        $this->assertSame('Firefox', UserAgentParser::browser(self::WINDOWS_FIREFOX));
        $this->assertNull(UserAgentParser::browser(null));
    }

    #[Test]
    public function it_detects_platform(): void
    {
        $this->assertSame('Android', UserAgentParser::platform(self::ANDROID_CHROME));
        $this->assertSame('iOS', UserAgentParser::platform(self::IPHONE_SAFARI));
        $this->assertSame('Windows', UserAgentParser::platform(self::WINDOWS_CHROME));
        $this->assertNull(UserAgentParser::platform(null));
    }
}
