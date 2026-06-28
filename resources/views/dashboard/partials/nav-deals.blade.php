<div class="db-nav-section db-nav-section--tight">
  <span class="db-nav-label">Deals</span>
  <a href="{{ route('deals.insurance') }}" class="db-nav-item {{ request()->routeIs('deals.insurance') ? 'active' : '' }}">
    <span class="ni">🛡</span><span class="db-nav-txt">Insurance</span>
  </a>
  <a href="{{ route('deals.coupons') }}" class="db-nav-item {{ request()->routeIs('deals.coupons') ? 'active' : '' }}">
    <span class="ni">🏷</span><span class="db-nav-txt">Coupons</span>
  </a>
</div>
