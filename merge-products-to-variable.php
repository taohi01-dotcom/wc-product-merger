<?php
/**
 * WooCommerce Product Merger
 *
 * Merge multiple simple products into one variable product with variations.
 *
 * @package     WC_Product_Merger
 * @version     1.0.0
 * @author      Klaus Arends / Claude AI
 * @license     GPL-2.0+
 *
 * USAGE:
 * 1. Configure the $config array below
 * 2. Upload to WordPress root directory
 * 3. Run via browser or CLI: php merge-products-to-variable.php
 * 4. Delete file after use (auto-deletes by default)
 *
 * EXAMPLE:
 * Merge "ARO Orange 2L" and "ARO Lemon 2L" into "ARO Limonade 2L" with flavor variations
 */

// Prevent direct access without WordPress
if (!file_exists(__DIR__ . '/wp-load.php')) {
    die("Error: This script must be placed in the WordPress root directory.\n");
}

require_once(__DIR__ . '/wp-load.php');

// ============================================
// CONFIGURATION - EDIT THIS SECTION
// ============================================

$config = [
    // Source products (simple products to merge)
    'source_products' => [
        [
            'id' => 43894,                    // Product ID or SKU
            'variation_value' => 'Orange',    // Value for the variation attribute
            'variation_label' => '🍊 Orange', // Display label (optional)
        ],
        [
            'id' => 43893,
            'variation_value' => 'Zitrone',
            'variation_label' => '🍋 Zitrone',
        ],
    ],

    // New variable product settings
    'variable_product' => [
        'name' => 'ARO Limonade 2L - Erfrischende Limonade für die ganze Familie',
        'slug' => 'aro-limonade-2l',
        'short_description' => 'Erfrischende ARO Limonade - wähle zwischen Orange 🍊 und Zitrone 🍋. Perfekt für die ganze Familie!',
        'status' => 'publish',
    ],

    // Variation attribute
    'attribute' => [
        'name' => 'Geschmack',           // Attribute name (will create pa_geschmack)
        'slug' => 'geschmack',           // Attribute slug
        'visible' => true,               // Show on product page
        'variation' => true,             // Use for variations
    ],

    // SEO Settings (Rank Math / Yoast)
    'seo' => [
        'focus_keyword' => 'ARO Limonade',
        'title' => 'ARO Limonade 2L kaufen | Orange & Zitrone | Erfrischend & Günstig',
        'description' => 'ARO Limonade im 2L Format ✓ Zwei Geschmacksrichtungen: Orange & Zitrone ✓ Erfrischend für die ganze Familie ✓ Jetzt günstig online bestellen!',
    ],

    // Options
    'options' => [
        'copy_images' => true,           // Copy images from source products
        'copy_categories' => true,       // Copy categories from first source product
        'combine_descriptions' => true,  // Combine descriptions from all sources
        'delete_source_products' => false, // Set to true to delete originals (careful!)
        'draft_source_products' => false,  // Set to true to set originals to draft
        'auto_delete_script' => true,    // Delete this script after running
        'dry_run' => false,              // Set to true to simulate without changes
    ],
];

// ============================================
// MAIN SCRIPT - DO NOT EDIT BELOW
// ============================================

class WC_Product_Merger {

    private $config;
    private $log = [];

    public function __construct($config) {
        $this->config = $config;
    }

    public function run() {
        $this->log("=== WooCommerce Product Merger ===\n");

        if ($this->config['options']['dry_run']) {
            $this->log("⚠️  DRY RUN MODE - No changes will be made\n");
        }

        // Step 1: Load source products
        $source_products = $this->loadSourceProducts();
        if (empty($source_products)) {
            $this->log("❌ No source products found!");
            return false;
        }

        // Step 2: Create/get attribute taxonomy
        $attribute_slug = $this->createAttribute();
        if (!$attribute_slug) {
            $this->log("❌ Failed to create attribute!");
            return false;
        }

        // Step 3: Create attribute terms
        $term_ids = $this->createAttributeTerms($attribute_slug);

        // Step 4: Create variable product
        $variable_product_id = $this->createVariableProduct($source_products, $attribute_slug, $term_ids);
        if (!$variable_product_id) {
            $this->log("❌ Failed to create variable product!");
            return false;
        }

        // Step 5: Create variations
        $this->createVariations($variable_product_id, $source_products, $attribute_slug);

        // Step 6: Set SEO meta
        $this->setSeoMeta($variable_product_id);

        // Step 7: Handle source products
        $this->handleSourceProducts($source_products);

        // Step 8: Sync and clear caches
        WC_Product_Variable::sync($variable_product_id);
        wc_delete_product_transients($variable_product_id);

        $this->log("\n" . str_repeat('=', 50));
        $this->log("🎉 SUCCESS!");
        $this->log(str_repeat('=', 50));
        $this->log("\nNew Product ID: $variable_product_id");
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
                    'variation_value' => $source['variation_value'],
                    'variation_label' => $source['variation_label'] ?? $source['variation_value'],
                ];
                $this->log("✅ Loaded: " . $product->get_name() . " (ID: $product_id)");
            } else {
                $this->log("⚠️  Product not found: " . $source['id']);
            }
        }

        return $products;
    }

    private function createAttribute() {
        $attr_name = $this->config['attribute']['name'];
        $attr_slug = $this->config['attribute']['slug'];
        $taxonomy = 'pa_' . $attr_slug;

        if (!taxonomy_exists($taxonomy)) {
            $this->log("\n⏳ Creating attribute taxonomy '$attr_name'...");

            if (!$this->config['options']['dry_run']) {
                $attribute_id = wc_create_attribute([
                    'name' => $attr_name,
                    'slug' => $attr_slug,
                    'type' => 'select',
                    'order_by' => 'menu_order',
                    'has_archives' => false,
                ]);

                if (is_wp_error($attribute_id)) {
                    $this->log("❌ Error: " . $attribute_id->get_error_message());
                    return false;
                }

                register_taxonomy($taxonomy, 'product', [
                    'hierarchical' => false,
                    'label' => $attr_name,
                ]);
            }

            $this->log("✅ Attribute created: $attr_name");
        } else {
            $this->log("✅ Attribute exists: $attr_name");
        }

        return $taxonomy;
    }

    private function createAttributeTerms($taxonomy) {
        $term_ids = [];

        foreach ($this->config['source_products'] as $source) {
            $term_name = $source['variation_value'];
            $term_slug = sanitize_title($term_name);

            $term = get_term_by('slug', $term_slug, $taxonomy);

            if (!$term && !$this->config['options']['dry_run']) {
                $result = wp_insert_term($term_name, $taxonomy, ['slug' => $term_slug]);
                if (!is_wp_error($result)) {
                    $term_ids[$term_name] = $result['term_id'];
                    $this->log("✅ Term created: $term_name");
                }
            } elseif ($term) {
                $term_ids[$term_name] = $term->term_id;
                $this->log("✅ Term exists: $term_name");
            }
        }

        return $term_ids;
    }

    private function createVariableProduct($source_products, $taxonomy, $term_ids) {
        $this->log("\n⏳ Creating variable product...");

        if ($this->config['options']['dry_run']) {
            return 99999; // Fake ID for dry run
        }

        $first_product = $source_products[0]['product'];
        $cfg = $this->config['variable_product'];

        $product = new WC_Product_Variable();
        $product->set_name($cfg['name']);
        $product->set_slug($cfg['slug']);
        $product->set_status($cfg['status']);
        $product->set_catalog_visibility('visible');
        $product->set_short_description($cfg['short_description']);

        // Copy image from first product
        if ($this->config['options']['copy_images']) {
            $product->set_image_id($first_product->get_image_id());
        }

        // Copy categories from first product
        if ($this->config['options']['copy_categories']) {
            $product->set_category_ids($first_product->get_category_ids());
        }

        // Combine descriptions
        if ($this->config['options']['combine_descriptions']) {
            $description = $this->combineDescriptions($source_products);
            $product->set_description($description);
        }

        // Set up attribute
        $attribute = new WC_Product_Attribute();
        $attribute->set_id(wc_attribute_taxonomy_id_by_name($taxonomy));
        $attribute->set_name($taxonomy);
        $attribute->set_options(array_values($term_ids));
        $attribute->set_position(0);
        $attribute->set_visible($this->config['attribute']['visible']);
        $attribute->set_variation($this->config['attribute']['variation']);

        $product->set_attributes([$attribute]);

        $product_id = $product->save();

        // Assign terms to product
        wp_set_object_terms($product_id, array_values($term_ids), $taxonomy);

        $this->log("✅ Variable product created (ID: $product_id)");

        return $product_id;
    }

    private function createVariations($parent_id, $source_products, $taxonomy) {
        $this->log("\n⏳ Creating variations...");

        foreach ($source_products as $source) {
            $original = $source['product'];
            $term_slug = sanitize_title($source['variation_value']);

            if ($this->config['options']['dry_run']) {
                $this->log("  [DRY RUN] Would create variation: " . $source['variation_value']);
                continue;
            }

            $variation = new WC_Product_Variation();
            $variation->set_parent_id($parent_id);
            $variation->set_attributes([$taxonomy => $term_slug]);
            $variation->set_regular_price($original->get_regular_price());

            if ($original->get_sale_price()) {
                $variation->set_sale_price($original->get_sale_price());
            }

            // Use modified SKU to avoid duplicates
            $variation->set_sku($original->get_sku() . '-VAR');
            $variation->set_stock_status($original->get_stock_status());
            $variation->set_manage_stock($original->get_manage_stock());

            if ($original->get_manage_stock()) {
                $variation->set_stock_quantity($original->get_stock_quantity());
            }

            if ($this->config['options']['copy_images']) {
                $variation->set_image_id($original->get_image_id());
            }

            $variation->set_status('publish');
            $variation->set_description($source['variation_label'] . ' - ' . $original->get_short_description());

            $variation_id = $variation->save();

            $this->log("  ✅ Variation '{$source['variation_value']}' created (ID: $variation_id)");
        }
    }

    private function combineDescriptions($source_products) {
        $attr_name = $this->config['attribute']['name'];

        $html = "<h2>{$this->config['variable_product']['name']}</h2>\n";
        $html .= "<p>Wähle deine bevorzugte Variante:</p>\n\n";

        foreach ($source_products as $source) {
            $label = $source['variation_label'];
            $desc = $source['product']->get_description();

            $html .= "<h3>$label</h3>\n";
            $html .= $desc . "\n\n";
            $html .= "<hr style=\"margin: 30px 0;\">\n\n";
        }

        return $html;
    }

    private function setSeoMeta($product_id) {
        if (empty($this->config['seo'])) {
            return;
        }

        $this->log("\n⏳ Setting SEO meta...");

        if ($this->config['options']['dry_run']) {
            return;
        }

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

    public function getLog() {
        return $this->log;
    }
}

// ============================================
// EXECUTE
// ============================================

$merger = new WC_Product_Merger($config);
$result = $merger->run();

// Auto-delete script
if ($config['options']['auto_delete_script'] && !$config['options']['dry_run']) {
    @unlink(__FILE__);
}
