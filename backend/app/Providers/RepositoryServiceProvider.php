<?php

namespace App\Providers;

use App\Repositories\BannerRepository;
use App\Repositories\CoupleRepository;
use App\Repositories\DigitalEnvelopeRepository;
use App\Repositories\FaqRepository;
use App\Repositories\GalleryRepository;
use App\Repositories\GuestbookRepository;
use App\Repositories\GuestRepository;
use App\Repositories\HonoreeRepository;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use App\Repositories\Interfaces\CoupleRepositoryInterface;
use App\Repositories\Interfaces\DigitalEnvelopeRepositoryInterface;
use App\Repositories\Interfaces\FaqRepositoryInterface;
use App\Repositories\Interfaces\GalleryRepositoryInterface;
use App\Repositories\Interfaces\GuestbookRepositoryInterface;
use App\Repositories\Interfaces\GuestRepositoryInterface;
use App\Repositories\Interfaces\HonoreeRepositoryInterface;
use App\Repositories\Interfaces\InvitationEventRepositoryInterface;
use App\Repositories\Interfaces\InvitationRepositoryInterface;
use App\Repositories\Interfaces\InvitationVisitRepositoryInterface;
use App\Repositories\Interfaces\LoveStoryRepositoryInterface;
use App\Repositories\Interfaces\MusicRepositoryInterface;
use App\Repositories\Interfaces\PackageRepositoryInterface;
use App\Repositories\Interfaces\ThemeCategoryRepositoryInterface;
use App\Repositories\Interfaces\ThemeRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\InvitationEventRepository;
use App\Repositories\InvitationRepository;
use App\Repositories\InvitationVisitRepository;
use App\Repositories\LoveStoryRepository;
use App\Repositories\MusicRepository;
use App\Repositories\PackageRepository;
use App\Repositories\ThemeCategoryRepository;
use App\Repositories\ThemeRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(InvitationRepositoryInterface::class, InvitationRepository::class);
        $this->app->bind(CoupleRepositoryInterface::class, CoupleRepository::class);
        $this->app->bind(InvitationEventRepositoryInterface::class, InvitationEventRepository::class);
        $this->app->bind(GalleryRepositoryInterface::class, GalleryRepository::class);
        $this->app->bind(GuestbookRepositoryInterface::class, GuestbookRepository::class);
        $this->app->bind(DigitalEnvelopeRepositoryInterface::class, DigitalEnvelopeRepository::class);
        $this->app->bind(InvitationVisitRepositoryInterface::class, InvitationVisitRepository::class);
        $this->app->bind(ThemeRepositoryInterface::class, ThemeRepository::class);
        $this->app->bind(ThemeCategoryRepositoryInterface::class, ThemeCategoryRepository::class);
        $this->app->bind(LoveStoryRepositoryInterface::class, LoveStoryRepository::class);
        $this->app->bind(PackageRepositoryInterface::class, PackageRepository::class);
        $this->app->bind(BannerRepositoryInterface::class, BannerRepository::class);
        $this->app->bind(FaqRepositoryInterface::class, FaqRepository::class);
        $this->app->bind(MusicRepositoryInterface::class, MusicRepository::class);
        $this->app->bind(HonoreeRepositoryInterface::class, HonoreeRepository::class);
        $this->app->bind(GuestRepositoryInterface::class, GuestRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
