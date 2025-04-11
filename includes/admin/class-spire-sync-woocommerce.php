<?php
namespace SpireSync\Admin;

class Spire_Sync_WooCommerce {

    /**
     * Inserts or updates WooCommerce products based on Spire API response records.
     *
     * Each record is expected to have an 'id' field, which is matched
     * against the WooCommerce product meta key 'id'. If a match is found,
     * the product is updated; otherwise, a new product is created.
     *
     * Progress is updated in a transient keyed by the brand.
     *
     * @param array  $response The Spire API response (expects 'records' and 'count').
     * @param string $brand    The brand used in the sync.
     * @return void
     */
    public function insert_or_update_products( $response, $brand ) {
        // Ensure a valid response.
        if ( ! is_array( $response ) || ! isset( $response['records'] ) ) {
            return;
        }

        // Instantiate WooCommerce Logger.
        $logger = new \WC_Logger();
        $logger->info( "Starting product sync for brand {$brand}.", [ 'source' => 'spire-sync' ] );

        $records = $response['records'];
        $total   = isset( $response['count'] ) ? intval( $response['count'] ) : count( $records );

        $logger->info( "Total records received: {$total}.", [ 'source' => 'spire-sync' ] );

        $progress = [
            'status'    => "Received {$total} product records for brand {$brand} from Spire.",
            'processed' => 0,
            'total'     => $total,
        ];
        set_transient( "spire_sync_progress_{$brand}", $progress, 5 * MINUTE_IN_SECONDS );

        foreach ( $records as $record ) {
            $spire_record_id = isset( $record['id'] ) ? $record['id'] : '';
            if ( empty( $spire_record_id ) ) {
                continue;
            }

            // Search for an existing product that has the meta field 'id' equal to $spire_record_id.
            $args = [
                'post_type'      => 'product',
                'meta_key'       => 'id',
                'meta_value'     => $spire_record_id,
                'posts_per_page' => 1,
                'fields'         => 'ids',
            ];
            $query = new \WP_Query( $args );

            if ( $query->have_posts() ) {
                $product_id = $query->posts[0];
                $product = wc_get_product( $product_id );
                if ( $product ) {
                    $logger->info( "Updating product ID {$product_id} for Spire record ID {$spire_record_id}.", [ 'source' => 'spire-sync' ] );
                    
                    // Update product title.
                    $product->set_name( isset( $record['description'] ) ? $record['description'] : 'Product ' . $spire_record_id );

                    // Update pricing.
                    $price = 0;
                    if ( isset( $record['pricing']['sellPrice'] ) && is_array( $record['pricing']['sellPrice'] ) ) {
                        $price = $record['pricing']['sellPrice'][0];
                    }
                    $product->set_regular_price( $price );
                    $product->set_price( $price );
                    
                    // Update weight.
                    if ( isset( $record['uom']['weight'] ) ) {
                        $product->set_weight( $record['uom']['weight'] );
                    }
                    
                    // Optionally update a brand or categories, etc.
                    // ...
                    
                    // Update featured image.
                    if ( isset( $record['udf']['featured_image'] ) && ! empty( $record['udf']['featured_image'] ) ) {
                        $image_url = esc_url( $record['udf']['featured_image'] );
                        $image_id = $this->get_image_id_from_url( $image_url );
                        if ( $image_id ) {
                            $product->set_image_id( $image_id );
                        } else {
                            $product->set_image_id( 0 );
                        }
                    } else {
                        $product->set_image_id( 0 );
                    }
                    
                    $product->save();
                }
            } else {
                // Create a new product.
                $logger->info( "Creating new product for Spire record ID {$spire_record_id}.", [ 'source' => 'spire-sync' ] );
                $price = 0;
                if ( isset( $record['pricing']['sellPrice'] ) && is_array( $record['pricing']['sellPrice'] ) ) {
                    $price = $record['pricing']['sellPrice'][0];
                }
                $new_product_args = [
                    'post_title'  => wp_strip_all_tags( isset( $record['description'] ) ? $record['description'] : 'Product ' . $spire_record_id ),
                    'post_status' => 'publish',
                    'post_type'   => 'product',
                    'meta_input'  => [
                        'id'          => $spire_record_id, // Matching meta key.
                        '_regular_price' => $price,
                        '_price'         => $price,
                        '_weight'        => isset( $record['uom']['weight'] ) ? $record['uom']['weight'] : '',
                    ]
                ];
                $new_product_id = wp_insert_post( $new_product_args );
                if ( $new_product_id && ! is_wp_error( $new_product_id ) ) {
                    $product = wc_get_product( $new_product_id );
                    if ( $product ) {
                        $product->save();
                    }
                }
            }
            wp_reset_postdata();
            
            // Update progress.
            $progress['processed']++;
            $progress['status'] = "Processed {$progress['processed']} of {$total} records for brand {$brand}...";
            set_transient( "spire_sync_progress_{$brand}", $progress, 5 * MINUTE_IN_SECONDS );
        }
        // Finalize progress.
        $progress['status'] = "Sync complete for brand {$brand}.";
        set_transient( "spire_sync_progress_{$brand}", $progress, 5 * MINUTE_IN_SECONDS );
        $logger->info( "Product sync complete for brand {$brand}: Processed {$progress['processed']} records.", [ 'source' => 'spire-sync' ] );
    }

    /**
     * Extracts the image ID from a given image URL.
     *
     * This method extracts the file name (without extension) from the URL
     * and queries the wp_posts table to find an attachment whose post_title matches.
     *
     * @param string $image_url The URL of the image.
     * @return int|null The attachment ID if found, or null if not found.
     */
    public function get_image_id_from_url( $image_url ) {
        if ( empty( $image_url ) || ! filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
            return null;
        }
        $filename = pathinfo( $image_url, PATHINFO_FILENAME );
        if ( empty( $filename ) ) {
            return null;
        }
        global $wpdb;
        $attachment_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM $wpdb->posts 
                 WHERE post_type = 'attachment' 
                 AND LOWER(post_title) = LOWER(%s)
                 LIMIT 1",
                $filename
            )
        );
        return $attachment_id ? (int)$attachment_id : null;
    }
}