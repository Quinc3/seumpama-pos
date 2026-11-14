@php
$menuGroups = [
[
'label' => null,
'items' => [
['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'permission' => null],
],
],
[
'label' => 'Operations',
'items' => [
['route' => 'pos.index', 'label' => 'Point of Sale', 'icon' => 'shopping-cart', 'permission' => 'pos.access'],
['route' => 'orders.index', 'label' => 'Transactions', 'icon' => 'receipt', 'permission' => 'pos.access'],
['route' => 'products.index', 'label' => 'Products', 'icon' => 'coffee', 'permission' => 'products.view'],
['route' => 'categories.index', 'label' => 'Categories', 'icon' => 'package', 'permission' => 'products.view'],
['route' => 'recipes.index', 'label' => 'Recipes / BOM', 'icon' => 'layers', 'permission' => 'products.view'],
],
],
[
'label' => 'Inventory',
'items' => [
['route' => 'units.index', 'label' => 'Units', 'icon' => 'layers', 'permission' => 'inventory.manage'],
['route' => 'ingredients.index', 'label' => 'Ingredients', 'icon' => 'package', 'permission' => 'inventory.view'],
['route' => 'stock.overview', 'label' => 'Stock Overview', 'icon' => 'layers', 'permission' => 'inventory.view'],
['route' => 'inventory.movements.index', 'label' => 'Stock Movement', 'icon' => 'arrow-right-left', 'permission' => 'inventory.view'],
['route' => 'stock-opnames.index', 'label' => 'Stock Opname', 'icon' => 'clipboard-check', 'permission' => 'inventory.view'],
],
],
[
'label' => 'Procurement',
'items' => [
['route' => 'suppliers.index', 'label' => 'Suppliers', 'icon' => 'truck', 'permission' => 'inventory.view'],
['route' => 'purchase-orders.index', 'label' => 'Purchasing', 'icon' => 'shopping-bag', 'permission' => 'inventory.view'],
],
],
[
'label' => 'Analytics',
'items' => [
['route' => 'reports.sales', 'label' => 'Sales Reports', 'icon' => 'bar-chart', 'permission' => 'reports.view'],
['route' => 'reports.stock', 'label' => 'Inventory Reports', 'icon' => 'layers', 'permission' => 'inventory.view'],
['route' => 'reports.movements', 'label' => 'Movement Report', 'icon' => 'arrow-right-left', 'permission' => 'inventory.view'],
['route' => 'reports.usage', 'label' => 'Usage Report', 'icon' => 'trending-up', 'permission' => 'inventory.view'],
['route' => 'reports.low-stock', 'label' => 'Low Stock Alert', 'icon' => 'shield', 'permission' => 'inventory.view'],
['route' => 'reports.purchasing.orders', 'label' => 'Purchasing Report', 'icon' => 'shopping-bag', 'permission' => 'inventory.view'],
['route' => 'weather.index', 'label' => 'Weather Data', 'icon' => 'cloud', 'permission' => 'reports.view'],
['route' => 'dataset.index', 'label' => 'Dataset', 'icon' => 'database', 'permission' => 'ann.view'],
['route' => 'ann.prediction', 'label' => 'ANN Prediction', 'icon' => 'brain', 'permission' => 'ann.view'],
['route' => 'forecasting.index', 'label' => 'Stock Forecasting', 'icon' => 'trending-up', 'permission' => 'ann.view'],
],
],
[
'label' => 'Management',
'items' => [
['route' => 'customers.index', 'label' => 'Customers', 'icon' => 'users', 'permission' => 'reports.view'],
['route' => 'promotions.index', 'label' => 'Promotions', 'icon' => 'percent', 'permission' => 'products.manage'],
['route' => 'expenses.index', 'label' => 'Expenses', 'icon' => 'wallet', 'permission' => 'reports.view'],
['route' => 'users.index', 'label' => 'Users & Roles', 'icon' => 'shield', 'permission' => 'users.manage'],
],
],
[
'label' => 'System',
'items' => [
['route' => 'settings.index', 'label' => 'Settings', 'icon' => 'settings', 'permission' => 'settings.manage'],
],
],
];
@endphp

<div class="flex flex-col h-full">
    {{-- Brand --}}
    <div class="flex items-center h-16 px-6 border-b border-slate-200 shrink-0">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center">
                <x-icon name="coffee" :size="18" class="text-white" />
            </div>
            <div>
                <div class="text-sm font-bold text-slate-900 leading-tight">Seumpama POS</div>
                <div class="text-xs text-slate-500 leading-tight">Coffee Shop</div>
            </div>
        </div>
    </div>

    {{-- Menu --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
        @foreach ($menuGroups as $group)
        @php
        $visibleItems = collect($group['items'])->filter(function ($item) {
        return $item['permission'] === null || auth()->user()?->hasPermission($item['permission']);
        });
        @endphp

        @if ($visibleItems->isNotEmpty())
        <div>
            @if ($group['label'])
            <div class="px-3 mb-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">
                {{ $group['label'] }}
            </div>
            @endif

            <ul class="space-y-0.5">
                @foreach ($visibleItems as $item)
                @php
                $isActive = request()->routeIs($item['route']);
                $exists = \Illuminate\Support\Facades\Route::has($item['route']);
                @endphp
                <li>
                    @if ($exists)
                    <a href="{{ route($item['route']) }}"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors
                                              {{ $isActive
                                                  ? 'bg-blue-50 text-blue-700'
                                                  : 'text-slate-700 hover:bg-slate-50 hover:text-slate-900' }}">
                        <x-icon :name="$item['icon']" :size="18" />
                        <span>{{ $item['label'] }}</span>
                    </a>
                    @else
                    <span class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-400 cursor-not-allowed"
                        title="Segera hadir">
                        <x-icon :name="$item['icon']" :size="18" />
                        <span>{{ $item['label'] }}</span>
                    </span>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>
        @endif
        @endforeach
    </nav>

    {{-- Footer --}}
    <div class="shrink-0 px-6 py-4 border-t border-slate-200">
        <p class="text-xs text-slate-400">&copy; {{ date('Y') }} Seumpama POS</p>
    </div>
</div>
