# WooCommerce Product Merger

Merge multiple simple WooCommerce products into one variable product with variations.

## Features

- ✅ Merge 2+ simple products into one variable product
- ✅ Automatic attribute & term creation
- ✅ Copy images, categories, descriptions
- ✅ SEO optimization (Rank Math / Yoast)
- ✅ Variation-specific descriptions
- ✅ Dry-run mode for testing
- ✅ Auto-cleanup of source products (optional)

## Use Case

You have multiple similar products that differ only by one attribute (e.g., flavor, color, size):

**Before:**
- ARO Orange Lemonade 2L (Simple Product)
- ARO Lemon Lemonade 2L (Simple Product)

**After:**
- ARO Lemonade 2L (Variable Product)
  - Variation: Orange
  - Variation: Lemon

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
        ],
        [
            'id' => 456,
            'variation_value' => 'Lemon',
            'variation_label' => '🍋 Lemon',
        ],
    ],

    // New variable product settings
    'variable_product' => [
        'name' => 'Product Name with Variations',
        'slug' => 'product-slug',
        'short_description' => 'Short description for cart',
        'status' => 'publish',
    ],

    // Variation attribute
    'attribute' => [
        'name' => 'Flavor',      // Human-readable name
        'slug' => 'flavor',      // URL-safe slug (creates pa_flavor)
        'visible' => true,
        'variation' => true,
    ],

    // SEO Settings
    'seo' => [
        'focus_keyword' => 'Product Keyword',
        'title' => 'SEO Title | Brand',
        'description' => 'Meta description for search engines',
    ],

    // Options
    'options' => [
        'copy_images' => true,
        'copy_categories' => true,
        'combine_descriptions' => true,
        'delete_source_products' => false,
        'draft_source_products' => false,
        'auto_delete_script' => true,
        'dry_run' => false,  // Set true to test without changes
    ],
];
```

## Options Explained

| Option | Default | Description |
|--------|---------|-------------|
| `copy_images` | `true` | Copy product images to variations |
| `copy_categories` | `true` | Copy categories from first source product |
| `combine_descriptions` | `true` | Merge all descriptions into one |
| `delete_source_products` | `false` | Permanently delete original products |
| `draft_source_products` | `false` | Set originals to draft status |
| `auto_delete_script` | `true` | Delete script file after execution |
| `dry_run` | `false` | Simulate without making changes |

## Output Example

```
=== WooCommerce Product Merger ===

✅ Loaded: ARO Orange 2L (ID: 43894)
✅ Loaded: ARO Lemon 2L (ID: 43893)

⏳ Creating attribute taxonomy 'Flavor'...
✅ Attribute created: Flavor
✅ Term created: Orange
✅ Term created: Lemon

⏳ Creating variable product...
✅ Variable product created (ID: 56551)

⏳ Creating variations...
  ✅ Variation 'Orange' created (ID: 56552)
  ✅ Variation 'Lemon' created (ID: 56553)

⏳ Setting SEO meta...
✅ SEO meta set

==================================================
🎉 SUCCESS!
==================================================

New Product ID: 56551
URL: https://example.com/product/aro-lemonade-2l/
Admin: https://example.com/wp-admin/post.php?post=56551&action=edit
```

## Requirements

- WordPress 5.0+
- WooCommerce 3.0+
- PHP 7.4+

## Safety Notes

1. **Always backup your database before running**
2. **Test with `dry_run => true` first**
3. **The script auto-deletes after execution**
4. **Original products are kept by default**

## License

GPL-2.0+

## Author

Klaus Arends / Claude AI
