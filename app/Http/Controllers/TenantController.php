<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Services\LandlordPortfolioStats;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = User::where('role', 'tenant')
            ->whereHas('leases.property', fn ($q) => $q->where('landlord_id', Auth::id()))
            ->with(['leases' => fn ($q) => $q->with(['property', 'mandates'])->latest()])
            ->get();

        $stats = LandlordPortfolioStats::tenants(Auth::id());

        return view('dashboard.tenants.index', compact('tenants', 'stats'));
    }
}
