//Agregar campo extra en checkout
function rfc_checkout_campoextra($c){
    $c['billing']['factura'] = array(
        'type' => 'checkbox',
        'css' => array('form-row-wide'),
        'label' => 'Requiere Factura?',
        'id' => 'factura'
   );
   $c['billing']['rfc'] = array(
        'type' => 'text',
        'css' => array('form-row-wide'),
        'label' => 'Introduzca su RFC ',
        'id' => 'rfc_factura'
   );
   //Encuesta
   $c['billing']['encuesta'] = array(
        'type' => 'select',
        'css' => array('form-row-wide'),
        'label' => 'Como te enteraste de nosotros? ',
        'options' => array(
            'default' => 'Seleccione una Opción',
            'twitter' => 'Twitter',
            'facebook' => 'Facebook',
            'instagram' => 'Instagram'
    )
);
   return $c;
}
add_filter( 'woocommerce_checkout_fields','rfc_checkout_campoextra',40);

//Checkout Dinamico (javascript +php)
function checkout_dinamico_factura(){
    //Aqui codigo JS / funciones JS nativas de woocomerce (cerrar y abrir etiquetas php)
    if(is_checkout()){ ?>
            <script>
                jQuery(document).ready(function(){
                    jQuery('input[type="checkbox"]#factura').on('change', function(){
                        jQuery('#rfc_factura').slideToggle();
                    })
                })
            </script>
    <?php }
}
add_action('wp_footer' ,'checkout_dinamico_factura');

//Insertando datos en la bd 
function guardar_datos_custom( $orden_id ){
    if( ! empty ($_POST['rfc'] )){
        update_post_meta($orden_id,'rfc', sanitize_text_field( $_POST['rfc'] ));
    }
    if( ! empty ($_POST['factura'] )){
        update_post_meta($orden_id,'factura', sanitize_text_field( $_POST['factura'] ));
    }
    if( ! empty ($_POST['encuesta'] )){
        update_post_meta($orden_id,'encuesta', sanitize_text_field( $_POST['encuesta'] ));
    }
}
add_action( 'woocommerce_checkout_update_order_meta','guardar_datos_custom');

//Agregando columnas personalizadas a la ordenes Part 1
function campos_extras_panel_pedidos_woocommerce($mi_columna){
    //var_dump($mi_columna);
    $mi_columna['factura'] = __('Factura', 'woocommerce');
    $mi_columna['rfc'] = __('RFC', 'woocommerce');
    $mi_columna['encuesta'] = __('Resultados Encuesta', 'woocommerce');
    return $mi_columna;
}
add_filter( 'manage_edit-shop_order_columns', 'campos_extras_panel_pedidos_woocommerce');

//Mostrando los datos en el panel - Part 2
function campos_extras_informacion($mi_columna){
    global $post, $woocommerce, $order;
    if( empty($order) || $order->id != $post->ID ){
        $order = new WC_Order( $post->ID);
        echo var_dump($order);
    }

    if( $mi_columna === 'factura'){
        $factura = get_post_meta( $post->ID, 'factura', true );
        if($factura){
            echo "Si Solicito";
        }
    }

    if( $mi_columna === 'rfc'){
        echo get_post_meta( $post->ID, 'rfc', true );
    }

    if( $mi_columna === 'encuesta'){
        echo get_post_meta( $post->ID, 'encuesta', true );
    }
}
add_action( 'manage_shop_order_posts_custom_column' ,'campos_extras_informacion');

//Mostrando los campos personalizados en la zona de pedidos
function campos_extras_info_pedidos($pedidos){
    $factura = get_post_meta( $pedidos->ID, 'factura', true );
    if($factura){
        echo '<p><strong>' . __('Factura','woocommerce') . ':</strong>Si</p>';
        echo '<p><strong>' . __('RFC','woocommerce') . ':</strong>'. get_post_meta( $pedidos->id , 'rfc' , true) .'</p>';
    }
    echo '<p><strong>' . __('Resultados Encuesta: ','woocommerce') . ':</strong>'. get_post_meta( $pedidos->id , 'encuesta' , true) .'</p>';
}
add_action('woocommerce_admin_order_data_after_billing_address','campos_extras_info_pedidos');
