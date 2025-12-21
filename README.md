# WooCommerce Product Merger v3.0

Merge multiple simple WooCommerce products into one variable product with variations.
**Now with multi-attribute support and full SEO optimization!**

## 🆕 New in v3.0

- ✨ **Multi-Attribute Support** - Create products with 2+ attributes (e.g., Size + Flavor)
- ✨ **Automatic attribute combinations** - Mix and match attributes for complex variations
- ✨ **Enhanced documentation** - Better examples and use cases

## Features

- ✅ Merge 2+ simple products into one variable product
- ✅ Automatic attribute & term creation (pa_geschmack, etc.)
- ✅ Copy images, categories from source products
- ✅ **Full product description** (min. 200 words for SEO)
- ✅ **Short description** for cart display
- ✅ **Warenkorb-Kurzbeschreibung** per variation (`_mini_desc` für WooCommerce Germanized)
- ✅ **SEO optimization** (Rank Math + Yoast)
  - Focus keyword
  - SEO title
  - Meta description
  - Image alt texts
- ✅ **Product tags** (Schlagwörter)
- ✅ Dry-run mode for testing
- ✅ Auto-cleanup of source products (draft/delete)

## Use Case

You have multiple similar products that differ only by one attribute (e.g., flavor, color, size):

**Before:**
- Pringles Original 165g (Simple Product)
- Pringles Paprika 165g (Simple Product)
- Pringles Cheese 165g (Simple Product)

**After:**
- Pringles Chips 165g (Variable Product)
  - Variation: Original
  - Variation: Paprika
  - Variation: Cheese

## Installation

1. Download `merge-products-to-variable.php`
2. Edit the `$config` array with your product IDs and settings
3. Upload to your WordPress root directory
4. Access via browser: `https://yoursite.com/merge-products-to-variable.php`
5. Script auto-deletes after successful execution

## Configuration

```php
$config = [
    // Source products (simple products to merge)
    'source_products' => [
        [
            'id' => 123,                      // Product ID or SKU
            'variation_value' => 'Orange',    // Variation attribute value
            'variation_label' => '🍊 Orange', // Display label
            'cart_description' => 'Erfrischende Orange Limonade!', // NEW: Warenkorb-Kurzbeschreibung
        ],
        [
            'id' => 456,
            'variation_value' => 'Lemon',
            'variation_label' => '🍋 Lemon',
            'cart_description' => 'Spritzige Zitronen Limonade!',
        ],
    ],

    // New variable product settings
    'variable_product' => [
        'name' => 'Product Name with Variations',
        'slug' => 'product-slug',
        'short_description' => 'Short description for product page',
        'status' => 'publish',
        // NEW: Full description (min. 200 words for SEO!)
        'description' => '
<h2>Product Title</h2>
<p>Long description with at least 200 words...</p>
<h3>Features</h3>
<ul>
<li>Feature 1</li>
<li>Feature 2</li>
</ul>
...
',
    ],

    // Variation attribute
    'attribute' => [
        'name' => 'Geschmack',      // Human-readable name
        'slug' => 'geschmack',      // URL-safe slug (creates pa_geschmack)
        'visible' => true,
        'variation' => true,
    ],

    // SEO Settings (Rank Math + Yoast)
    'seo' => [
        'focus_keyword' => 'Product Keyword',
        'title' => 'SEO Title | Brand',
        'description' => 'Meta description for search engines (max 160 chars)',
        'image_alt' => 'Product image alt text with keyword', // NEW
    ],

    // NEW: Product Tags (Schlagwörter)
    'tags' => ['Tag1', 'Tag2', 'Brand', 'Category'],

    // Options
    'options' => [
        'copy_images' => true,
        'copy_categories' => true,
        'copy_tags' => true,              // NEW
        'set_image_alt' => true,          // NEW
        'delete_source_products' => false,
        'draft_source_products' => true,
        'auto_delete_script' => true,
        'dry_run' => false,
    ],
];
```

## Multi-Attribute Support (v3.0)

For products with **multiple varying attributes** (e.g., Size AND Flavor), use `merge-products-multi-attribute.php`:

### Use Case

**Before:**
- KAS Orange 12x1L (Simple Product)
- KAS Zitrone 12x1L (Simple Product)
- KAS Orange 12x0,5L (Simple Product)
- KAS Zitrone 12x0,5L (Simple Product)

**After:**
- KAS Limonade PET-Flaschen (Variable Product)
  - Attribute 1: Größe (12x1L, 12x0,5L)
  - Attribute 2: Geschmack (Orange, Zitrone)
  - Total: 4 variations (all combinations)

### Configuration

```php
'source_products' => [
    [
        'id' => 123,
        'attributes' => [
            'groesse' => '12x1L',      // Attribute 1
            'geschmack' => 'Orange',   // Attribute 2
        ],
        'cart_description' => 'KAS Orange 1L - Description...',
    ],
    // ... more products with different attribute combinations
],

'attributes' => [
    ['name' => 'Größe', 'slug' => 'groesse', 'visible' => true, 'variation' => true],
    ['name' => 'Geschmack', 'slug' => 'geschmack', 'visible' => true, 'variation' => true],
],
```

### When to Use Which Script

| Script | Best For | Example |
|--------|----------|---------|
| `merge-products-to-variable.php` | 1 attribute | Pringles (only flavor varies) |
| `merge-products-multi-attribute.php` | 2+ attributes | KAS (size + flavor vary) |

## SEO Checklist

The script now handles all Rank Math SEO requirements:

| SEO Element | ✅ Handled |
|-------------|-----------|
| Focus keyword in title | Yes |
| Focus keyword in description | Yes |
| Focus keyword in URL | Yes (via slug) |
| Focus keyword in content | Yes (via description) |
| Content min. 200 words | Yes |
| Image alt text | Yes |
| Product schema | Auto (WooCommerce) |
| Product tags | Yes |

## Output Example

```
=== WooCommerce Product Merger v2.0 ===

✅ Loaded: Pringles Original 165g (ID: 42894)
✅ Loaded: Pringles Paprika 165g (ID: 42628)

✅ Attribute exists: Geschmack
✅ Term exists: Original
✅ Term exists: Paprika

⏳ Creating variable product...
✅ Description set (222 words)
✅ Variable product created (ID: 61005)

⏳ Creating variations...
  ✅ Variation 'Original' created (ID: 61006)
     → Warenkorb-Beschreibung: Knusprige Original Chips...
  ✅ Variation 'Paprika' created (ID: 61008)
     → Warenkorb-Beschreibung: Würzige Paprika Chips...

⏳ Setting SEO meta...
✅ SEO meta set
   → Focus Keyword: Pringles Chips
   → Title: Pringles Chips 165g kaufen | 6 Sorten | Snacks

⏳ Setting product tags...
✅ Tags set: Chips, Pringles, Snacks, Knabberei

⏳ Setting image alt texts...
✅ Main image alt: Pringles Chips 165g Dose
  ✅ Variation image alt: Pringles - Original
  ✅ Variation image alt: Pringles - Paprika

⏳ Setting source products to draft...
  📝 Drafted: Pringles Original 165g
  📝 Drafted: Pringles Paprika 165g

==================================================
🎉 SUCCESS!
==================================================

New Product ID: 61005
Description: 222 Wörter ✅
URL: https://example.com/product/pringles-chips-165g/
Admin: https://example.com/wp-admin/post.php?post=61005&action=edit
```

## Requirements

- WordPress 5.0+
- WooCommerce 3.0+
- PHP 7.4+

## Safety Notes

1. **Always backup your database before running**
2. **Test with `dry_run => true` first**
3. **The script auto-deletes after execution**
4. **Original products are set to draft by default**

## Changelog

### v2.2.0 (2024-12-13)
- **SKU conflict fix**: Automatically renames original SKUs before creating variations
- Prevents "Duplicate SKU" errors when merging products

### v2.1.0 (2024-12-12)
- **WooCommerce Germanized support**: `_mini_desc` field for cart descriptions
- Fixed variation cart descriptions to work with German shops

### v2.0.0 (2024-12-12)
- Added full product description support (min. 200 words)
- Added Warenkorb-Kurzbeschreibung per variation
- Added product tags support
- Added image alt text for SEO
- Improved SEO meta handling
- Better logging output
- Keep original SKU (removed -VAR suffix)

### v1.0.0
- Initial release

## License

GPL-2.0+

## Author

Klaus Arends / Claude AI
