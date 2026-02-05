namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // Tambahkan ini di atas!

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        // Paksa HTTPS jika di server (Railway)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
