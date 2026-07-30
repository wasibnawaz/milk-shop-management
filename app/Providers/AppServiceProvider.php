<?php

namespace App\Providers;

use App\Support\Money;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureModels();
        $this->configureBladeDirectives();
        $this->configureRateLimiting();
    }

    private function configureRateLimiting(): void
    {
        /*
        | Login throttle keyed on IP. This sits on top of the per-email
        | lockout inside LoginRequest: that one stops a single account being
        | brute forced, this one stops an attacker spraying many accounts
        | from one host.
        */
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(20)->by($request->ip()));

        // Blanket ceiling for authenticated write traffic.
        RateLimiter::for('writes', fn (Request $request) => Limit::perMinute(60)
            ->by($request->user()?->id ?: $request->ip()));
    }

    private function configureModels(): void
    {
        /*
        | Fail loudly on a lazy load during development. The old index view
        | rendered related names inside a loop; now that those are real
        | relationships that would be an N+1, and this turns it into an
        | exception rather than a silent performance problem. Disabled in
        | production so a missed eager load degrades instead of 500ing.
        */
        Model::preventLazyLoading(! $this->app->isProduction());

        // Throw if a guarded attribute is silently dropped on save.
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());
    }

    private function configureBladeDirectives(): void
    {
        $money = '\\'.Money::class;

        // @money($amount) — always 2dp with the configured currency symbol.
        Blade::directive('money', fn (string $expression) => "<?php echo e({$money}::format({$expression})); ?>");

        // @qty($quantity) — trims trailing zeros, so 2.500 renders as "2.5".
        Blade::directive('qty', fn (string $expression) => "<?php echo e({$money}::quantity({$expression})); ?>");
    }
}
