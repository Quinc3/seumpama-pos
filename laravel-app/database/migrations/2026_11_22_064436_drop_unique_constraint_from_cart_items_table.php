<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Drop FK cart_id (bound to composite unique)
        DB::statement('ALTER TABLE cart_items DROP FOREIGN KEY cart_items_cart_id_foreign');

        // 2. Add new regular index (satisfies FK requirement)
        DB::statement('ALTER TABLE cart_items ADD INDEX cart_items_cart_product_index (cart_id, product_id)');

        // 3. Drop unique (no FK bound to it now)
        DB::statement('ALTER TABLE cart_items DROP INDEX cart_items_cart_id_product_id_unique');

        // 4. Recreate FK (uses new regular index)
        DB::statement('ALTER TABLE cart_items ADD CONSTRAINT cart_items_cart_id_foreign FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        // 1. Drop FK
        DB::statement('ALTER TABLE cart_items DROP FOREIGN KEY cart_items_cart_id_foreign');

        // 2. Re-add unique (will fail if duplicate rows exist — acceptable for rollback)
        DB::statement('ALTER TABLE cart_items ADD UNIQUE cart_items_cart_id_product_id_unique (cart_id, product_id)');

        // 3. Drop new regular index
        DB::statement('ALTER TABLE cart_items DROP INDEX cart_items_cart_product_index');

        // 4. Recreate FK
        DB::statement('ALTER TABLE cart_items ADD CONSTRAINT cart_items_cart_id_foreign FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE');
    }
};
