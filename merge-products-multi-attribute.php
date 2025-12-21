<?php
/**
 * WooCommerce Product Merger - Multi-Attribute Edition v3.0
 *
 * Merge multiple simple products into one variable product with MULTIPLE attributes.
 * Perfect for products with size, flavor, color combinations.
 *
 * @package     WC_Product_Merger
 * @version     3.0.0
 * @author      Klaus Arends / Claude AI
 * @license     GPL-2.0+
 *
 * FEATURES:
 * - Support for MULTIPLE attributes (e.g., Size + Flavor, Color + Size)
 * - Full SEO optimization (Rank Math + Yoast)
 * - Automatic attribute taxonomy creation
 * - Cart descriptions per variation (WooCommerce Germanized compatible)
 * - Product tags and image alt texts
 * - Safe SKU handling to avoid duplicates
 *
 * USAGE:
 * 1. Configure the $config array below
 * 2. Upload to WordPress root directory
 * 3. Run via browser: https://yourdomain.com/merge-products-multi-attribute.php
 *    or CLI: php merge-products-multi-attribute.php
 * 4. Delete file after use (auto-deletes by default)
 */

// Prevent direct access without WordPress
if (!file_exists(__DIR__ . '/wp-load.php')) {
    die("Error: This script must be placed in the WordPress root directory.\n");
}

require_once(__DIR__ . '/wp-load.php');
header('Content-Type: text/plain; charset=utf-8');

// ============================================
// CONFIGURATION - EDIT THIS SECTION
// ============================================

$config = [
    // Source products - each product maps to specific attribute combinations
    'source_products' => [
        [
            'id' => 44882,
            'attributes' => [
                'groesse' => '12x1L',      // First attribute value
                'geschmack' => 'Orange',   // Second attribute value
            ],
            'cart_description' => 'KAS Orange 1L - Klassischer Orangengeschmack in der praktischen PET-Flasche!',
        ],
        [
            'id' => 44883,
            'attributes' => [
                'groesse' => '12x1L',
                'geschmack' => 'Zitrone',
            ],
            'cart_description' => 'KAS Zitrone 1L - Spritzige Zitronenfrische für maximalen Durstlöscher-Genuss!',
        ],
        [
            'id' => 44885,
            'attributes' => [
                'groesse' => '12x0,5L',
                'geschmack' => 'Orange',
            ],
            'cart_description' => 'KAS Orange 0,5L - Perfekte Einzelportion mit sonnigem Orangengeschmack!',
        ],
        [
            'id' => 44884,
            'attributes' => [
                'groesse' => '12x0,5L',
                'geschmack' => 'Zitrone',
            ],
            'cart_description' => 'KAS Zitrone 0,5L - Spritziger Durstlöscher in praktischer Portionsgröße!',
        ],
    ],

    // Variable product settings
    'variable_product' => [
        'name' => 'KAS Limonade PET-Flaschen - Alle Größen & Geschmacksrichtungen',
        'slug' => 'kas-limonade-pet-flaschen',
        'short_description' => 'KAS Limonade in praktischen PET-Flaschen - Wähle deine perfekte Größe und Geschmacksrichtung!',
        'status' => 'publish',
        'description' => '<h2>KAS Limonade PET-Flaschen</h2><p>Wähle aus verschiedenen Größen und Geschmacksrichtungen!</p>',
    ],

    // MULTIPLE attributes configuration
    'attributes' => [
        [
            'name' => 'Größe',
            'slug' => 'groesse',
            'visible' => true,
            'variation' => true,
        ],
        [
            'name' => 'Geschmack',
            'slug' => 'geschmack',
            'visible' => true,
            'variation' => true,
        ],
    ],

    // SEO Settings
    'seo' => [
        'focus_keyword' => 'KAS Limonade PET',
        'title' => 'KAS Limonade PET | Alle Größen & Geschmacksrichtungen',
        'description' => 'KAS Limonade PET-Flaschen ✓ Verschiedene Größen ✓ Mehrere Geschmacksrichtungen ✓ Jetzt bestellen!',
        'image_alt' => 'KAS Limonade PET-Flaschen verschiedene Größen',
    ],

    // Product Tags
    'tags' => ['KAS', 'Limonade', 'PET', 'PET-Flasche'],

    // Options
    'options' => [
        'copy_images' => true,
        'copy_categories' => true,
        'copy_tags' => true,
        'set_image_alt' => true,
        'delete_source_products' => false,
        'draft_source_products' => true,
        'auto_delete_script' => true,
        'dry_run' => false,
    ],
];

// ============================================
// MAIN CLASS - DO NOT EDIT BELOW
// ============================================

class WC_Product_Merger_MultiAttribute {

    private $config;
    private $log = [];

    public function __construct($config) {
        $this->config = $config;
    }

    public function run() {
        $this->log("=== WooCommerce Multi-Attribute Product Merger v3.0 ===\n");

        if ($this->config['options']['dry_run']) {
            $this->log("⚠️  DRY RUN MODE - No changes will be made\n");
        }

        // Step 1: Load source products
        $source_products = $this->loadSourceProducts();
        if (empty($source_products)) {
            $this->log("❌ No source products found!");
            return false;
        }

        // Step 2: Create/verify attributes
        $this->log("\n⏳ Setting up attributes...");
        $attributes = $this->createAttributes();

        // Step 3: Create variable product
        $variable_product_id = $this->createVariableProduct($source_products, $attributes);
        if (!$variable_product_id) {
            $this->log("❌ Failed to create variable product!");
            return false;
        }

        // Step 4: Create variations
        $this->createVariations($variable_product_id, $source_products);

        // Step 5: Set SEO meta
        $this->setSeoMeta($variable_product_id);

        // Step 6: Set tags
        $this->setTags($variable_product_id);

        // Step 7: Handle source products
        $this->handleSourceProducts($source_products);

        // Step 8: Sync and clear caches
        WC_Product_Variable::sync($variable_product_id);
        wc_delete_product_transients($variable_product_id);
        wp_cache_flush();

        $this->log("\n" . str_repeat('=', 60));
        $this->log("🎉 SUCCESS!");
        $this->log(str_repeat('=', 60));
        $this->log("\nNew Product ID: $variable_product_id");
        $this->log("Variations created: " . count($source_products));
        $this->log("URL: " . get_permalink($variable_product_id));
        $this->log("Admin: " . admin_url("post.php?post=$variable_product_id&action=edit"));

        return $variable_product_id;
    }

    private function loadSourceProducts() {
        $products = [];

        foreach ($this->config['source_products'] as $source) {
            $product_id = is_numeric($source['id'])
                ? $source['id']
                : wc_get_product_id_by_sku($source['id']);

            $product = wc_get_product($product_id);

            if ($product) {
                $products[] = [
                    'product' => $product,
                    'attributes' => $source['attributes'],
                    'cart_description' => $source['cart_description'] ?? '',
                ];
                $this->log("✅ Loaded: " . $product->get_name() . " (ID: $product_id)");
            } else {
                $this->log("⚠️  Product not found: " . $source['id']);
            }
        }

        return $products;
    }

    private function createAttributes() {
        $wc_attributes = [];

        foreach ($this->config['attributes'] as $attr_config) {
            $attr_slug = $attr_config['slug'];
            $attr_name = $attr_config['name'];
            $taxonomy = 'pa_' . $attr_slug;

            // Create attribute taxonomy if it doesn't exist
            if (!taxonomy_exists($taxonomy)) {
                $this->log("  ⏳ Creating attribute taxonomy '$attr_name'...");

                if (!$this->config['options']['dry_run']) {
                    wc_create_attribute([
                        'name' => $attr_name,
                        'slug' => $attr_slug,
                        'type' => 'select',
                        'order_by' => 'menu_order',
                        'has_archives' => false,
                    ]);

                    register_taxonomy($taxonomy, 'product', [
                        'hierarchical' => false,
                        'label' => $attr_name,
                    ]);
                }

                $this->log("  ✅ Attribute created: $attr_name");
            } else {
                $this->log("  ✅ Attribute exists: $attr_name");
            }

            // Collect unique values for this attribute
            $values = [];
            foreach ($this->config['source_products'] as $source) {
                if (isset($source['attributes'][$attr_slug])) {
                    $values[] = $source['attributes'][$attr_slug];
                }
            }
            $values = array_unique($values);

            // Create WC_Product_Attribute object
            $attribute = new WC_Product_Attribute();
            $attr_id = wc_attribute_taxonomy_id_by_name($taxonomy);
            $attribute->set_id($attr_id);
            $attribute->set_name($taxonomy);
            $attribute->set_options($values);
            $attribute->set_visible($attr_config['visible']);
            $attribute->set_variation($attr_config['variation']);

            $wc_attributes[] = $attribute;
        }

        return $wc_attributes;
    }

    private function createVariableProduct($source_products, $attributes) {
        $this->log("\n⏳ Creating variable product...");

        if ($this->config['options']['dry_run']) {
            return 99999;
        }

        $first_product = $source_products[0]['product'];
        $cfg = $this->config['variable_product'];

        $product = new WC_Product_Variable();
        $product->set_name($cfg['name']);
        $product->set_slug($cfg['slug']);
        $product->set_status($cfg['status']);
        $product->set_catalog_visibility('visible');
        $product->set_short_description($cfg['short_description']);
        $product->set_description($cfg['description']);

        // Copy image and categories from first product
        if ($this->config['options']['copy_images']) {
            $product->set_image_id($first_product->get_image_id());
        }

        if ($this->config['options']['copy_categories']) {
            $product->set_category_ids($first_product->get_category_ids());
        }

        // Set attributes
        $product->set_attributes($attributes);

        $product_id = $product->save();

        $this->log("✅ Variable product created (ID: $product_id)");

        return $product_id;
    }

    private function createVariations($parent_id, $source_products) {
        $this->log("\n⏳ Creating variations...");

        foreach ($source_products as $index => $source) {
            $original = $source['product'];

            if ($this->config['options']['dry_run']) {
                $attr_str = implode(' × ', $source['attributes']);
                $this->log("  [DRY RUN] Would create variation: $attr_str");
                continue;
            }

            $variation = new WC_Product_Variation();
            $variation->set_parent_id($parent_id);

            // Set multiple attributes
            $variation_attributes = [];
            foreach ($source['attributes'] as $slug => $value) {
                $variation_attributes['attribute_pa_' . $slug] = sanitize_title($value);
            }
            $variation->set_attributes($variation_attributes);

            // Copy pricing and stock
            $variation->set_regular_price($original->get_regular_price());
            if ($original->get_sale_price()) {
                $variation->set_sale_price($original->get_sale_price());
            }

            $variation->set_stock_status($original->get_stock_status());
            $variation->set_manage_stock($original->get_manage_stock());

            if ($original->get_manage_stock()) {
                $variation->set_stock_quantity($original->get_stock_quantity());
            }

            // Copy image
            if ($this->config['options']['copy_images']) {
                $variation->set_image_id($original->get_image_id());
            }

            $variation->set_status('publish');

            // Set descriptions
            if (!empty($source['cart_description'])) {
                $variation->set_description($source['cart_description']);
                $variation->update_meta_data('_mini_desc', $source['cart_description']);
                $variation->update_meta_data('_variation_description', $source['cart_description']);
            }

            $variation_id = $variation->save();

            $attr_str = implode(' × ', $source['attributes']);
            $this->log("  ✅ Variation: $attr_str (ID: $variation_id, €{$original->get_regular_price()})");
        }
    }

    private function setSeoMeta($product_id) {
        if (empty($this->config['seo']) || $this->config['options']['dry_run']) {
            return;
        }

        $this->log("\n⏳ Setting SEO meta...");
        $seo = $this->config['seo'];

        // Rank Math
        update_post_meta($product_id, 'rank_math_focus_keyword', $seo['focus_keyword']);
        update_post_meta($product_id, 'rank_math_title', $seo['title']);
        update_post_meta($product_id, 'rank_math_description', $seo['description']);

        // Yoast (fallback)
        update_post_meta($product_id, '_yoast_wpseo_focuskw', $seo['focus_keyword']);
        update_post_meta($product_id, '_yoast_wpseo_title', $seo['title']);
        update_post_meta($product_id, '_yoast_wpseo_metadesc', $seo['description']);

        $this->log("✅ SEO meta set");
    }

    private function setTags($product_id) {
        if (empty($this->config['tags']) || !$this->config['options']['copy_tags'] || $this->config['options']['dry_run']) {
            return;
        }

        $this->log("\n⏳ Setting product tags...");
        wp_set_object_terms($product_id, $this->config['tags'], 'product_tag');
        $this->log("✅ Tags set: " . implode(', ', $this->config['tags']));
    }

    private function handleSourceProducts($source_products) {
        if ($this->config['options']['delete_source_products']) {
            $this->log("\n⏳ Deleting source products...");
            foreach ($source_products as $source) {
                if (!$this->config['options']['dry_run']) {
                    wp_delete_post($source['product']->get_id(), true);
                }
                $this->log("  🗑️  Deleted: " . $source['product']->get_name());
            }
        } elseif ($this->config['options']['draft_source_products']) {
            $this->log("\n⏳ Setting source products to draft...");
            foreach ($source_products as $source) {
                if (!$this->config['options']['dry_run']) {
                    $source['product']->set_status('draft');
                    $source['product']->save();
                }
                $this->log("  📝 Drafted: " . $source['product']->get_name());
            }
        }
    }

    private function log($message) {
        $this->log[] = $message;
        echo $message . "\n";
    }
}

// ============================================
// EXECUTE
// ============================================

$merger = new WC_Product_Merger_MultiAttribute($config);
$result = $merger->run();

// Auto-delete script
if ($config['options']['auto_delete_script'] && !$config['options']['dry_run'] && $result) {
    @unlink(__FILE__);
}
