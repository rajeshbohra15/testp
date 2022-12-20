<?php 
     $settings       = $this->settings_obj;
     do_action('wpc_before_minicart');

if ( !empty($settings['minicart_style']) && $settings['minicart_style'] === 'style-1' ) { 
    // style 1
    ?>
    <div class="wpc_cart_block wpc-minicart-wrapper style1 wpc-cart_main_block">

    <a href="#" class="wpc_cart_icon">
        <div class="wpc-cart-message"><?php echo esc_html__('Product has been added', 'wpcafe'); ?></div>

        <i class="<?php echo esc_attr($wpc_cart_icon); ?>"></i>
        <sup class="basket-item-count" style="display: inline-block;">
            <span class="cart-items-count count wpc-mini-cart-count"></span>
        </sup>
    </a>
        <div class="wpc-menu-mini-cart wpc_background_color">
                <div class="widget_shopping_cart_content"> 
                     <?php
                        if(file_exists(\Wpcafe::core_dir().'modules/mini-cart/views/mini-cart-template.php')){
                            include_once \Wpcafe::core_dir().'modules/mini-cart/views/mini-cart-template.php';
                        }
                    ?>
                </div>
                
            </div>
        </div>
    <?php  
}else{ 
    // style 2
    ?>
    <div class="wpc-minicart-wrapper style2 wpc-cart_main_block">
        <a href="#" class="wpc_cart_icon">
            <div class="wpc-cart-message"><?php echo esc_html__('Product has been added', 'wpcafe'); ?></div>

            <i class="<?php echo esc_attr($wpc_cart_icon); ?>"></i>
            <sup class="basket-item-count" style="display: inline-block;">
                <span class="cart-items-count count wpc-mini-cart-count"></span>
            </sup>
        </a>
        <div class="wpc_cart_block">
            <div class="wpc-minicart-header">
                <div class="cart-counts">
                    <?php echo esc_html__('Cart', 'wpcafe'); ?>
                    <span class="cart-count">
                        (<span class="cart-items-count count wpc-mini-cart-count"></span><?php echo esc_html__(' items', 'wpcafe'); ?>)
                    </span>
                </div>
                <button type="button" class="minicart-close wpc-btn-border wpc-btn">
                    <svg width="12" height="12" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path 
                        fill-rule="evenodd" 
                        clip-rule="evenodd" 
                        d="M2.76992 0.7523C2.28177 0.264145 1.49031 0.264145 1.00216 0.7523C0.514001 1.24046 0.514001 2.03191 1.00216 2.52007L6.48223 8.00014L1.00216 13.4802C0.514002 13.9684 0.514001 14.7598 1.00216 15.248C1.49031 15.7361 2.28177 15.7361 2.76992 15.248L8.25 9.76791L13.7301 15.248C14.2182 15.7361 15.0097 15.7361 15.4978 15.248C15.986 14.7598 15.986 13.9684 15.4978 13.4802L10.0178 8.00014L15.4978 2.52007C15.986 2.03191 15.986 1.24046 15.4978 0.7523C15.0097 0.264145 14.2182 0.264145 13.7301 0.7523L8.25 6.23238L2.76992 0.7523Z" 
                        fill="white"/>
                    </svg>
                </button>
            </div>
            <div class="wpc-menu-mini-cart wpc_background_color">
                <div class="widget_shopping_cart_content">
                     <?php
                        
                        if(file_exists(\Wpcafe::core_dir().'modules/mini-cart/views/mini-cart-template.php')){
                            include_once \Wpcafe::core_dir().'modules/mini-cart/views/mini-cart-template.php';
                        }
                        
                    ?>
                </div>
                
            </div>
        </div>
    </div>
    <?php
}
?>

<?php do_action('wpc_after_minicart');?>