<?php
/**
 * WooCommerce Product Merger v2.0
 *
 * Merge multiple simple products into one variable product with variations.
 * Includes full SEO optimization, descriptions, tags, and image alt texts.
 *
 * @package     WC_Product_Merger
 * @version     2.2.0
 * @author      Klaus Arends / Claude AI
 * @license     GPL-2.0+
 *
 * USAGE:
 * 1. Configure the $config array below
 * 2. Upload to WordPress root directory
 * 3. Run via browser or CLI: php merge-products-to-variable.php
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
    // Source products (simple products to merge)
    'source_products' => [
        [
            'id' => 43894,                    // Product ID or SKU
            'variation_value' => 'Orange',    // Value for the variation attribute
            'variation_label' => '🍊 Orange', // Display label (optional)
            'cart_description' => 'Erfrischende Orange Limonade - perfekt gekühlt genießen!', // Warenkorb-Kurzbeschreibung
        ],
        [
            'id' => 43893,
            'variation_value' => 'Zitrone',
            'variation_label' => '🍋 Zitrone',
            'cart_description' => 'Spritzige Zitronen Limonade - der Durstlöscher für heiße Tage!',
        ],
    ],

    // New variable product settings
    'variable_product' => [
        'name' => 'ARO Limonade 2L - Erfrischende Limonade für die ganze Familie',
        'slug' => 'aro-limonade-2l',
        'short_description' => 'Erfrischende ARO Limonade - wähle zwischen Orange 🍊 und Zitrone 🍋. Perfekt für die ganze Familie!',
        'status' => 'publish',
        // WICHTIG: Ausführliche Beschreibung (min. 200 Wörter für SEO!)
        'description' => '
<h2>ARO Limonade - Der erfrischende Klassiker für die ganze Familie</h2>

<p>Die ARO Limonade im praktischen 2-Liter-Format ist der perfekte Durstlöscher für jeden Anlass. Ob beim Familienessen, auf der Gartenparty oder einfach als erfrischender Genuss zwischendurch - unsere Limonade überzeugt mit natürlichem Geschmack und prickelnder Frische.</p>

<h3>Warum ARO Limonade?</h3>

<ul>
<li><strong>Erfrischender Geschmack</strong> - Natürliche Aromen für authentischen Genuss</li>
<li><strong>Praktisches 2L Format</strong> - Ideal für die ganze Familie</li>
<li><strong>Zwei beliebte Sorten</strong> - Orange und Zitrone zur Auswahl</li>
<li><strong>Perfekt gekühlt</strong> - Am besten bei 6-8°C servieren</li>
</ul>

<h3>Unsere Geschmacksrichtungen</h3>

<p>Wähle aus zwei beliebten Klassikern:</p>

<ul>
<li><strong>🍊 Orange</strong> - Fruchtig-süß mit dem vollen Geschmack sonnengereifter Orangen. Perfekt für alle, die es fruchtig mögen.</li>
<li><strong>🍋 Zitrone</strong> - Spritzig-frisch mit der natürlichen Säure der Zitrone. Ideal als Durstlöscher an heißen Tagen.</li>
</ul>

<h3>Perfekt für jeden Anlass</h3>

<p>Die ARO Limonade eignet sich hervorragend für:</p>

<ul>
<li>Familienessen und gemeinsame Mahlzeiten</li>
<li>Gartenpartys und Grillabende</li>
<li>Kindergeburtstage und Feiern</li>
<li>Als erfrischender Begleiter zum Mittagessen</li>
<li>Picknicks und Ausflüge</li>
</ul>

<h3>Qualität, der Sie vertrauen können</h3>

<p>ARO steht für hochwertige Getränke zu fairen Preisen. Unsere Limonade wird nach bewährten Rezepturen hergestellt und bietet gleichbleibend hohe Qualität, auf die Sie sich verlassen können.</p>

<h3>Lieferung auf Mallorca</h3>

<p>Wir liefern ARO Limonade schnell und zuverlässig auf ganz Mallorca. Bestelle jetzt deine Lieblingssorte und genieße erfrischenden Limonaden-Genuss für die ganze Familie!</p>
',
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
        'image_alt' => 'ARO Limonade 2L Flasche - Erfrischende Limonade in Orange und Zitrone',
    ],

    // Product Tags (Schlagwörter)
    'tags' => ['Limonade', 'ARO', 'Erfrischungsgetränk', 'Softdrink', '2 Liter', 'Orange', 'Zitrone'],

    // Options
    'options' => [
        'copy_images' => true,           // Copy images from source products
        'copy_categories' => true,       // Copy categories from first source product
        'copy_tags' => true,             // Copy/create tags
        'set_image_alt' => true,         // Set image alt text for SEO
        'delete_source_products' => false, // Set to true to delete originals (careful!)
        'draft_source_products' => true,   // Set to true to set originals to draft
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
        $this->log("=== WooCommerce Product Merger v2.2 ===\n");

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

        // Step 7: Set tags
        $this->setTags($variable_product_id);

        // Step 8: Set image alt texts
        $this->setImageAltTexts($variable_product_id, $source_products);

        // Step 9: Handle source products
        $this->handleSourceProducts($source_products);

        // Step 10: Sync and clear caches
        WC_Product_Variable::sync($variable_product_id);
        wc_delete_product_transients($variable_product_id);
        wp_cache_flush();

        // Verify description word count
        $product = wc_get_product($variable_product_id);
        $word_count = str_word_count(strip_tags($product->get_description()));

        $this->log("\n" . str_repeat('=', 50));
        $this->log("🎉 SUCCESS!");
        $this->log(str_repeat('=', 50));
        $this->log("\nNew Product ID: $variable_product_id");
        $this->log("Description: $word_count Wörter " . ($word_count >= 200 ? "✅" : "⚠️ (min. 200 empfohlen)"));
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
                    'cart_description' => $source['cart_description'] ?? '',
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

        // Set full description (IMPORTANT for SEO - min 200 words!)
        if (!empty($cfg['description'])) {
            $product->set_description($cfg['description']);
            $this->log("✅ Description set (" . str_word_count(strip_tags($cfg['description'])) . " words)");
        }

        // Copy image from first product
        if ($this->config['options']['copy_images']) {
            $product->set_image_id($first_product->get_image_id());
        }

        // Copy categories from first product
        if ($this->config['options']['copy_categories']) {
            $product->set_category_ids($first_product->get_category_ids());
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

        // First: Rename original SKUs to avoid conflicts
        $this->log("  ⏳ Renaming original SKUs to avoid conflicts...");
        foreach ($source_products as $source) {
            $original = $source['product'];
            $old_sku = $original->get_sku();
            if ($old_sku && !$this->config['options']['dry_run']) {
                $original->set_sku($old_sku . '-REPLACED');
                $original->save();
            }
        }

        foreach ($source_products as $index => $source) {
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

            // Use original SKU (without -REPLACED suffix)
            $sku = str_replace('-REPLACED', '', $original->get_sku());
            $variation->set_sku($sku);
            $variation->set_stock_status($original->get_stock_status());
            $variation->set_manage_stock($original->get_manage_stock());

            if ($original->get_manage_stock()) {
                $variation->set_stock_quantity($original->get_stock_quantity());
            }

            if ($this->config['options']['copy_images']) {
                $variation->set_image_id($original->get_image_id());
            }

            $variation->set_status('publish');

            // Set variation description (Warenkorb-Kurzbeschreibung)
            $cart_desc = !empty($source['cart_description'])
                ? $source['cart_description']
                : $source['variation_label'] . ' - ' . $original->get_short_description();
            $variation->set_description($cart_desc);

            $variation_id = $variation->save();

            // Also set as post meta for compatibility
            update_post_meta($variation_id, '_variation_description', $cart_desc);

            // WooCommerce Germanized: _mini_desc for cart short description
            update_post_meta($variation_id, '_mini_desc', $cart_desc);

            $this->log("  ✅ Variation '{$source['variation_value']}' created (ID: $variation_id)");
            $this->log("     → Warenkorb-Beschreibung: " . substr($cart_desc, 0, 50) . "...");
        }
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
        $this->log("   → Focus Keyword: {$seo['focus_keyword']}");
        $this->log("   → Title: {$seo['title']}");
    }

    private function setTags($product_id) {
        if (empty($this->config['tags']) || !$this->config['options']['copy_tags']) {
            return;
        }

        $this->log("\n⏳ Setting product tags...");

        if ($this->config['options']['dry_run']) {
            return;
        }

        $tags = $this->config['tags'];
        wp_set_object_terms($product_id, $tags, 'product_tag');

        $this->log("✅ Tags set: " . implode(', ', $tags));
    }

    private function setImageAltTexts($product_id, $source_products) {
        if (!$this->config['options']['set_image_alt']) {
            return;
        }

        $this->log("\n⏳ Setting image alt texts...");

        if ($this->config['options']['dry_run']) {
            return;
        }

        $product = wc_get_product($product_id);

        // Main product image
        $main_image_id = $product->get_image_id();
        if ($main_image_id && !empty($this->config['seo']['image_alt'])) {
            update_post_meta($main_image_id, '_wp_attachment_image_alt', $this->config['seo']['image_alt']);
            $this->log("✅ Main image alt: {$this->config['seo']['image_alt']}");
        }

        // Variation images
        $children = $product->get_children();
        foreach ($children as $child_id) {
            $child = wc_get_product($child_id);
            if ($child) {
                $child_image_id = $child->get_image_id();
                if ($child_image_id) {
                    $attrs = $child->get_attributes();
                    $attr_slug = $this->config['attribute']['slug'];
                    $taxonomy = 'pa_' . $attr_slug;
                    $flavor = isset($attrs[$taxonomy]) ? ucfirst(str_replace('-', ' ', $attrs[$taxonomy])) : '';

                    $product_name = $this->config['variable_product']['name'];
                    $alt_text = "$product_name - $flavor";

                    update_post_meta($child_image_id, '_wp_attachment_image_alt', $alt_text);
                    $this->log("  ✅ Variation image alt: $alt_text");
                }
            }
        }
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
if ($config['options']['auto_delete_script'] && !$config['options']['dry_run'] && $result) {
    @unlink(__FILE__);
}
