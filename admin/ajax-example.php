<?php
  add_action( 'admin_ajax_notices', 'my_ajax_example_action_button' );

  function my_ajax_example_action_button() {
    $id   = 54321;
    $data = array(
        'data-nonce' => wp_create_nonce( MY_AJAX_EXAMPLE_ACTION_NONCE . $id ),
        'data-id'    => $id,
    );
    echo get_submit_button( "Ajax Primary", 'primary large', 'my-ajax-example-action-button-1', FALSE, $data );

    $id   += 12345;
    $data = array(
        'data-nonce' => wp_create_nonce( MY_AJAX_EXAMPLE_ACTION_NONCE . $id ),
        'data-id'    => $id,
    );
    echo get_submit_button( "Ajax Secundary", 'secondary', 'my-ajax-example-action-button-2', FALSE, $data );
  }
  my_ajax_example_action_button();


  add_action( 'admin_footer', 'my_ajax_example_action_javascript' ); // Write our JS below here

  function my_ajax_example_action_javascript() {
      ?>
      <script type="text/javascript">
          jQuery(document).ready(function ($) {
              $('#my-ajax-example-action-button-1,#my-ajax-example-action-button-2').click(function () {
                  var $button = $(this);
  
                  var data = {
                      'action': 'my_ajax_example_action',
                      'id': $button.data('id'),
                      'nonce': $button.data('nonce')
                  };
                  // Give user cue not to click again
                  $button.addClass('disabled');
                  // Invalidate the nonce
                  $button.data('nonce', 'invalid');
  
                  $.post(ajaxurl, data, function (response) {
                      alert('Got this from the server: ' + response);
  
                  });
              });
          });
      </script>
      <?php
  }