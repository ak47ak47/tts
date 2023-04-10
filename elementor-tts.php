<?php
/*
Plugin Name: Elementor Text-to-Speech
Plugin URI: https://xllearners.tech
Description: A plugin that adds text-to-speech functionality to Elementor.
Version: 1.0
Author: Anas khan
Author URI: https://xllearners.tech
*/

// Add a new section to the Elementor widget panel
add_action( 'elementor/elements/categories_registered', function( $elements_manager ) {
    $elements_manager->add_category(
        'tts',
        array(
            'title' => __( 'Text-to-Speech', 'elementor-tts' ),
            'icon' => 'fa fa-microphone',
        )
    );
} );

// Register the TTS widget
add_action( 'elementor/widgets/widgets_registered', function( $widgets_manager ) {
    require_once( 'widget.php' );
    $widgets_manager->register_widget_type( new Elementor_TTS_Widget() );
} );

// Add a new pricing plan to the Elementor Pro pricing widget
add_filter( 'elementor_pro/forms/purchase_options/pricing_table', function( $pricing_table ) {
    $pricing_table['tts'] = array(
        'label' => __( 'Text-to-Speech', 'elementor-tts' ),
        'value' => 'tts',
        'featured' => false,
    );
    return $pricing_table;
} );

// Handle the TTS widget form submission
add_action( 'wp_ajax_tts_submit', 'tts_submit' );
add_action( 'wp_ajax_nopriv_tts_submit', 'tts_submit' );
function tts_submit() {
    // Validate the input
    $nonce = $_POST['nonce'];
    if ( ! wp_verify_nonce( $nonce, 'tts_nonce' ) ) {
        wp_send_json_error( 'Invalid nonce' );
    }
    $text = sanitize_text_field( $_POST['text'] );
    $voice = sanitize_text_field( $_POST['voice'] );
    $language = sanitize_text_field( $_POST['language'] );
    $price = sanitize_text_field( $_POST['price'] );

    // Save the TTS request to the database
    $data = array(
        'text' => $text,
        'voice' => $voice,
        'language' => $language,
        'price' => $price,
        'timestamp' => time(),
    );
    $result = wp_insert_post( array(
        'post_type' => 'tts_request',
        'post_title' => 'TTS Request',
        'post_content' => json_encode( $data ),
        'post_status' => 'publish',
    ) );

    // Return the result
    if ( $result ) {
        wp_send_json_success();
    } else {
        wp_send_json_error( 'Failed to save TTS request' );
    }
}

// Define the TTS widget class
class Elementor_TTS_Widget extends \Elementor\Widget_Base {
    public function get_name() {
        return 'tts_widget';
    }
    public function get_title() {
        return __( 'Text-to-Speech', 'elementor-tts' );
    }
    public function get_icon() {
        return 'fa fa-microphone';
    }
    public function get_categories() {
        return array( 'tts' );
    }
    protected function _register_controls() {
        // Add the widget controls
        $this->start_controls_section(
            'tts_section',
            array(
            'label' => __( 'Text-to-Speech', 'elementor-tts' ),
            'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
            );
$this->add_control(
'text',
array(
'label' => __( 'Text', 'elementor-tts' ),
'type' => \Elementor\Controls_Manager::TEXTAREA,
'rows' => 5,
'default' => __( 'Enter your text here', 'elementor-tts' ),
)
);
$this->add_control(
'voice',
array(
'label' => __( 'Voice', 'elementor-tts' ),
'type' => \Elementor\Controls_Manager::SELECT,
'default' => 'male',
'options' => array(
'male' => __( 'Male', 'elementor-tts' ),
'female' => __( 'Female', 'elementor-tts' ),
),
)
);
$this->add_control(
'language',
array(
'label' => __( 'Language', 'elementor-tts' ),
'type' => \Elementor\Controls_Manager::SELECT,
'default' => 'en-US',
'options' => array(
'en-US' => __( 'English (US)', 'elementor-tts' ),
'en-GB' => __( 'English (UK)', 'elementor-tts' ),
'fr-FR' => __( 'French', 'elementor-tts' ),
'de-DE' => __( 'German', 'elementor-tts' ),
'es-ES' => __( 'Spanish', 'elementor-tts' ),
),
)
);
$this->end_controls_section();
}
protected function render() {
$settings = $this->get_settings_for_display();
$nonce = wp_create_nonce( 'tts_nonce' );
$voice = $settings['voice'];
$language = $settings['language'];
$price = isset( $_GET['price'] ) ? sanitize_text_field( $_GET['price'] ) : '';
    // Render the TTS widget
    echo '<div class="tts-widget">';
    echo '<textarea class="tts-textarea">' . esc_html( $settings['text'] ) . '</textarea>';
    echo '<div class="tts-options">';
    echo '<select class="tts-voice">';
    echo '<option value="male" ' . selected( $voice, 'male', false ) . '>' . __( 'Male', 'elementor-tts' ) . '</option>';
    echo '<option value="female" ' . selected( $voice, 'female', false ) . '>' . __( 'Female', 'elementor-tts' ) . '</option>';
    echo '</select>';
    echo '<select class="tts-language">';
    echo '<option value="en-US" ' . selected( $language, 'en-US', false ) . '>' . __( 'English (US)', 'elementor-tts' ) . '</option>';
    echo '<option value="en-GB" ' . selected( $language, 'en-GB', false ) . '>' . __( 'English (UK)', 'elementor-tts' ) . '</option>';
    echo '<option value="fr-FR" ' . selected( $language, 'fr-FR', false ) . '>' . __( 'French', 'elementor-tts' ) . '</option>';
    echo '<option value="de-DE" ' . selected( $language, 'de-DE', false ) . '>' . __( 'German', 'elementor-tts' ) . '</option>';
    echo '<option value="es-ES" ' . selected( $language, 'es-ES', false ) . '>' . __( 'Spanish', 'elementor-tts' ) . '</option>';
    echo '</select>';
    echo '<button class="tts-button" data-nonce="' . esc_attr( $nonce ) . '">' . __( 'Speak', 'elementor-tts' ) . '</button>';
    echo '</div>';
    echo '</div>';
    // Render the pricing plans
    if ( $price !== '' ) {
        echo '<div class="tts-pricing">';
        echo '<h3>' . __( 'Pricing Plans', 'elementor-tts' ) . '</h3>';
        if ( $price === 'basic' ) {
            echo '<ul>';
            echo '<li>' . __( '1 voice', 'elementor-tts' ) . '</li>';
            echo '<li>' . __( '1 language', 'elementor-tts' ) . '</li>';
            echo '<li>' . __( 'Unlimited texts', 'elementor-tts' ) . '</li>';
            echo '</ul>';
            echo '<a href="https://example.com/checkout?plan=basic" class="tts-button">' . __( 'Buy Now', 'elementor-tts' ) . '</a>';
        } elseif ( $price === 'pro' ) {
            echo '<ul>';
            echo '<li>' . __( '2 voices', 'elementor-tts' ) . '</li>';
            echo '<li>' . __( '3 languages', 'elementor-tts' ) . '</li>';
            echo '<li>' . __( 'Unlimited texts', 'elementor-tts' ) . '</li>';
            echo '</ul>';
            echo '<a href="https://example.com/checkout?plan=pro" class="tts-button">' . __( 'Buy Now', 'elementor-tts' ) . '</a>';
        } elseif ( $price === 'premium' ) {
            echo '<ul>';
            echo '<li>' . __( '5 voices', 'elementor-tts' ) . '</li>';
            echo '<li>' . __( '5 languages', 'elementor-tts' ) . '</li>';
            echo '<li>' . __( 'Unlimited texts', 'elementor-tts' ) . '</li>';
            echo '</ul>';
            echo '<a href="https://example.com/checkout?plan=premium" class="tts-button">' . __( 'Buy Now', 'elementor-tts' ) . '</a>';
        }
        echo '</div>';
    }
}

protected function _content_template() {
    ?>
    <#
    view.addInlineEditingAttributes( 'text', 'none' );
    view.addRenderAttribute( 'tts-textarea', 'rows', 5 );
    #>
    <div class="tts-widget">
        <textarea {{{ view.getRenderAttributeString( 'tts-textarea' ) }}} class="tts-textarea" {{{ view.getInlineEditingAttributes( 'text' ) }}}>{{{ settings.text }}}</textarea>
        <div class="tts-options">
            <select class="tts-voice">
                <option value="male" <# if ( 'male' === settings.voice ) { #>selected="selected"<# } #>>{{ __( 'Male', 'elementor-tts' ) }}</option>
                <option value="female" <# if ( 'female' === settings.voice ) { #>selected="selected"<# } #>>{{ __( 'Female', 'elementor-tts' ) }}</option>
            <option value="en-US" <# if ( 'en-US' === settings.language ) { #>selected="selected"<# } #>>{{ __( 'English', 'elementor-tts' ) }}</option>
                    <option value="es-ES" <# if ( 'es-ES' === settings.language ) { #>selected="selected"<# } #>>{{ __( 'Spanish', 'elementor-tts' ) }}</option>
                </select>
                <button class="tts-button" data-nonce="{{{ settings.nonce }}}">{{{ settings.button_text }}}</button>
            </div>
        </div>
        <# if ( settings.price ) { #>
            <div class="tts-pricing">
                <h3>{{{ settings.price_title }}}</h3>
                <ul>
                    <# if ( 'basic' === settings.price ) { #>
                        <li>{{{ __( '1 voice', 'elementor-tts' ) }}}</li>
                        <li>{{{ __( '1 language', 'elementor-tts' ) }}}</li>
                        <li>{{{ __( 'Unlimited texts', 'elementor-tts' ) }}}</li>
                        <li><a href="https://example.com/checkout?plan=basic" class="tts-button">{{{ __( 'Buy Now', 'elementor-tts' ) }}}</a></li>
                    <# } else if ( 'pro' === settings.price ) { #>
                        <li>{{{ __( '2 voices', 'elementor-tts' ) }}}</li>
                        <li>{{{ __( '3 languages', 'elementor-tts' ) }}}</li>
                        <li>{{{ __( 'Unlimited texts', 'elementor-tts' ) }}}</li>
                        <li><a href="https://example.com/checkout?plan=pro" class="tts-button">{{{ __( 'Buy Now', 'elementor-tts' ) }}}</a></li>
                    <# } else if ( 'premium' === settings.price ) { #>
                        <li>{{{ __( '5 voices', 'elementor-tts' ) }}}</li>
                        <li>{{{ __( '5 languages', 'elementor-tts' ) }}}</li>
                        <li>{{{ __( 'Unlimited texts', 'elementor-tts' ) }}}</li>
                        <li><a href="https://example.com/checkout?plan=premium" class="tts-button">{{{ __( 'Buy Now', 'elementor-tts' ) }}}</a></li>
                    <# } #>
                </ul>
            </div>
        <# } #>
        <?php
    }
}
function elementor_tts_register_widget() {
\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Elementor_TTS_Widget() );
}
add_action( 'elementor/widgets/widgets_registered', 'elementor_tts_register_widget' );

/**

Enqueue the necessary scripts and styles for the widget
**/
function elementor_tts_enqueue_scripts() {
wp_enqueue_script( 'elementor-tts-script', plugins_url( 'elementor-tts.js', FILE ), array( 'jquery' ), '1.0', true );
wp_localize_script( 'elementor-tts-script', 'elementorTTS', array(
'ajaxurl' => admin_url( 'admin-ajax.php' ),
'nonce' => wp_create_nonce( 'elementor-tts-nonce' ),
'default_voice' => 'en-US',
'default_language' => 'en-US',
'male_voice' => 'en-US-Wavenet-Male',
'female_voice' => 'en-US-Wavenet-C',
'price_title' => __( 'Pricing', 'elementor-tts' ),
'basic_price' => 'basic',
'pro_price' => 'pro',
'premium_price' => 'premium',
'buy_now' => __( 'Buy Now', 'elementor-tts' ),
) );
wp_enqueue_style( 'elementor-tts-style', plugins_url( 'elementor-tts.css', FILE ), array(), '1.0', 'all' );
}
add_action( 'elementor/frontend/after_enqueue_styles', 'elementor_tts_enqueue_scripts' );

/**

Handle the text-to-speech request
**/
function elementor_tts_request() {
// Verify the nonce
if ( ! wp_verify_nonce( $_POST['nonce'], 'elementor-tts-nonce' ) ) 
{
                       ( wp_send_json_error( 'Invalid nonce' ));
}

// Get the user input
$text = sanitize_text_field( $_POST['text'] );
$voice = sanitize_text_field( $_POST['voice'] );
$language = sanitize_text_field( $_POST['language'] );

// Build the API request URL
$url = 'https://texttospeech.googleapis.com/v1/text:synthesize?key=' . get_option( 'elementor_tts_api_key' );
$data = array(
'input' => array(
'text' => $text,
),
'voice' => array(
'languageCode' => $language,
'name' => $voice,
),
'audioConfig' => array(
'audioEncoding' => 'MP3',
),
);
$args = array(
'body' => json_encode( $data ),
'headers' => array(
'Content-Type' => 'application/json',
),
);

// Send the API request and return the audio URL
$response = wp_remote_post( $url, $args );
if ( is_wp_error( $response ) ) {
wp_send_json_error( $response->get_error_message() );
}
$data = json_decode( wp_remote_retrieve_body( $response ) );
if ( empty( $data ) || empty( $data->audioContent ) ) {
wp_send_json_error( 'Invalid API response' );
}
$audio_url = 'data:audio/mp3;base64,' . $data->audioContent;
wp_send_json_success( array( 'audio_url' => $audio_url ) );
}
add_action( 'wp_ajax_elementor_tts_request', 'elementor_tts_request' );
/**

Register the plugin settings
*/
function elementor_tts_register_settings() {
register_setting( 'elementor_tts_settings', 'elementor_tts_api_key' );
}
add_action( 'admin_init', 'elementor_tts_register_settings' );
/**

Add the plugin settings page
**/
function elementor_tts_add_settings_page() {
add_submenu_page(
'options-general.php',
                    __( 'Text-to-Speech', 'elementor-tts' ),
__( 'Text-to-Speech', 'elementor-tts' ),
'manage_options',
'elementor-tts',
'elementor_tts_render_settings_page'
);
}
add_action( 'admin_menu', 'elementor_tts_add_settings_page' );
/**

Render the plugin settings page
*/
function elementor_tts_render_settings_page() {
if ( ! current_user_can( 'manage_options' ) ) {
wp_die( __( 'You do not have sufficient permissions to access this page.', 'elementor-tts' ) );
}
?>
 <div class="wrap">
     <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
     <form method="post" action="options.php">
         <?php settings_fields( 'elementor_tts_settings' ); ?>
         <?php do_settings_sections( 'elementor_tts_settings' ); ?>
         <table class="form-table">
             <tr>
                 <th scope="row"><label for="elementor_tts_api_key"><?php _e( 'Google Cloud API Key', 'elementor-tts' ); ?></label></th>
                 <td><input type="text" id="elementor_tts_api_key" name="elementor_tts_api_key" value="<?php echo esc_attr( get_option( 'elementor_tts_api_key' ) ); ?>" class="regular-text"></td>
             </tr>
         </table>
         <?php submit_button(); ?>
     </form>
 </div>
 <?php
}