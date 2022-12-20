<?php
/**
 * Product methods.
 *
 * @package Orderable/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Products class.
 */
class Orderable_Products {
	/**
	 * Init
	 */
	public static function run() {
		add_filter( 'woocommerce_format_price_range', array( __CLASS__, 'format_price_range' ), 10, 3 );
		add_filter( 'woocommerce_product_is_visible', array( __CLASS__, 'set_product_visibility' ), 10, 2 );
		add_action( 'template_redirect', array( __CLASS__, 'products_404' ) );
		add_filter( 'woocommerce_cart_item_permalink', array( __CLASS__, 'disable_cart_link' ), 10, 3 );
		add_filter( 'woocommerce_product_query_tax_query', array( __CLASS__, 'remove_hidden_categories_from_products_query' ), 10, 2 );
		add_filter( 'get_terms_args', array( __CLASS__, 'remove_hidden_categories_from_terms_query' ), 10, 2 );
		add_filter( 'wp_sitemaps_posts_query_args', array( __CLASS__, 'remove_hidden_products_from_sitemap' ), 10, 2 );
		add_filter( 'wp_sitemaps_taxonomies_query_args', array( __CLASS__, 'remove_hidden_categories_from_sitemap' ), 10, 2 );
	}

	/**
	 * Format price range.
	 *
	 * @param string $price
	 * @param float  $from
	 * @param float  $to
	 *
	 * @return string
	 */
	public static function format_price_range( $price, $from, $to ) {
		return sprintf( '%s: %s', __( 'From', 'iconic' ), wc_price( $from ) );
	}

	/**
	 * Get products, sorted by category.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public static function get_products_by_category( $args = array() ) {
		// Disable this filter as we don't want to disable categories during our own calls to them.
		remove_filter( 'get_terms_args', array( __CLASS__, 'remove_hidden_categories_from_terms_query' ), 10 );

		$categories     = ! empty( $args['categories'] ) ? $args['categories'] : array();
		$categories     = is_string( $categories ) ? array_filter( explode( ',', $categories ) ) : $categories;
		$has_categories = ! empty( $categories );

		$categories = self::order_categories_by_menu_order( $categories );

		$products = array();

		if ( $has_categories ) {
			foreach ( $categories as $category_id ) {
				$category = get_term( $category_id, 'product_cat' );

				if ( is_wp_error( $category ) || empty( $category ) ) {
					continue;
				}

				$products[ $category_id ] = array(
					'category' => array(
						'name'        => $category->name,
						'slug'        => $category->slug,
						'description' => $category->description,
						'depth'       => 0,
						'children'    => null,
					),
					'products' => array(),
				);

				$children_categories = get_terms(
					array(
						'taxonomy'   => 'product_cat',
						'orderby'    => 'menu_order',
						'hide_empty' => true,
						'parent'     => $category->term_id,
					)
				);

				if ( ! empty( $children_categories ) ) {
					$products[ $category_id ]['category']['children'] = array();

					foreach ( $children_categories as $child_category ) {
						$category_products = self::get_products(
							array(
								'limit'    => - 1,
								'category' => array( $child_category->slug ),
								'orderby'  => 'menu_order',
							)
						);

						if ( ! empty( $category_products ) ) {
							$products[ $category_id ]['category']['children'][ $child_category->term_id ] = array(
								'category' => array(
									'name'        => $child_category->name,
									'description' => $child_category->description,
									'depth'       => 1,
									'parent'      => $category_id,
								),
								'products' => $category_products,
							);
						}
					}
				} else {
					$category_products = self::get_products(
						array(
							'limit'    => - 1,
							'category' => array( $category->slug ),
							'orderby'  => 'menu_order',
						)
					);

					if ( ! empty( $category_products ) ) {
						$products[ $category_id ]['products'] = $category_products;

						// Add parent attribute if parent is a root category.
						if ( in_array( $category->parent, $categories, true ) ) {
							$products[ $category_id ]['category']['parent'] = $category->parent;
						}
					}
				}
			}
		} else {
			$category_products = self::get_products(
				array(
					'limit'   => - 1,
					'orderby' => 'menu_order',
				)
			);

			if ( ! empty( $category_products ) ) {
				$products[] = array(
					'category' => null,
					'products' => $category_products,
				);
			}
		}

		// Remove categories with no products.
		foreach ( $products as $key => $product_collection ) {
			if ( ! empty( $product_collection['products'] ) || ! empty( $product_collection['category']['children'] ) ) {
				continue;
			}

			unset( $products[ $key ] );
		}

		// Turn this back on to re-disable hidden categories.
		add_filter( 'get_terms_args', array( __CLASS__, 'remove_hidden_categories_from_terms_query' ), 10, 2 );

		$products = self::maybe_flatten_products_by_category( array_filter( $products ), $args );

		return apply_filters( 'orderable_get_products_by_category', $products, $args );
	}

	/**
	 * Get Products.
	 *
	 * A wrapper around the WooCommerce `wc_get_products` and `wc_products_array_orderby` functions.
	 *
	 * @param array $args Arguments.
	 *
	 * @return array
	 */
	public static function get_products( $args ) {
		$args['status'] = 'publish'; // Ensure only published products are returned.

		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$args['stock_status'] = 'instock';
		}

		/**
		 * Filter arguments used to retrieve products from database
		 *
		 * @param array $args WC_Product_Query arguments
		 *
		 * @return array New query arguments
		 * @since 1.2.1
		 * @hook  orderable_get_products_args
		 */
		$products = wc_get_products( apply_filters( 'orderable_get_products_args', $args ) );

		if ( ! empty( $products ) && isset( $args['orderby'] ) ) {
			$order    = isset( $args['order'] ) ? $args['order'] : 'asc';
			$products = wc_products_array_orderby( $products, $args['orderby'], $order );
		}

		return apply_filters( 'orderable_get_products', $products, $args );
	}

	/**
	 * Order Categories by menu order
	 *
	 * @param array $categories Categories.
	 *
	 * @return array
	 */
	public static function order_categories_by_menu_order( $categories ) {
		$categories_ordered = array();
		$category_terms     = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'orderby'    => 'menu_order',
				'hide_empty' => true,
			)
		);

		foreach ( $category_terms as $term ) {
			if ( ! in_array( $term->term_id, $categories, true ) ) {
				continue;
			}

			$categories_ordered[] = $term->term_id;
		}

		return $categories_ordered;
	}

	/**
	 * Maybe flatten products by category.
	 *
	 * @param array $products
	 * @param array $args
	 *
	 * @return array
	 */
	public static function maybe_flatten_products_by_category( $products, $args = array() ) {
		// If we're already listing all products with no categories, escape.
		if ( empty( $products ) || isset( $products[0] ) ) {
			return $products;
		}

		$flatten_level = apply_filters( 'orderable_flatten_products_by_category_level', 'all', $args );

		// Don't flatten if set to "none".
		if ( 'none' === $flatten_level ) {
			return $products;
		}

		$flattened_products = array();

		if ( 'children' === $flatten_level ) {
			foreach ( $products as $category_id => $product_group ) {
				$flattened_products[ $category_id ] = $product_group;

				if ( ! empty( $product_group['category']['children'] ) ) {
					$flattened_products[ $category_id ]['category']['children'] = array(
						array(
							'category' => null,
							'products' => array(),
						),
					);

					foreach ( $product_group['category']['children'] as $child_product_group ) {
						// Add category products to list.
						$flattened_products[ $category_id ]['category']['children'][0]['products'] = array_merge( $flattened_products[ $category_id ]['category']['children'][0]['products'], $child_product_group['products'] );

						continue;
					}
				}
			}
		} else {
			$flattened_products[] = array(
				'category' => null,
				'products' => array(),
			);

			foreach ( $products as $product_group ) {
				if ( ! empty( $product_group['products'] ) ) {
					// Add category products to list.
					$flattened_products[0]['products'] = array_merge( $flattened_products[0]['products'], $product_group['products'] );

					continue;
				} elseif ( ! empty( $product_group['category']['children'] ) ) {
					foreach ( $product_group['category']['children'] as $child_product_group ) {
						// Add category products to list.
						$flattened_products[0]['products'] = array_merge( $flattened_products[0]['products'], $child_product_group['products'] );

						continue;
					}
				}
			}
		}

		return $flattened_products;
	}

	/**
	 * Get add to cart button.
	 *
	 * @param WC_Product $product Product.
	 * @param string     $classes Button classes.
	 *
	 * @return string
	 */
	public static function get_add_to_cart_button( $product, $classes = '' ) {
		global $orderable_single_product;

		$args = array(
			'trigger'              => self::get_add_to_cart_trigger( $product ),
			'product_id'           => $product->get_id(),
			'product_type'         => $product->get_type(),
			'variation_id'         => null,
			'variation_attributes' => array(),
			'text'                 => __( 'Add', 'orderable' ),
			'classes'              => $classes,
		);

		if ( 'variable' === $args['product_type'] ) {
			$args['text'] = empty( $orderable_single_product ) ? __( 'Select', 'orderable' ) : $args['text'];
		} elseif ( 'variation' === $args['product_type'] ) {
			$args['product_id']   = $product->get_parent_id();
			$args['variation_id'] = $product->get_id();
		}

		if ( ! $product->is_in_stock() ) {
			$args['classes'] .= ' orderable-button--out-of-stock';
			$args['text']    = __( 'Out of Stock', 'orderable' );
		}

		$args = apply_filters( 'orderable_add_to_cart_button_args', $args, $product );

		return sprintf( '<button class="orderable-button %s" data-orderable-trigger="%s" data-orderable-product-id="%d" data-orderable-product-type="%s" data-orderable-variation-id="%d" data-orderable-variation-attributes="">%s</button>', esc_attr( $args['classes'] ), esc_attr( $args['trigger'] ), esc_attr( $args['product_id'] ), esc_attr( $args['product_type'] ), esc_attr( $args['variation_id'] ), wp_kses_post( $args['text'] ) );
	}

	/**
	 * Get update cart item button.
	 *
	 * @param string     $cart_item_key The cart item key.
	 * @param WC_Product $product The product to be edited.
	 * @param string     $classes Button classes.
	 *
	 * @return string
	 */
	public static function get_update_cart_item_button( $cart_item_key, $product, $classes = '' ) {
		$cart_item = WC()->cart->get_cart_item( $cart_item_key );

		$args = array(
			'trigger'              => 'update-cart-item',
			'product_id'           => $cart_item['product_id'],
			'cart_item_key'        => $cart_item_key,
			'variation_id'         => $cart_item['variation_id'],
			'variation_attributes' => empty( array_filter( $cart_item['variation'] ) ) ? false : wp_json_encode( $cart_item['variation'] ),
			'product_type'         => $product->get_type(),
			'text'                 => __( 'Update', 'orderable' ),
			'classes'              => $classes,
		);

		/**
		 * Filter arguments used to the update cart item button.
		 *
		 * @param array $args The arguments.
		 *
		 * @return array New arguments
		 * @since 1.4.0
		 * @hook  orderable_update_cart_item_button_args
		 */
		$args = apply_filters( 'orderable_update_cart_item_button_args', $args, $cart_item_key );

		ob_start();
		?>
		<button
			class="orderable-button orderable-product__cancel-update"
			data-orderable-trigger="show-cart"
		>
			<?php echo esc_html__( 'Cancel', 'orderable' ); ?>
		</button>
		<button
			class="orderable-button <?php echo esc_attr( $args['classes'] ); ?>"
			data-orderable-trigger="<?php echo esc_attr( $args['trigger'] ); ?>"
			data-orderable-cart-item-key="<?php echo esc_attr( $args['cart_item_key'] ); ?>"
			data-orderable-product-id="<?php echo esc_attr( $args['product_id'] ); ?>"
			data-orderable-variation-id="<?php echo esc_attr( $args['variation_id'] ); ?>"
			data-orderable-variation-attributes="<?php echo esc_attr( $args['variation_attributes'] ); ?>"
			data-orderable-product-type="<?php echo esc_attr( $args['product_type'] ); ?>"
		>
			<?php echo esc_html( $args['text'] ); ?>
		</button>
		<?php

		/**
		 * Filter the Update Cart Item button HTML.
		 *
		 * @param array $update_cart_item_button_html The Update Cart Item button HTML.
		 *
		 * @return array New HTML
		 * @since 1.4.0
		 * @hook  orderable_update_cart_item_button_html
		 */
		$html = apply_filters( 'orderable_update_cart_item_button_html', ob_get_clean() );

		return $html;
	}

	/**
	 * Get add to cart trigger value.
	 *
	 * @param WC_Product $product
	 *
	 * @return string
	 */
	public static function get_add_to_cart_trigger( $product ) {
		$trigger = $product->is_type( 'variable' ) ? 'product-options' : 'add-to-cart';

		return apply_filters( 'orderable_get_add_to_cart_trigger', $trigger, $product );
	}

	/**
	 * Get a list of attributes for available variations.
	 *
	 * @param WC_Product_Variable $product
	 *
	 * @return array
	 */
	public static function get_available_variation_attributes( $product ) {
		$available_variation_attributes = array();
		$available_variations           = $product->get_available_variations();
		$available_variations           = wc_list_pluck( $available_variations, 'attributes' );

		if ( empty( $available_variations ) ) {
			return $available_variation_attributes;
		}

		foreach ( $available_variations as $available_variation ) {
			foreach ( $available_variation as $attribute_slug => $attribute_value ) {
				if ( ! isset( $available_variation_attributes[ $attribute_slug ] ) ) {
					$available_variation_attributes[ $attribute_slug ] = array();
				}

				$available_variation_attributes[ $attribute_slug ][] = $attribute_value;
			}
		}

		return $available_variation_attributes;
	}

	/**
	 * Get available attributes.
	 *
	 * This method remove an attributes which don't belong to
	 * an active variation.
	 *
	 * @param WC_Product_Variable $product
	 *
	 * @return array
	 */
	public static function get_available_attributes( $product ) {
		$available_attributes           = array();
		$attributes                     = $product->get_variation_attributes();
		$available_variation_attributes = self::get_available_variation_attributes( $product );

		if ( empty( $attributes ) ) {
			return $available_attributes;
		}

		foreach ( $attributes as $attribute_name => $attribute_terms ) {
			$attribute_name_sanitized = wc_variation_attribute_name( $attribute_name );

			if ( empty( $available_variation_attributes[ $attribute_name_sanitized ] ) ) {
				continue;
			}

			if ( ! isset( $available_attributes[ $attribute_name ] ) ) {
				$available_attributes[ $attribute_name ] = array();
			}

			foreach ( $attribute_terms as $attribute_term ) {
				if ( ! in_array( $attribute_term, $available_variation_attributes[ $attribute_name_sanitized ], true ) && ! in_array( '', $available_variation_attributes[ $attribute_name_sanitized ], true ) ) {
					continue;
				}

				$available_attributes[ $attribute_name ][] = $attribute_term;
			}
		}

		return $available_attributes;
	}

	/**
	 * Is product single page hidden?
	 *
	 * @param int|WC_Product $product
	 *
	 * @return bool
	 */
	public static function is_product_hidden( $product ) {
		if ( is_numeric( $product ) ) {
			$product = wc_get_product( $product );
		}

		if ( empty( $product ) ) {
			return false;
		}

		$categories = $product->get_category_ids();

		if ( empty( $categories ) ) {
			return false;
		}

		foreach ( $categories as $category_id ) {
			if ( self::is_category_hidden( $category_id ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Is this category hidden?
	 *
	 * @param $category_id
	 *
	 * @return bool
	 */
	public static function is_category_hidden( $category_id ) {
		$hidden_categories = Orderable_Settings::get_hidden_categories();

		return in_array( $category_id, $hidden_categories, true );
	}

	/**
	 * Set if product is visible, based on category settings.
	 *
	 * @param bool $visible
	 * @param int  $product_id
	 *
	 * @return bool
	 */
	public static function set_product_visibility( $visible, $product_id ) {
		$hidden = self::is_product_hidden( $product_id );

		return $hidden ? false : $visible;
	}

	/**
	 * Disable cart permalink.
	 *
	 * @param string $permalink
	 *
	 * @return bool
	 */
	public static function disable_cart_link( $permalink, $cart_item, $cart_item_key ) {
		if ( ! isset( $cart_item['data'] ) ) {
			return $permalink;
		}

		return self::is_product_hidden( $cart_item['data'] ) ? false : $permalink;
	}

	/**
	 * Set product page to 404 if required.
	 */
	public static function products_404() {
		if ( is_admin() || ! ( is_product() && is_single() ) ) {
			return;
		}

		global $post;

		if ( empty( $post ) ) {
			return;
		}

		$product = wc_get_product( $post->ID );

		if ( empty( $product ) || ! self::is_product_hidden( $product ) ) {
			return;
		}

		global $wp_query;

		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}

	/**
	 * Exclude hidden categories from queries.
	 *
	 * @param $tax_query
	 * @param $wc_query
	 *
	 * @return array
	 */
	public static function remove_hidden_categories_from_products_query( $tax_query, $wc_query ) {
		$hidden_categories = Orderable_Settings::get_hidden_categories();

		if ( empty( $hidden_categories ) ) {
			return $tax_query;
		}

		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $hidden_categories,
			'operator' => 'NOT IN',
		);

		return $tax_query;
	}

	/**
	 * Remove hidden categories from terms query.
	 *
	 * As well as hiding from terms lists (sidebar widgets, etc),
	 * this also disables the archive page.
	 *
	 * @param array $args       Args.
	 * @param array $taxonomies Taxonomies.
	 *
	 * @return mixed
	 */
	public static function remove_hidden_categories_from_terms_query( $args, $taxonomies ) {
		if ( is_admin() ) {
			return $args;
		}

		$taxonomy = ! empty( $args['taxonomy'] ) && isset( $args['taxonomy'][0] ) ? $args['taxonomy'][0] : false;

		if ( 'product_cat' !== $taxonomy || is_admin() ) {
			return $args;
		}

		// Exclude hidden categories.
		$hidden_categories = Orderable_Settings::get_hidden_categories();
		$args['exclude']   = ! is_array( $args['exclude'] ) ? array() : $args['exclude'];
		$args['exclude']   = array_merge( $args['exclude'], $hidden_categories );

		return $args;
	}

	/**
	 * Remove hidden products from sitemap.
	 *
	 * @param array  $query_args Query args.
	 * @param string $post_type  Post type.
	 *
	 * @return mixed
	 */
	public static function remove_hidden_products_from_sitemap( $query_args, $post_type ) {
		if ( 'product' !== $post_type ) {
			return $query_args;
		}

		$hidden_categories = Orderable_Settings::get_hidden_categories();

		if ( empty( $hidden_categories ) ) {
			return $query_args;
		}

		$tax_query = array(
			'taxonomy'         => 'product_cat',
			'terms'            => $hidden_categories,
			'field'            => 'term_id',
			'include_children' => true,
			'operator'         => 'NOT IN',
		);

		if ( ! isset( $query_args['tax_query'] ) ) {
			$query_args['tax_query'] = array();
		}

		$query_args['tax_query'][] = $tax_query;

		return $query_args;
	}

	/**
	 * Exclude hidden categories from sitemap.
	 *
	 * @param array  $query_args Query args.
	 * @param string $taxonomy   Taxonomy.
	 *
	 * @return mixed
	 */
	public static function remove_hidden_categories_from_sitemap( $query_args, $taxonomy ) {
		if ( 'product_cat' !== $taxonomy ) {
			return $query_args;
		}

		$hidden_categories = Orderable_Settings::get_hidden_categories();

		if ( empty( $hidden_categories ) ) {
			return $query_args;
		}

		if ( ! isset( $query_args['exclude'] ) ) {
			$query_args['exclude'] = $hidden_categories;
		} else {
			if ( is_array( $hidden_categories ) ) {
				$query_args['exclude'] = array_merge( $query_args['exclude'], $hidden_categories );
			} else {
				$query_args['exclude'] = $query_args['exclude'] . ',' . implode( ',', $hidden_categories );
			}
		}

		return $query_args;
	}

	/**
	 * Get product accordion data.
	 *
	 * @param WC_Product $product Product.
	 *
	 * @return array
	 */
	public static function get_accordion_data( $product ) {
		$data = array();

		$description = Orderable_Settings::get_setting( 'drawer_quickview_description' );

		if ( 'none' !== $description ) {
			$description = 'short' === $description ? $product->get_short_description() : $product->get_description();

			$data[] = array(
				'title'   => __( 'Description', 'orderable' ),
				'content' => apply_filters( 'the_content', $description ),
				'id'      => 'accordion-description',
			);
		}

		/**
		 * Filter product accordion data.
		 *
		 * @var array      $data
		 * @var WC_Product $product
		 */
		return apply_filters( 'orderable_get_accordion_data', $data, $product );
	}
}
