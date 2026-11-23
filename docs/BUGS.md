ask, add 2 esspresso ( satu dengan notes "no ice") itu harusnya espresso nya kepisah jadi 2 dengan yang 1 ada notes yang satu tidak atau masing masing 1pcs atau digabung jadi 2 pcs, dengan notes

E. Checkout dengan Notes

Add 2 Espresso (satu dengan notes "no ice")

Checkout → submit (Cash 50000)

Order created

Verify DB:

powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "USE seumpama_pos; SELECT id, product_name, quantity, notes FROM order_items WHERE order_id = (SELECT MAX(id) FROM orders);"
Expected:

Espresso 1: notes = no ice (atau null kalau tidak ada)

Espresso 2: notes = null

F. Order Detail Show Notes

Buka /orders/{new_id}

Item dengan notes → tampil kuning highlight di kolom Produk

G. Receipt Show Notes

Klik "Print Struk"

Item dengan notes → tampil \* no ice (italic) di bawah item

H. PDF

Download PDF → sama

I. Notes tidak affect Stock

powershell
C:\xampp\mysql\bin\mysql.exe -u root -e "USE seumpama_pos; SELECT ingredient_id, total_quantity FROM sale_ingredient_usages WHERE order_item_id = (SELECT MAX(id) FROM order_items);"
Expected: Quantity = normal (sesuai resep, tidak kurang karena notes)

J. Notes tidak affect discount

Order dengan promo + notes → discount_amount tetap sesuai promo (notes tidak affect diskon).
