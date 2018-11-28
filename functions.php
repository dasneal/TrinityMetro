
function show_alert_govdelivery_checkbox()
{
    $post_id = get_the_ID();
    $id      = $name . '_id';
    $value   = esc_attr( get_post_meta( $post_id, 'sendalert', TRUE ) );
    $checked = checked( $value, 1, FALSE );
    $label   = 'Send post to govdelivery?';
    $nonce   = wp_nonce_field( '_sendalert', '_sendalert_nonce', TRUE, FALSE );
       print <<<EOD
<div class="misc-pub-section">
    <label for="$id">
        <input type="checkbox" $checked id="$id" name="$name" value="1" />
        $nonce
        $label
    </label>
</div>
EOD;
}

function send_alert_govdelivery($post_id, $post) {
    if ( wp_is_post_autosave( $post ) )
        return;

    // if ( ! current_user_can( 'edit_post', $post_id ) )
    //     return;

    // if ( ! isset ( $_POST[ '_noindex_nonce' ] ) )
    //     return;

    // if ( ! wp_verify_nonce( $_POST[ '_noindex_nonce' ], '_noindex' ) )
    //     return;

    // if ( ! isset ( $_POST[ 'noindex' ] ) )
    //     return delete_post_meta( $post_id, '_noindex' );

    // if ( 1 != $_POST[ 'noindex' ] )
    //     return;

    $base_url = 'https://stage-api.govdelivery.com/';
    $account_code = 'TXTRINITY';
    $endpoint = 'api/account/' . $account_code . '/bulletins.xml';
    $username = 'alex@sigmategy.com';
    $password = 'zk1s5!RrO$nJFNwilGZ&yW';

    $url = $base_url . $endpoint;


    switch (get_post_type($post)) {
    	case 'wpdmpro':

    		$topics = wp_get_post_terms($post->ID,'route_tags'); // get assigned route codes
		    $categories = wp_get_post_terms($post->ID,'govdelivery_categories'); // get assigned categories


		    if ($topics) {

		    	$topics_list = array();

		    	foreach ($topics as $route_code) {
			    	array_push($topics_list, get_field('route_code','route_tags_' . $route_code->term_id) );
			    }
		    }
			    
		    if ($categories) {

		    	$categories_list = array();

		    	foreach ($categories as $category_code) {
		    		array_push($categories_list, get_field('category_code', 'govdelivery_categories_' . $category_code->term_id) );
		    	}
		    }

    		$subject = $post->post_title . ' - ' . $post->post_date;

    		$message .= 'New content has been posted here: <a href="https://ridetrinitymetro.org/business-center/procurement/current-procurement-opportunities/">Click</a>';

    		$body .= '<bulletin>';
		    $body .= '<subject>' . $subject . '</subject>';
		    $body .= '<body><![CDATA[' . $message . ']]></body>';

		    if ( $topics ) {
		    	$body .= "<topics type='array'>";
		    	foreach ( $topics_list as $topic_code )
		    		$body .= "<topic><code>" . $topic_code . "</code></topic>";
		    	$body .= "</topics>";
		    }

		    if ( $categories ) {
		    	$body .= "<categories type='array'>";
		    	foreach ( $categories_list as $category_code )
		    		$body .= "<category><code>" . $category_code . "</code></category>";
		    	$body .= "</categories>";
 		    }

		    $body .= '</bulletin>';

    		$response = wp_remote_post( $url, array(
		        'method' => 'POST',
		        'timeout' => 45,
		        'redirection' => 5,
		        'httpversion' => '1.1',
		        'blocking' => true,
		        'sslverify' => true,
		        'headers' => array( 
		        	'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password ),
		        	'Content-type'	=> 'text/xml'),
		        'body' => $body,
		        'cookies' => array()
		        )
		    );

		    break;

    	case '_alerts_detours':

		    $topics = wp_get_post_terms($post->ID,'route_tags'); // get assigned route codes
		    $categories = wp_get_post_terms($post->ID,'govdelivery_categories'); // get assigned categories


		    if ($topics) {

		    	$topics_list = array();

		    	foreach ($topics as $route_code) {
			    	array_push($topics_list, get_field('route_code','route_tags_' . $route_code->term_id) );
			    }
		    }
			    
		    if ($categories) {

		    	$categories_list = array();

		    	foreach ($categories as $category_code) {
		    		array_push($categories_list, get_field('category_code', 'govdelivery_categories_' . $category_code->term_id) );
		    	}
		    }

		    $subject = $post->post_title; // Get Title of the Alert

    		$alert_reason = get_field('reason');
    		$alert_start_date = get_field('updated');
		    $alert_duration = get_field('duration');
		    $alert_completion_date = get_field('completion_date');

		    $message .= 'Reason: ' . $alert_reason . '<br>';
		    $message .= 'Start date: ' . $alert_start_date . '<br>';
		    $message .= 'Duration: ' . $alert_duration . '<br>';
		    $message .= 'Completion date: ' . $alert_completion_date . '<br>';

		    $body .= '<bulletin>';
		    $body .= '<subject>' . $subject . '</subject>';
		    $body .= '<body><![CDATA[' . $message . ']]></body>';

		    if ( $topics ) {
		    	$body .= "<topics type='array'>";
		    	foreach ( $topics_list as $topic_code )
		    		$body .= "<topic><code>" . $topic_code . "</code></topic>";
		    	$body .= "</topics>";
		    }

		    if ( $categories ) {
		    	$body .= "<categories type='array'>";
		    	foreach ( $categories_list as $category_code )
		    		$body .= "<category><code>" . $category_code . "</code></category>";
		    	$body .= "</categories>";
 		    }

		    $body .= '</bulletin>';


		    $response = wp_remote_post( $url, array(
		        'method' => 'POST',
		        'timeout' => 45,
		        'redirection' => 5,
		        'httpversion' => '1.1',
		        'blocking' => true,
		        'sslverify' => true,
		        'headers' => array( 
		        	'Authorization' => 'Basic ' . base64_encode( $username . ':' . $password ),
		    		'Content-type'	=> 'text/xml'),
		        'body' => $body,
		        'cookies' => array()
		        )
		    );

		    break;
    }   


}

add_action('save_post', 'send_alert_govdelivery', 100, 2 );
add_action('post_submitbox_misc_actions', 'show_alert_govdelivery_checkbox'); 

